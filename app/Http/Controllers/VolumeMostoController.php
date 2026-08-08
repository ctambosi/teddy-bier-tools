<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\VolumeMostoRequest;
use App\Data\ToolsMetadata;
use App\Services\EquipamentoService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class VolumeMostoController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('volume-mosto');

        return Inertia::render('Calculo/VolumeMosto', [
            'meta' => $meta,
        ]);
    }

    public function calcular(VolumeMostoRequest $request): JsonResponse
    {
        $data   = $request->validated();
        $volume = EquipamentoService::calcularVolumeMosto(
            (float) $data['diametro'],
            (float) $data['altura_liquido'],
            isset($data['perda']) ? (float) $data['perda'] : 0.0
        );

        return response()->json(['volume' => $volume]);
    }
}
