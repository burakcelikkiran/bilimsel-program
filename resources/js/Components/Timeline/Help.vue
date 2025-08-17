<template>
    <AdminLayout>
        <Head title="Timeline Yardım" />
        
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Timeline Yardım
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Timeline özelliklerini kullanma kılavuzu
                    </p>
                </div>
            </div>

            <!-- Help Sections -->
            <div class="space-y-6">
                <div 
                    v-for="(section, index) in helpSections" 
                    :key="index"
                    class="bg-white shadow rounded-lg"
                >
                    <div class="px-6 py-4">
                        <h2 class="text-lg font-medium text-gray-900 mb-3">
                            {{ section.title }}
                        </h2>
                        <p class="text-gray-700 leading-relaxed">
                            {{ section.content }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-8 flex justify-start">
                <Link 
                    :href="route('admin.dashboard')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
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