<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\VolumeMostoRequest;
use App\Services\EquipamentoService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class VolumeMostoController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Calculo/VolumeMosto');
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
