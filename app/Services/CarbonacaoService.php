<?php

declare(strict_types=1);

namespace App\Services;

class CarbonacaoService
{
    const PSI_POR_BAR = 14.5037738007;

    const FATORES_ACUCAR = [
        'sucrose'         => 2.0,
        'dextrose_mono'   => 2.09,
        'dextrose_anidra' => 1.89,
        'dme'             => 2.73,
        'mel'             => 2.69,
    ];

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
        return 3.0378 - (0.050062 * $tempC) + (0.00026555 * ($tempC ** 2));
    }

    public static function calcularPriming(
        float $volumeLitros,
        float $tempFermentacao,
        float $targetCO2,
        string $tipoAcucar,
        ?float $volumeSolucaoMl = null
    ): array {
        $co2Residual    = self::co2Residual($tempFermentacao);
        $co2Adicional   = max(0.0, $targetCO2 - $co2Residual);
        $fator          = self::FATORES_ACUCAR[$tipoAcucar] ?? 2.0;
        $gramas         = $co2Adicional * $volumeLitros * $fator;
        $gramasPorLitro = $volumeLitros > 0 ? $gramas / $volumeLitros : 0.0;

        $resultado = [
            'co2_residual'     => round($co2Residual, 3),
            'co2_adicional'    => round($co2Adicional, 3),
            'gramas'           => round($gramas, 1),
            'gramas_por_litro' => round($gramasPorLitro, 2),
        ];

        if ($volumeSolucaoMl !== null && $volumeSolucaoMl > 0 && $gramas > 0) {
            $mlPorLitro   = $volumeSolucaoMl / $volumeLitros;
            $concGPorMl   = $gramas / $volumeSolucaoMl;
            $tamanhos     = [250, 275, 310, 330, 355, 500, 600];
            $distribuicao = [];

            foreach ($tamanhos as $ml) {
                $distribuicao[] = [
                    'tamanho'    => $ml,
                    'ml_solucao' => round($mlPorLitro * $ml / 1000, 2),
                ];
            }

            $resultado['ml_por_litro']          = round($mlPorLitro, 2);
            $resultado['concentracao_g_por_ml'] = round($concGPorMl, 4);
            $resultado['distribuicao']           = $distribuicao;
        }

        return $resultado;
    }
}