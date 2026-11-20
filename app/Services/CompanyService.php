<?php

namespace App\Services;

use App\Models\Company;
use App\Http\Requests\CreateCompanyRequest;
use Illuminate\Support\Str;

class CompanyService
{
    /**
     * Create a new company owned by the employer.
     * An employer can own multiple companies.
     */
    public function createCompany(CreateCompanyRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        // If email is not explicitly provided, generate a unique company contact email
        if (empty($data['email'])) {
            $baseSlug = Str::slug($data['name']) ?: 'company';
            $uniqueEmail = 'contact@' . $baseSlug . '.com';
            if (Company::where('email', $uniqueEmail)->exists()) {
                $uniqueEmail = 'contact_' . time() . '_' . rand(100, 999) . '@' . $baseSlug . '.com';
            }
            $data['email'] = $uniqueEmail;
        }

        // Handle logo file upload or URL fallback
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = asset('storage/' . $path);
        } elseif (empty($data['logo'])) {
            $data['logo'] = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=128&h=128&fit=crop';
        }

        // Handle cover file upload or URL fallback
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('covers', 'public');
            $data['cover'] = asset('storage/' . $path);
        } elseif (empty($data['cover'])) {
            $data['cover'] = 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1600&h=400&fit=crop';
        }

        // Set default description if empty
        if (empty($data['description'])) {
            $data['description'] = 'Innovative organization founded by ' . $user->name;
        }

        // Create the company with owner_id set to the authenticated employer
        $company = Company::create([
            ...$data,
            'owner_id' => $user->id,
        ]);

        // Keep user's active company_id pointing to their latest company
        $user->update([
            'company_id' => $company->id,
        ]);

        return [
            'success' => true,
            'message' => 'Company created successfully.',
            'company' => $company,
        ];
    }
}
