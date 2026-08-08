<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AdicaoAlcoolicaRequest;
use App\Data\ToolsMetadata;
use App\Services\AlcoolService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdicaoAlcoolicaController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('adicao-alcoolica');

        return Inertia::render('Calculo/AdicaoAlcoolica', [
            'meta' => $meta,
        ]);
    }

    public function calcular(AdicaoAlcoolicaRequest $request): JsonResponse
    {
        $data   = $request->validated();
        $volume = AlcoolService::calcularAdicaoAlcoolica(
            (float) $data['volume_base'],
            (float) $data['graduacao_alcool'],
            (float) $data['graduacao_desejada']
        );

        return response()->json(['volume_adicionar' => round($volume, 3), 'unidade' => $data['unidade']]);
    }
}
