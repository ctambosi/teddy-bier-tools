<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const page = usePage()
const mobileMenuOpen = ref(false)
const openDropdown = ref(null)

const navGroups = computed(() => {
    const grouped = page.props.toolsMetadata || {}
    return Object.entries(grouped).map(([categoria, tools]) => ({
        name: categoria.toLowerCase().replace(/\s+/g, '-'),
        label: categoria,
        links: Object.entries(tools).map(([key, tool]) => ({
            label: tool.label,
            href: route(key),
        })),
    }))
})

function toggleDropdown(name) {
    openDropdown.value = openDropdown.value === name ? null : name
}

function closeDropdowns() {
    openDropdown.value = null
    mobileMenuOpen.value = false
}
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <!-- Navbar -->
        <nav class="bg-gray-900 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-2 text-white font-bold text-lg hover:text-amber-400 transition-colors">
                        <span class="text-2xl">🍺</span>
                        <span class="hidden sm:block">Teddy Bier Tools</span>
                    </Link>

                    <!-- Menu desktop -->
                    <div class="hidden md:flex items-center gap-1">
                        <div
                            v-for="group in navGroups"
                            :key="group.name"
                            class="relative"
                        >
                            <button
                                @click.stop="toggleDropdown(group.name)"
                                class="flex items-center gap-1 px-3 py-2 text-sm text-gray-300 hover:text-amber-400 hover:bg-gray-800 rounded-md transition-colors"
                                :class="{ 'text-amber-400 bg-gray-800': openDropdown === group.name }"
                            >
                                {{ group.label }}
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': openDropdown === group.name }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown -->
                            <div
                                v-show="openDropdown === group.name"
                                class="absolute left-0 top-full mt-1 w-52 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50 py-1"
                            >
                                <a
                                    v-for="link in group.links"
                                    :key="link.href"
                                    :href="link.href"
                                    @click="closeDropdowns"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors"
                                >
                                    {{ link.label }}
                                </a>
                            </div>
                        </div>

                    </div>

                    <!-- Botão hambúrguer (mobile) -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen; openDropdown = null"
                        class="md:hidden p-2 text-gray-300 hover:text-amber-400 hover:bg-gray-800 rounded-md transition-colors"
                        aria-label="Menu"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Menu mobile -->
            <div v-show="mobileMenuOpen" class="md:hidden border-t border-gray-800 pb-2">
                <div v-for="group in navGroups" :key="group.name">
                    <div class="px-4 py-2 text-xs font-semibold text-amber-400 uppercase tracking-wider mt-2">
                        {{ group.label }}
                    </div>
                    <a
                        v-for="link in group.links"
                        :key="link.href"
                        :href="link.href"
                        @click="closeDropdowns"
                        class="block px-6 py-2 text-sm text-gray-300 hover:text-amber-400 hover:bg-gray-800 transition-colors"
                    >
                        {{ link.label }}
                    </a>
                </div>
            </div>
        </nav>

        <!-- Conteúdo da página -->
        <main
            class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8"
            @click="closeDropdowns"
        >
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-500 text-center text-xs py-4 mt-auto">
            Teddy Bier Tools &copy; {{ new Date().getFullYear() }}
        </footer>
    </div>
</template>
