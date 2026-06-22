<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DensimetroRequest;
use App\Services\DensidadeService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DensimetroController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Correcao/Densimetro');
    }

    public function calcular(DensimetroRequest $request): JsonResponse
    {
        $data = $request->validated();

        $sgCorrigido = DensidadeService::corrigirDensimetro(
            sg:                (float) $data['sg'],
            tempCelsius:       (float) $data['temperatura'],
            calibracaoCelsius: (float) $data['calibracao'],
        );

        return response()->json(['sg_corrigido' => $sgCorrigido]);
    }
}
