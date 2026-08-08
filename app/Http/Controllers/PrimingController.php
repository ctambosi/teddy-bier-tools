<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PrimingRequest;
use App\Data\ToolsMetadata;
use App\Services\CarbonacaoService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PrimingController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('priming');

        return Inertia::render('Priming', [
            'meta' => $meta,
        ]);
    }

    public function calcular(PrimingRequest $request): JsonResponse
    {
        $data      = $request->validated();
        $resultado = CarbonacaoService::calcularPriming(
            (float) $data['volume_litros'],
            (float) $data['temp_fermentacao'],
            (float) $data['target_co2'],
            $data['tipo_acucar'],
            isset($data['volume_solucao_ml']) ? (float) $data['volume_solucao_ml'] : null
        );

        return response()->json($resultado);
    }
}
