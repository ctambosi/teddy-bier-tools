<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PressaoRequest;
use App\Services\CarbonacaoService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PressaoController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Conversao/Pressao');
    }

    public function calcular(PressaoRequest $request): JsonResponse
    {
        return response()->json(
            CarbonacaoService::calcularPressao($request->validated())
        );
    }
}
