<?php

declare(strict_types=1);

namespace App\Services;

class AlcoolService
{
    public static function calcularAbv(float $og, float $fg): float
    {
        return ($og - $fg) * 131;
    }

    public static function calcularDiluicaoAlcoolica(
        float $quantidade,
        float $graduacaoAlcool,
        float $graduacaoDesejada
    ): array {
        $volumeTotal     = $quantidade * $graduacaoAlcool / $graduacaoDesejada;
        $volumeAdicionar = $volumeTotal - $quantidade;

        return [
            'volume_adicionar' => $volumeAdicionar,
            'volume_total'     => $volumeTotal,
        ];
    }

    public static function calcularAdicaoAlcoolica(
        float $volumeBase,
        float $graduacaoAlcool,
        float $graduacaoDesejada
    ): float {
        return ($volumeBase * $graduacaoDesejada) / ($graduacaoAlcool - $graduacaoDesejada);
    }
}