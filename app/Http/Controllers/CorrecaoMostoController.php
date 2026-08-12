<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CorrecaoMostoRequest;
use App\Data\ToolsMetadata;
use App\Services\DensidadeService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CorrecaoMostoController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('correcao-mosto');

        return Inertia::render('CorrecaoMosto', [
            'meta' => $meta,
        ]);
    }

    public function calcular(CorrecaoMostoRequest $request): JsonResponse
    {
        $data      = $request->validated();
        $resultado = DensidadeService::calcularCorrecaoMosto(
            (float) $data['volume'],
            (float) $data['densidade_atual'],
            (float) $data['densidade_desejada']
        );

        return response()->json($resultado);
    }
}
