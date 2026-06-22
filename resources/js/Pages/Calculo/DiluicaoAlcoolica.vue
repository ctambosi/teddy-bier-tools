<script setup>
import { reactive, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'

const form = reactive({ quantidade: '', graduacao_alcool: '', graduacao_desejada: '', unidade: 'L' })
const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

async function calcular() {
    erroGeral.value = ''
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('diluicao-alcoolica.calcular'), form)
        resultado.value = data
    } catch (e) {
        const erros = e.response?.data?.errors ?? {}
        erroGeral.value = erros.graduacao_desejada?.[0]
            ?? erros.geral?.[0]
            ?? Object.values(erros)[0]?.[0]
            ?? 'Erro ao calcular. Verifique os valores informados.'
    } finally {
        loading.value = false
    }
}

function limpar() {
    form.quantidade = form.graduacao_alcool = form.graduacao_desejada = ''
    form.unidade = 'L'
    resultado.value = null
    erroGeral.value = ''
}

function fmt(val) {
    return parseFloat(val).toFixed(3)
}
</script>

<template>
    <Head title="Diluição de Álcool" />
    <AppLayout>
        <CalculadoraCard
            titulo="Diluição de Álcool"
            descricao="Calcula quanta água adicionar para reduzir a graduação alcoólica."
        >
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Quantidade de álcool
                        </label>
                        <input
                            v-model="form.quantidade"
                            type="text"
                            inputmode="decimal"
                            placeholder="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        />
                    </div>
                    <div class="w-24">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidade</label>
                        <select
                            v-model="form.unidade"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        >
                            <option value="L">L</option>
                            <option value="ml">ml</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Graduação do álcool (%)
                    </label>
                    <input
                        v-model="form.graduacao_alcool"
                        type="text"
                        inputmode="decimal"
                        placeholder="40"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Graduação desejada (%)
                    </label>
                    <input
                        v-model="form.graduacao_desejada"
                        type="text"
                        inputmode="decimal"
                        placeholder="20"
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
                        <span class="text-sm text-gray-600">Água a adicionar</span>
                        <span class="text-lg font-bold text-green-800">{{ fmt(resultado.volume_adicionar) }} {{ resultado.unidade }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Volume total resultante</span>
                        <span class="text-lg font-bold text-green-800">{{ fmt(resultado.volume_total) }} {{ resultado.unidade }}</span>
                    </div>
                </div>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
