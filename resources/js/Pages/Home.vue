<script setup>
import { Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'

const categories = [
    {
        title: 'Correções',
        color: 'amber',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        tools: [
            {
                label: 'Densímetro',
                description: 'Corrige a leitura do densímetro em função da temperatura da amostra.',
                href: route('correcao.densimetro'),
            },
            {
                label: 'Refratômetro',
                description: 'Corrige a leitura do refratômetro após o início da fermentação (fórmula de Novotný).',
                href: route('correcao.refratometro'),
            },
            {
                label: 'Pressão × Temperatura',
                description: 'Calcula a pressão necessária para carbonatação forçada (serving pressure).',
                href: route('correcao.pressao-temperatura'),
            },
        ],
    },
    {
        title: 'Conversões',
        color: 'blue',
        icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
        tools: [
            {
                label: 'Densidade',
                description: 'Converte entre SG, Brix e Plato em ambas as direções.',
                href: route('conversao.densidade'),
            },
            {
                label: 'Temperatura',
                description: 'Converte entre graus Celsius e Fahrenheit.',
                href: route('conversao.temperatura'),
            },
            {
                label: 'Cor',
                description: 'Converte entre EBC, SRM e Lovibond.',
                href: route('conversao.cor'),
            },
            {
                label: 'Pressão',
                description: 'Converte entre BAR e PSI.',
                href: route('conversao.pressao'),
            },
        ],
    },
    {
        title: 'Cálculos',
        color: 'green',
        icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z',
        tools: [
            {
                label: 'Graduação Alcoólica (ABV)',
                description: 'Calcula o teor alcoólico a partir da OG e FG.',
                href: route('calculo.abv'),
            },
            {
                label: 'Priming',
                description: 'Calcula o açúcar de priming com correção por CO₂ residual e distribuição por garrafa.',
                href: route('calculo.priming'),
            },
            {
                label: 'Volume de Mosto',
                description: 'Calcula o volume de líquido em uma panela cilíndrica. Salva suas panelas localmente.',
                href: route('calculo.volume-mosto'),
            },
            {
                label: 'Levedura por Peso',
                description: 'Calcula a quantidade de lama de levedura em gramas para uma pitching rate alvo.',
                href: route('calculo.levedura'),
            },
            {
                label: 'Extração a Frio',
                description: 'Calcula o volume de água para extração a frio de lúpulo.',
                href: route('calculo.extracao-frio'),
            },
            {
                label: 'Percentual de Açúcar',
                description: 'Calcula a densidade-alvo do grist dado o percentual de açúcar na receita.',
                href: route('calculo.percentual-acucar'),
            },
            {
                label: 'Diluição de Álcool',
                description: 'Calcula a água a adicionar para reduzir a graduação alcoólica.',
                href: route('calculo.diluicao-alcoolica'),
            },
            {
                label: 'Adição de Álcool',
                description: 'Calcula o álcool a adicionar para elevar a graduação de uma base.',
                href: route('calculo.adicao-alcoolica'),
            },
            {
                label: 'Motorizar Moedor',
                description: 'Calcula a quarta variável da equação de polias: n₁ × D₁ = n₂ × D₂.',
                href: route('calculo.motor'),
            },
        ],
    },
]

const colorMap = {
    amber: {
        header:   'bg-amber-600 text-white',
        card:     'hover:border-amber-300 hover:shadow-md hover:shadow-amber-100',
        iconBg:   'bg-amber-100',
        iconText: 'text-amber-600',
        badge:    'bg-amber-50 text-amber-700 border-amber-200',
    },
    blue: {
        header:   'bg-blue-600 text-white',
        card:     'hover:border-blue-300 hover:shadow-md hover:shadow-blue-100',
        iconBg:   'bg-blue-100',
        iconText: 'text-blue-600',
        badge:    'bg-blue-50 text-blue-700 border-blue-200',
    },
    green: {
        header:   'bg-green-700 text-white',
        card:     'hover:border-green-300 hover:shadow-md hover:shadow-green-100',
        iconBg:   'bg-green-100',
        iconText: 'text-green-700',
        badge:    'bg-green-50 text-green-700 border-green-200',
    },
}
</script>

<template>
    <Head title="Início" />
    <AppLayout>

        <!-- Hero -->
        <div class="mb-10 text-center py-6">
            <div class="text-5xl mb-3">🍺</div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Teddy Bier Tools</h1>
            <p class="text-gray-500 text-base max-w-md mx-auto">
                Ferramentas de cálculo para produção de cerveja.
            </p>
        </div>

        <!-- Categorias -->
        <div class="space-y-8">
            <section v-for="category in categories" :key="category.title">

                <!-- Cabeçalho -->
                <div :class="colorMap[category.color].header" class="rounded-t-xl px-5 py-3 flex items-center gap-3">
                    <svg class="w-5 h-5 opacity-90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="category.icon" />
                    </svg>
                    <h2 class="font-semibold text-lg">{{ category.title }}</h2>
                    <span class="ml-auto text-xs font-normal opacity-75">
                        {{ category.tools.length }} ferramenta{{ category.tools.length > 1 ? 's' : '' }}
                    </span>
                </div>

                <!-- Grid de ferramentas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 border border-t-0 border-gray-200 rounded-b-xl p-4 bg-white">
                    <a
                        v-for="tool in category.tools"
                        :key="tool.href"
                        :href="tool.href"
                        class="group flex items-start gap-3 border border-gray-200 rounded-lg p-4 transition-all duration-150"
                        :class="colorMap[category.color].card"
                    >
                        <div :class="[colorMap[category.color].iconBg, colorMap[category.color].iconText]"
                             class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="category.icon" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 text-sm leading-snug">{{ tool.label }}</p>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ tool.description }}</p>
                        </div>
                    </a>
                </div>
            </section>
        </div>

        <!-- Rodapé informativo -->
        <p class="mt-10 text-center text-xs text-gray-400">
            Todos os cálculos são realizados no servidor. Nenhum dado é armazenado permanentemente.
        </p>

    </AppLayout>
</template>
