<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import CalculadoraCard from '@/Components/CalculadoraCard.vue'

defineProps({
    total: { type: Number, required: true },
})
</script>

<template>
    <Head title="Contador de Acessos" />
    <AppLayout>
        <CalculadoraCard titulo="Contador de Acessos">
            <div class="text-center py-8">
                <p class="text-5xl font-bold text-amber-600">{{ total.toLocaleString('pt-BR') }}</p>
                <p class="text-sm text-gray-500 mt-2">acessos registrados</p>
            </div>
        </CalculadoraCard>
    </AppLayout>
</template>
