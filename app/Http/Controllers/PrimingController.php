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
        $data = $request->validated();

        $resultado = CarbonacaoService::calcularPriming(
            $data['modo'],
            $data['tipo_acucar'],
            isset($data['volume_litros']) ? (float) $data['volume_litros'] : null,
            isset($data['gramas_por_litro']) ? (float) $data['gramas_por_litro'] : null,
            isset($data['target_co2']) ? (float) $data['target_co2'] : null,
            isset($data['temp_fermentacao']) ? (float) $data['temp_fermentacao'] : null,
            isset($data['volume_solucao_inicial_ml']) ? (float) $data['volume_solucao_inicial_ml'] : null,
            isset($data['volume_solucao_final_ml']) ? (float) $data['volume_solucao_final_ml'] : null
        );

        return response()->json($resultado);
    }
}
