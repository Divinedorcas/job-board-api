<?php

namespace App\Services;

use App\Models\Company;
use App\Http\Requests\CreateCompanyRequest;

class CompanyService
{
    public function createCompany(CreateCompanyRequest $request)
    {
        $user = $request->user();

        // Check if user already owns a company
        $existing = Company::where('owner_id', $user->id)->first();
        if ($existing) {
            if (!$user->company_id) {
                $user->company_id = $existing->id;
                $user->save();
            }
            return [
                'success' => true,
                'message' => 'Company already exists for your account.',
                'company' => $existing,
            ];
        }

        $data = $request->validated();

        // Handle logo file upload or URL
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = asset('storage/' . $path);
        } elseif (empty($data['logo'])) {
            $data['logo'] = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=128&h=128&fit=crop';
        }

        // Handle cover file upload or URL
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('covers', 'public');
            $data['cover'] = asset('storage/' . $path);
        } elseif (empty($data['cover'])) {
            $data['cover'] = 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1600&h=400&fit=crop';
        }

        $company = Company::create([
            ...$data,
            'owner_id' => $user->id,
        ]);

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
