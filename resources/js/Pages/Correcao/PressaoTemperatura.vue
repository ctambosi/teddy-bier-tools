<script setup>
import { reactive, ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'

const form = reactive({ volume_co2: '', temperatura: '' })
const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

// Se a pressão calculada for negativa, a carbonatação já ocorre naturalmente
const pressaoNegativa = computed(() => resultado.value && resultado.value.barRaw < 0)

async function calcular() {
    erroGeral.value = ''
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('correcao.pressao-temperatura.calcular'), form)
        resultado.value = {
            bar: parseFloat(data.bar).toFixed(2),
            psi: Math.round(parseFloat(data.psi)),
            barRaw: parseFloat(data.bar),
        }
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
    form.volume_co2 = form.temperatura = ''
    resultado.value = null
    erroGeral.value = ''
}
</script>

<template>
    <Head title="Pressão × Temperatura" />
    <AppLayout>
        <CalculadoraCard
            titulo="Pressão × Temperatura"
            descricao="Calcula a pressão necessária para carbonatação forçada (serving pressure)."
        >
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">

                <!-- Volume de CO₂ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Volume de CO₂ desejado (vols)
                    </label>
                    <input
                        v-model="form.volume_co2"
                        type="text"
                        inputmode="decimal"
                        placeholder="2.4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">
                        Referência: Lager ≈ 2.2 | Ale ≈ 2.4 | Weizen ≈ 3.5
                    </p>
                </div>

                <!-- Temperatura da cerveja -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Temperatura da cerveja (°C)
                    </label>
                    <input
                        v-model="form.temperatura"
                        type="text"
                        inputmode="decimal"
                        placeholder="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">Temperatura real do líquido no barril/keg</p>
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

            <!-- Resultado: pressão negativa (não precisa de pressão) -->
            <div
                v-if="resultado !== null && pressaoNegativa"
                class="mt-5 p-4 rounded-lg bg-blue-50 border border-blue-200 text-center"
            >
                <p class="text-sm text-blue-700 font-medium">
                    A cerveja nessa temperatura já contém CO₂ suficiente de forma natural.
                    Nenhuma pressão positiva é necessária.
                </p>
            </div>

            <!-- Resultado: pressão calculada -->
            <div
                v-else-if="resultado !== null"
                class="mt-5 p-4 rounded-lg bg-green-50 border border-green-200"
            >
                <p class="text-sm text-green-700 font-medium text-center mb-3">
                    Regular a pressão para:
                </p>
                <div class="flex justify-center gap-8">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-green-800">{{ resultado.bar }}</p>
                        <p class="text-xs text-green-600 mt-1 uppercase tracking-wide">BAR</p>
                    </div>
                    <div class="w-px bg-green-200"></div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-green-800">{{ resultado.psi }}</p>
                        <p class="text-xs text-green-600 mt-1 uppercase tracking-wide">PSI</p>
                    </div>
                </div>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
