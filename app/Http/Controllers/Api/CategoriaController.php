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
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->categoriaService->list($request->query()));
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

    // Detalle publico de categoria por id.
    public function show(int $id): JsonResponse
    {
        $result = $this->categoriaService->detail($id);

        return response()->json($result['body'], $result['status']);
    }

    // Busqueda publica por nombre (query param `q`).
    public function search(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
  $result = $this->categoriaService->search($request->query());

    // Como la respuesta ya viene estructurada idéntica a list, el estatus es 200 directo
    return response()->json($result, 200);
    }
}
