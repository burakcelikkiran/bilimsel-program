<template>
    <AdminLayout>
        <Head title="Timeline Yardım" />
        
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white dark:bg-slate-800 shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">
                        Timeline Yardım
                    </h1>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Timeline özelliklerini kullanma kılavuzu
                    </p>
                </div>
            </div>

            <!-- Help Sections -->
            <div class="space-y-6">
                <div 
                    v-for="(section, index) in helpSections" 
                    :key="index"
                    class="bg-white dark:bg-slate-800 shadow rounded-lg"
                >
                    <div class="px-6 py-4">
                        <h2 class="text-lg font-medium text-slate-900 dark:text-white mb-3">
                            {{ section.title }}
                        </h2>
                        <p class="text-slate-700 dark:text-slate-300 leading-relaxed">
                            {{ section.content }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-8 flex justify-start">
                <Link 
                    :href="route('admin.dashboard')"
                    class="inline-flex items-center px-4 py-2 bg-slate-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 active:bg-slate-900 focus:outline-none focus:border-slate-900 focus:ring ring-slate-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Geri Dön
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

// Props from controller
defineProps({
    helpSections: {
        type: Array,
        default: () => []
    }
})
</script>