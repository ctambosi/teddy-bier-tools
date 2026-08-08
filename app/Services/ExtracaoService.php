<?php

declare(strict_types=1);

namespace App\Services;

class ExtracaoService
{
    public static function calcularExtracaoFrio(float $gramas): float
    {
        return 1.9 * $gramas / 454;
    }
}
