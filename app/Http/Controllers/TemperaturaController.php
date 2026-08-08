<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TemperaturaRequest;
use App\Data\ToolsMetadata;
use App\Services\TemperaturaService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class TemperaturaController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('temperatura');

        return Inertia::render('Temperatura', [
            'meta' => $meta,
        ]);
    }

    public function calcular(TemperaturaRequest $request): JsonResponse
    {
        return response()->json(
            TemperaturaService::calcularTemperatura($request->validated())
        );
    }
}
