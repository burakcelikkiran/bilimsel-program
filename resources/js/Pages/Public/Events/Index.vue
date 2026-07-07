<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    events: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="Etkinlikler" />

    <div class="min-h-screen bg-slate-50">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
                <Link :href="route('welcome')" class="text-lg font-semibold text-slate-800">
                    Bilimsel Program
                </Link>
                <Link
                    v-if="route().has('login')"
                    :href="route('login')"
                    class="text-sm text-blue-600 hover:text-blue-700"
                >
                    Giriş
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8">
            <h1 class="mb-6 text-2xl font-bold text-slate-900">Yayınlanan Etkinlikler</h1>

            <div v-if="events.length === 0" class="rounded-lg border border-dashed border-slate-300 p-8 text-center text-slate-500">
                Henüz yayınlanmış etkinlik bulunmuyor.
            </div>

            <ul v-else class="space-y-4">
                <li
                    v-for="event in events"
                    :key="event.id"
                    class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <Link :href="route('events.show', event.slug)" class="block">
                        <h2 class="text-lg font-semibold text-slate-900">{{ event.name }}</h2>
                        <p v-if="event.organization" class="mt-1 text-sm text-slate-500">
                            {{ event.organization.name }}
                        </p>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ event.start_date }} — {{ event.end_date }}
                        </p>
                        <p v-if="event.description" class="mt-2 line-clamp-2 text-sm text-slate-600">
                            {{ event.description }}
                        </p>
                    </Link>
                </li>
            </ul>
        </main>
    </div>
</template>
