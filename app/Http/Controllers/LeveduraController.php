<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LeveduraRequest;
use App\Data\ToolsMetadata;
use App\Services\LeveduraService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class LeveduraController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('levedura');

        return Inertia::render('Levedura', [
            'meta' => $meta,
        ]);
    }

    public function calcular(LeveduraRequest $request): JsonResponse
    {
        $data   = $request->validated();
        $gramas = LeveduraService::calcularLevedura(
            (float) $data['celulas'],
            (float) $data['concentracao']
        );

        return response()->json(['gramas' => $gramas]);
    }
}
