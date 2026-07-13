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
            'message' => 'You already own a company.',];
    }
            $company = Company::create([
            ...$request->validated(),
            'owner_id' => $user->id,]);
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