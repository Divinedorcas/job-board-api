<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Http\Requests\CreateCompanyRequest;


class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Company::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createCompany(CreateCompanyRequest $request)
    {
         $user = $request->user();
    $company = Company::create([
        ...$request->validated(),
        'owner_id' => $user->id,
    ]);
    $user->update([
        'company_id' => $company->id,
    ]);

    return response()->json([
        'message' => 'Company created successfully.',
        'company' => $company,
    ], 201);
    }

// public function createCompany(CreateCompanyRequest $request)
// {
//     $user = $request->user();

//     $company = Company::create([
//         ...$request->validated(),
//         'owner_id' => $user->id,
//     ]);

//     $updated = $user->update([
//         'company_id' => $company->id,
//     ]);

//     return response()->json([
//         'user_id' => $user->id,
//         'company_id_created' => $company->id,
//         'updated' => $updated,
//         'fresh_user' => $user->fresh(),
//     ]);
// }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
