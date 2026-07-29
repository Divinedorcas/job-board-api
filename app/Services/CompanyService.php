<?php

namespace App\Services;

use App\Models\Company;
use App\Http\Requests\CreateCompanyRequest;

class CompanyService
{
    public function createCompany(CreateCompanyRequest $request)
    {
        $user = $request->user();
        if ($user->company_id) {
            return [
                'success' => false,
                'message' => 'You already own a company.',
            ];
        }

        $data = $request->validated();

        // Handle logo file upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = asset('storage/' . $path);
        }

        // Handle cover file upload
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('covers', 'public');
            $data['cover'] = asset('storage/' . $path);
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