<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CorRequest;
use App\Data\ToolsMetadata;
use App\Services\CorService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CorController extends Controller
{
    public function show(): Response
    {
        $meta = ToolsMetadata::get('cor');

        return Inertia::render('Cor', [
            'meta' => $meta,
        ]);
    }

    public function calcular(CorRequest $request): JsonResponse
    {
        return response()->json(
            CorService::calcularCor($request->validated())
        );
    }
}
