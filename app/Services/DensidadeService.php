<?php

declare(strict_types=1);

namespace App\Services;

class DensidadeService
{
    public static function sgParaBrix(float $sg): float
    {
        return ((182.4601 * $sg - 775.6821) * $sg + 1262.7794) * $sg - 669.5622;
    }

    public static function brixParaSg(float $brix): float
    {
        return ($brix / (258.6 - ($brix / 258.2) * 227.1)) + 1;
    }

    public static function sgParaPlato(float $sg): float
    {
        return -616.868 + 1111.14 * $sg - 630.272 * $sg ** 2 + 135.997 * $sg ** 3;
    }

    public static function platoParaSg(float $plato): float
    {
        return 1 + ($plato / (258.6 - ($plato / 258.2) * 227.1));
    }

    public static function calcularDensidade(array $input): array
    {
        if ($input['sg'] !== null) {
            $sg = $input['sg'];
            return [
                'sg'              => $sg,
                'brix'            => self::sgParaBrix($sg),
                'plato'           => self::sgParaPlato($sg),
                'campo_informado' => 'sg',
            ];
        }

        if ($input['brix'] !== null) {
            $sg = self::brixParaSg($input['brix']);
            return [
                'sg'              => $sg,
                'brix'            => $input['brix'],
                'plato'           => self::sgParaPlato($sg),
                'campo_informado' => 'brix',
            ];
        }

        $sg = self::platoParaSg($input['plato']);
        return [
            'sg'              => $sg,
            'brix'            => self::sgParaBrix($sg),
            'plato'           => $input['plato'],
            'campo_informado' => 'plato',
        ];
    }

    public static function corrigirDensimetro(
        float $sg,
        float $tempCelsius,
        float $calibracaoCelsius = 20.0
    ): float {
        $tempF       = $tempCelsius * 1.8 + 32;
        $calibracaoF = $calibracaoCelsius * 1.8 + 32;

        return $sg * (self::fatorNBS($tempF) / self::fatorNBS($calibracaoF));
    }

    public static function corrigirRefratometro(float $og, float $leituraAtual): float
    {
        $ogBrix = self::sgParaBrix($og);
        $cgBrix = self::sgParaBrix($leituraAtual);

        return 0.00628 * $cgBrix - 0.0025 * $ogBrix + 1.0013;
    }

    private static function fatorNBS(float $fahrenheit): float
    {
        return 1.00130346
            - 0.000134722124   * $fahrenheit
            + 0.00000204052596 * $fahrenheit ** 2
            - 0.00000000232820948 * $fahrenheit ** 3;
    }
}