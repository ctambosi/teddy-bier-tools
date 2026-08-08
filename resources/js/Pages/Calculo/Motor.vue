<script setup>
import { reactive, ref, computed } from 'vue'
import { Head, usePage  } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'

const form = reactive({
    n1: '',
    d1: '',
    unidade_d1: 'mm',
    n2: '',
    d2: '',
    unidade_d2: 'mm',
})

const page = usePage()
const meta = computed(() => page.props.meta || {})

const resultado = ref(null)
const erroGeral = ref('')
const loading = ref(false)

// Destaque do campo calculado
const campoCalculado = computed(() => resultado.value?.campo ?? null)

// Alerta quando n2 calculado estiver fora do ideal
const n2ForaDoIdeal = computed(() =>
    resultado.value?.campo === 'n2' &&
    (resultado.value.valor < 150 || resultado.value.valor > 300)
)

const LABELS = {
    n1: 'Velocidade do motor (n1)',
    d1: 'Diâmetro polia do motor (D1)',
    n2: 'Velocidade do moedor (n2)',
    d2: 'Diâmetro polia do moedor (D2)',
}

function vaziosCount() {
    return [form.n1, form.d1, form.n2, form.d2].filter(v => v === '').length
}

async function calcular() {
    erroGeral.value = ''
    resultado.value = null

    const vazios = vaziosCount()
    if (vazios === 0) {
        erroGeral.value = 'Deixe exatamente um campo em branco. Ele será calculado.'
        return
    }
    if (vazios > 1) {
        erroGeral.value = 'Preencha três dos quatro campos. Apenas um pode ficar em branco.'
        return
    }

    loading.value = true
    try {
        const { data } = await axios.post(route('motor.calcular'), {
            n1: form.n1 || null,
            d1: form.d1 || null,
            n2: form.n2 || null,
            d2: form.d2 || null,
            unidade_d1: form.unidade_d1,
            unidade_d2: form.unidade_d2,
        })
        resultado.value = data

        // Preenche o campo calculado no formulário
        if (data.campo === 'n1') form.n1 = String(data.valor)
        if (data.campo === 'd1') form.d1 = String(data.valor)
        if (data.campo === 'n2') form.n2 = String(data.valor)
        if (data.campo === 'd2') form.d2 = String(data.valor)
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
    form.n1 = form.d1 = form.n2 = form.d2 = ''
    form.unidade_d1 = form.unidade_d2 = 'mm'
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
            <!-- Dica -->
            <div class="mb-5 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 space-y-1">
                <p>
                    A velocidade ideal do de rotação do moedor (n₂) é entre <strong>150 e 300 RPM</strong>.<br>
                    Você pode já ter um motor que queira usar, que tem determinada rotação.
                    Esse motor pode já ter uma polia embutida.<br>
                    Ou você pode já ter alguma polia tanto para o motor, quanto para o moedor.<br>
                    Enfim, o objetivo desta funcionalidade é ajudar a obter uma rotação dentro do ideal utilizando o que
                    você já tem ou pode comprar.
                </p>
                <p class="font-medium">Como usar:</p>
                <p>Preencha <strong>três</strong> dos quatro campos e
                    <strong>deixe o campo que deseja calcular em branco</strong>.</p>

            </div>

            <div v-if="erroGeral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erroGeral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-4">

                <!-- n1: Velocidade do motor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Velocidade do motor — n₁
                        <span
                            v-if="campoCalculado === 'n1'"
                            class="ml-1 text-xs font-normal text-amber-600"
                        >← calculado</span>
                    </label>
                    <div class="flex gap-2 items-center">
                        <input
                            v-model="form.n1"
                            type="text"
                            inputmode="decimal"
                            placeholder="deixe em branco para calcular"
                            @input="resultado = null"
                            :class="[
                                'flex-1 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                campoCalculado === 'n1'
                                    ? 'bg-amber-50 border-amber-400 font-semibold'
                                    : 'border-gray-300'
                            ]"
                        />
                        <span class="text-sm text-gray-500 w-10 shrink-0">RPM</span>
                    </div>
                </div>

                <!-- D1: Diâmetro polia do motor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Diâmetro da polia do motor — D₁
                        <span
                            v-if="campoCalculado === 'd1'"
                            class="ml-1 text-xs font-normal text-amber-600"
                        >← calculado (mm)</span>
                    </label>
                    <div class="flex gap-2">
                        <input
                            v-model="form.d1"
                            type="text"
                            inputmode="decimal"
                            placeholder="deixe em branco para calcular"
                            @input="resultado = null"
                            :class="[
                                'flex-1 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                campoCalculado === 'd1'
                                    ? 'bg-amber-50 border-amber-400 font-semibold'
                                    : 'border-gray-300'
                            ]"
                        />
                        <select
                            v-model="form.unidade_d1"
                            class="w-20 px-2 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        >
                            <option value="mm">mm</option>
                            <option value="cm">cm</option>
                        </select>
                    </div>
                </div>

                <hr class="border-gray-100" />

                <!-- n2: Velocidade do moedor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Velocidade do moedor — n₂
                        <span
                            v-if="campoCalculado === 'n2'"
                            class="ml-1 text-xs font-normal text-amber-600"
                        >← calculado</span>
                    </label>
                    <div class="flex gap-2 items-center">
                        <input
                            v-model="form.n2"
                            type="text"
                            inputmode="decimal"
                            placeholder="ideal: 150–300 RPM"
                            @input="resultado = null"
                            :class="[
                                'flex-1 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                campoCalculado === 'n2'
                                    ? 'bg-amber-50 border-amber-400 font-semibold'
                                    : 'border-gray-300'
                            ]"
                        />
                        <span class="text-sm text-gray-500 w-10 shrink-0">RPM</span>
                    </div>
                </div>

                <!-- D2: Diâmetro polia do moedor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Diâmetro da polia do moedor — D₂
                        <span
                            v-if="campoCalculado === 'd2'"
                            class="ml-1 text-xs font-normal text-amber-600"
                        >← calculado (mm)</span>
                    </label>
                    <div class="flex gap-2">
                        <input
                            v-model="form.d2"
                            type="text"
                            inputmode="decimal"
                            placeholder="deixe em branco para calcular"
                            @input="resultado = null"
                            :class="[
                                'flex-1 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                campoCalculado === 'd2'
                                    ? 'bg-amber-50 border-amber-400 font-semibold'
                                    : 'border-gray-300'
                            ]"
                        />
                        <select
                            v-model="form.unidade_d2"
                            class="w-20 px-2 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        >
                            <option value="mm">mm</option>
                            <option value="cm">cm</option>
                        </select>
                    </div>
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
            <div v-if="resultado" class="mt-5 space-y-3">
                <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-center">
                    <p class="text-sm text-green-700 font-medium mb-1">{{ LABELS[resultado.campo] }}</p>
                    <p class="text-4xl font-bold text-green-800">
                        {{ resultado.valor }}
                        <span class="text-xl font-normal text-green-600">{{ resultado.unidade }}</span>
                    </p>
                </div>

                <!-- Aviso n2 fora do ideal -->
                <div
                    v-if="n2ForaDoIdeal"
                    class="p-3 rounded-lg bg-yellow-50 border border-yellow-300 text-sm text-yellow-800"
                >
                    <p class="font-medium">Atenção: velocidade fora do ideal</p>
                    <p class="mt-0.5">
                        O moedor calculado girará a {{ resultado.valor }} RPM,
                        fora da faixa recomendada de <strong>150–300 RPM</strong>.
                        Ajuste os diâmetros das polias para atingir a faixa ideal.
                    </p>
                </div>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
