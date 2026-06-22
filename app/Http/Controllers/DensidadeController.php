<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DensidadeRequest;
use App\Services\DensidadeService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DensidadeController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Conversao/Densidade');
    }

    public function calcular(DensidadeRequest $request): JsonResponse
    {
        return response()->json(
            DensidadeService::calcularDensidade($request->validated())
        );
    }
}
