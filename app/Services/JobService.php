<?php

namespace App\Services;

use App\Models\JobListings;
use App\Models\Company;
use App\Http\Requests\CreateJobRequest;

class JobService
{
    /**
     * Get all jobs with company relationship.
     */
    public function index()
    {
        $allJobs = JobListings::with('company')->latest()->get();

        return response()->json([
            'message' => 'All Jobs',
            'jobs' => $allJobs,
            'data' => $allJobs,
        ], 200);
    }

    /**
     * Create a new job listing (Employer only).
     */
    public function createJob(CreateJobRequest $request)
    {
        $user = $request->user();

        // 1. Resolve company_id: check user's assigned company
        $companyId = $user->company_id;

        // 2. Check if user owns an existing company record
        if (!$companyId) {
            $existingCompany = Company::where('owner_id', $user->id)->first();
            if ($existingCompany) {
                $companyId = $existingCompany->id;
                $user->company_id = $companyId;
                $user->save();
            }
        }

        // 3. Check if company_id was explicitly provided in request
        if (!$companyId && $request->filled('company_id')) {
            $companyId = $request->input('company_id');
        }

        // 4. If employer still doesn't have a company, auto-create one
        if (!$companyId) {
            $companyName = $request->input('company_name') ?: ($user->name . ' Co.');
            $companyEmail = $request->input('company_email') ?: ('employer_' . $user->id . '_' . time() . '@jobflow.local');

            $company = Company::create([
                'owner_id' => $user->id,
                'name' => $companyName,
                'email' => $companyEmail,
                'description' => $request->input('company_description', 'Organization managed by ' . $user->name),
                'location' => $request->input('location', 'Remote'),
                'website' => $request->input('company_website', null),
                'logo' => $request->input('company_logo', 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=128&h=128&fit=crop'),
                'industry' => $request->input('category', 'Technology'),
            ]);

            $companyId = $company->id;
            $user->company_id = $companyId;
            $user->save();
        }

        $validated = $request->validated();

        // Extract only joblistings table columns
        $jobData = [
            'company_id' => $companyId,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'work_mode' => $validated['work_mode'],
            'job_type' => $validated['job_type'],
            'experience_level' => $validated['experience_level'],
            'salary' => $validated['salary'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'responsibilities' => $validated['responsibilities'] ?? null,
            'benefits' => $validated['benefits'] ?? null,
            'application_deadline' => $validated['application_deadline'] ?? null,
            'status' => $validated['status'] ?? 'open',
        ];

        $job = JobListings::create($jobData);

        return $job->load('company');
    }

    /**
     * Show one job listing by ID with company.
     */
    public function showOneJob($id)
    {
        $oneJob = JobListings::with('company')->find($id);

        if (!$oneJob) {
            return null;
        }

        return $oneJob;
    }

    /**
     * Update job listing by ID.
     */
    public function updateJob($id, array $data)
    {
        $job = JobListings::find($id);

        if (!$job) {
            return null;
        }

        $job->update($data);

        return $job->load('company');
    }
}
