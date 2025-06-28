<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::paginate(10);
        return response()->json([
            'success' => true,
            'data' => $permissions
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        try {
            $permission = Permission::create(['name' => strtolower($validated['name'])]);
            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully.',
                'data' => $permission
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('PERMISSION_CREATION_FAILED: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create permission.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        $permission = Permission::find($id);

        if (empty($permission)) {
            return response()->json([
                'success' => false,
                'data' => $permission
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $permission
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id = null)
    {
        if (empty($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Permission id is required.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $permission = Permission::find($id);

        if (empty($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('permissions', 'name')->ignore($id)],
        ]);

        try {
            $permission->update(['name' => strtolower($validated['name'])]);

            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully.',
                'data' => $permission
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('PERMISSION_UPDATE_FAILED: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        $permission = Permission::find($id);

        try {
            if (!empty($permission)) {
                $permission->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Permission deleted successfully.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Permission not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error('PERMISSION_DELETION_FAILED: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
