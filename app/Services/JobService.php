<?php

namespace App\Services;

use App\Models\JobListings;
use App\Models\Company;
use App\Http\Requests\CreateJobRequest;
use Exception;
use Illuminate\Support\Str;

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
     * An employer can own multiple companies and selects which company to post for.
     */
    public function createJob(CreateJobRequest $request)
    {
        $user = $request->user();
        $companyId = null;

        // 1. If employer selected a specific company, verify they own it
        if ($request->filled('company_id')) {
            $candidateId = (int) $request->input('company_id');
            $ownedCompany = Company::where('id', $candidateId)
                ->where(function ($q) use ($user) {
                    $q->where('owner_id', $user->id);
                    if ($user->role === 'admin') {
                        $q->orWhereRaw('1=1');
                    }
                })
                ->first();

            if (!$ownedCompany) {
                throw new Exception('Unauthorized. You can only post job listings for companies you own.');
            }
            $companyId = $ownedCompany->id;
        }

        // 2. If no company_id provided, but employer provided company details to create one on the fly
        if (!$companyId && $request->filled('company_name')) {
            $companyName = trim($request->input('company_name'));
            
            // Check if employer already has a company with this exact name
            $existing = Company::where('owner_id', $user->id)->where('name', $companyName)->first();
            if ($existing) {
                $companyId = $existing->id;
            } else {
                $baseSlug = Str::slug($companyName) ?: 'company';
                $companyEmail = $request->input('company_email');
                if (empty($companyEmail) || Company::where('email', $companyEmail)->exists()) {
                    $companyEmail = 'contact_' . time() . '_' . rand(100, 999) . '@' . $baseSlug . '.com';
                }

                $newCompany = Company::create([
                    'owner_id' => $user->id,
                    'name' => $companyName,
                    'email' => $companyEmail,
                    'description' => $request->input('company_description', 'Organization managed by ' . $user->name),
                    'location' => $request->input('location', 'Remote'),
                    'website' => $request->input('company_website', null),
                    'logo' => $request->input('company_logo', 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=128&h=128&fit=crop'),
                    'industry' => $request->input('category', 'Technology'),
                ]);
                $companyId = $newCompany->id;
            }
        }

        // 3. If still no company_id, default to the employer's active or latest owned company
        if (!$companyId) {
            $latestOwnedCompany = Company::where('owner_id', $user->id)->latest()->first();
            if ($latestOwnedCompany) {
                $companyId = $latestOwnedCompany->id;
            }
        }

        // 4. If employer has zero companies, auto-create a default company for them
        if (!$companyId) {
            $newCompany = Company::create([
                'owner_id' => $user->id,
                'name' => $user->name . ' Co.',
                'email' => 'employer_' . $user->id . '_' . time() . '@jobflow.local',
                'description' => 'Organization managed by ' . $user->name,
                'location' => $request->input('location', 'Remote'),
                'website' => null,
                'logo' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=128&h=128&fit=crop',
                'industry' => $request->input('category', 'Technology'),
            ]);
            $companyId = $newCompany->id;
        }

        // Keep active company_id synced
        if ($user->company_id !== $companyId) {
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
