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
     * Seed the application's database with demo and registered users, companies, and jobs.
     */
    public function run(): void
    {
        // 1. Create or Update Wisdom (Existing Employer)
        $wisdomEmployer = User::updateOrCreate(
            ['email' => 'wis@gmail.com'],
            [
                'name' => 'Wisdom Ono',
                'password' => Hash::make('password123'),
                'role' => 'employer',
            ]
        );

        // 2. Create or Update Dorcas (Existing Candidate)
        $dorcasCandidate = User::updateOrCreate(
            ['email' => 'dorcas@gmail.com'],
            [
                'name' => 'Dorcas Oguche',
                'password' => Hash::make('password123'),
                'role' => 'job_seeker',
            ]
        );

        // 3. Create Demo Employer
        $demoEmployer = User::updateOrCreate(
            ['email' => 'employer@jobflow.com'],
            [
                'name' => 'Sarah Connor',
                'password' => Hash::make('password123'),
                'role' => 'employer',
            ]
        );

        // 4. Create Demo Candidate
        $demoCandidate = User::updateOrCreate(
            ['email' => 'candidate@jobflow.com'],
            [
                'name' => 'Alex Rivera',
                'password' => Hash::make('password123'),
                'role' => 'job_seeker',
            ]
        );

        // 5. Create Company for Wisdom (CloudScale Systems)
        $cloudScale = Company::updateOrCreate(
            ['email' => 'contact@cloudscale.io'],
            [
                'owner_id' => $wisdomEmployer->id,
                'name' => 'CloudScale Systems',
                'description' => 'Building the future of distributed cloud infrastructure and automated container orchestration for global enterprise workloads.',
                'website' => 'https://cloudscale.io',
                'logo' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=150&auto=format&fit=crop&q=80',
                'cover' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&auto=format&fit=crop&q=80',
                'location' => 'San Francisco, CA (Remote)',
                'industry' => 'Cloud Infrastructure / DevOps',
                'size' => '250 - 500 employees',
                'about' => 'CloudScale Systems provides high-performance managed infrastructure and automated deployments. We enable engineering teams worldwide to ship mission-critical software with resilience and velocity.',
                'founded' => '2019',
            ]
        );
        $wisdomEmployer->company_id = $cloudScale->id;
        $wisdomEmployer->save();

        // 6. Create Company for Demo Employer (Stripe)
        $stripe = Company::updateOrCreate(
            ['email' => 'contact@stripe.com'],
            [
                'owner_id' => $demoEmployer->id,
                'name' => 'Stripe',
                'description' => 'Financial infrastructure for the internet. Millions of companies of all sizes use Stripe software to accept payments and manage transactions online.',
                'website' => 'https://stripe.com',
                'logo' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=150&auto=format&fit=crop&q=80',
                'cover' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1600&h=400&fit=crop',
                'location' => 'San Francisco, CA (Hybrid)',
                'industry' => 'Fintech / Payments',
                'size' => '1,000 - 5,000 employees',
                'about' => 'Stripe powers online commerce and economic infrastructure across 190+ countries. Our team is passionate about APIs, developer experience, and reliable systems.',
                'founded' => '2010',
            ]
        );
        $demoEmployer->company_id = $stripe->id;
        $demoEmployer->save();

        // 7. Seed Jobs for CloudScale Systems (Wisdom)
        $job1 = JobListings::updateOrCreate(
            ['title' => 'Senior Frontend Engineer (React/Tailwind)', 'company_id' => $cloudScale->id],
            [
                'created_by' => $wisdomEmployer->id,
                'category' => 'Engineering',
                'description' => 'Lead frontend architecture for our cloud monitoring dashboards. You will build high-frequency real-time telemetry visualizations, reusable UI components, and intuitive developer workflows.',
                'location' => 'San Francisco, CA',
                'work_mode' => 'remote',
                'job_type' => 'full-time',
                'experience_level' => 'senior',
                'salary' => '$145,000 - $185,000',
                'requirements' => "• 5+ years building modern web applications with React, TypeScript, and Tailwind CSS\n• Experience with WebSocket/SSE live data feeds and canvas/SVG data charts\n• Solid grasp of frontend performance, caching strategies, and Core Web Vitals",
                'responsibilities' => "• Develop responsive dashboards matching Stitch Figma design guidelines\n• Collaborate with backend engineers to consume REST and streaming APIs\n• Maintain design tokens and component standards across the frontend codebase",
                'benefits' => "• Competitive base salary and equity options\n• 100% employer-covered health, vision, and dental\n• \$3,000 annual home office setup and conference budget\n• Unlimited PTO policy",
                'application_deadline' => now()->addDays(30),
                'status' => 'open',
            ]
        );

        $job2 = JobListings::updateOrCreate(
            ['title' => 'Staff Site Reliability Engineer (Kubernetes/Go)', 'company_id' => $cloudScale->id],
            [
                'created_by' => $wisdomEmployer->id,
                'category' => 'DevOps',
                'description' => 'Join our core platform reliability team managing multi-region Kubernetes clusters handling petabytes of telemetry and high-availability customer deployments.',
                'location' => 'New York, NY',
                'work_mode' => 'hybrid',
                'job_type' => 'full-time',
                'experience_level' => 'senior',
                'salary' => '$175,000 - $225,000',
                'requirements' => "• 6+ years hands-on experience in cloud infrastructure (AWS/GCP), Terraform, and Kubernetes\n• Proficiency in Go, Python, or shell automation for cluster tooling\n• Experience maintaining 99.99% availability SLAs and managing incident responses",
                'responsibilities' => "• Architect fault-tolerant infrastructure and automated disaster recovery failover\n• Scale observability pipelines using Prometheus, Grafana, and OpenTelemetry\n• Lead post-mortem retrospectives to drive proactive reliability improvements",
                'benefits' => "• Comprehensive medical, dental, and life coverage\n• 401(k) matching up to 5%\n• Generous parental leave and wellness subsidy",
                'application_deadline' => now()->addDays(45),
                'status' => 'open',
            ]
        );

        // 8. Seed Jobs for Stripe (Demo Employer)
        $job3 = JobListings::updateOrCreate(
            ['title' => 'Product Designer (Design Systems)', 'company_id' => $stripe->id],
            [
                'created_by' => $demoEmployer->id,
                'category' => 'Design',
                'description' => 'Shape the visual language and interaction patterns for checkout flows experienced by millions of shoppers daily. You will define design tokens and build out reusable design system libraries.',
                'location' => 'Remote',
                'work_mode' => 'remote',
                'job_type' => 'full-time',
                'experience_level' => 'mid',
                'salary' => '$125,000 - $160,000',
                'requirements' => "• 3+ years experience designing consumer or enterprise SaaS products in Figma\n• Strong portfolio demonstrating micro-interactions, accessibility (WCAG AA), and design systems\n• High aesthetic standards and empathy for developers implementing your designs",
                'responsibilities' => "• Maintain and evolve component libraries and documentation in Figma\n• Partner closely with React frontend engineers to guarantee pixel-perfect delivery\n• Conduct rapid user testing and translate insights into polished UI prototypes",
                'benefits' => "• Flexible remote-first work environment\n• Hardware budget (MacBook Pro + dual 4K monitors)\n• Annual professional development fund",
                'application_deadline' => now()->addDays(25),
                'status' => 'open',
            ]
        );

        $job4 = JobListings::updateOrCreate(
            ['title' => 'Backend API Engineer (Laravel/MySQL)', 'company_id' => $stripe->id],
            [
                'created_by' => $demoEmployer->id,
                'category' => 'Engineering',
                'description' => 'Build high-performance REST APIs and background queues powering merchant payouts, webhooks, and billing integrations.',
                'location' => 'Austin, TX',
                'work_mode' => 'on-site',
                'job_type' => 'full-time',
                'experience_level' => 'mid',
                'salary' => '$110,000 - $145,000',
                'requirements' => "• 3+ years backend development experience with PHP/Laravel or similar modern frameworks\n• Deep understanding of relational databases, indexing, and transactional integrity\n• Experience designing versioned RESTful APIs with automated test suites",
                'responsibilities' => "• Build robust endpoints and middleware for authentication, authorization, and rate limiting\n• Optimize SQL queries and caching with Redis to minimize response latency\n• Write clean, maintainable, and well-documented code",
                'benefits' => "• Full healthcare coverage\n• Relocation package for Austin office\n• Catered daily lunch and commuter benefits",
                'application_deadline' => now()->addDays(40),
                'status' => 'open',
            ]
        );

        // 9. Seed Application for Dorcas
        Application::updateOrCreate(
            ['user_id' => $dorcasCandidate->id, 'job_id' => $job1->id],
            [
                'resume' => 'uploads/resumes/dorcas_oguche_resume.pdf',
                'cover_letter' => "Dear Hiring Team at CloudScale Systems,\n\nI am writing to express my strong interest in the Senior Frontend Engineer position. With extensive experience in React, responsive design systems, and frontend architecture, I am confident in my ability to contribute meaningfully to your team.\n\nWarm regards,\nDorcas Oguche",
                'status' => 'interview',
            ]
        );
    }
}
