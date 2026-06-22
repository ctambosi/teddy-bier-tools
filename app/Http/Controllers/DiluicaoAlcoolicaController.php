<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DiluicaoAlcoolicaRequest;
use App\Services\AlcoolService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DiluicaoAlcoolicaController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Calculo/DiluicaoAlcoolica');
    }

    public function calcular(DiluicaoAlcoolicaRequest $request): JsonResponse
    {
        $data      = $request->validated();
        $resultado = AlcoolService::calcularDiluicaoAlcoolica(
            (float) $data['quantidade'],
            (float) $data['graduacao_alcool'],
            (float) $data['graduacao_desejada']
        );

        return response()->json(array_merge($resultado, ['unidade' => $data['unidade']]));
    }
}
