<script setup>
// Input especializado para SG (specific gravity).
// O usuário digita os dígitos (ex: 1048) e o campo exibe 1.048 automaticamente.
const props = defineProps({
    modelValue: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue'])

function onInput(e) {
    const digits = e.target.value.replace(/\D/g, '').slice(0, 4)
    const formatted = digits.length <= 1 ? digits : `${digits[0]}.${digits.slice(1)}`
    e.target.value = formatted
    emit('update:modelValue', formatted)
}
</script>

<template>
    <input
        type="text"
        inputmode="numeric"
        :value="modelValue"
        placeholder="1.048"
        maxlength="5"
        v-bind="$attrs"
        @input="onInput"
    />
</template>
