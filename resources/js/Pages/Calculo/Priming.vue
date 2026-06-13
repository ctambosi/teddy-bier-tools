<script setup>
import { reactive, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'

const ESTILOS = [
    { label: 'Ales britânicas / Cask',  co2: 1.7 },
    { label: 'Ales americanas',          co2: 2.4 },
    { label: 'Stout / Porter',           co2: 2.1 },
    { label: 'Lager europeia',           co2: 2.5 },
    { label: 'Saison',                   co2: 3.0 },
    { label: 'Belgian Ale / Tripel',     co2: 3.2 },
    { label: 'Weizen',                   co2: 3.7 },
]

const ACUCARES = [
    { value: 'sucrose',         label: 'Açúcar refinado / cristal (sacarose)' },
    { value: 'dextrose_mono',   label: 'Dextrose monohidratada (glicose)' },
    { value: 'dextrose_anidra', label: 'Dextrose anidra' },
    { value: 'dme',             label: 'Extrato seco de malte (DME)' },
    { value: 'mel',             label: 'Mel' },
]

const form = reactive({
    volume_litros:    '',
    temp_fermentacao: '',
    target_co2:       '',
    tipo_acucar:      'sucrose',
    volume_solucao_ml: '',
})

const resultado = ref(null)
const erros = ref({})
const loading = ref(false)

function aplicarEstilo(co2) {
    form.target_co2 = String(co2)
}

async function calcular() {
    erros.value = {}
    resultado.value = null
    loading.value = true
    try {
        const payload = { ...form }
        if (!payload.volume_solucao_ml) delete payload.volume_solucao_ml
        const { data } = await axios.post(route('calculo.priming.calcular'), payload)
        resultado.value = data
    } catch (e) {
        erros.value = e.response?.data?.errors ?? {}
        if (!Object.keys(erros.value).length) {
            erros.value = { _geral: 'Erro ao calcular. Verifique os valores informados.' }
        }
    } finally {
        loading.value = false
    }
}

function limpar() {
    form.volume_litros = ''
    form.temp_fermentacao = ''
    form.target_co2 = ''
    form.tipo_acucar = 'sucrose'
    form.volume_solucao_ml = ''
    resultado.value = null
    erros.value = {}
}

function erroField(field) {
    return erros.value[field]?.[0] ?? ''
}
</script>

<template>
    <Head title="Priming" />
    <AppLayout>
        <CalculadoraCard
            titulo="Cálculo de Priming"
            descricao="Calcula a quantidade de açúcar para carbonatação em garrafa ou barril, com correção por CO₂ residual da temperatura."
        >
            <!-- Erro geral -->
            <div v-if="erros._geral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erros._geral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-5">

                <!-- Presets de estilo -->
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                        Preset por estilo de cerveja
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="estilo in ESTILOS"
                            :key="estilo.label"
                            type="button"
                            @click="aplicarEstilo(estilo.co2)"
                            :class="[
                                'px-2 py-1 rounded text-xs border transition',
                                form.target_co2 === String(estilo.co2)
                                    ? 'bg-amber-600 text-white border-amber-600'
                                    : 'bg-white text-gray-600 border-gray-300 hover:border-amber-400 hover:text-amber-700'
                            ]"
                        >
                            {{ estilo.label }} ({{ estilo.co2 }})
                        </button>
                    </div>
                </div>

                <hr class="border-gray-100" />

                <!-- Volume e temperatura -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Volume a envasar (L)
                        </label>
                        <input
                            v-model="form.volume_litros"
                            type="text"
                            inputmode="decimal"
                            placeholder="20"
                            :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                erroField('volume_litros') ? 'border-red-400' : 'border-gray-300']"
                        />
                        <p v-if="erroField('volume_litros')" class="mt-1 text-xs text-red-600">{{ erroField('volume_litros') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Temperatura de fermentação (°C)
                        </label>
                        <input
                            v-model="form.temp_fermentacao"
                            type="text"
                            inputmode="decimal"
                            placeholder="20"
                            :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                erroField('temp_fermentacao') ? 'border-red-400' : 'border-gray-300']"
                        />
                        <p v-if="erroField('temp_fermentacao')" class="mt-1 text-xs text-red-600">{{ erroField('temp_fermentacao') }}</p>
                        <p class="mt-1 text-xs text-gray-400">Temp. final de fermentação / lagering</p>
                    </div>
                </div>

                <!-- CO₂ alvo e tipo de açúcar -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            CO₂ desejado (volumes)
                        </label>
                        <input
                            v-model="form.target_co2"
                            type="text"
                            inputmode="decimal"
                            placeholder="2.4"
                            :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                erroField('target_co2') ? 'border-red-400' : 'border-gray-300']"
                        />
                        <p v-if="erroField('target_co2')" class="mt-1 text-xs text-red-600">{{ erroField('target_co2') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo de açúcar
                        </label>
                        <select
                            v-model="form.tipo_acucar"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        >
                            <option v-for="a in ACUCARES" :key="a.value" :value="a.value">
                                {{ a.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Volume da solução (opcional) -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Volume da solução de priming após fervura
                        <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <input
                        v-model="form.volume_solucao_ml"
                        type="text"
                        inputmode="decimal"
                        placeholder="400"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">
                        Em ml. Se preenchido, calcula a distribuição por garrafa.
                        Dica: use mesma medida de açúcar e água (ex: 200g + 200ml).
                    </p>
                </div>

                <div class="flex gap-3 pt-1">
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
            <div v-if="resultado" class="mt-6 space-y-4">

                <!-- Bloco principal -->
                <div class="p-4 rounded-lg bg-green-50 border border-green-200">
                    <p class="text-sm text-green-700 font-medium text-center mb-3">Açúcar necessário</p>
                    <div class="text-center mb-4">
                        <p class="text-5xl font-bold text-green-800">{{ resultado.gramas }} g</p>
                        <p class="text-sm text-green-600 mt-1">{{ resultado.gramas_por_litro }} g/L</p>
                    </div>

                    <!-- CO₂ breakdown -->
                    <div class="grid grid-cols-2 gap-2 pt-3 border-t border-green-200 text-center">
                        <div>
                            <p class="text-xs text-green-600">CO₂ residual</p>
                            <p class="text-lg font-semibold text-green-800">{{ resultado.co2_residual }} vol</p>
                        </div>
                        <div>
                            <p class="text-xs text-green-600">CO₂ a adicionar</p>
                            <p class="text-lg font-semibold text-green-800">{{ resultado.co2_adicional }} vol</p>
                        </div>
                    </div>

                    <p v-if="resultado.co2_adicional === 0" class="mt-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2 text-center">
                        A cerveja já possui CO₂ residual suficiente para a meta. Nenhum açúcar é necessário.
                    </p>
                </div>

                <!-- Distribuição por garrafa -->
                <div v-if="resultado.distribuicao" class="p-4 rounded-lg bg-blue-50 border border-blue-200">
                    <p class="text-sm text-blue-700 font-medium mb-1">Distribuição por garrafa</p>
                    <p class="text-xs text-blue-600 mb-3">
                        {{ resultado.ml_por_litro }} ml de solução por litro
                        &mdash; concentração {{ resultado.concentracao_g_por_ml }} g/ml
                    </p>
                    <div class="space-y-1">
                        <div
                            v-for="item in resultado.distribuicao"
                            :key="item.tamanho"
                            class="flex justify-between items-center py-1.5 border-b border-blue-100 last:border-0"
                        >
                            <span class="text-sm text-gray-600">Garrafa {{ item.tamanho }} ml</span>
                            <span class="text-sm font-semibold text-blue-800">{{ item.ml_solucao }} ml</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-blue-100">
                            <span class="text-sm text-gray-600">Por litro</span>
                            <span class="text-sm font-semibold text-blue-800">{{ resultado.ml_por_litro }} ml</span>
                        </div>
                    </div>
                </div>

            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
