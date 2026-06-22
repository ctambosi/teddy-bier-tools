<script setup>
import { reactive, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import SgInput from '@/Components/SgInput.vue'

const form = reactive({ og: '', percentual: '' })
const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

async function calcular() {
    erroGeral.value = ''
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('percentual-acucar.calcular'), form)
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
    form.og = form.percentual = ''
    resultado.value = null
    erroGeral.value = ''
}

function sgDisplay(val) {
    return parseFloat(val).toFixed(3)
}
</script>

<template>
    <Head title="Percentual de Açúcar" />
    <AppLayout>
        <CalculadoraCard
            titulo="Percentual de Açúcar"
            descricao="Calcula a densidade-alvo do grist (malte) dado o percentual de açúcar na receita."
        >
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        OG desejada (densidade original)
                    </label>
                    <SgInput
                        v-model="form.og"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Percentual de açúcar na receita (%)
                    </label>
                    <input
                        v-model="form.percentual"
                        type="text"
                        inputmode="decimal"
                        placeholder="10"
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
                        <span class="text-sm text-gray-600">Densidade do grist (malte)</span>
                        <span class="text-lg font-bold text-green-800">{{ sgDisplay(resultado.densidade_grist) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-green-100">
                        <span class="text-sm text-gray-600">Densidade do açúcar</span>
                        <span class="text-lg font-bold text-green-800">{{ sgDisplay(resultado.densidade_acucar) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">OG final (com açúcar)</span>
                        <span class="text-lg font-bold text-green-800">{{ sgDisplay(resultado.og) }}</span>
                    </div>
                </div>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
