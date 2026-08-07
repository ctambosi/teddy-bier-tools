<script setup>
import { reactive, ref } from 'vue'
import { Head, usePage  } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import SgInput from '@/Components/SgInput.vue'

const form = reactive({ og: '', leitura_atual: '' })
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
        const { data } = await axios.post(route('refratometro.calcular'), form)
        resultado.value = parseFloat(data.sg_corrigido).toFixed(3)
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
    form.og = form.leitura_atual = ''
    resultado.value = null
    erroGeral.value = ''
}
</script>

<template>
    <Head :title="meta.label" />
    <AppLayout>
        <CalculadoraCard
            :titulo="meta.label"
            :descricao="meta.description"
        >
            <!-- Explicação compacta -->
            <div class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-xs leading-relaxed">
                <strong>Como usar:</strong> informe a OG (medida antes de inocular a levedura) e a leitura atual do refratômetro — ambas em SG. O álcool produzido durante a fermentação interfere no índice de refração; esta fórmula corrige esse efeito.
            </div>

            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Densidade original — OG (SG)
                    </label>
                    <SgInput
                        v-model="form.og"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">Leitura antes de iniciar a fermentação</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Leitura atual do refratômetro (SG)
                    </label>
                    <SgInput
                        v-model="form.leitura_atual"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">Leitura durante ou após a fermentação</p>
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

            <!-- Resultado -->
            <div
                v-if="resultado !== null"
                class="mt-5 p-4 rounded-lg bg-green-50 border border-green-200 text-center"
            >
                <p class="text-sm text-green-700 mb-1">Densidade real corrigida</p>
                <p class="text-3xl font-bold text-green-800 tracking-wide">{{ resultado }}</p>
                <p class="text-xs text-green-600 mt-1">SG</p>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
