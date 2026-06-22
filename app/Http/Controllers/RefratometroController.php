<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RefratometroRequest;
use App\Services\DensidadeService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class RefratometroController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Correcao/Refratometro');
    }

    public function calcular(RefratometroRequest $request): JsonResponse
    {
        $data = $request->validated();

        $sgCorrigido = DensidadeService::corrigirRefratometro(
            og:          (float) $data['og'],
            leituraAtual: (float) $data['leitura_atual'],
        );

        return response()->json(['sg_corrigido' => $sgCorrigido]);
    }
}
