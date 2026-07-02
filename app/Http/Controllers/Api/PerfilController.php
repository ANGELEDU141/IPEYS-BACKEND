<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PerfilService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\PerfilGrilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache; // <-- Importamos la fachada de caché

class PerfilController extends Controller
{
    public function __construct(private PerfilService $perfilService)
    {
    }

    // Listado publico para grilla con busqueda (CON CACHÉ OPTIMIZADA)
   public function index(Request $request): JsonResponse
{
    $queryParams = $request->query();
    /*
    $cacheKey = 'perfiles_list_' . md5(json_encode($queryParams));

    $data = Cache::remember($cacheKey, 300, function () use ($queryParams) {
        return $this->perfilService->list($queryParams);
    });
*/

    $data = $this->perfilService->list($queryParams);
    // Retornamos el JSON pero le prohibimos al navegador del cliente almacenar la respuesta
    return response()->json($data)
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
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
        $start = microtime(true);
        $adminId = $request->attributes->get('auth_user')->id;
       $result = $this->perfilService->create($adminId, $request->all());

       $totalTime = microtime(true) - $start;
         Log::info("DEBUG - Tiempo total de creación: " . $totalTime . " segundos");

        if ($result['status'] === 200 || $result['status'] === 210) { // Si se creó con éxito
            $this->clearPerfilesCache();
        }

        return response()->json($result['body'], $result['status']);
    } 

    // Edicion protegida de perfil.
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->perfilService->update($id, $request->all());

        $this->clearPerfilesCache(); // Limpiamos caché para ver los cambios

        return response()->json($result['body'], $result['status']);
    }

    // Eliminacion protegida de perfil.
    public function destroy(int $id): JsonResponse
    {
        $result = $this->perfilService->delete($id);

        $this->clearPerfilesCache();

        return response()->json($result['body'], $result['status']);
    }

    // Baja logica de perfil.
    public function softDelete(int $id): JsonResponse
    {
        $result = $this->perfilService->softDelete($id);

        $this->clearPerfilesCache();

        return response()->json($result['body'], $result['status']);
    }

public function verPdf($id)
{
    $perfil = PerfilGrilla::findOrFail($id);

    abort_if(!$perfil->pdf, 404, 'Este perfil no tiene PDF.');

    $ruta = public_path($perfil->pdf);

    abort_unless(File::exists($ruta), 404);

    return response()->file($ruta);
}

    // Restauracion de perfil desactivado.
    public function restore(int $id): JsonResponse
    {
        $result = $this->perfilService->restore($id);

        $this->clearPerfilesCache();

        return response()->json($result['body'], $result['status']);
    }

    /**
     * Helper privado para limpiar las búsquedas cacheadas cuando cambien los datos.
     */
    private function clearPerfilesCache(): void
    {
        // En hosting compartido, al no usar Redis/Memcached (sino archivos), 
        // lo más seguro y limpio para evitar que queden datos desactualizados en las grillas es flush.
        // Si compartes caché con otros modelos, me avisas para hacerlo por tags.
        Cache::forget('perfiles_list_cache_key');
    }
}