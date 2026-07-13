<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Http\Requests\CreateCompanyRequest;
use App\Services\CompanyService;


class CompanyController extends Controller
{

public function __construct(
        private CompanyService $companyService
        
    ) {}
    public function index()
    {
        return Company::all();
    }

    public function createCompany(CreateCompanyRequest $request)
    {
       $result = $this->companyService->createCompany($request);
 if (!$result['success']) {
        return response()->json([
            'message' => $result['message'],
        ], );
    }
    return response()->json([
        'message' => $result['message'],
        'company' => $result['company'],
    ], );
}
    
    public function show(string $id)
    {
        //
    }

    
    public function update(Request $request, string $id)
    {
        //
    }

    
    public function destroy(string $id)
    {
        //
    }
}
