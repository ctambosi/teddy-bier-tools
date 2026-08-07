<script setup>
import { reactive, ref } from 'vue'
import { Head, usePage  } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'

const form = reactive({ volume_base: '', graduacao_alcool: '', graduacao_desejada: '', unidade: 'L' })
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
        const { data } = await axios.post(route('adicao-alcoolica.calcular'), form)
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
    form.volume_base = form.graduacao_alcool = form.graduacao_desejada = ''
    form.unidade = 'L'
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
            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Volume da base
                        </label>
                        <input
                            v-model="form.volume_base"
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
                        Graduação do álcool a adicionar (%)
                    </label>
                    <input
                        v-model="form.graduacao_alcool"
                        type="text"
                        inputmode="decimal"
                        placeholder="96"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Graduação desejada na mistura (%)
                    </label>
                    <input
                        v-model="form.graduacao_desejada"
                        type="text"
                        inputmode="decimal"
                        placeholder="40"
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
                <p class="text-sm text-green-700 font-medium mb-1">Álcool a adicionar</p>
                <p class="text-4xl font-bold text-green-800">
                    {{ parseFloat(resultado.volume_adicionar).toFixed(3) }} {{ resultado.unidade }}
                </p>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
