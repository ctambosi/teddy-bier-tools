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
}
