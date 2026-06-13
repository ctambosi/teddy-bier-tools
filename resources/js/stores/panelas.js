import { defineStore } from 'pinia'
import { useLocalStorage } from '@vueuse/core'

export const usePanelasStore = defineStore('panelas', () => {
    const panelas = useLocalStorage('bier_panelas_v1', [])

    function salvar({ id, nome, diametro, alturaPanela, perda }) {
        if (id !== null && id !== undefined) {
            const idx = panelas.value.findIndex(p => p.id === id)
            if (idx !== -1) {
                panelas.value[idx] = { id, nome, diametro, alturaPanela, perda }
                return
            }
        }
        panelas.value = [...panelas.value, { id: Date.now(), nome, diametro, alturaPanela, perda }]
    }

    function remover(id) {
        panelas.value = panelas.value.filter(p => p.id !== id)
    }

    return { panelas, salvar, remover }
})
