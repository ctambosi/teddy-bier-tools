<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Services\CalculoService;
use App\Http\Requests\CalculoAbvRequest;
use App\Http\Requests\CalculoExtracaoFrioRequest;
use App\Http\Requests\CalculoPercentualAcucarRequest;
use App\Http\Requests\CalculoLeveduraRequest;
use App\Http\Requests\CalculoDiluicaoAlcoolicaRequest;
use App\Http\Requests\CalculoAdicaoAlcoolicaRequest;
use App\Http\Requests\CalculoPrimingRequest;
use App\Http\Requests\CalculoVolumeMostoRequest;
use App\Http\Requests\CalculoMotorRequest;

class CalculoController extends Controller
{
    public function abv()
    {
        return Inertia::render('Calculo/Abv');
    }

    public function calcularAbv(CalculoAbvRequest $request)
    {
        $data = $request->validated();
        $abv = CalculoService::calcularAbv((float) $data['og'], (float) $data['fg']);
        return response()->json(['abv' => round($abv, 2)]);
    }

    public function extracaoFrio()
    {
        return Inertia::render('Calculo/ExtracaoFrio');
    }

    public function calcularExtracaoFrio(CalculoExtracaoFrioRequest $request)
    {
        $data = $request->validated();
        $litros = CalculoService::calcularExtracaoFrio((float) $data['gramas']);
        return response()->json(['litros' => round($litros, 3)]);
    }

    public function percentualAcucar()
    {
        return Inertia::render('Calculo/PercentualAcucar');
    }

    public function calcularPercentualAcucar(CalculoPercentualAcucarRequest $request)
    {
        $data = $request->validated();
        $resultado = CalculoService::calcularPercentualAcucar((float) $data['og'], (float) $data['percentual']);
        return response()->json($resultado);
    }

    public function levedura()
    {
        return Inertia::render('Calculo/Levedura');
    }

    public function calcularLevedura(CalculoLeveduraRequest $request)
    {
        $data = $request->validated();
        $gramas = CalculoService::calcularLevedura((float) $data['celulas'], (float) $data['concentracao']);
        return response()->json(['gramas' => $gramas]);
    }

    public function diluicaoAlcoolica()
    {
        return Inertia::render('Calculo/DiluicaoAlcoolica');
    }

    public function calcularDiluicaoAlcoolica(CalculoDiluicaoAlcoolicaRequest $request)
    {
        $data = $request->validated();
        $resultado = CalculoService::calcularDiluicaoAlcoolica(
            (float) $data['quantidade'],
            (float) $data['graduacao_alcool'],
            (float) $data['graduacao_desejada']
        );
        return response()->json(array_merge($resultado, ['unidade' => $data['unidade']]));
    }

    public function adicaoAlcoolica()
    {
        return Inertia::render('Calculo/AdicaoAlcoolica');
    }

    public function calcularAdicaoAlcoolica(CalculoAdicaoAlcoolicaRequest $request)
    {
        $data = $request->validated();
        $volume = CalculoService::calcularAdicaoAlcoolica(
            (float) $data['volume_base'],
            (float) $data['graduacao_alcool'],
            (float) $data['graduacao_desejada']
        );
        return response()->json(['volume_adicionar' => round($volume, 3), 'unidade' => $data['unidade']]);
    }

    public function motor()
    {
        return Inertia::render('Calculo/Motor');
    }

    public function calcularMotor(CalculoMotorRequest $request)
    {
        $data = $request->validated();

        // Normaliza diâmetros para mm
        $toMm = fn(?string $val, string $unidade) =>
            filled($val) ? (float) $val * ($unidade === 'cm' ? 10 : 1) : null;

        $resultado = CalculoService::calcularPolia(
            filled($data['n1']) ? (float) $data['n1'] : null,
            $toMm($data['d1'] ?? null, $data['unidade_d1']),
            filled($data['n2']) ? (float) $data['n2'] : null,
            $toMm($data['d2'] ?? null, $data['unidade_d2'])
        );

        return response()->json($resultado);
    }

    public function volumeMosto()
    {
        return Inertia::render('Calculo/VolumeMosto');
    }

    public function calcularVolumeMosto(CalculoVolumeMostoRequest $request)
    {
        $data   = $request->validated();
        $volume = CalculoService::calcularVolumeMosto(
            (float) $data['diametro'],
            (float) $data['altura_liquido'],
            isset($data['perda']) ? (float) $data['perda'] : 0.0
        );
        return response()->json(['volume' => $volume]);
    }

    public function priming()
    {
        return Inertia::render('Calculo/Priming');
    }

    public function calcularPriming(CalculoPrimingRequest $request)
    {
        $data = $request->validated();
        $resultado = CalculoService::calcularPriming(
            (float) $data['volume_litros'],
            (float) $data['temp_fermentacao'],
            (float) $data['target_co2'],
            $data['tipo_acucar'],
            isset($data['volume_solucao_ml']) ? (float) $data['volume_solucao_ml'] : null
        );
        return response()->json($resultado);
    }
}
