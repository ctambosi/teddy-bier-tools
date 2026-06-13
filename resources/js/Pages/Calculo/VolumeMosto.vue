<script setup>
import { reactive, ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'
import { usePanelasStore } from '@/stores/panelas.js'

const store = usePanelasStore()

// ── Panelas ───────────────────────────────────────────────────────────────────
const panelaSelecionadaId = ref(null)
const nomePanela = ref('')
const editando = ref(false)
const gerenciarAberto = ref(false)

const panelaSelecionada = computed(() =>
    store.panelas.find(p => p.id === panelaSelecionadaId.value) ?? null
)

function selecionarPanela(id) {
    const p = store.panelas.find(x => x.id === id)
    if (!p) {
        panelaSelecionadaId.value = null
        return
    }
    panelaSelecionadaId.value = id
    nomePanela.value = p.nome
    form.diametro    = String(p.diametro)
    form.alturaPanela = String(p.alturaPanela ?? '')
    form.perda       = String(p.perda ?? '')
}

function salvarPanela() {
    const nome = nomePanela.value.trim()
    if (!nome || !form.diametro) return

    store.salvar({
        id:          editando.value ? panelaSelecionadaId.value : null,
        nome,
        diametro:    parseFloat(form.diametro) || 0,
        alturaPanela: parseFloat(form.alturaPanela) || 0,
        perda:       parseFloat(form.perda) || 0,
    })

    if (!editando.value) {
        const novaId = store.panelas[store.panelas.length - 1]?.id
        panelaSelecionadaId.value = novaId
    }
    editando.value = false
}

function novaPanel() {
    panelaSelecionadaId.value = null
    nomePanela.value = ''
    form.diametro = ''
    form.alturaPanela = ''
    form.perda = ''
    editando.value = false
}

function excluirPanela() {
    if (!panelaSelecionadaId.value) return
    store.remover(panelaSelecionadaId.value)
    novaPanel()
}

function prepararEdicao() {
    editando.value = true
}

// ── Cálculo ───────────────────────────────────────────────────────────────────
const modo = ref('altura') // 'altura' | 'distancia'

const form = reactive({
    diametro:         '',
    alturaLiquido:    '',
    alturaPanela:     '',
    distanciaTopoPanela: '',
    perda:            '',
})

const resultado = ref(null)
const erros = ref({})
const loading = ref(false)

function erroField(field) {
    return erros.value[field]?.[0] ?? ''
}

async function calcular() {
    erros.value = {}
    resultado.value = null

    let alturaLiquidoFinal = parseFloat(form.alturaLiquido)

    if (modo.value === 'distancia') {
        const alturaPanela  = parseFloat(form.alturaPanela)
        const distancia     = parseFloat(form.distanciaTopoPanela)
        if (!alturaPanela || !distancia) {
            erros.value = { _geral: 'Informe a altura da panela e a distância entre o mosto e o topo.' }
            return
        }
        if (distancia >= alturaPanela) {
            erros.value = { _geral: 'A distância ao topo deve ser menor que a altura total da panela.' }
            return
        }
        alturaLiquidoFinal = alturaPanela - distancia
    }

    loading.value = true
    try {
        const payload = {
            diametro:       form.diametro,
            altura_liquido: alturaLiquidoFinal,
            perda:          form.perda || undefined,
        }
        const { data } = await axios.post(route('calculo.volume-mosto.calcular'), payload)
        resultado.value = data
    } catch (e) {
        erros.value = e.response?.data?.errors ?? { _geral: 'Erro ao calcular. Verifique os valores.' }
    } finally {
        loading.value = false
    }
}

function limpar() {
    form.alturaLiquido = ''
    form.distanciaTopoPanela = ''
    resultado.value = null
    erros.value = {}
}
</script>

<template>
    <Head title="Volume de Mosto" />
    <AppLayout>
        <CalculadoraCard
            titulo="Volume de Mosto"
            descricao="Calcula o volume de líquido em uma panela cilíndrica a partir das medidas internas."
        >
            <!-- ── Gerenciar panelas ─────────────────────────────────────────── -->
            <div class="mb-5 border border-gray-200 rounded-lg overflow-hidden">
                <button
                    type="button"
                    @click="gerenciarAberto = !gerenciarAberto"
                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 text-sm font-medium text-gray-700 transition"
                >
                    <span>Minhas Panelas</span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': gerenciarAberto }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div v-show="gerenciarAberto" class="p-4 space-y-4 border-t border-gray-200">

                    <!-- Lista de panelas salvas -->
                    <div v-if="store.panelas.length">
                        <p class="text-xs text-gray-500 mb-2">Selecione uma panela:</p>
                        <div class="space-y-1">
                            <button
                                v-for="p in store.panelas"
                                :key="p.id"
                                type="button"
                                @click="selecionarPanela(p.id)"
                                :class="[
                                    'w-full text-left px-3 py-2 rounded-lg text-sm border transition',
                                    panelaSelecionadaId === p.id
                                        ? 'bg-amber-50 border-amber-400 text-amber-800'
                                        : 'bg-white border-gray-200 text-gray-700 hover:border-amber-300'
                                ]"
                            >
                                <span class="font-medium">{{ p.nome }}</span>
                                <span class="text-xs text-gray-400 ml-2">
                                    Ø {{ p.diametro }} cm
                                    <template v-if="p.alturaPanela"> · alt. {{ p.alturaPanela }} cm</template>
                                    <template v-if="p.perda"> · perda {{ p.perda }} L</template>
                                </span>
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-xs text-gray-400 italic">Nenhuma panela cadastrada ainda.</p>

                    <!-- Formulário de cadastro/edição -->
                    <div class="pt-3 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-500 mb-3">
                            {{ panelaSelecionadaId && editando ? 'Editar panela' : 'Cadastrar nova panela' }}
                        </p>
                        <div class="space-y-3">
                            <input
                                v-model="nomePanela"
                                type="text"
                                placeholder="Nome da panela (ex: Panela 50L)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                            />
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Diâmetro (cm)</label>
                                    <input v-model="form.diametro" type="text" inputmode="decimal" placeholder="40"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Altura total (cm)</label>
                                    <input v-model="form.alturaPanela" type="text" inputmode="decimal" placeholder="60"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Perda (L)</label>
                                    <input v-model="form.perda" type="text" inputmode="decimal" placeholder="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition" />
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="salvarPanela"
                                    :disabled="!nomePanela.trim() || !form.diametro"
                                    class="flex-1 bg-gray-800 hover:bg-gray-700 disabled:opacity-40 text-white text-sm font-medium py-2 px-3 rounded-lg transition"
                                >
                                    {{ panelaSelecionadaId && editando ? 'Salvar alterações' : 'Cadastrar' }}
                                </button>
                                <button
                                    v-if="panelaSelecionadaId && !editando"
                                    type="button"
                                    @click="prepararEdicao"
                                    class="px-3 py-2 border border-gray-300 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition"
                                >
                                    Editar
                                </button>
                                <button
                                    v-if="panelaSelecionadaId"
                                    type="button"
                                    @click="excluirPanela"
                                    class="px-3 py-2 border border-red-200 text-red-600 hover:bg-red-50 rounded-lg text-sm transition"
                                >
                                    Excluir
                                </button>
                                <button
                                    v-if="panelaSelecionadaId"
                                    type="button"
                                    @click="novaPanel"
                                    class="px-3 py-2 border border-gray-300 text-gray-500 hover:bg-gray-50 rounded-lg text-sm transition"
                                >
                                    Nova
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Modo de cálculo ──────────────────────────────────────────── -->
            <div class="mb-4">
                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                    <button
                        type="button"
                        @click="modo = 'altura'; resultado = null"
                        :class="['flex-1 py-2 text-sm font-medium transition',
                            modo === 'altura'
                                ? 'bg-amber-600 text-white'
                                : 'bg-white text-gray-600 hover:bg-gray-50']"
                    >
                        Altura do mosto
                    </button>
                    <button
                        type="button"
                        @click="modo = 'distancia'; resultado = null"
                        :class="['flex-1 py-2 text-sm font-medium transition border-l border-gray-200',
                            modo === 'distancia'
                                ? 'bg-amber-600 text-white'
                                : 'bg-white text-gray-600 hover:bg-gray-50']"
                    >
                        Espaço até o topo
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-400">
                    <template v-if="modo === 'altura'">
                        Mergulhe a colher no mosto e meça a marca deixada.
                    </template>
                    <template v-else>
                        Meça a distância entre o nível do mosto e a borda da panela.
                    </template>
                </p>
            </div>

            <!-- ── Erro geral ───────────────────────────────────────────────── -->
            <div v-if="erros._geral" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ erros._geral }}
            </div>

            <!-- ── Formulário de cálculo ────────────────────────────────────── -->
            <form @submit.prevent="calcular" class="space-y-4">

                <!-- Diâmetro (sempre visível) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Diâmetro interno da panela (cm)
                    </label>
                    <input
                        v-model="form.diametro"
                        type="text"
                        inputmode="decimal"
                        placeholder="40"
                        :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                            erroField('diametro') ? 'border-red-400' : 'border-gray-300']"
                    />
                    <p v-if="erroField('diametro')" class="mt-1 text-xs text-red-600">{{ erroField('diametro') }}</p>
                </div>

                <!-- Modo: altura do mosto -->
                <div v-if="modo === 'altura'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Altura do mosto (cm)
                    </label>
                    <input
                        v-model="form.alturaLiquido"
                        type="text"
                        inputmode="decimal"
                        placeholder="30"
                        :class="['w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition',
                            erroField('altura_liquido') ? 'border-red-400' : 'border-gray-300']"
                    />
                    <p v-if="erroField('altura_liquido')" class="mt-1 text-xs text-red-600">{{ erroField('altura_liquido') }}</p>
                </div>

                <!-- Modo: distância ao topo -->
                <template v-else>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Altura total interna da panela (cm)
                        </label>
                        <input
                            v-model="form.alturaPanela"
                            type="text"
                            inputmode="decimal"
                            placeholder="60"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Distância entre o mosto e o topo (cm)
                        </label>
                        <input
                            v-model="form.distanciaTopoPanela"
                            type="text"
                            inputmode="decimal"
                            placeholder="25"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                        />
                    </div>
                </template>

                <!-- Perda (opcional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Perda (L)
                        <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <input
                        v-model="form.perda"
                        type="text"
                        inputmode="decimal"
                        placeholder="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition"
                    />
                    <p class="mt-1 text-xs text-gray-400">Volume abaixo da torneira ou perda estimada</p>
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

            <!-- ── Resultado ────────────────────────────────────────────────── -->
            <div v-if="resultado !== null" class="mt-5 p-4 rounded-lg bg-green-50 border border-green-200 text-center">
                <p class="text-sm text-green-700 font-medium mb-1">Volume de mosto</p>
                <p class="text-5xl font-bold text-green-800">{{ resultado.volume }}</p>
                <p class="text-sm text-green-600 mt-1">litros</p>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
