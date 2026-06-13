<script setup>
import { reactive, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import SgInput from '@/Components/SgInput.vue'

const form = reactive({
    sg:          '',
    temperatura: '',
    calibracao:  '20',
})
const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

async function calcular() {
    erroGeral.value = ''
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('correcao.densimetro.calcular'), form)
        resultado.value = parseFloat(data.sg_corrigido).toFixed(3)
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
    form.sg = form.temperatura = ''
    form.calibracao = '20'
    resultado.value = null
    erroGeral.value = ''
}
</script>

<template>
    <Head title="Correção de Densímetro" />
    <AppLayout>
        <CalculadoraCard
            titulo="Correção de Densímetro"
            descricao="Corrige a leitura do densímetro em função da temperatura da amostra."
        >
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">

                <!-- SG medido -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Densidade medida (SG)
                    </label>
                    <SgInput
                        v-model="form.sg"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">Digite os 4 dígitos. Ex.: 1048 → 1.048</p>
                </div>

                <!-- Temperatura da amostra -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Temperatura da amostra (°C)
                    </label>
                    <input
                        v-model="form.temperatura"
                        type="text"
                        inputmode="decimal"
                        placeholder="25"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                </div>

                <!-- Temperatura de calibração -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Densímetro calibrado a
                    </label>
                    <select
                        v-model="form.calibracao"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    >
                        <option value="20">20 °C (mais comum no Brasil)</option>
                        <option value="15">15 °C (padrão OIML)</option>
                    </select>
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

            <!-- Resultado -->
            <div
                v-if="resultado !== null"
                class="mt-5 p-4 rounded-lg bg-green-50 border border-green-200 text-center"
            >
                <p class="text-sm text-green-700 mb-1">Densidade corrigida</p>
                <p class="text-3xl font-bold text-green-800 tracking-wide">{{ resultado }}</p>
                <p class="text-xs text-green-600 mt-1">SG</p>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
