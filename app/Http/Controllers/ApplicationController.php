<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobListings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    /**
     * Candidate: Apply for a job listing.
     * Protected by auth:sanctum & candidate/job_seeker middleware.
     */
    public function apply(Request $request, $jobId)
    {
        $user = $request->user();

        $job = JobListings::with('company')->find($jobId);
        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job listing not found.'
            ], 404);
        }

        if ($job->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'This job listing is currently closed to new applications.'
            ], 400);
        }

        // Prevent duplicate applications
        $existing = Application::where('user_id', $user->id)
            ->where('job_id', $job->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this job listing.'
            ], 409);
        }

        $validated = $request->validate([
            'cover_letter' => 'nullable|string',
            'resume' => 'nullable',
        ]);

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
        } elseif ($request->filled('resume')) {
            $resumePath = $request->input('resume');
        }

        $application = Application::create([
            'user_id' => $user->id,
            'job_id' => $job->id,
            'resume' => $resumePath,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'status' => 'applied',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully!',
            'application' => $application->load('job.company'),
        ], 201);
    }

    /**
     * Candidate: Get candidate's applications and dashboard metrics.
     * Protected by auth:sanctum & candidate/job_seeker middleware.
     */
    public function candidateDashboard(Request $request)
    {
        $user = $request->user();

        $applications = Application::with(['job.company'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $stats = [
            'total_applications' => $applications->count(),
            'applied' => $applications->where('status', 'applied')->count(),
            'reviewed' => $applications->where('status', 'reviewed')->count(),
            'shortlisted' => $applications->where('status', 'shortlisted')->count(),
            'interviews' => $applications->where('status', 'interview')->count(),
            'hired' => $applications->where('status', 'hired')->count(),
            'rejected' => $applications->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'candidate' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'stats' => $stats,
            'applications' => $applications,
        ], 200);
    }

    /**
     * Employer: Get employer dashboard overview, stats, posted jobs, and applicant pipeline.
     * Protected by auth:sanctum & employer middleware.
     */
    public function employerDashboard(Request $request)
    {
        $user = $request->user();

        // Jobs belonging to employer's company or created by this employer
        $jobsQuery = JobListings::query();
        if ($user->company_id) {
            $jobsQuery->where('company_id', $user->company_id);
        } else {
            $jobsQuery->where('created_by', $user->id);
        }

        $jobs = $jobsQuery->withCount('applications')->latest()->get();
        $jobIds = $jobs->pluck('id');

        $applications = Application::with(['user', 'job.company'])
            ->whereIn('job_id', $jobIds)
            ->latest()
            ->get();

        $stats = [
            'total_jobs' => $jobs->count(),
            'active_jobs' => $jobs->where('status', 'open')->count(),
            'closed_jobs' => $jobs->where('status', 'closed')->count(),
            'total_applications' => $applications->count(),
            'hired_candidates' => $applications->where('status', 'hired')->count(),
        ];

        return response()->json([
            'success' => true,
            'employer' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_id' => $user->company_id,
            ],
            'stats' => $stats,
            'jobs' => $jobs,
            'applications' => $applications,
        ], 200);
    }

    /**
     * Employer: Update candidate application status (e.g. reviewed, shortlisted, interview, hired, rejected).
     * Protected by auth:sanctum & employer middleware.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();

        $application = Application::with('job')->find($id);
        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found.'
            ], 404);
        }

        // Verify that this employer owns the job listing
        if ($user->company_id && $application->job->company_id !== $user->company_id && $application->job->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update applications for your own company jobs.'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:applied,reviewed,shortlisted,interview,rejected,hired',
        ]);

        $application->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Application status updated to {$validated['status']}.",
            'application' => $application,
        ], 200);
    }
}
