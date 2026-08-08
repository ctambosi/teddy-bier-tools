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
        $pontosOG = $og - 1;
        $pontosAcucar = $pontosOG * $percentual / 100;
        $pontosGrist = $pontosOG - $pontosAcucar;

        $densidadeAcucar = 1 + $pontosAcucar;
        $densidadeGrist = 1 + $pontosGrist;
        $ogFinal = 1 + $pontosAcucar + $pontosGrist;

        return [
            'densidade_grist'  => $densidadeGrist,
            'densidade_acucar' => $densidadeAcucar,
            'og'               => $ogFinal,
        ];
    }
}
