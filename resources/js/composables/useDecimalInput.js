import { ref } from 'vue'

export function formataUmaCasaDecimal(digits) {
    if (!digits) return ''
    if (digits.length < 2) return digits
    const intPart = digits.slice(0, -1)
    const decPart = digits.slice(-1)
    const intFormatted = parseInt(intPart, 10).toLocaleString('pt-BR')
    return intFormatted + ',' + decPart
}

export function formataDuasCasasDecimais(digits) {
    if (!digits) return ''
    if (digits.length < 3) return '0,' + digits.padStart(2, '0')
    const intPart = digits.slice(0, -2)
    const decPart = digits.slice(-2)
    const intFormatted = parseInt(intPart, 10).toLocaleString('pt-BR')
    return intFormatted + ',' + decPart
}

export function formatarPtBr(value, decimais = 1) {
    if (value === null || value === undefined || value === '') return ''
    const str = String(value).replace(/\./g, '').replace(',', '.')
    const num = parseFloat(str)
    if (isNaN(num)) return ''
    return num.toLocaleString('pt-BR', { minimumFractionDigits: decimais, maximumFractionDigits: decimais })
}

export function parsePtBr(str) {
    if (str === null || str === undefined || str === '') return ''
    return String(str).replace(/\./g, '').replace(',', '.')
}

export function useDecimalInput({ negativo = false, casas = 1 } = {}) {
    const display = ref('')
    const numeric = ref('')

    function _formatar(digits) {
        return casas === 2 ? formataDuasCasasDecimais(digits) : formataUmaCasaDecimal(digits)
    }

    function _toNumeric(digits) {
        if (casas === 2) {
            return digits.length < 3
                ? '0.' + digits.padStart(2, '0')
                : digits.slice(0, -2) + '.' + digits.slice(-2)
        }
        return digits.length < 2 ? digits : digits.slice(0, -1) + '.' + digits.slice(-1)
    }

    function onInput(e) {
        const raw = e.target.value
        const isNeg = negativo && raw.startsWith('-')
        const digits = raw.replace(/\D/g, '')
        if (!digits) {
            display.value = ''
            numeric.value = ''
            e.target.value = ''
            return
        }
        const formatted = _formatar(digits)
        display.value = isNeg ? '-' + formatted : formatted
        const numVal = _toNumeric(digits)
        numeric.value = isNeg ? '-' + numVal : numVal
        e.target.value = display.value
    }

    function reset() {
        display.value = ''
        numeric.value = ''
    }

    function set(value) {
        if (value === null || value === undefined || value === '') { reset(); return }
        const num = parseFloat(String(value).replace(',', '.'))
        if (isNaN(num)) { reset(); return }
        const isNeg = num < 0
        const digits = Math.abs(num).toFixed(casas).replace('.', '')
        const formatted = _formatar(digits)
        display.value = isNeg ? '-' + formatted : formatted
        numeric.value = num.toFixed(casas)
    }

    return { display, numeric, onInput, reset, set }
}
