<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    CalendarDaysIcon,
    BuildingOffice2Icon,
    QueueListIcon,
    PresentationChartBarIcon,
    MapPinIcon,
} from '@heroicons/vue/24/outline';
import ProgramScheduleTable from '@/Components/Public/ProgramScheduleTable.vue';
import PublicEventLayout from '@/Layouts/PublicEventLayout.vue';
import {
    PRESENTATION_LEGEND_ITEMS,
    SESSION_LEGEND_ITEMS,
} from '@/Support/programTypeStyles';

const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    statistics: {
        type: Object,
        default: () => ({}),
    },
    days: {
        type: Array,
        default: () => [],
    },
    activeTab: {
        type: String,
        default: 'program',
    },
});

const selectedDayId = ref(props.days[0]?.id ?? null);
const selectedVenueId = ref(props.days[0]?.venues?.[0]?.id ?? null);

const selectedDay = computed(() =>
    props.days.find((day) => day.id === selectedDayId.value) ?? null,
);

const selectedVenue = computed(() =>
    selectedDay.value?.venues?.find((venue) => venue.id === selectedVenueId.value) ?? null,
);

const selectedSessions = computed(() => selectedVenue.value?.sessions ?? []);

watch(selectedDayId, (dayId) => {
    const day = props.days.find((item) => item.id === dayId);
    selectedVenueId.value = day?.venues?.[0]?.id ?? null;
});

function selectDay(dayId) {
    selectedDayId.value = dayId;
}

function selectVenue(venueId) {
    selectedVenueId.value = venueId;
}

const statItems = computed(() => [
    {
        label: 'Gün',
        value: props.statistics.total_days ?? 0,
        icon: CalendarDaysIcon,
        accent: 'from-blue-500 to-blue-600',
        bg: 'bg-blue-50',
        text: 'text-blue-700',
    },
    {
        label: 'Salon',
        value: props.statistics.total_venues ?? 0,
        icon: BuildingOffice2Icon,
        accent: 'from-violet-500 to-violet-600',
        bg: 'bg-violet-50',
        text: 'text-violet-700',
    },
    {
        label: 'Oturum',
        value: props.statistics.total_sessions ?? 0,
        icon: QueueListIcon,
        accent: 'from-emerald-500 to-emerald-600',
        bg: 'bg-emerald-50',
        text: 'text-emerald-700',
    },
    {
        label: 'Sunum',
        value: props.statistics.total_presentations ?? 0,
        icon: PresentationChartBarIcon,
        accent: 'from-amber-500 to-amber-600',
        bg: 'bg-amber-50',
        text: 'text-amber-700',
    },
]);
</script>

<template>
    <Head :title="`${event.name} — Program`" />

    <PublicEventLayout :event="event" :active-tab="activeTab">
        <div
            v-if="!days.length"
            class="flex flex-col items-center rounded-2xl border border-dashed border-slate-300 bg-white px-8 py-16 text-center shadow-sm"
        >
            <CalendarDaysIcon class="mb-4 h-12 w-12 text-slate-300" />
            <p class="text-lg font-medium text-slate-700">Program henüz yayınlanmadı</p>
            <p class="mt-1 text-sm text-slate-500">Etkinlik programı hazır olduğunda burada görüntülenecek.</p>
        </div>

        <template v-else>
            <div class="mb-8 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                <div
                    v-for="item in statItems"
                    :key="item.label"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-shadow hover:shadow-md"
                >
                    <div
                        class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r opacity-80"
                        :class="item.accent"
                    />
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="item.bg"
                        >
                            <component :is="item.icon" class="h-5 w-5" :class="item.text" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold tabular-nums text-slate-900">{{ item.value }}</div>
                            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ item.label }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/50 px-4 py-3 sm:px-5">
                    <h2 class="text-sm font-semibold text-slate-700">Program Günleri</h2>
                </div>
                <div class="flex gap-2 overflow-x-auto p-3 sm:flex-wrap sm:p-4">
                    <button
                        v-for="day in days"
                        :key="day.id"
                        type="button"
                        class="shrink-0 rounded-xl border px-4 py-3 text-left transition-all duration-150"
                        :class="selectedDayId === day.id
                            ? 'border-blue-200 bg-blue-600 text-white shadow-md shadow-blue-600/20'
                            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50'"
                        @click="selectDay(day.id)"
                    >
                        <span class="block text-sm font-semibold">{{ day.title }}</span>
                        <span
                            class="mt-0.5 block text-xs"
                            :class="selectedDayId === day.id ? 'text-blue-100' : 'text-slate-500'"
                        >
                            {{ day.formatted_date }} · {{ day.day_name }}
                        </span>
                    </button>
                </div>
            </section>

            <section
                v-if="selectedDay?.venues?.length"
                class="mb-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
            >
                <div class="border-b border-slate-100 bg-slate-50/50 px-4 py-3 sm:px-5">
                    <h2 class="text-sm font-semibold text-slate-700">Salonlar</h2>
                </div>
                <div class="flex gap-2 overflow-x-auto p-3 sm:flex-wrap sm:p-4">
                    <button
                        v-for="venue in selectedDay.venues"
                        :key="venue.id"
                        type="button"
                        class="inline-flex shrink-0 items-center gap-2.5 rounded-full border px-4 py-2 text-sm font-medium transition-all duration-150"
                        :class="selectedVenueId === venue.id
                            ? 'border-transparent text-white shadow-md'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                        :style="selectedVenueId === venue.id
                            ? { backgroundColor: venue.color || '#2563eb', boxShadow: `0 4px 14px ${venue.color || '#2563eb'}40` }
                            : {}"
                        @click="selectVenue(venue.id)"
                    >
                        <span
                            class="h-2.5 w-2.5 rounded-full ring-2 ring-white/30"
                            :style="{ backgroundColor: selectedVenueId === venue.id ? '#fff' : (venue.color || '#94a3b8') }"
                        />
                        {{ venue.display_name || venue.name }}
                        <span
                            class="rounded-full px-1.5 py-0.5 text-xs tabular-nums"
                            :class="selectedVenueId === venue.id
                                ? 'bg-white/20 text-white'
                                : 'bg-slate-100 text-slate-500'"
                        >
                            {{ venue.sessions?.length || 0 }}
                        </span>
                    </button>
                </div>
            </section>

            <section
                v-if="selectedVenue"
                class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-2 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                    :style="{ borderLeftWidth: '4px', borderLeftColor: selectedVenue.color || '#2563eb' }"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <MapPinIcon class="h-4 w-4 text-slate-400" />
                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ selectedVenue.display_name || selectedVenue.name }}
                            </h3>
                        </div>
                        <p v-if="selectedDay" class="mt-0.5 text-sm text-slate-500">
                            {{ selectedDay.title }} · {{ selectedDay.formatted_date }}
                        </p>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm text-slate-600">
                        <QueueListIcon class="h-4 w-4 text-slate-400" />
                        <span class="font-medium tabular-nums">{{ selectedSessions.length }}</span>
                        oturum
                    </div>
                </div>

                <div class="border-b border-slate-100 bg-slate-50/50 px-4 py-3 sm:px-6">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Oturum tipleri</span>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="item in SESSION_LEGEND_ITEMS"
                                    :key="item.key"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-xs font-medium text-slate-700"
                                >
                                    <span
                                        class="h-2.5 w-2.5 rounded-full"
                                        :style="{ backgroundColor: item.color }"
                                    />
                                    {{ item.label }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sunum tipleri</span>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="item in PRESENTATION_LEGEND_ITEMS"
                                    :key="item.key"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-xs font-medium text-slate-700"
                                >
                                    <span
                                        class="h-2.5 w-2.5 rounded-full"
                                        :style="{ backgroundColor: item.color }"
                                    />
                                    {{ item.label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <ProgramScheduleTable
                        :sessions="selectedSessions"
                        :venue-color="selectedVenue.color"
                    />
                </div>
            </section>
        </template>
    </PublicEventLayout>
</template>
