<script setup>
import { ref, computed } from 'vue'
import { Head, usePage  } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import { useDecimalInput } from '@/composables/useDecimalInput'

const page = usePage()
const meta = computed(() => page.props.meta || {})

const celsius    = useDecimalInput({ negativo: true })
const fahrenheit = useDecimalInput({ negativo: true })
const resultado  = ref(null)
const erroGeral  = ref('')
const loading    = ref(false)

function aoDigitar(campo) {
    resultado.value = null
    erroGeral.value = ''
    if (campo === 'celsius') fahrenheit.reset()
    else celsius.reset()
}

async function calcular() {
    erroGeral.value = ''
    loading.value = true
    console.log(celsius.numeric.value, fahrenheit.numeric.value)
    try {
        const { data } = await axios.post(route('temperatura.calcular'), {
            celsius:    celsius.numeric.value    || '',
            fahrenheit: fahrenheit.numeric.value || '',
        })
        console.log(data)
        resultado.value = data
        celsius.set(data.celsius)
        fahrenheit.set(data.fahrenheit)
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
    celsius.reset()
    fahrenheit.reset()
    resultado.value = null
    erroGeral.value = ''
}

function eResultado(campo) {
    return resultado.value && resultado.value.campo_informado !== campo
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

            <form @submit.prevent="calcular" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Celsius (°C)</label>
                    <input
                        :value="celsius.display.value"
                        @input="e => { celsius.onInput(e); aoDigitar('celsius') }"
                        type="text"
                        inputmode="decimal"
                        placeholder="20,0"
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        :class="eResultado('celsius') ? 'bg-amber-50 border-amber-300' : 'border-gray-300'"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fahrenheit (°F)</label>
                    <input
                        :value="fahrenheit.display.value"
                        @input="e => { fahrenheit.onInput(e); aoDigitar('fahrenheit') }"
                        type="text"
                        inputmode="decimal"
                        placeholder="68,0"
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        :class="eResultado('fahrenheit') ? 'bg-amber-50 border-amber-300' : 'border-gray-300'"
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

            <p v-if="resultado" class="mt-4 text-xs text-amber-700 text-center">
                Campos em destaque são os valores calculados.
            </p>
        </CalculadoraCard>
    </AppLayout>
</template>
