<?php

namespace App\Services;

class CalculoService
{
    // Bilhões de células por grama de lama (densidade do slurry de levedura)
    const CELULAS_POR_GRAMA = 1.087;

    // ── ABV ───────────────────────────────────────────────────────────────────
    // Fórmula simplificada padrão do homebrewing.

    public static function calcularAbv(float $og, float $fg): float
    {
        return ($og - $fg) * 131;
    }

    // ── Extração a Frio ───────────────────────────────────────────────────────
    // Proporção 1,9 oz de água por grama de malte (baseada em 454 g = 1 lb).

    public static function calcularExtracaoFrio(float $gramas): float
    {
        return 1.9 * $gramas / 454; // resultado em litros
    }

    // ── Percentual de Açúcar ──────────────────────────────────────────────────
    // Calcula a densidade-alvo do grist (sem o açúcar), dado a OG final desejada
    // e o percentual de açúcar na receita.

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

    // ── Levedura por Peso ─────────────────────────────────────────────────────
    // Determina quantos gramas de lama de levedura usar.
    // concentracao = bilhões de células por mL de lama (padrão: 2).

    public static function calcularLevedura(float $celulas, float $concentracao): int
    {
        return (int) ceil($celulas * self::CELULAS_POR_GRAMA / $concentracao);
    }

    // ── Diluição de Álcool ────────────────────────────────────────────────────
    // Calcula quanta água adicionar para reduzir a graduação alcoólica.
    // Entradas e saídas sempre na mesma unidade informada (ml ou L).

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

    // ── Adição de Álcool ──────────────────────────────────────────────────────
    // Calcula quanto álcool adicionar para atingir uma graduação desejada.
    // Entradas e saídas sempre na mesma unidade informada (ml ou L).

    public static function calcularAdicaoAlcoolica(
        float $volumeBase,
        float $graduacaoAlcool,
        float $graduacaoDesejada
    ): float {
        return ($volumeBase * $graduacaoDesejada) / ($graduacaoAlcool - $graduacaoDesejada);
    }

    // ── Polias (Motorizar Moedor) ─────────────────────────────────────────────
    // Equação de polias: n1 × D1 = n2 × D2
    // Diâmetros devem ser passados na mesma unidade (normalizados pelo caller).

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

    // ── Volume de Mosto ───────────────────────────────────────────────────────
    // Panela cilíndrica: V (litros) = π × r² × h / 1000 − perda
    // Todas as medidas em centímetros; perda em litros.

    public static function calcularVolumeMosto(
        float $diametro,
        float $alturaLiquido,
        float $perda = 0.0
    ): float {
        $raio   = $diametro / 2;
        $volume = M_PI * ($raio ** 2) * $alturaLiquido / 1000;
        return round(max(0.0, $volume - $perda), 2);
    }

    // ── Priming ───────────────────────────────────────────────────────────────
    // Fórmula de CO₂ residual: Brewer's Friend (empírica, T em °C).
    // Fatores de açúcar: gramas por litro por volume de CO₂.

    const FATORES_ACUCAR = [
        'sucrose'        => 2.0,   // açúcar refinado/cristal
        'dextrose_mono'  => 2.09,  // dextrose monohidratada
        'dextrose_anidra'=> 1.89,  // dextrose anidra
        'dme'            => 2.73,  // extrato seco de malte
        'mel'            => 2.69,  // mel (≈75% fermentável)
    ];

    public static function co2Residual(float $tempC): float
    {
        return 3.0378 - (0.050062 * $tempC) + (0.00026555 * ($tempC ** 2));
    }

    public static function calcularPriming(
        float $volumeLitros,
        float $tempFermentacao,
        float $targetCO2,
        string $tipoAcucar,
        ?float $volumeSolucaoMl = null
    ): array {
        $co2Residual  = self::co2Residual($tempFermentacao);
        $co2Adicional = max(0.0, $targetCO2 - $co2Residual);
        $fator        = self::FATORES_ACUCAR[$tipoAcucar] ?? 2.0;
        $gramas       = $co2Adicional * $volumeLitros * $fator;
        $gramasPorLitro = $volumeLitros > 0 ? $gramas / $volumeLitros : 0.0;

        $resultado = [
            'co2_residual'     => round($co2Residual, 3),
            'co2_adicional'    => round($co2Adicional, 3),
            'gramas'           => round($gramas, 1),
            'gramas_por_litro' => round($gramasPorLitro, 2),
        ];

        if ($volumeSolucaoMl !== null && $volumeSolucaoMl > 0 && $gramas > 0) {
            $mlPorLitro     = $volumeSolucaoMl / $volumeLitros;
            $concGPorMl     = $gramas / $volumeSolucaoMl;
            $tamanhos       = [250, 275, 310, 330, 355, 500, 600];
            $distribuicao   = [];

            foreach ($tamanhos as $ml) {
                $distribuicao[] = [
                    'tamanho'    => $ml,
                    'ml_solucao' => round($mlPorLitro * $ml / 1000, 2),
                ];
            }

            $resultado['ml_por_litro']           = round($mlPorLitro, 2);
            $resultado['concentracao_g_por_ml']  = round($concGPorMl, 4);
            $resultado['distribuicao']            = $distribuicao;
        }

        return $resultado;
    }
}
