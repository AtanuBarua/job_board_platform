<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UserRoleController extends Controller
{
    public function assignRole(Request $request, $userId)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::find($userId);

        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No user found.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            if ($user->hasRole($validated['role'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has this role.'
                ], Response::HTTP_CONFLICT);
            }

            $user->assignRole($validated['role']);
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully.',
                'data' => $user
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Failed to assign role: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Role assignment failed.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function removeRole(Request $request, $userId)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::find($userId);

        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'No user found.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            if (! $user->hasRole($validated['role'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have this role.'
                ], Response::HTTP_NOT_FOUND);
            }

            $user->removeRole($validated['role']);
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully.',
                'data' => $user
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to remove role: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Role removal failed.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
