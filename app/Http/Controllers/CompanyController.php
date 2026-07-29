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

    //Create Company

    public function index()
    {
        return response()->json([
            'companies' => Company::with('jobs')->get()
        ]);
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

    //View Company
    public function show( $id)
    {
        $company = Company::with('jobs')->findOrFail($id);
        return response()->json([
            'company' => $company,
        ], );
    }

    //Update Company
    public function update(Request $request, $id)
    {
        $upcompany = Company::find($id);
            if(!$upcompany){

            return "Company Not Found";
        }
    }

    //Delete Company
    public function destroy( $id)
    {
        $decompany = Company::find($id);
        if(!$decompany){
            return "Company Not Found";
        }
        $decompany->delete();
    }


    //Company Profile
}
