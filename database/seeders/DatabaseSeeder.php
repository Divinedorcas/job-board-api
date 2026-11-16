<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\JobListings;
use App\Models\Application;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Employer User
        $employer = User::updateOrCreate(
            ['email' => 'employer@jobflow.com'],
            [
                'name' => 'Sarah Connor',
                'password' => Hash::make('password123'),
                'role' => 'employer',
            ]
        );

        // 2. Create Candidate User
        $candidate = User::updateOrCreate(
            ['email' => 'candidate@jobflow.com'],
            [
                'name' => 'Alex Rivera',
                'password' => Hash::make('password123'),
                'role' => 'job_seeker',
            ]
        );

        // 3. Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@jobflow.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 4. Create Company owned by Employer
        $company = Company::updateOrCreate(
            ['email' => 'contact@stripe.com'],
            [
                'owner_id' => $employer->id,
                'name' => 'Stripe',
                'description' => 'Financial infrastructure for the internet. Millions of companies of all sizes use Stripe to accept payments and manage their businesses online.',
                'website' => 'https://stripe.com',
                'logo' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=128&h=128&fit=crop',
                'cover' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1600&h=400&fit=crop',
                'location' => 'San Francisco, CA (Remote)',
                'industry' => 'Fintech / Payments',
                'size' => '1,000 - 5,000 employees',
                'about' => 'Stripe builds economic infrastructure for the internet. Businesses of every size use our software to accept payments and manage their businesses online.',
                'founded' => '2010',
            ]
        );

        // Associate company with employer user
        $employer->company_id = $company->id;
        $employer->save();

        // 5. Create Sample Job Listings
        $job1 = JobListings::updateOrCreate(
            [
                'title' => 'Senior Frontend Engineer (React/Tailwind)',
                'company_id' => $company->id,
            ],
            [
                'created_by' => $employer->id,
                'category' => 'Engineering',
                'description' => 'We are seeking an exceptional Senior Frontend Engineer to lead UI development for our next-generation financial checkout suite. You will work closely with product designers and backend engineers to craft silky smooth, accessible, and high-performance interfaces.',
                'location' => 'San Francisco, CA',
                'work_mode' => 'remote',
                'job_type' => 'full-time',
                'experience_level' => 'senior',
                'salary' => '$145,000 - $185,000',
                'requirements' => "• 5+ years building production web apps with React, TypeScript, and modern CSS frameworks\n• Deep understanding of DOM performance, accessibility standards (WCAG AA), and browser internals\n• Proven track record designing reusable design systems and component libraries\n• Experience with state management, client caching, and API integration",
                'responsibilities' => "• Architect scalable and reusable UI components matching Figma design specs\n• Collaborate across engineering and design teams to build accessible payment interfaces\n• Optimize client bundle size, rendering performance, and Core Web Vitals\n• Mentor mid and junior engineers through empathetic code reviews",
                'benefits' => "• Competitive base salary and equity grant\n• 100% covered health, dental, and vision insurance\n• 401(k) retirement matching up to 5%\n• \$2,500 annual home office setup and learning stipend\n• Unlimited paid time off (PTO) with minimum recommendation",
                'application_deadline' => now()->addDays(30),
                'status' => 'open',
            ]
        );

        $job2 = JobListings::updateOrCreate(
            [
                'title' => 'Staff Backend Engineer (API & Distributed Systems)',
                'company_id' => $company->id,
            ],
            [
                'created_by' => $employer->id,
                'category' => 'Engineering',
                'description' => 'Join our Core Ledger team responsible for processing billions of dollars in daily transactions with zero downtime and strict ACID guarantees.',
                'location' => 'New York, NY',
                'work_mode' => 'hybrid',
                'job_type' => 'full-time',
                'experience_level' => 'senior',
                'salary' => '$170,000 - $220,000',
                'requirements' => "• 7+ years backend development experience in high-throughput distributed systems\n• Expertise in PostgreSQL, Redis, Kafka, and idempotent API architecture\n• High bar for reliability, unit/integration testing, and automated observability",
                'responsibilities' => "• Design fault-tolerant payment APIs capable of processing 10k+ requests per second\n• Modernize database schema and caching layers to scale horizontally\n• Participate in blameless on-call incident retrospectives",
                'benefits' => "• Comprehensive health and wellness coverage\n• 401(k) matching\n• Generous parental leave\n• Hybrid flexibility with commute subsidy",
                'application_deadline' => now()->addDays(45),
                'status' => 'open',
            ]
        );

        $job3 = JobListings::updateOrCreate(
            [
                'title' => 'Product Designer (Design Systems)',
                'company_id' => $company->id,
            ],
            [
                'created_by' => $employer->id,
                'category' => 'Design',
                'description' => 'Help define the future visual identity and interaction paradigms of our design system used by hundreds of engineers and millions of end users worldwide.',
                'location' => 'Remote',
                'work_mode' => 'remote',
                'job_type' => 'full-time',
                'experience_level' => 'mid',
                'salary' => '$120,000 - $155,000',
                'requirements' => "• 3+ years product design experience with dedicated focus on design tokens and component systems in Figma\n• Strong grasp of responsive design, micro-interactions, and accessibility standards\n• Solid portfolio showcasing end-to-end product design processes",
                'responsibilities' => "• Maintain and expand the JobFlow Figma UI Kit and component tokens\n• Partner with frontend engineers to ensure 1:1 parity between design and code\n• Conduct user usability tests and translate feedback into intuitive designs",
                'benefits' => "• Flexible hours and remote-first setup\n• New MacBook Pro and top-tier monitor stipend\n• Generous annual education fund",
                'application_deadline' => now()->addDays(20),
                'status' => 'open',
            ]
        );

        // 6. Create a Sample Application for Candidate
        Application::updateOrCreate(
            [
                'user_id' => $candidate->id,
                'job_id' => $job1->id,
            ],
            [
                'resume' => 'uploads/resumes/alex_rivera_cv.pdf',
                'cover_letter' => "Hello Stripe team,\n\nI am thrilled to apply for the Senior Frontend Engineer role. With over 6 years of experience building modern React and Tailwind web applications, I have a deep passion for craft, fluid UI interactions, and robust state architectures. I look forward to contributing to your team!\n\nBest regards,\nAlex Rivera",
                'status' => 'interview',
            ]
        );
    }
}
