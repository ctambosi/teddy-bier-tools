<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ExtracaoFrioRequest;
use App\Services\LeveduraService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExtracaoFrioController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Calculo/ExtracaoFrio');
    }

    public function calcular(ExtracaoFrioRequest $request): JsonResponse
    {
        $data   = $request->validated();
        $litros = LeveduraService::calcularExtracaoFrio((float) $data['gramas']);

        return response()->json(['litros' => round($litros, 3)]);
    }
}
