<script setup>
import { reactive, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'

const form = reactive({ celulas: '', concentracao: '2' })
const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

async function calcular() {
    erroGeral.value = ''
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('calculo.levedura.calcular'), form)
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
    form.celulas = ''
    form.concentracao = '2'
    resultado.value = null
    erroGeral.value = ''
}
</script>

<template>
    <Head title="Levedura por Peso" />
    <AppLayout>
        <CalculadoraCard
            titulo="Levedura por Peso"
            descricao="Calcula quantos gramas de lama de levedura usar para atingir a quantidade desejada de células."
        >
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Bilhões de células necessários
                    </label>
                    <input
                        v-model="form.celulas"
                        type="text"
                        inputmode="decimal"
                        placeholder="200"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">Ex: 200 bilhões de células para um lote de 20L</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Concentração da lama (bilhões/mL)
                    </label>
                    <input
                        v-model="form.concentracao"
                        type="text"
                        inputmode="decimal"
                        placeholder="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">Padrão de slurry fresco ≈ 2 bilhões/mL</p>
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

            <div v-if="resultado !== null" class="mt-5 p-4 rounded-lg bg-green-50 border border-green-200 text-center">
                <p class="text-sm text-green-700 font-medium mb-1">Quantidade de lama necessária</p>
                <p class="text-4xl font-bold text-green-800">{{ resultado.gramas }} g</p>
                <p class="text-xs text-green-600 mt-1">Equivalente a {{ (resultado.gramas / 1000).toFixed(3) }} kg</p>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
