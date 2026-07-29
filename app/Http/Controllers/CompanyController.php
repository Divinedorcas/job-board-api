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

    //Create Company

    public function index(Request $request)
    {
        $query = Company::with('jobs');

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
            'companies' => $companies->items(), // backwards compatibility
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

    public function createCompany(CreateCompanyRequest $request)
    {
       $result = $this->companyService->createCompany($request);
 if (!$result['success']) {
        return response()->json([
            'message' => $result['message'],
        ], );
    }
    return response()->json([
        'message' => $result['message'],
        'company' => $result['company'],
    ], );
}

    //View Company
    public function show( $id)
    {
        $company = Company::with('jobs')->findOrFail($id);
        return response()->json([
            'company' => $company,
        ], );
    }

    //Update Company
    public function update(Request $request, $id)
    {
        $upcompany = Company::find($id);
            if(!$upcompany){

            return "Company Not Found";
        }
    }

    //Delete Company
    public function destroy( $id)
    {
        $decompany = Company::find($id);
        if(!$decompany){
            return "Company Not Found";
        }
        $decompany->delete();
    }


    //Company Profile
}
