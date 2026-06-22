<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AbvRequest;
use App\Services\AlcoolService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AbvController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Calculo/Abv');
    }

    public function calcular(AbvRequest $request): JsonResponse
    {
        $data = $request->validated();
        $abv  = AlcoolService::calcularAbv((float) $data['og'], (float) $data['fg']);

        return response()->json(['abv' => round($abv, 2)]);
    }
}
