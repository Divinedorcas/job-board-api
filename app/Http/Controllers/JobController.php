<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobListings;
use App\Http\Requests\CreateJobRequest;
use App\Services\JobService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;


class JobController extends Controller
{
public function __construct(
    protected JobService $jobService
){}

    // get all jobs with search, filters, and pagination
    public function index(Request $request){
        $query = JobListings::with('company');

        // Search across title, description, category, location, work_mode, job_type, skills, and company name
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
                 // ->orWhere('skills', 'like', "%{$searchTerm}%")
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
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Work Mode filter (Remote, Hybrid, On-site)
        if ($request->filled('work_mode')) {
            $query->where('work_mode', 'like', "%" . trim($request->input('work_mode')) . "%");
        }

        // Job Type filter (Full-time, Part-time, Contract)
        if ($request->filled('job_type')) {
            $query->where('job_type', 'like', "%" . trim($request->input('job_type')) . "%");
        }

        // Experience Level filter (Junior, Mid, Senior)
        if ($request->filled('experience_level')) {
            $query->where('experience_level', 'like', "%" . trim($request->input('experience_level')) . "%");
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'latest');
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
        if ($perPage <= 0) {
            $perPage = 10;
        }

        $jobs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'jobs' => $jobs->items(), // backwards compatibility
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'from' => $jobs->firstItem() ?? 0,
                'to' => $jobs->lastItem() ?? 0,
            ]
        ], 200);
    }



    
    //create a new job
    public function createJob(CreateJobRequest $request)
{
    
    $job = $this->jobService->createJob($request);

    return response()->json([
        'message' => 'Job created successfully',
        'job' => $job
    ], 201);
}
    //show one job
    public function showOneJob($id){

        $oneJob = JobListings::with('company')->find($id);

        if (!$oneJob) {
            return response()->json([
                'message' => 'Job not found'
            ], 404);
        }
        return response()->json([
            'message' => 'Job details',
            'job' => $oneJob
        ], 200);
    }

    //update a job
    public function updateJob($id, CreateJobRequest $request){
        $updateJob = JobListings::find($id);

        if (!$updateJob) {
            return response()->json([
                'message' => 'Job not found'
            ], 404);
        }

        $validatedData = $request->validated();
        $updateJob->update($validatedData);

        return response()->json([
            'message' => 'Job updated successfully',
            'job' => $updateJob
        ], 200);
    }

    //delete a job
    public function deleteJob($id){
        $deleteJob = JobListings::find($id);

        if (!$deleteJob) {
            return response()->json([
                'message' => 'Job not found'
            ], 404);
        }

        $deleteJob->delete();

        return response()->json([
            'message' => 'Job deleted successfully'
        ], 200);
    }


    
}
