<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrecaoDensimetroRequest;
use App\Http\Requests\CorrecaoPressaoTemperaturaRequest;
use App\Http\Requests\CorrecaoRefratometroRequest;
use App\Services\CorrecaoService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CorrecaoController extends Controller
{
    public function densimetro(): Response
    {
        return Inertia::render('Correcao/Densimetro');
    }

    public function calcularDensimetro(CorrecaoDensimetroRequest $request): JsonResponse
    {
        $data = $request->validated();

        $sgCorrigido = CorrecaoService::corrigirDensimetro(
            sg:               (float) $data['sg'],
            tempCelsius:      (float) $data['temperatura'],
            calibracaoCelsius: (float) $data['calibracao'],
        );

        return response()->json(['sg_corrigido' => $sgCorrigido]);
    }

    public function refratometro(): Response
    {
        return Inertia::render('Correcao/Refratometro');
    }

    public function calcularRefratometro(CorrecaoRefratometroRequest $request): JsonResponse
    {
        $data = $request->validated();

        $sgCorrigido = CorrecaoService::corrigirRefratometro(
            og:          (float) $data['og'],
            leituraAtual: (float) $data['leitura_atual'],
        );

        return response()->json(['sg_corrigido' => $sgCorrigido]);
    }

    public function pressaoTemperatura(): Response
    {
        return Inertia::render('Correcao/PressaoTemperatura');
    }

    public function calcularPressaoTemperatura(CorrecaoPressaoTemperaturaRequest $request): JsonResponse
    {
        $data = $request->validated();

        $resultado = CorrecaoService::calcularPressaoCarbonatacao(
            volumeCO2:   (float) $data['volume_co2'],
            tempCelsius: (float) $data['temperatura'],
        );

        return response()->json($resultado);
    }
}
