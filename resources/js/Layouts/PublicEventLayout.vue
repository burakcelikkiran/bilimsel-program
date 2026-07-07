<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { formatEventDateRange } from '@/Support/dateFormat';

const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    activeTab: {
        type: String,
        default: 'overview',
    },
});

const tabs = computed(() => [
    { key: 'overview', label: 'Genel', href: route('events.show', props.event.slug) },
    { key: 'program', label: 'Program', href: route('events.program', props.event.slug) },
    { key: 'speakers', label: 'Konuşmacılar', href: route('events.speakers', props.event.slug) },
    { key: 'sponsors', label: 'Sponsorlar', href: route('events.sponsors', props.event.slug) },
]);

const eventDateRange = computed(() =>
    formatEventDateRange(props.event.start_date, props.event.end_date),
);
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-slate-100 via-slate-50 to-white">
        <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 pb-5 pt-6 sm:px-6">
                <Link
                    :href="route('events.index')"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-blue-600"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Tüm etkinlikler
                </Link>

                <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p v-if="event.organization" class="text-xs font-semibold uppercase tracking-widest text-blue-600">
                            {{ event.organization.name }}
                        </p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            {{ event.name }}
                        </h1>
                        <p v-if="eventDateRange" class="mt-1.5 text-sm text-slate-500">
                            {{ eventDateRange }}
                        </p>
                    </div>
                </div>

                <nav class="mt-6 flex gap-1 overflow-x-auto rounded-xl bg-slate-100/80 p-1">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.key"
                        :href="tab.href"
                        class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition-all duration-150"
                        :class="activeTab === tab.key
                            ? 'bg-white text-blue-700 shadow-sm ring-1 ring-slate-200/80'
                            : 'text-slate-600 hover:bg-white/60 hover:text-slate-900'"
                    >
                        {{ tab.label }}
                    </Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
            <slot />
        </main>
    </div>
</template>
