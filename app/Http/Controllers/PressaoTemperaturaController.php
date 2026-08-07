<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\ToolsMetadata;
use App\Http\Requests\PressaoTemperaturaRequest;
use App\Services\CarbonacaoService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PressaoTemperaturaController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('pressao-temperatura');

        return Inertia::render('Correcao/PressaoTemperatura', [
            'meta' => $meta,
        ]);
    }

    public function calcular(PressaoTemperaturaRequest $request): JsonResponse
    {
        $data = $request->validated();

        $resultado = CarbonacaoService::calcularPressaoCarbonatacao(
            volumeCO2:   (float) $data['volume_co2'],
            tempCelsius: (float) $data['temperatura'],
        );

        return response()->json($resultado);
    }
}
