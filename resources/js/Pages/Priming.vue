<script setup>
import { computed, ref } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import { useDecimalInput, formatarPtBr } from '@/composables/useDecimalInput'

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

const GRAMAS_POR_LITRO_SUGERIDO = 7

const page = usePage()
const meta = computed(() => page.props.meta || {})

const modo = ref('manual')
const tipoAcucar = ref('sucrose')

// Só faz sentido em modo CO₂, onde o tipo de açúcar realmente muda a dosagem —
// em modo manual o usuário já digita o g/L direto, então o tipo não tem efeito.
const usandoMel = computed(() => modo.value === 'co2' && tipoAcucar.value === 'mel')

const volumeLitros           = useDecimalInput({ casas: 2 })
const gramasPorLitroManual   = useDecimalInput({ casas: 1 })
const targetCo2              = useDecimalInput({ casas: 1 })
const tempFermentacao        = useDecimalInput({ casas: 1, negativo: true })
const volumeSolucaoInicial   = useDecimalInput({ casas: 0 })
const volumeSolucaoFinal     = useDecimalInput({ casas: 0 })

// Dosagem sugerida já vem preenchida, não é só placeholder.
gramasPorLitroManual.set(GRAMAS_POR_LITRO_SUGERIDO)

function aplicarEstilo(co2) {
    modo.value = 'co2'
    targetCo2.set(co2)
}

const resultado = ref(null)
// Baseado no resultado já calculado (não no select ao vivo), pra não mostrar
// texto de mel com números de outro tipo caso o usuário troque o select sem recalcular.
const resultadoUsandoMel = computed(() => resultado.value?.modo === 'co2' && resultado.value?.tipo_acucar === 'mel')
const erros = ref({})
const loading = ref(false)
const recalculando = ref(false)
let debounceTimer = null

function construirPayloadBase() {
    const payload = {
        modo: modo.value,
        tipo_acucar: tipoAcucar.value,
    }
    if (volumeLitros.numeric.value) payload.volume_litros = volumeLitros.numeric.value
    if (modo.value === 'co2') {
        payload.temp_fermentacao = tempFermentacao.numeric.value
        payload.target_co2 = targetCo2.numeric.value
    } else {
        payload.gramas_por_litro = gramasPorLitroManual.numeric.value
    }
    return payload
}

async function calcular() {
    erros.value = {}
    resultado.value = null
    loading.value = true
    try {
        const { data } = await axios.post(route('priming.calcular'), construirPayloadBase())
        resultado.value = data
        // Sugestão inicial: volumes antes/depois já na razão que reproduz a estimativa
        // padrão (mesmo resultado mostrado acima) através da fórmula real — assim editar
        // os campos altera o cálculo de forma contínua, sem saltos.
        if (data.volume_solucao_sugerido_ml !== undefined) {
            volumeSolucaoInicial.set(data.volume_solucao_sugerido_ml)
            volumeSolucaoFinal.set(data.volume_solucao_final_sugerido_ml)
        } else {
            volumeSolucaoInicial.reset()
            volumeSolucaoFinal.reset()
        }
    } catch (e) {
        erros.value = e.response?.data?.errors ?? {}
        if (!Object.keys(erros.value).length) {
            erros.value = { _geral: 'Erro ao calcular. Verifique os valores informados.' }
        }
    } finally {
        loading.value = false
    }
}

// Ajustar os volumes antes/depois de ferver recalcula a distribuição por garrafa
// automaticamente, sem precisar clicar em nenhum botão de novo.
function aoAjustarVolumeSolucao(campo, e) {
    campo.onInput(e)
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(recalcularConcentracao, 400)
}

async function recalcularConcentracao() {
    if (!resultado.value) return
    recalculando.value = true
    try {
        const payload = construirPayloadBase()
        // Sempre usa o cálculo real quando os dois volumes estão preenchidos — os valores
        // pré-preenchidos já reproduzem a estimativa padrão, então não há necessidade de
        // um caso especial para "campos iguais" (isso causava um salto no resultado).
        const vi = volumeSolucaoInicial.numeric.value
        const vf = volumeSolucaoFinal.numeric.value
        if (vi && vf) {
            payload.volume_solucao_inicial_ml = vi
            payload.volume_solucao_final_ml = vf
        }
        const { data } = await axios.post(route('priming.calcular'), payload)
        resultado.value = data
    } catch (e) {
        erros.value = e.response?.data?.errors ?? {}
    } finally {
        recalculando.value = false
    }
}

function limpar() {
    clearTimeout(debounceTimer)
    modo.value = 'manual'
    tipoAcucar.value = 'sucrose'
    volumeLitros.reset()
    gramasPorLitroManual.set(GRAMAS_POR_LITRO_SUGERIDO)
    targetCo2.reset()
    tempFermentacao.reset()
    volumeSolucaoInicial.reset()
    volumeSolucaoFinal.reset()
    resultado.value = null
    erros.value = {}
}

function erroField(field) {
    return erros.value[field]?.[0] ?? ''
}
</script>

<template>
    <Head :title="meta.label" />
    <AppLayout>
        <CalculadoraCard
            :titulo="meta.label"
            :descricao="meta.description"
        >
            <!-- Intro didática -->
            <div class="mb-6 p-4 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-800 space-y-2">
                <p v-if="usandoMel">
                    Mel já é líquido — não é preciso diluir 1:1 com água como no açúcar cristalino.
                    Ferva o mel com só um pouco de água, o suficiente para pasteurizar, e deixe esfriar
                    antes de misturar à cerveja.
                </p>
                <p v-else>
                    Prepare a solução de priming com <strong>partes iguais de água e açúcar</strong>
                    (ex.: 100 g de açúcar + 100 ml de água), ferva por 5 a 10 minutos e deixe esfriar
                    antes de misturar à cerveja.
                </p>
<!--                <p>
                    A fervura evapora água e concentra o açúcar — por isso a solução pronta rende mais
                    açúcar por ml do que a mistura crua. Sem essa informação, calculamos com uma solução
                    típica (reduzida por uma fervura normal); se você medir o volume antes e depois de
                    ferver, o cálculo fica exato para a sua receita.
                </p>-->
                <p>
                    Primeiro, calcule o volume de solução de priming
                    que você vai precisar fazer para carbonatar todo o lote.
                </p>
                <p>
                    Depois, obtenha a quantidade de solução a adicionar no tipo de garrafa que você vai utilizar.
                </p>
            </div>

            <div v-if="erros._geral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erros._geral }}
            </div>

            <form @submit.prevent="calcular" class="space-y-5">
                <!-- Modo de dosagem -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-sm font-medium text-gray-700 mb-2">Como quer definir a dosagem?</p>
                    <div class="flex gap-2 mb-3">
                        <button
                            type="button"
                            @click="modo = 'manual'"
                            :class="['flex-1 px-3 py-2 rounded-lg text-xs font-medium border transition',
                                modo === 'manual'
                                    ? 'bg-amber-600 text-white border-amber-600'
                                    : 'bg-white text-gray-600 border-gray-300 hover:border-amber-400']"
                        >
                            Gramas por litro
                        </button>
                        <button
                            type="button"
                            @click="modo = 'co2'"
                            :class="['flex-1 px-3 py-2 rounded-lg text-xs font-medium border transition',
                                modo === 'co2'
                                    ? 'bg-amber-600 text-white border-amber-600'
                                    : 'bg-white text-gray-600 border-gray-300 hover:border-amber-400']"
                        >
                            CO₂ desejado
                        </button>
                    </div>

                    <!-- Modo manual -->
                    <div v-if="modo === 'manual'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Gramas de açúcar por litro
                        </label>
                        <input
                            :value="gramasPorLitroManual.display.value"
                            @input="gramasPorLitroManual.onInput"
                            type="text"
                            inputmode="decimal"
                            :class="['w-full px-3 py-2 border rounded-lg text-sm text-right bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                erroField('gramas_por_litro') ? 'border-red-400' : 'border-gray-300']"
                        />
                        <p v-if="erroField('gramas_por_litro')" class="mt-1 text-xs text-red-600">{{ erroField('gramas_por_litro') }}</p>
                        <p class="mt-1 text-xs text-gray-400">
                            Sugestão: {{ formatarPtBr(GRAMAS_POR_LITRO_SUGERIDO, 1) }} g/L funciona bem para a maioria dos estilos.
                            Com a solução já reduzida pela fervura, isso equivale à regra prática de 1 ml de solução para
                            cada 100 ml de cerveja (ex.: 5 ml numa garrafa de 500 ml). Ajuste se quiser mais ou menos gás.
                        </p>
                    </div>

                    <!-- Modo CO2 -->
                    <div v-else class="space-y-3">
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
                                        targetCo2.numeric.value === estilo.co2.toFixed(1)
                                            ? 'bg-amber-600 text-white border-amber-600'
                                            : 'bg-white text-gray-600 border-gray-300 hover:border-amber-400 hover:text-amber-700'
                                    ]"
                                >
                                    {{ estilo.label }} ({{ formatarPtBr(estilo.co2, 1) }})
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    CO₂ desejado (volumes)
                                </label>
                                <input
                                    :value="targetCo2.display.value"
                                    @input="targetCo2.onInput"
                                    type="text"
                                    inputmode="decimal"
                                    placeholder="2,4"
                                    :class="['w-full px-3 py-2 border rounded-lg text-sm text-right bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                        erroField('target_co2') ? 'border-red-400' : 'border-gray-300']"
                                />
                                <p v-if="erroField('target_co2')" class="mt-1 text-xs text-red-600">{{ erroField('target_co2') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Temp. de fermentação (°C)
                                </label>
                                <input
                                    :value="tempFermentacao.display.value"
                                    @input="tempFermentacao.onInput"
                                    type="text"
                                    inputmode="decimal"
                                    placeholder="20,0"
                                    :class="['w-full px-3 py-2 border rounded-lg text-sm text-right bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                        erroField('temp_fermentacao') ? 'border-red-400' : 'border-gray-300']"
                                />
                                <p v-if="erroField('temp_fermentacao')" class="mt-1 text-xs text-red-600">{{ erroField('temp_fermentacao') }}</p>
                                <p class="mt-1 text-xs text-gray-400">Temp. final de fermentação / lagering</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tipo de açúcar (só influencia a dosagem no modo CO₂) -->
                <div v-if="modo === 'co2'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de açúcar
                    </label>
                    <select
                        v-model="tipoAcucar"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    >
                        <option v-for="a in ACUCARES" :key="a.value" :value="a.value">
                            {{ a.label }}
                        </option>
                    </select>
                </div>

                <hr class="border-gray-100" />

                <!-- Refinamento opcional: total do lote -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Volume a envasar (L)
<!--                        <span class="text-gray-400 font-normal">(opcional)</span>-->
                    </label>
                    <input
                        :value="volumeLitros.display.value"
                        @input="volumeLitros.onInput"
                        type="text"
                        inputmode="decimal"
                        placeholder="20,00"
                        :class="['w-full px-3 py-2 border rounded-lg text-sm text-right bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                            erroField('volume_litros') ? 'border-red-400' : 'border-gray-300']"
                    />
                    <p v-if="erroField('volume_litros')" class="mt-1 text-xs text-red-600">{{ erroField('volume_litros') }}</p>
                    <p class="mt-1 text-xs text-gray-400">
                        Para saber quanto de solução de priming você deve preparar
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

            <!-- ==================== RESULTADO ==================== -->
            <div v-if="resultado" class="mt-6 space-y-4">

                <!-- Para o lote (só se volume informado) -->
                <div v-if="resultado.gramas_total !== undefined" class="p-4 rounded-lg bg-green-50 border border-green-200">
                    <p class="text-sm text-green-700 font-medium text-center mb-3">Açúcar total para o lote</p>
                    <div class="text-center mb-3">
                        <p class="text-5xl font-bold text-green-800">{{ formatarPtBr(resultado.gramas_total, 1) }} g</p>
                        <p class="text-sm text-green-600 mt-1">{{ formatarPtBr(resultado.gramas_por_litro, 1) }} g/L</p>
                    </div>

                    <div v-if="resultado.modo === 'co2'" class="grid grid-cols-2 gap-2 pb-3 border-b border-green-200 text-center">
                        <div>
                            <p class="text-xs text-green-600">CO₂ residual</p>
                            <p class="text-lg font-semibold text-green-800">{{ formatarPtBr(resultado.co2_residual, 2) }} vol</p>
                        </div>
                        <div>
                            <p class="text-xs text-green-600">CO₂ a adicionar</p>
                            <p class="text-lg font-semibold text-green-800">{{ formatarPtBr(resultado.co2_adicional, 2) }} vol</p>
                        </div>
                    </div>
                    <p v-if="resultado.modo === 'co2' && resultado.co2_adicional === 0" class="mt-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2 text-center">
                        A cerveja já possui CO₂ residual suficiente para a meta. Nenhum açúcar é necessário.
                    </p>

                    <div v-if="resultadoUsandoMel" class="mt-4 pt-3 border-t border-green-200 text-center">
                        <p class="text-xs text-green-600">
                            Para mel: ferva os {{ formatarPtBr(resultado.gramas_total, 1) }} g de mel com só um pouco
                            de água (o suficiente para pasteurizar) — sem diluir na proporção 1:1 usada para açúcar cristalino.
                        </p>
                    </div>
                    <div v-else class="mt-4 pt-3 border-t border-green-200 text-center">
                        <p class="text-xs text-green-600">
                            Receita sugerida (partes iguais): {{ formatarPtBr(resultado.gramas_total, 1) }} g de açúcar +
                            {{ formatarPtBr(resultado.gramas_total, 1) }} ml de água
                        </p>
                        <p class="text-xs text-green-600 mt-2">Volume sugerido da solução antes da fervura</p>
                        <p class="text-3xl font-bold text-green-800">{{ formatarPtBr(resultado.volume_solucao_sugerido_ml, 0) }} ml</p>
                    </div>
                </div>

                <!-- Concentração real da solução (opcional, não se aplica a mel) -->
                <div v-if="!resultadoUsandoMel" class="p-4 rounded-lg bg-amber-50 border border-amber-200">
<!--                    <p class="text-sm text-amber-800 font-medium mb-1">Quer uma dosagem ainda mais precisa?</p>-->
                    <p class="text-xs text-amber-700 mb-3">
                        O ideal é medir o volume da solução antes e depois de ferver para ter uma precisão maior da
                        quantidade da açúcar presente na solução
                        e então sabe quantos ml exatos de solução adicionar por litro.<br>
                        Porém, preferir não medir, use os valores estimados abaixo que dará certo dentro de uma
                        carbonatação média.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Volume antes de ferver (ml)
                            </label>
                            <input
                                :value="volumeSolucaoInicial.display.value"
                                @input="aoAjustarVolumeSolucao(volumeSolucaoInicial, $event)"
                                type="text"
                                inputmode="decimal"
                                placeholder="200"
                                :class="['w-full px-3 py-2 border rounded-lg text-sm text-right bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                    erroField('volume_solucao_inicial_ml') ? 'border-red-400' : 'border-gray-300']"
                            />
                            <p v-if="erroField('volume_solucao_inicial_ml')" class="mt-1 text-xs text-red-600">{{ erroField('volume_solucao_inicial_ml') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Volume depois de ferver (ml)
                            </label>
                            <input
                                :value="volumeSolucaoFinal.display.value"
                                @input="aoAjustarVolumeSolucao(volumeSolucaoFinal, $event)"
                                type="text"
                                inputmode="decimal"
                                placeholder="180"
                                :class="['w-full px-3 py-2 border rounded-lg text-sm text-right bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                                    erroField('volume_solucao_final_ml') ? 'border-red-400' : 'border-gray-300']"
                            />
                            <p v-if="erroField('volume_solucao_final_ml')" class="mt-1 text-xs text-red-600">{{ erroField('volume_solucao_final_ml') }}</p>
                        </div>
                    </div>
                    <p v-if="recalculando" class="mt-2 text-xs text-amber-600">Recalculando…</p>
                </div>

                <!-- Distribuição por garrafa (sempre) -->
                <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
                    <p class="text-sm text-blue-700 font-medium mb-1">Solução por garrafa</p>
                    <p class="text-xs text-blue-600 mb-3">
                        {{ formatarPtBr(resultado.gramas_por_litro, 1) }} g/L de dosagem
                        &mdash; {{ formatarPtBr(resultado.ml_por_litro, 1) }} ml de solução por litro de cerveja
                    </p>
                    <div class="space-y-1">
                        <div
                            v-for="item in resultado.distribuicao"
                            :key="item.tamanho"
                            class="flex justify-between items-center py-1.5 border-b border-blue-100 last:border-0"
                        >
                            <span class="text-sm text-gray-600">Garrafa {{ item.tamanho }} ml</span>
                            <span class="text-sm font-semibold text-blue-800">{{ formatarPtBr(item.ml_solucao, 1) }} ml</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-blue-100">
                            <span class="text-sm text-gray-600">Por litro</span>
                            <span class="text-sm font-semibold text-blue-800">{{ formatarPtBr(resultado.ml_por_litro, 1) }} ml</span>
                        </div>
                    </div>
                    <p v-if="resultadoUsandoMel" class="mt-3 text-xs text-blue-600">
                        Os ml acima assumem uma solução equivalente à do açúcar cristalino, que não se aplica bem ao mel.
                        Para mel, prefira dosar pelo total em gramas ({{ formatarPtBr(resultado.gramas_por_litro, 1) }} g/L)
                        em vez de medir por volume.
                    </p>
                    <p v-else class="mt-3 text-xs text-blue-600">
                        <template v-if="resultado.concentracao_real">
                            Concentração real da sua solução (considerando a água evaporada): {{ formatarPtBr(resultado.concentracao_g_por_ml, 3) }} g/ml.
                        </template>
                        <template v-else>
                            Estimativa para uma solução 50/50 reduzida por uma fervura típica: {{ formatarPtBr(resultado.concentracao_g_por_ml, 1) }} g/ml.
                            O quanto sua solução realmente reduz varia com o tempo de fervura — informe os volumes
                            antes/depois de ferver acima para o cálculo exato da sua receita.
                        </template>
                    </p>
                </div>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
