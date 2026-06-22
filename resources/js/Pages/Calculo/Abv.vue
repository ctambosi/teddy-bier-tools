<script setup>
import { reactive, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import SgInput from '@/Components/SgInput.vue'

const form = reactive({ og: '', fg: '' })
const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

async function calcular() {
    erroGeral.value = ''
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('abv.calcular'), form)
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
    form.og = form.fg = ''
    resultado.value = null
    erroGeral.value = ''
}
</script>

<template>
    <Head title="Graduação Alcoólica (ABV)" />
    <AppLayout>
        <CalculadoraCard
            titulo="Graduação Alcoólica (ABV)"
            descricao="Calcula o teor alcoólico da cerveja a partir da densidade original e final."
        >
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Densidade Original (OG)
                    </label>
                    <SgInput
                        v-model="form.og"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Densidade Final (FG)
                    </label>
                    <SgInput
                        v-model="form.fg"
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

            <div v-if="resultado !== null" class="mt-5 p-4 rounded-lg bg-green-50 border border-green-200 text-center">
                <p class="text-sm text-green-700 font-medium mb-1">Teor alcoólico estimado</p>
                <p class="text-4xl font-bold text-green-800">{{ resultado.abv }}%</p>
                <p class="text-xs text-green-600 mt-1">ABV (Alcohol by Volume)</p>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
