<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobListings;
use App\Http\Requests\CreateJobRequest;
use App\Services\JobService;


class JobController extends Controller
{
public function __construct(
    protected JobService $jobService
){}

    //get all jobs
    public function index(){
        // Eager-load the related company so the frontend receives company
        // name and logo alongside each job without a separate API call.
        $allJobs = JobListings::with('company')->get();

        return response()->json([
            'message' => 'All Jobs',
            'jobs' => $allJobs
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
