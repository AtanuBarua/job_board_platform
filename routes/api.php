<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\UserRoleController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Role Management Routes (Admin only)
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Permission
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{id}', [PermissionController::class, 'show'])->name('permissions.show');
    Route::put('/permissions/{id?}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    // Role
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
    Route::put('/roles/{id?}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // User Role
    Route::post('/assign-role/{userId}', [UserRoleController::class, 'assignRole']);
    Route::post('/remove-role/{userId}', [UserRoleController::class, 'removeRole']);
});
