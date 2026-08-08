<script setup>
import { computed, reactive, ref } from 'vue'
import { Head, usePage  } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import SgInput from '@/Components/SgInput.vue'
import { useDecimalInput } from '@/composables/useDecimalInput'

const form = reactive({ og: '' })
const page = usePage()
const meta = computed(() => page.props.meta || {})

const percentual = useDecimalInput()
const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

async function calcular() {
    erroGeral.value = ''
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('percentual-acucar.calcular'), {
            og: form.og,
            percentual: percentual.numeric.value,
        })
        resultado.value = data
    } catch (e) {
        const erros = e.response?.data?.errors ?? {}
        erroGeral.value = erros.geral?.[0]
            ?? Object.values(erros)[0]?.[0]
            ?? 'Erro ao calcular. Verifique os valores informados.'
    } finally {
        loading.value = false
    }
}

function limpar() {
    form.og = ''
    percentual.reset()
    resultado.value = null
    erroGeral.value = ''
}

function sgDisplay(val) {
    return parseFloat(val).toFixed(3)
}
</script>

<template>
    <Head :title="meta.label" />
    <AppLayout>
        <CalculadoraCard
            :titulo="meta.label"
            :descricao="meta.description"
        >
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800 leading-relaxed">
                    O percentual de açúcar pode ser definido pela contribuição para a densidade original (OG), e não apenas pelo peso dos ingredientes.
                </p>
                <p class="text-sm text-blue-800 leading-relaxed mt-2">
                    Por exemplo, se você deseja uma OG de 1.058 e quer que 15% da densidade seja proveniente do açúcar, os 58 pontos de densidade da OG serão divididos entre açúcar e grist: 15% (8,7 pontos) virão do açúcar e 85% (49,3 pontos) virão do grist.
                </p>
                <p class="text-sm text-blue-800 leading-relaxed mt-2">
                    Isso é diferente de simplesmente adicionar 15% de açúcar em relação ao peso dos grãos. Como o açúcar possui maior potencial de contribuição para a densidade do que o malte, 15% em peso não corresponde necessariamente a 15% dos pontos de OG.
                </p>
                <p class="text-sm text-blue-800 leading-relaxed mt-2">
                    Use esta ferramenta para determinar qual parcela da OG será fornecida pelo açúcar e qual parcela será fornecida pelo grist.
                </p>
            </div>

            <form @submit.prevent="calcular" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Densidade original (OG)
                    </label>
                    <SgInput
                        v-model="form.og"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Percentual da OG proveniente do açúcar (%)
                    </label>
                    <input
                        :value="percentual.display.value"
                        @input="percentual.onInput"
                        type="text"
                        inputmode="decimal"
                        placeholder="15,0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="loading"
                        class="flex-1 bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white font-semibold py-2 px-4 rounded-lg text-sm transition"
                    >
                        {{ loading ? 'Calculando…' : 'Calcular' }}
                    </button>
                    <button
                        type="button"
                        @click="limpar"
                        class="px-4 py-2 border border-gray-300 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition"
                    >
                        Limpar
                    </button>
                </div>
            </form>

            <div v-if="resultado !== null" class="mt-5 p-4 rounded-lg bg-green-50 border border-green-200">
                <p class="text-sm text-green-700 font-medium text-center mb-3">Resultado</p>
                <div class="space-y-2">
                    <div class="flex justify-between items-center py-2 border-b border-green-100">
                        <span class="text-sm text-gray-600">Contribuição do grist para a OG</span>
                        <span class="text-lg font-bold text-green-800">{{ sgDisplay(resultado.densidade_grist) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-green-100">
                        <span class="text-sm text-gray-600">Contribuição do açúcar para a OG</span>
                        <span class="text-lg font-bold text-green-800">{{ sgDisplay(resultado.densidade_acucar) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">OG final</span>
                        <span class="text-lg font-bold text-green-800">{{ sgDisplay(resultado.og) }}</span>
                    </div>
                </div>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
