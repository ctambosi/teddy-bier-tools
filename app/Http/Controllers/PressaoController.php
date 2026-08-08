<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PressaoRequest;
use App\Data\ToolsMetadata;
use App\Services\CarbonacaoService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PressaoController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('pressao');

        return Inertia::render('Pressao', [
            'meta' => $meta,
        ]);
    }

    public function calcular(PressaoRequest $request): JsonResponse
    {
        return response()->json(
            CarbonacaoService::calcularPressao($request->validated())
        );
    }
}
