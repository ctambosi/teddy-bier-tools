<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PercentualAcucarRequest;
use App\Data\ToolsMetadata;
use App\Services\LeveduraService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PercentualAcucarController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('percentual-acucar');

        return Inertia::render('Calculo/PercentualAcucar', [
            'meta' => $meta,
        ]);
    }

    public function calcular(PercentualAcucarRequest $request): JsonResponse
    {
        $data      = $request->validated();
        $resultado = LeveduraService::calcularPercentualAcucar(
            (float) $data['og'],
            (float) $data['percentual']
        );

        return response()->json($resultado);
    }
}
