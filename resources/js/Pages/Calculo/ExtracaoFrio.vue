<script setup>
import { computed, reactive, ref } from 'vue'
import { Head, usePage  } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'

const form = reactive({ gramas: '' })
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
        const { data } = await axios.post(route('extracao-frio.calcular'), form)
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
    form.gramas = ''
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Quantidade de malte (gramas)
                    </label>
                    <input
                        v-model="form.gramas"
                        type="text"
                        inputmode="decimal"
                        placeholder="500"
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
                <p class="text-sm text-green-700 font-medium mb-1">Volume de água necessário</p>
                <p class="text-4xl font-bold text-green-800">{{ resultado.litros }} L</p>
            </div>
        </CalculadoraCard>

        <div class="mt-8 max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Como fazer Cold Steeping</h2>
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <ol class="space-y-3 list-decimal list-inside">
                    <li class="text-gray-700">
                        Moer a quantidade desejada de malte. Pode fazer uma moagem um pouco mais fina do que a moagem comum para mostura, se desejar.
                    </li>
                    <li class="text-gray-700">
                        Utilize um recipiente, preferencialmente de vidro, como uma jarra, becker ou erlenmeyer.
                    </li>
                    <li class="text-gray-700">
                        Coloque o malte e adicione a quantidade de água calculada (filtrada ou fervida e <strong>resfriada</strong>).
                    </li>
                    <li class="text-gray-700">
                        Cubra com papel alumínio ou filme plástico.
                    </li>
                    <li class="text-gray-700">
                        Deixe descansar por 24h. Agite de vez em quando.
                    </li>
                    <li class="text-gray-700">
                        Após 24h, coe a solução para deixar o bagaço para trás. <strong>Sempre cuide com os recipientes e utensílios, para que sejam alimentícios e estejam bem limpos.</strong>
                    </li>
                    <li class="text-gray-700">
                        Se for adicionar na fermentação/maturação, faça uma pasteurização:
                        <ul class="ml-6 mt-2 space-y-2 list-disc list-inside">
                            <li>Aqueça a 70ºC e mantenha por 10min.</li>
                            <li>Resfrie rapidamente (utilizando uma bacia com gelo, por exemplo).</li>
                        </ul>
                    </li>
                </ol>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-gray-700 mb-3">Quer entender os objetivos e procedimentos da extração a frio? Leia:</p>
                    <ul class="space-y-2">
                        <li>
                            <a href="http://beersmith.com/blog/2011/11/17/brewing-beer-with-dark-grains-steeping-versus-mashing/" target="_blank" class="text-amber-600 hover:text-amber-700 underline">
                                Artigo do Beersmith
                            </a>
                        </li>
                        <li>
                            <a href="http://www.homebrewersassociation.org/how-to-brew/cold-steeping-getting-the-most-out-of-dark-grains/" target="_blank" class="text-amber-600 hover:text-amber-700 underline">
                                Artigo da AHA
                            </a>
                        </li>
                        <li>
                            <a href="http://www.amazon.com/Brewing-Better-Beer-Advanced-Homebrewers/dp/0937381985" target="_blank" class="text-amber-600 hover:text-amber-700 underline">
                                Livro Brewing Better Beer
                            </a> do Gordon Strong
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
