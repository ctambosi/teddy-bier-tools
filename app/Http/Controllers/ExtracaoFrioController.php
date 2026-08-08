<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ExtracaoFrioRequest;
use App\Data\ToolsMetadata;
use App\Services\LeveduraService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExtracaoFrioController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('extracao-frio');

        return Inertia::render('Calculo/ExtracaoFrio', [
            'meta' => $meta,
        ]);
    }

    public function calcular(ExtracaoFrioRequest $request): JsonResponse
    {
        $data   = $request->validated();
        $litros = LeveduraService::calcularExtracaoFrio((float) $data['gramas']);

        return response()->json(['litros' => round($litros, 3)]);
    }
}
