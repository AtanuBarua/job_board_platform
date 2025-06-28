<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $roles
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array|required',
            'permissions.*' => 'exists:permissions,name',
        ]);

        DB::beginTransaction();

        try {
            $role = Role::create(['name' => strtolower($validated['name'])]);

            $role->syncPermissions($validated['permissions']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data' => $role->load('permissions')
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Role creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while creating the role.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        $role = Role::with('permissions')->find($id);

        if (empty($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $role
        ], Response::HTTP_OK);
    }

    public function update(Request $request, $id = null)
    {
        if (empty($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Role id is required.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $role = Role::find($id);

        if (empty($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', Rule::unique('roles', 'name')->ignore($id)],
            'permissions' => 'array|required',
            'permissions.*' => 'exists:permissions,name',
        ]);

        DB::beginTransaction();

        try {
            if (!empty($validated['name'])) {
                $role->name = strtolower($validated['name']);
                $role->save();
            }

            if (!empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'data' => $role->load('permissions')
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Role update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update role.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        try {
            if (empty($role)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found.'
                ], Response::HTTP_NOT_FOUND);
            }
            $role->delete();
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.'
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Role deletion failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
