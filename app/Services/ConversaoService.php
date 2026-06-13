<?php

namespace App\Services;

class ConversaoService
{
    const PSI_POR_BAR = 14.5037738007;

    // ── Densidade ─────────────────────────────────────────────────────────────

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

        // plato garantido não-nulo pela validação
        $sg = self::platoParaSg($input['plato']);
        return [
            'sg'              => $sg,
            'brix'            => self::sgParaBrix($sg),
            'plato'           => $input['plato'],
            'campo_informado' => 'plato',
        ];
    }

    // ── Temperatura ───────────────────────────────────────────────────────────

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
            return [
                'celsius'         => $input['celsius'],
                'fahrenheit'      => self::celsiusParaFahrenheit($input['celsius']),
                'campo_informado' => 'celsius',
            ];
        }

        return [
            'celsius'         => self::fahrenheitParaCelsius($input['fahrenheit']),
            'fahrenheit'      => $input['fahrenheit'],
            'campo_informado' => 'fahrenheit',
        ];
    }

    // ── Cor ───────────────────────────────────────────────────────────────────
    // EBC ↔ SRM: SRM = EBC * 0.508  /  EBC = SRM * 1.97
    // SRM ↔ Lovibond: Lov = (SRM + 0.76) / 1.3546  /  SRM = 1.3546 * Lov - 0.76

    public static function calcularCor(array $input): array
    {
        if ($input['ebc'] !== null) {
            $srm = $input['ebc'] * 0.508;
            return [
                'ebc'             => $input['ebc'],
                'srm'             => $srm,
                'lovibond'        => ($srm + 0.76) / 1.3546,
                'campo_informado' => 'ebc',
            ];
        }

        if ($input['srm'] !== null) {
            $srm = $input['srm'];
            return [
                'ebc'             => $srm * 1.97,
                'srm'             => $srm,
                'lovibond'        => ($srm + 0.76) / 1.3546,
                'campo_informado' => 'srm',
            ];
        }

        $srm = 1.3546 * $input['lovibond'] - 0.76;
        return [
            'ebc'             => $srm * 1.97,
            'srm'             => $srm,
            'lovibond'        => $input['lovibond'],
            'campo_informado' => 'lovibond',
        ];
    }

    // ── Pressão ───────────────────────────────────────────────────────────────

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
}
