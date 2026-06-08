<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {
    }

    // Listado de usuarios para el panel administrador.
    public function index(): JsonResponse
    {
        return response()->json($this->userService->list());
    }

    // Creacion de usuarios usando parametro user.
    public function store(Request $request): JsonResponse
    {
        $result = $this->userService->create($request->all());

        return response()->json($result['body'], $result['status']);
    }

    // Edicion parcial o completa de usuario.
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->userService->update($id, $request->all());

        return response()->json($result['body'], $result['status']);
    }

    // Eliminacion de usuario administrado.
    public function destroy(Request $request, int $id): JsonResponse
    {
        $adminId = $request->attributes->get('auth_user')->id;
        $result = $this->userService->delete($id, $adminId);

        return response()->json($result['body'], $result['status']);
    }

      // Baja logica de usuario.
    public function softDelete(Request $request, int $id): JsonResponse
    {
        $adminId = $request->attributes->get('auth_user')->id;
        $result = $this->userService->softDelete($id, $adminId);

        return response()->json($result['body'], $result['status']);
    }

    // Restauracion de usuario desactivado.
    public function restore(int $id): JsonResponse
    {
        $result = $this->userService->restore($id);

        return response()->json($result['body'], $result['status']);
    }
}
