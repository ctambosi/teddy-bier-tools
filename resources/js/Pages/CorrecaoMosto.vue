<script setup>
import { computed, reactive, ref } from 'vue'
import { Head, usePage  } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import SgInput from '@/Components/SgInput.vue'
import { useDecimalInput, formatarPtBr } from '@/composables/useDecimalInput'

const form = reactive({ densidade_atual: '', densidade_desejada: '' })
const volume = useDecimalInput()
const page = usePage()
const meta = computed(() => page.props.meta || {})

const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

async function calcular() {
    erroGeral.value = ''
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('correcao-mosto.calcular'), {
            volume:             volume.numeric.value,
            densidade_atual:    form.densidade_atual,
            densidade_desejada: form.densidade_desejada,
        })
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
    volume.reset()
    form.densidade_atual = form.densidade_desejada = ''
    resultado.value = null
    erroGeral.value = ''
}

function fmtVolumeAgua(litros) {
    const valor = parseFloat(litros)
    if (Math.abs(valor) < 1) {
        return `${formatarPtBr(valor * 1000, 0)} ml`
    }
    return `${formatarPtBr(valor, 2)} L`
}

function fmtMassa(kg) {
    const valor = parseFloat(kg)
    if (Math.abs(valor) < 1) {
        return `${(valor * 1000).toFixed(0)} g`
    }
    return `${valor.toFixed(3)} kg`
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

                <!-- Volume do mosto -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Volume do mosto (L)
                    </label>
                    <input
                        :value="volume.display.value"
                        @input="volume.onInput"
                        type="text"
                        inputmode="decimal"
                        placeholder="20,0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                </div>

                <!-- Densidade atual -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Densidade atual (SG)
                    </label>
                    <SgInput
                        v-model="form.densidade_atual"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">Digite os 4 dígitos. Ex.: 1048 → 1.048</p>
                </div>

                <!-- Densidade desejada -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Densidade desejada (SG)
                    </label>
                    <SgInput
                        v-model="form.densidade_desejada"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">Digite os 4 dígitos. Ex.: 1048 → 1.048</p>
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

            <!-- Resultado: densidade acima da desejada — adicionar água -->
            <div
                v-if="resultado?.tipo === 'agua'"
                class="mt-5 p-4 rounded-lg bg-green-50 border border-green-200 text-center"
            >
                <p class="text-sm text-green-700 mb-1">Água a adicionar</p>
                <p class="text-3xl font-bold text-green-800">{{ fmtVolumeAgua(resultado.volume_agua_litros) }}</p>
                <p class="text-xs text-green-600 mt-2">
                    Volume final do mosto: {{ fmtVolumeAgua(resultado.volume_final_litros) }}
                </p>
            </div>

            <!-- Resultado: densidade abaixo da desejada — adicionar açúcar ou extrato -->
            <div
                v-else-if="resultado?.tipo === 'fermentavel'"
                class="mt-5 p-4 rounded-lg bg-green-50 border border-green-200 text-center"
            >
                <p class="text-sm text-green-700 mb-2">
                    Adicionar {{ fmtMassa(resultado.acucar_kg) }} de açúcar
                    ou {{ fmtMassa(resultado.extrato_kg) }} de extrato de malte
                </p>
                <div class="space-y-2 text-left mt-3">
                    <div class="flex justify-between items-center py-2 border-b border-green-100">
                        <span class="text-sm text-gray-600">Açúcar</span>
                        <span class="text-lg font-bold text-green-800">{{ fmtMassa(resultado.acucar_kg) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Extrato de malte</span>
                        <span class="text-lg font-bold text-green-800">{{ fmtMassa(resultado.extrato_kg) }}</span>
                    </div>
                </div>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
