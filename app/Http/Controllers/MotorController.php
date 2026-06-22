<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MotorRequest;
use App\Services\EquipamentoService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class MotorController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Calculo/Motor');
    }

    public function calcular(MotorRequest $request): JsonResponse
    {
        $data = $request->validated();

        $toMm = fn(?string $val, string $unidade) =>
            filled($val) ? (float) $val * ($unidade === 'cm' ? 10 : 1) : null;

        $resultado = EquipamentoService::calcularPolia(
            filled($data['n1']) ? (float) $data['n1'] : null,
            $toMm($data['d1'] ?? null, $data['unidade_d1']),
            filled($data['n2']) ? (float) $data['n2'] : null,
            $toMm($data['d2'] ?? null, $data['unidade_d2'])
        );

        return response()->json($resultado);
    }
}
