<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TemperaturaRequest;
use App\Services\TemperaturaService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class TemperaturaController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Conversao/Temperatura');
    }

    public function calcular(TemperaturaRequest $request): JsonResponse
    {
        return response()->json(
            TemperaturaService::calcularTemperatura($request->validated())
        );
    }
}
