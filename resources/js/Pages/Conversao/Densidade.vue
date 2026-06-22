<script setup>
import { reactive, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import SgInput from '@/Components/SgInput.vue'
import { useDecimalInput } from '@/composables/useDecimalInput'

const form = reactive({ sg: '' })
const brix = useDecimalInput()
const plato = useDecimalInput()
const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

function aoDigitar(campo) {
    resultado.value = null
    erroGeral.value = ''
    if (campo !== 'sg') form.sg = ''
    if (campo !== 'brix') brix.reset()
    if (campo !== 'plato') plato.reset()
}

function aoDigitarBrix(e) {
    brix.onInput(e)
    aoDigitar('brix')
}

function aoDigitarPlato(e) {
    plato.onInput(e)
    aoDigitar('plato')
}

async function calcular() {
    erroGeral.value = ''
    loading.value = true
    try {
        const { data } = await axios.post(route('densidade.calcular'), {
            sg: form.sg,
            brix: brix.numeric.value,
            plato: plato.numeric.value,
        })
        resultado.value = data
        form.sg = parseFloat(data.sg).toFixed(3)
        brix.display.value = String(parseFloat(data.brix).toFixed(1)).replace('.', ',')
        brix.numeric.value = parseFloat(data.brix).toFixed(1)
        plato.display.value = String(parseFloat(data.plato).toFixed(1)).replace('.', ',')
        plato.numeric.value = parseFloat(data.plato).toFixed(1)
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
    form.sg = ''
    brix.reset()
    plato.reset()
    resultado.value = null
    erroGeral.value = ''
}

function eResultado(campo) {
    return resultado.value && resultado.value.campo_informado !== campo
}
</script>

<template>
    <Head title="Conversão de Densidade" />
    <AppLayout>
        <CalculadoraCard
            titulo="Conversão de Densidade"
            descricao="Preencha um dos campos e clique em Calcular."
        >
            <!-- Alerta de erro geral -->
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">

                <!-- SG -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Densidade (SG)
                    </label>
                    <SgInput
                        v-model="form.sg"
                        @input="aoDigitar('sg')"
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        :class="eResultado('sg') ? 'bg-amber-50 border-amber-300' : 'border-gray-300'"
                    />
                    <p class="mt-1 text-xs text-gray-400">Ex.: 1.048</p>
                </div>

                <!-- Brix -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brix</label>
                    <input
                        :value="brix.display.value"
                        @input="aoDigitarBrix"
                        type="text"
                        inputmode="numeric"
                        placeholder="12,0"
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        :class="eResultado('brix') ? 'bg-amber-50 border-amber-300' : 'border-gray-300'"
                    />
                </div>

                <!-- Plato -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plato (°P)</label>
                    <input
                        :value="plato.display.value"
                        @input="aoDigitarPlato"
                        type="text"
                        inputmode="numeric"
                        placeholder="11,9"
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        :class="eResultado('plato') ? 'bg-amber-50 border-amber-300' : 'border-gray-300'"
                    />
                </div>

                <!-- Botões -->
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

            <!-- Legenda de resultado -->
            <p v-if="resultado" class="mt-4 text-xs text-amber-700 text-center">
                Campos em destaque são os valores calculados.
            </p>
        </CalculadoraCard>
    </AppLayout>
</template>
