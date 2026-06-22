<?php

declare(strict_types=1);

namespace App\Services;

class EquipamentoService
{
    public static function calcularVolumeMosto(
        float $diametro,
        float $alturaLiquido,
        float $perda = 0.0
    ): float {
        $raio   = $diametro / 2;
        $volume = M_PI * ($raio ** 2) * $alturaLiquido / 1000;
        return round(max(0.0, $volume - $perda), 2);
    }

    public static function calcularPolia(
        ?float $n1,
        ?float $d1,
        ?float $n2,
        ?float $d2
    ): array {
        if ($n1 === null) {
            return ['campo' => 'n1', 'valor' => (int) round(($n2 * $d2) / $d1), 'unidade' => 'RPM'];
        }
        if ($d1 === null) {
            return ['campo' => 'd1', 'valor' => round(($n2 * $d2) / $n1, 1), 'unidade' => 'mm'];
        }
        if ($n2 === null) {
            return ['campo' => 'n2', 'valor' => (int) round(($n1 * $d1) / $d2), 'unidade' => 'RPM'];
        }
        return ['campo' => 'd2', 'valor' => round(($n1 * $d1) / $n2, 1), 'unidade' => 'mm'];
    }
}