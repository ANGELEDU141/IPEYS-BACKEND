<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct(private CategoriaService $categoriaService)
    {
    }

    // Listado publico de categorias.
    public function index(): JsonResponse
    {
        return response()->json($this->categoriaService->list());
    }

    // Creacion protegida de categoria.
    public function store(Request $request): JsonResponse
    {
        $result = $this->categoriaService->create($request->all());

        return response()->json($result['body'], $result['status']);
    }

    // Edicion protegida de categoria.
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->categoriaService->update($id, $request->all());

        return response()->json($result['body'], $result['status']);
    }

    // Eliminacion protegida de categoria.
    public function destroy(int $id): JsonResponse
    {
        $result = $this->categoriaService->delete($id);

        return response()->json($result['body'], $result['status']);
    }

    
    // Baja logica de categoria.
    public function softDelete(int $id): JsonResponse
    {
        $result = $this->categoriaService->softDelete($id);

        return response()->json($result['body'], $result['status']);
    }

    // Restauracion de categoria desactivada.
    public function restore(int $id): JsonResponse
    {
        $result = $this->categoriaService->restore($id);

        return response()->json($result['body'], $result['status']);
    }
}
