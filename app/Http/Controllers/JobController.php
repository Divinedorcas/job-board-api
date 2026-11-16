<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobListings;
use App\Http\Requests\CreateJobRequest;
use App\Services\JobService;
use Exception;

class JobController extends Controller
{
    public function __construct(
        protected JobService $jobService
    ) {}

    /**
     * Get all jobs with search, filters, and pagination.
     */
    public function index(Request $request)
    {
        try {
            $query = JobListings::with('company');

            // Search across title, description, category, location, work_mode, job_type, and company name
            if ($request->filled('search')) {
                $searchTerm = trim($request->input('search'));
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%")
                      ->orWhere('location', 'like', "%{$searchTerm}%")
                      ->orWhere('work_mode', 'like', "%{$searchTerm}%")
                      ->orWhere('job_type', 'like', "%{$searchTerm}%")
                      ->orWhere('experience_level', 'like', "%{$searchTerm}%")
                      ->orWhereHas('company', function ($companyQuery) use ($searchTerm) {
                          $companyQuery->where('name', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Location filter
            if ($request->filled('location')) {
                $location = trim($request->input('location'));
                $query->where(function ($q) use ($location) {
                    $q->where('location', 'like', "%{$location}%")
                      ->orWhere('work_mode', 'like', "%{$location}%");
                });
            }

            // Category filter
            if ($request->filled('category') && strtolower($request->input('category')) !== 'all') {
                $query->where('category', $request->input('category'));
            }

            // Work mode filter
            if ($request->filled('work_mode') && strtolower($request->input('work_mode')) !== 'all') {
                $query->where('work_mode', $request->input('work_mode'));
            }

            // Job type filter
            if ($request->filled('job_type') && strtolower($request->input('job_type')) !== 'all') {
                $query->where('job_type', $request->input('job_type'));
            }

            // Experience level filter
            if ($request->filled('experience_level') && strtolower($request->input('experience_level')) !== 'all') {
                $query->where('experience_level', $request->input('experience_level'));
            }

            // Sorting
            $sortBy = $request->input('sort', 'latest');
            switch ($sortBy) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'title':
                    $query->orderBy('title', 'asc');
                    break;
                case 'latest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $perPage = (int) $request->input('per_page', 10);
            if ($perPage <= 0 || $perPage > 100) {
                $perPage = 10;
            }

            $jobs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $jobs->items(),
                'jobs' => $jobs->items(),
                'pagination' => [
                    'current_page' => $jobs->currentPage(),
                    'last_page' => $jobs->lastPage(),
                    'per_page' => $jobs->perPage(),
                    'total' => $jobs->total(),
                    'from' => $jobs->firstItem() ?? 0,
                    'to' => $jobs->lastItem() ?? 0,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch job listings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new job listing (Employer only).
     */
    public function createJob(CreateJobRequest $request)
    {
        try {
            $job = $this->jobService->createJob($request);

            return response()->json([
                'success' => true,
                'message' => 'Job listing created successfully!',
                'job' => $job,
                'data' => $job,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create job listing: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show one job listing by ID with company relationship.
     */
    public function showOneJob($id)
    {
        $oneJob = JobListings::with('company')->find($id);

        if (!$oneJob) {
            return response()->json([
                'success' => false,
                'message' => 'Job listing not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Job details retrieved successfully.',
            'job' => $oneJob,
            'data' => $oneJob
        ], 200);
    }

    /**
     * Update an existing job listing (Employer owner or Admin only).
     */
    public function updateJob($id, Request $request)
    {
        $job = JobListings::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job listing not found.'
            ], 404);
        }

        $user = $request->user();
        if ($user->role !== 'admin' && $job->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update job listings created by your account.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'work_mode' => 'sometimes|in:remote,on-site,hybrid',
            'job_type' => 'sometimes|in:full-time,part-time,contract,internship',
            'experience_level' => 'sometimes|in:entry,mid,senior',
            'location' => 'sometimes|string|max:255',
            'salary' => 'nullable|string|max:255',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'benefits' => 'nullable|string',
            'application_deadline' => 'nullable|date',
            'status' => 'sometimes|in:open,closed',
        ]);

        $job->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job listing updated successfully.',
            'job' => $job->load('company'),
            'data' => $job->load('company'),
        ], 200);
    }

    /**
     * Delete a job listing (Employer owner or Admin only).
     */
    public function deleteJob($id, Request $request)
    {
        $job = JobListings::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job listing not found.'
            ], 404);
        }

        $user = $request->user();
        if ($user->role !== 'admin' && $job->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only delete job listings created by your account.'
            ], 403);
        }

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job listing deleted successfully.'
        ], 200);
    }
}
