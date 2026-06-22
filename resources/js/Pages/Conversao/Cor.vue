<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import { useDecimalInput } from '@/composables/useDecimalInput'

const ebc      = useDecimalInput()
const srm      = useDecimalInput()
const lovibond = useDecimalInput()

const campos = { ebc, srm, lovibond }

const resultado  = ref(null)
const erroGeral  = ref('')
const loading    = ref(false)

function aoDigitar(campo) {
    resultado.value = null
    erroGeral.value = ''
    for (const c of ['ebc', 'srm', 'lovibond']) {
        if (c !== campo) campos[c].reset()
    }
}

async function calcular() {
    erroGeral.value = ''
    loading.value = true
    try {
        const { data } = await axios.post(route('cor.calcular'), {
            ebc:      ebc.numeric.value,
            srm:      srm.numeric.value,
            lovibond: lovibond.numeric.value,
        })
        resultado.value = data
        ebc.set(data.ebc)
        srm.set(data.srm)
        lovibond.set(data.lovibond)
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
    ebc.reset()
    srm.reset()
    lovibond.reset()
    resultado.value = null
    erroGeral.value = ''
}

function eResultado(campo) {
    return resultado.value && resultado.value.campo_informado !== campo
}
</script>

<template>
    <Head title="Conversão de Cor" />
    <AppLayout>
        <CalculadoraCard
            titulo="Conversão de Cor"
            descricao="Preencha um dos campos e clique em Calcular."
        >
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">EBC</label>
                    <input
                        :value="ebc.display.value"
                        @input="(e) => { ebc.onInput(e); aoDigitar('ebc') }"
                        type="text"
                        inputmode="numeric"
                        placeholder="8,0"
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        :class="eResultado('ebc') ? 'bg-amber-50 border-amber-300' : 'border-gray-300'"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SRM</label>
                    <input
                        :value="srm.display.value"
                        @input="(e) => { srm.onInput(e); aoDigitar('srm') }"
                        type="text"
                        inputmode="numeric"
                        placeholder="4,1"
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        :class="eResultado('srm') ? 'bg-amber-50 border-amber-300' : 'border-gray-300'"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lovibond</label>
                    <input
                        :value="lovibond.display.value"
                        @input="(e) => { lovibond.onInput(e); aoDigitar('lovibond') }"
                        type="text"
                        inputmode="numeric"
                        placeholder="3,3"
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        :class="eResultado('lovibond') ? 'bg-amber-50 border-amber-300' : 'border-gray-300'"
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
