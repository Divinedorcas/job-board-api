<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\JobListings;
use App\Http\Requests\CreateJobRequest;

class JobService
{
    // get all jobs
    public function index()
    {
        $allJobs = JobListings::with('company')->latest()->get();

        return response()->json([
            'message' => 'All Jobs',
            'jobs' => $allJobs
        ], 200);
    }

    // create a new job
    public function createJob(CreateJobRequest $request)
    {
        $user = $request->user();

        $job = JobListings::create([
            ...$request->validated(),
            'company_id' => $user->company_id,
            'created_by' => $user->id,
        ]);

        return $job;
    }

    // show one job
    public function showOneJob($id)
    {
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

    // update a job
    public function updateJob($id, CreateJobRequest $request)
    {
        $updateJob = JobListings::find($id);

        if (!$updateJob) {
            return response()->json([
                'message' => 'Job not found'
            ], 404);
        }

        if ($updateJob->company_id != $request->user()->company_id) {
            return response()->json([
                'message' => 'Unauthorized to update this job listing.'
            ], 403);
        }

        $validatedData = $request->validated();
        $updateJob->update($validatedData);

        return response()->json([
            'message' => 'Job updated successfully',
            'job' => $updateJob
        ], 200);
    }

    // delete a job
    public function deleteJob($id)
    {
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
