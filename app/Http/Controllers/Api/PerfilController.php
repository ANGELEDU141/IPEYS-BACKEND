<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PerfilService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function __construct(private PerfilService $perfilService)
    {
    }

    // Listado publico para grilla con busqueda.
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->perfilService->list($request->query()));
    }

    // Detalle publico para modal.
    public function show(int $id): JsonResponse
    {
        $result = $this->perfilService->detail($id);

        return response()->json($result['body'], $result['status']);
    }

    // Creacion protegida de perfil.
    public function store(Request $request): JsonResponse
    {
        $adminId = $request->attributes->get('auth_user')->id;
        $result = $this->perfilService->create($adminId, $request->all());

        return response()->json($result['body'], $result['status']);
    }

    // Edicion protegida de perfil.
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->perfilService->update($id, $request->all());

        return response()->json($result['body'], $result['status']);
    }

    // Eliminacion protegida de perfil.
    public function destroy(int $id): JsonResponse
    {
        $result = $this->perfilService->delete($id);

        return response()->json($result['body'], $result['status']);
    }

      // Baja logica de perfil.
    public function softDelete(int $id): JsonResponse
    {
        $result = $this->perfilService->softDelete($id);

        return response()->json($result['body'], $result['status']);
    }

    // Restauracion de perfil desactivado.
    public function restore(int $id): JsonResponse
    {
        $result = $this->perfilService->restore($id);

        return response()->json($result['body'], $result['status']);
    }
}
