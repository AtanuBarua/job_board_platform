<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user->hasRole(User::ROLE_COMPANY_ADMIN) &&
            ! empty($user->company) &&
            $user->company->status !== Company::STATUS_APPROVED
        ) {
            return response()->json([
                'message' => 'Access denied. Your company is not approved.'
            ], 403);
        }

        return $next($request);
    }
}
