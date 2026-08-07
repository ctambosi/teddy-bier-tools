<?php

declare(strict_types=1);

namespace App\Data;

class ToolsMetadata
{
    public static function all(): array
    {
        return [
            'densimetro' => [
                'label' => 'Densímetro',
                'description' => 'Corrige a leitura do densímetro em função da temperatura da amostra.',
                'categoria' => 'Correções',
            ],
            'refratometro' => [
                'label' => 'Refratômetro',
                'description' => 'Corrige a leitura do refratômetro após o início da fermentação (fórmula de Novotný).',
                'categoria' => 'Correções',
            ],
            'pressao-temperatura' => [
                'label' => 'Pressão × Temperatura',
                'description' => 'Calcula a pressão necessária para carbonatação forçada (serving pressure).',
                'categoria' => 'Correções',
            ],
            'densidade' => [
                'label' => 'Densidade',
                'description' => 'Converte entre SG, Brix e Plato em ambas as direções.',
                'categoria' => 'Conversões',
            ],
            'temperatura' => [
                'label' => 'Temperatura',
                'description' => 'Converte entre graus Celsius e Fahrenheit.',
                'categoria' => 'Conversões',
            ],
            'cor' => [
                'label' => 'Cor',
                'description' => 'Converte entre EBC, SRM e Lovibond.',
                'categoria' => 'Conversões',
            ],
            'pressao' => [
                'label' => 'Pressão',
                'description' => 'Converte entre BAR e PSI.',
                'categoria' => 'Conversões',
            ],
            'abv' => [
                'label' => 'Graduação Alcoólica (ABV)',
                'description' => 'Calcula o teor alcoólico a partir da OG e FG.',
                'categoria' => 'Cálculos',
            ],
            'priming' => [
                'label' => 'Priming',
                'description' => 'Calcula o açúcar de priming com correção por CO₂ residual e distribuição por garrafa.',
                'categoria' => 'Cálculos',
            ],
            'volume-mosto' => [
                'label' => 'Volume de Mosto',
                'description' => 'Calcula o volume de líquido em uma panela cilíndrica. Salva suas panelas localmente.',
                'categoria' => 'Cálculos',
            ],
            'levedura' => [
                'label' => 'Levedura por Peso',
                'description' => 'Calcula a quantidade de lama de levedura em gramas para uma pitching rate alvo.',
                'categoria' => 'Cálculos',
            ],
            'extracao-frio' => [
                'label' => 'Extração a Frio',
                'description' => 'Calcula o volume de água para extração a frio de lúpulo.',
                'categoria' => 'Cálculos',
            ],
            'percentual-acucar' => [
                'label' => 'Percentual de Açúcar',
                'description' => 'Calcula a densidade-alvo do grist dado o percentual de açúcar na receita.',
                'categoria' => 'Cálculos',
            ],
            'diluicao-alcoolica' => [
                'label' => 'Diluição de Álcool',
                'description' => 'Calcula a água a adicionar para reduzir a graduação alcoólica.',
                'categoria' => 'Cálculos',
            ],
            'adicao-alcoolica' => [
                'label' => 'Adição de Álcool',
                'description' => 'Calcula o álcool a adicionar para elevar a graduação de uma base.',
                'categoria' => 'Cálculos',
            ],
            'motor' => [
                'label' => 'Motorizar Moedor',
                'description' => 'Calcula a quarta variável da equação de polias: n₁ × D₁ = n₂ × D₂.',
                'categoria' => 'Cálculos',
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function grouped(): array
    {
        $grouped = [];
        foreach (self::all() as $key => $tool) {
            $categoria = $tool['categoria'];
            if (!isset($grouped[$categoria])) {
                $grouped[$categoria] = [];
            }
            $grouped[$categoria][$key] = $tool;
        }
        return $grouped;
    }
}
