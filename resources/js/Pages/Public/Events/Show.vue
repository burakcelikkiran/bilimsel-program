<script setup>
import { Head } from '@inertiajs/vue3';
import PublicEventLayout from '@/Layouts/PublicEventLayout.vue';

defineProps({
    event: {
        type: Object,
        required: true,
    },
    activeTab: {
        type: String,
        default: 'overview',
    },
    sponsors: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="event.name" />

    <PublicEventLayout :event="event" :active-tab="activeTab">
        <section v-if="activeTab === 'overview'" class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-base leading-relaxed text-slate-700">
                {{ event.description || 'Açıklama bulunmuyor.' }}
            </p>
            <p v-if="event.start_date" class="mt-6 inline-flex rounded-full bg-slate-100 px-4 py-1.5 text-sm text-slate-600">
                {{ event.start_date }} — {{ event.end_date }}
            </p>
        </section>

        <section v-else-if="activeTab === 'speakers'" class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-slate-500 shadow-sm">
            Konuşmacı listesi yakında güncellenecek.
        </section>

        <section v-else-if="activeTab === 'sponsors'">
            <ul v-if="sponsors.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <li
                    v-for="sponsor in sponsors"
                    :key="sponsor.id"
                    class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
                >
                    <p class="font-semibold text-slate-900">{{ sponsor.name }}</p>
                    <p v-if="sponsor.sponsor_level" class="mt-1 text-sm capitalize text-slate-500">{{ sponsor.sponsor_level }}</p>
                </li>
            </ul>
            <p v-else class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center text-slate-500 shadow-sm">
                Sponsor bilgisi bulunmuyor.
            </p>
        </section>
    </PublicEventLayout>
</template>
