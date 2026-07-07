<template>
    <nav
        v-if="links.length > 3"
        class="flex items-center justify-between"
    >
        <div class="flex justify-between flex-1 sm:hidden">
            <Link
                v-if="links[0].url"
                :href="links[0].url"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-300 rounded-md hover:text-slate-400"
                v-html="links[0].label"
            />
            <Link
                v-if="links[links.length - 1].url"
                :href="links[links.length - 1].url"
                class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-500 bg-white border border-slate-300 rounded-md hover:text-slate-400"
                v-html="links[links.length - 1].label"
            />
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    <span class="font-medium">{{ from }}</span>
                    -
                    <span class="font-medium">{{ to }}</span>
                    /
                    <span class="font-medium">{{ total }}</span>
                    sonuç gösteriliyor
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm rounded-md">
                    <template v-for="(link, key) in links" :key="key">
                        <Link
                            v-if="link.url === null"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-300 cursor-default leading-5"
                            :class="{
                                'rounded-l-md': key === 0,
                                'rounded-r-md': key === links.length - 1,
                                'ml-px': key > 0,
                            }"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out border border-slate-300 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue"
                            :class="{
                                'bg-white text-slate-500 hover:text-slate-400': !link.active,
                                'bg-blue-50 border-blue-500 text-blue-600': link.active,
                                'rounded-l-md': key === 0,
                                'rounded-r-md': key === links.length - 1,
                                'ml-px': key > 0,
                            }"
                            :href="link.url"
                            v-html="link.label"
                        />
                    </template>
                </span>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    links: {
        type: Array,
        required: true
    },
    from: {
        type: Number,
        default: 0
    },
    to: {
        type: Number,
        default: 0
    },
    total: {
        type: Number,
        default: 0
    }
})
</script>