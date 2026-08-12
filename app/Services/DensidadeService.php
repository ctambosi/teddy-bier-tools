<?php

declare(strict_types=1);

namespace App\Services;

class DensidadeService
{
    // Potencial de extrato em pontos de densidade (GU = (SG-1)*1000) por kg
    // dissolvido em 1 litro do volume total do lote. Derivado do PPG
    // (points per pound per gallon) tradicional multiplicado por 8,3454
    // (fator de conversão lb/gal → kg/L): sacarose 46 PPG, extrato de malte
    // seco (DME) 44 PPG.
    const POTENCIAL_ACUCAR_GU_KG_L  = 384.0;
    const POTENCIAL_EXTRATO_GU_KG_L = 367.0;

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
            $sg = (float) $input['sg'];
            return [
                'sg'              => $sg,
                'brix'            => self::sgParaBrix($sg),
                'plato'           => self::sgParaPlato($sg),
                'campo_informado' => 'sg',
            ];
        }

        if ($input['brix'] !== null) {
            $brix = (float) $input['brix'];
            $sg   = self::brixParaSg($brix);
            return [
                'sg'              => $sg,
                'brix'            => $brix,
                'plato'           => self::sgParaPlato($sg),
                'campo_informado' => 'brix',
            ];
        }

        $plato = (float) $input['plato'];
        $sg    = self::platoParaSg($plato);
        return [
            'sg'              => $sg,
            'brix'            => self::sgParaBrix($sg),
            'plato'           => $plato,
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

    public static function calcularCorrecaoMosto(
        float $volume,
        float $densidadeAtual,
        float $densidadeDesejada
    ): array {
        if ($densidadeAtual > $densidadeDesejada) {
            $pontosAtual    = ($densidadeAtual - 1) * 1000;
            $pontosDesejado = ($densidadeDesejada - 1) * 1000;
            $volumeFinal    = $volume * $pontosAtual / $pontosDesejado;

            return [
                'tipo'                 => 'agua',
                'volume_agua_litros'   => $volumeFinal - $volume,
                'volume_final_litros'  => $volumeFinal,
            ];
        }

        $pontosNecessarios = ($densidadeDesejada - $densidadeAtual) * 1000;

        return [
            'tipo'       => 'fermentavel',
            'acucar_kg'  => $pontosNecessarios * $volume / self::POTENCIAL_ACUCAR_GU_KG_L,
            'extrato_kg' => $pontosNecessarios * $volume / self::POTENCIAL_EXTRATO_GU_KG_L,
        ];
    }

    private static function fatorNBS(float $fahrenheit): float
    {
        return 1.00130346
            - 0.000134722124   * $fahrenheit
            + 0.00000204052596 * $fahrenheit ** 2
            - 0.00000000232820948 * $fahrenheit ** 3;
    }
}