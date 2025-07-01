<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanyManagementController extends Controller
{
    public function updateStatus(Request $request, $companyId = 0)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                Company::STATUS_APPROVED, Company::STATUS_REJECTED, Company::STATUS_SUSPENDED
            ]),
        ]);

        $company = Company::find($companyId);

        if (empty($company)) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $company->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Company status updated successfully.',
            'company' => $company,
        ]);
    }
}