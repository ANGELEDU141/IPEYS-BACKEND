<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService)
    {
    }

    // Listado de roles disponibles.
    public function index(): JsonResponse
    {
        return response()->json($this->roleService->list());
    }

    // Creacion de rol.
    public function store(Request $request): JsonResponse
    {
        $result = $this->roleService->create($request->all());

        return response()->json($result['body'], $result['status']);
    }

    // Edicion de rol.
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->roleService->update($id, $request->all());

        return response()->json($result['body'], $result['status']);
    }

    // Eliminacion de rol.
    public function destroy(int $id): JsonResponse
    {
        $result = $this->roleService->delete($id);

        return response()->json($result['body'], $result['status']);
    }
}
