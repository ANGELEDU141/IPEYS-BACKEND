<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\PerfilController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Ruta tecnica para comprobar que la API esta activa.
Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Login exclusivo para administradores.
Route::post('/auth/login', [AuthController::class, 'login']);

// Rutas publicas consumidas por la grilla y el modal.
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/perfiles', [PerfilController::class, 'index']);
Route::get('/perfiles/{id}', [PerfilController::class, 'show']);

// Rutas protegidas por token Bearer de administrador.
Route::middleware('admin.token')->group(function () {
    // Control de sesion administrativa.
    Route::get('/auth/validate', [AuthController::class, 'validateToken']);
    Route::get('/auth/session', [AuthController::class, 'session']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // CRUD de roles para escalar permisos.
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::patch('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    // CRUD de usuarios con parametro user.
    Route::get('/users', [UserController::class, 'index']);
     Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::patch('/users/{id}', [UserController::class, 'update']);
    Route::post('/users/{id}/soft-delete', [UserController::class, 'softDelete']);
    Route::post('/users/{id}/restore', [UserController::class, 'restore']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // CRUD protegido de categorias.
    Route::post('/categorias', [CategoriaController::class, 'store']);
    Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
    Route::patch('/categorias/{id}', [CategoriaController::class, 'update']);
    Route::post('/categorias/{id}/soft-delete', [CategoriaController::class, 'softDelete']);
    Route::post('/categorias/{id}/restore', [CategoriaController::class, 'restore']);
    Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

    // CRUD protegido de perfiles.
    Route::post('/perfiles', [PerfilController::class, 'store']);
    Route::put('/perfiles/{id}', [PerfilController::class, 'update']);
    Route::patch('/perfiles/{id}', [PerfilController::class, 'update']);
    Route::post('/perfiles/{id}/soft-delete', [PerfilController::class, 'softDelete']);
    Route::post('/perfiles/{id}/restore', [PerfilController::class, 'restore']);
    Route::delete('/perfiles/{id}', [PerfilController::class, 'destroy']);
});
