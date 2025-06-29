<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Company;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CompanyRegistrationController extends Controller
{
    public function registerWithCompany(Request $request)
    {
        $validated = $request->validate([
            //user
            'name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            //company
            'company_name' => 'required|string|max:200|unique:companies,name',
            'company_email' => 'required|email|unique:companies,email',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'logo' => 'required|image|mimes:jpeg,png,jpeg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole(User::ROLE_COMPANY_ADMIN);

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
            }

            // Create Company
            $company = Company::create([
                'name' => $validated['company_name'],
                'slug' => Str::slug($validated['company_name']),
                'email' => $validated['company_email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'website' => $validated['website'] ?? null,
                'description' => $validated['description'] ?? null,
                'address' => $validated['address'] ?? null,
                'industry' => $validated['industry'] ?? null,
                'logo' => $logoPath,
                'owner_id' => $user->id,
            ]);

            $user->company_id = $company->id;
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User and company registered successfully.',
                'token' => $token,
                'user' => $user,
                'company' => $company,
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Registration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
