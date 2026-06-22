<?php

declare(strict_types=1);

namespace App\Services;

class LeveduraService
{
    const CELULAS_POR_GRAMA = 1.087;

    public static function calcularLevedura(float $celulas, float $concentracao): int
    {
        return (int) ceil($celulas * self::CELULAS_POR_GRAMA / $concentracao);
    }

    public static function calcularExtracaoFrio(float $gramas): float
    {
        return 1.9 * $gramas / 454;
    }

    public static function calcularPercentualAcucar(float $og, float $percentual): array
    {
        $densidadeDoAcucar = 1 + ($og - 1) * $percentual / 100;
        $densidadeDoGrist  = 1 + ($og - $densidadeDoAcucar);

        return [
            'densidade_grist'  => $densidadeDoGrist,
            'densidade_acucar' => $densidadeDoAcucar,
            'og'               => $og,
        ];
    }
}
