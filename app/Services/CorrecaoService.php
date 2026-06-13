<?php

namespace App\Services;

class CorrecaoService
{
    // ── Densímetro ────────────────────────────────────────────────────────────
    // Fórmula de razão NBS/NIST — mais precisa que o polinômio aditivo do projeto antigo.
    // Mantém a temperatura de calibração padrão em 20 °C (igual ao projeto antigo).
    //
    // Ref: Handbook of Chemistry and Physics, National Bureau of Standards

    private static function fatorNBS(float $fahrenheit): float
    {
        return 1.00130346
            - 0.000134722124   * $fahrenheit
            + 0.00000204052596 * $fahrenheit ** 2
            - 0.00000000232820948 * $fahrenheit ** 3;
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

    // ── Refratômetro (Novotný) ─────────────────────────────────────────────────
    // Corrige a leitura do refratômetro durante a fermentação, quando o álcool
    // interfere no índice de refração.
    //
    // Fórmula: SG = 0.00628 × CG_brix − 0.0025 × OG_brix + 1.0013
    // Fonte: Novotný (1997), citado em Zymurgy magazine

    public static function corrigirRefratometro(float $og, float $leituraAtual): float
    {
        $ogBrix    = ConversaoService::sgParaBrix($og);
        $cgBrix    = ConversaoService::sgParaBrix($leituraAtual);

        return 0.00628 * $cgBrix - 0.0025 * $ogBrix + 1.0013;
    }

    // ── Pressão × Temperatura (carbonatação forçada) ──────────────────────────
    // Fórmula original (direta):
    //   Vols = (P × 14.50377 + 14.695) × [0.01821 + 0.090115 × e^(−T×1.8/43.11)] − 0.003342
    // P em BAR, T em °C.
    //
    // Aqui fazemos o "desenrolar" da equação para isolar P dado o volume desejado.

    public static function calcularPressaoCarbonatacao(float $volumeCO2, float $tempCelsius): array
    {
        $passo1 = 0.01821 + 0.090115 * exp(-($tempCelsius * 1.8 / 43.11));
        $passo2 = $passo1 * 14.50377;
        $passo3 = $passo1 * 14.695 - 0.003342;
        $bar    = ($volumeCO2 - $passo3) / $passo2;

        return [
            'bar' => $bar,
            'psi' => $bar * ConversaoService::PSI_POR_BAR,
        ];
    }
}
