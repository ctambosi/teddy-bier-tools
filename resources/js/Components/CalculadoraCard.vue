<script setup>
defineProps({
    titulo:    { type: String, required: true },
    descricao: { type: String, default: '' },
})
</script>

<template>
    <div class="max-w-md mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ titulo }}</h1>
            <p v-if="descricao" class="mt-1 text-gray-500 text-sm">{{ descricao }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <slot />
        </div>
    </div>
</template>
