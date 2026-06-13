<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConversaoCorRequest;
use App\Http\Requests\ConversaoDensidadeRequest;
use App\Http\Requests\ConversaoPressaoRequest;
use App\Http\Requests\ConversaoTemperaturaRequest;
use App\Services\ConversaoService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ConversaoController extends Controller
{
    public function densidade(): Response
    {
        return Inertia::render('Conversao/Densidade');
    }

    public function calcularDensidade(ConversaoDensidadeRequest $request): JsonResponse
    {
        return response()->json(
            ConversaoService::calcularDensidade($request->validated())
        );
    }

    public function temperatura(): Response
    {
        return Inertia::render('Conversao/Temperatura');
    }

    public function calcularTemperatura(ConversaoTemperaturaRequest $request): JsonResponse
    {
        return response()->json(
            ConversaoService::calcularTemperatura($request->validated())
        );
    }

    public function cor(): Response
    {
        return Inertia::render('Conversao/Cor');
    }

    public function calcularCor(ConversaoCorRequest $request): JsonResponse
    {
        return response()->json(
            ConversaoService::calcularCor($request->validated())
        );
    }

    public function pressao(): Response
    {
        return Inertia::render('Conversao/Pressao');
    }

    public function calcularPressao(ConversaoPressaoRequest $request): JsonResponse
    {
        return response()->json(
            ConversaoService::calcularPressao($request->validated())
        );
    }
}
