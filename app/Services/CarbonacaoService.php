<?php

declare(strict_types=1);

namespace App\Services;

class CarbonacaoService
{
    const PSI_POR_BAR = 14.5037738007;

    const FATORES_ACUCAR = [
        'sucrose'         => 3.86,
        'dextrose_mono'   => 4.48,
        'dextrose_anidra' => 4.07,
        'dme'             => 5.25,
        'mel'             => 4.95,
    ];

    const TAMANHOS_GARRAFA = [250, 275, 310, 330, 355, 500, 600];

    public static function calcularPressao(array $input): array
    {
        if ($input['bar'] !== null) {
            return [
                'bar'             => $input['bar'],
                'psi'             => $input['bar'] * self::PSI_POR_BAR,
                'campo_informado' => 'bar',
            ];
        }

        return [
            'bar'             => $input['psi'] / self::PSI_POR_BAR,
            'psi'             => $input['psi'],
            'campo_informado' => 'psi',
        ];
    }

    public static function calcularPressaoCarbonatacao(float $volumeCO2, float $tempCelsius): array
    {
        $passo1 = 0.01821 + 0.090115 * exp(-($tempCelsius * 1.8 / 43.11));
        $passo2 = $passo1 * 14.50377;
        $passo3 = $passo1 * 14.695 - 0.003342;
        $bar    = ($volumeCO2 - $passo3) / $passo2;

        return [
            'bar' => $bar,
            'psi' => $bar * self::PSI_POR_BAR,
        ];
    }

    public static function co2Residual(float $tempC): float
    {
        $tempF = $tempC * 9 / 5 + 32;

        return 3.0378 - (0.050062 * $tempF) + (0.00026555 * ($tempF ** 2));
    }

    // Solução recém-misturada (1g de açúcar : 1ml de água, antes de ferver):
    // por balanço de massa, isso é 0,5 g de açúcar por ml de solução — é o
    // valor "puro" usado para converter entre volume inicial/final quando o
    // usuário mede os dois (ver calcularPriming()).
    const CONCENTRACAO_MISTURA_G_POR_ML = 0.5;

    // Estimativa de referência quando ninguém mediu o volume antes/depois de
    // ferver: assume uma redução típica de fervura de 5-10 min (ex.: 100g de
    // açúcar rendendo ~140ml de xarope pronto, ~1,4 ml por grama de açúcar).
    // Esse valor reconcilia a dosagem de referência de 7 g/L com a regra
    // prática de 1 ml de solução para cada 100 ml de cerveja. Como a redução
    // real varia com o tempo/intensidade da fervura, medir o volume
    // antes/depois de ferver (campo opcional) dá um resultado mais preciso
    // do que essa estimativa.
    const CONCENTRACAO_PADRAO_G_POR_ML = 0.7;

    public static function calcularPriming(
        string $modo,
        string $tipoAcucar,
        ?float $volumeLitros = null,
        ?float $gramasPorLitroManual = null,
        ?float $targetCO2 = null,
        ?float $tempFermentacao = null,
        ?float $volumeSolucaoInicialMl = null,
        ?float $volumeSolucaoFinalMl = null
    ): array {
        $fator = self::FATORES_ACUCAR[$tipoAcucar] ?? self::FATORES_ACUCAR['sucrose'];

        if ($modo === 'manual') {
            $gramasPorLitro = $gramasPorLitroManual;
            $resultado      = ['modo' => 'manual', 'tipo_acucar' => $tipoAcucar];
        } else {
            $co2Residual    = self::co2Residual($tempFermentacao);
            $co2Adicional   = max(0.0, $targetCO2 - $co2Residual);
            $gramasPorLitro = $co2Adicional * $fator;
            $resultado      = [
                'modo'          => 'co2',
                'tipo_acucar'   => $tipoAcucar,
                'co2_residual'  => round($co2Residual, 3),
                'co2_adicional' => round($co2Adicional, 3),
            ];
        }

        $resultado['gramas_por_litro'] = round($gramasPorLitro, 2);

        // Total do lote (opcional) — independente da concentração da
        // solução calculada abaixo. Serve só para dizer quanto açúcar
        // comprar/pesar no total e sugerir um volume de solução para preparar.
        if ($volumeLitros !== null && $volumeLitros > 0) {
            $gramasTotal = $gramasPorLitro * $volumeLitros;

            $resultado['gramas_total']                     = round($gramasTotal, 1);
            $resultado['volume_solucao_sugerido_ml']        = round($gramasTotal / self::CONCENTRACAO_MISTURA_G_POR_ML, 1);
            // Volume "depois de ferver" coerente com a própria estimativa padrão (0,7 g/ml)
            // usada mais abaixo — preenche os dois campos já na razão que reproduz 0,7 g/ml
            // pela fórmula real, evitando um salto entre a estimativa e o cálculo medido.
            $resultado['volume_solucao_final_sugerido_ml']  = round($gramasTotal / self::CONCENTRACAO_PADRAO_G_POR_ML, 1);
        }

        // Se o usuário mediu o volume antes e depois de ferver, calculamos a
        // concentração real por balanço de massa: a solução recém-misturada
        // (meio a meio) tem 0,5 g de açúcar por ml; durante a fervura só a
        // água evapora — a massa de açúcar não muda — então a concentração
        // final é a inicial multiplicada pela razão entre os dois volumes
        // (menos água no mesmo açúcar = solução mais concentrada). Esse
        // cálculo não depende do total do lote. Sem essa medição, usamos a
        // estimativa de referência (solução já reduzida por uma fervura típica).
        $concentracao     = self::CONCENTRACAO_PADRAO_G_POR_ML;
        $concentracaoReal = false;
        if ($volumeSolucaoInicialMl !== null && $volumeSolucaoInicialMl > 0
            && $volumeSolucaoFinalMl !== null && $volumeSolucaoFinalMl > 0) {
            $concentracao     = self::CONCENTRACAO_MISTURA_G_POR_ML * ($volumeSolucaoInicialMl / $volumeSolucaoFinalMl);
            $concentracaoReal = true;
        }

        $mlPorLitro   = $gramasPorLitro / $concentracao;
        $distribuicao = [];
        foreach (self::TAMANHOS_GARRAFA as $ml) {
            $distribuicao[] = [
                'tamanho'    => $ml,
                'ml_solucao' => round($mlPorLitro * $ml / 1000, 1),
            ];
        }

        $resultado['ml_por_litro']          = round($mlPorLitro, 1);
        $resultado['concentracao_g_por_ml'] = round($concentracao, 4);
        $resultado['concentracao_real']     = $concentracaoReal;
        $resultado['distribuicao']          = $distribuicao;

        return $resultado;
    }
}