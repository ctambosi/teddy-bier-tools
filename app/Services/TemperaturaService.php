<?php

declare(strict_types=1);

namespace App\Services;

class TemperaturaService
{
    public static function celsiusParaFahrenheit(float $c): float
    {
        return $c * 1.8 + 32;
    }

    public static function fahrenheitParaCelsius(float $f): float
    {
        return ($f - 32) / 1.8;
    }

    public static function calcularTemperatura(array $input): array
    {
        if ($input['celsius'] !== null) {
            $c = (float) $input['celsius'];
            return [
                'celsius'         => $c,
                'fahrenheit'      => self::celsiusParaFahrenheit($c),
                'campo_informado' => 'celsius',
            ];
        }

        $f = (float) $input['fahrenheit'];
        return [
            'celsius'         => self::fahrenheitParaCelsius($f),
            'fahrenheit'      => $f,
            'campo_informado' => 'fahrenheit',
        ];
    }
}