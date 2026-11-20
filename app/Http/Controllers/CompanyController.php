<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Http\Requests\CreateCompanyRequest;
use App\Services\CompanyService;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyService $companyService
    ) {}

    /**
     * Get all companies with search, filters, and pagination.
     */
    public function index(Request $request)
    {
        $query = Company::withCount('jobs');

        // Search by company name, industry, location, description, or about
        if ($request->filled('search')) {
            $searchTerm = trim($request->input('search'));
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('industry', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('about', 'like', "%{$searchTerm}%");
            });
        }

        // Industry filter
        if ($request->filled('industry')) {
            $query->where('industry', 'like', "%" . trim($request->input('industry')) . "%");
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('location', 'like', "%" . trim($request->input('location')) . "%");
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'latest');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = (int) $request->input('per_page', 12);
        if ($perPage <= 0) {
            $perPage = 12;
        }

        $companies = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $companies->items(),
            'companies' => $companies->items(),
            'pagination' => [
                'current_page' => $companies->currentPage(),
                'last_page' => $companies->lastPage(),
                'per_page' => $companies->perPage(),
                'total' => $companies->total(),
                'from' => $companies->firstItem() ?? 0,
                'to' => $companies->lastItem() ?? 0,
            ]
        ], 200);
    }

    /**
     * Get the logged-in employer's active/default company profile.
     */
    public function myCompany(Request $request)
    {
        $user = $request->user();
        $company = null;

        if ($user->company_id) {
            $company = Company::with('jobs')->find($user->company_id);
        }

        if (!$company) {
            $company = Company::with('jobs')->where('owner_id', $user->id)->latest()->first();
        }

        $allCompanies = Company::where('owner_id', $user->id)
            ->withCount('jobs')
            ->latest()
            ->get();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No company found for this employer.',
                'company' => null,
                'companies' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'company' => $company,
            'companies' => $allCompanies,
        ], 200);
    }

    /**
     * Get all companies owned by the logged-in employer.
     * An employer can own multiple companies.
     */
    public function myCompanies(Request $request)
    {
        $user = $request->user();
        $companies = Company::where('owner_id', $user->id)
            ->withCount('jobs')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'companies' => $companies,
            'data' => $companies,
        ], 200);
    }

    /**
     * Create a new company (Employer only).
     */
    public function createCompany(CreateCompanyRequest $request)
    {
        $result = $this->companyService->createCompany($request);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'company' => $result['company'],
            'data' => $result['company'],
        ], 201);
    }

    /**
     * View Company Details with Jobs.
     */
    public function show($id)
    {
        $company = Company::with(['jobs' => function ($q) {
            $q->where('status', 'open')->latest();
        }, 'owner'])->find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'company' => $company,
            'data' => $company,
        ], 200);
    }

    /**
     * Update Company Profile (Owner or Admin only).
     */
    public function update(Request $request, $id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.',
            ], 404);
        }

        $user = $request->user();
        if ($user->role !== 'admin' && $company->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update your own company profile.',
            ], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:companies,email,' . $id,
            'website' => 'nullable|string',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string',
            'logo' => 'nullable|string',
            'cover' => 'nullable|string',
            'industry' => 'nullable|string',
            'size' => 'nullable|string',
            'about' => 'nullable|string',
            'founded' => 'nullable|string',
        ]);

        $company->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully.',
            'company' => $company,
            'data' => $company,
        ], 200);
    }

    /**
     * Delete Company (Owner or Admin only).
     */
    public function destroy(Request $request, $id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.',
            ], 404);
        }

        $user = $request->user();
        if ($user->role !== 'admin' && $company->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only delete your own company.',
            ], 403);
        }

        // Unset company_id on user
        if ($user->company_id === $company->id) {
            $user->company_id = null;
            $user->save();
        }

        $company->delete();

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully.',
        ], 200);
    }
}
