<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    MagnifyingGlassIcon,
    UserGroupIcon,
    MicrophoneIcon,
    ChevronDownIcon,
    ChevronUpIcon,
    CalendarDaysIcon,
    MapPinIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';
import PublicEventLayout from '@/Layouts/PublicEventLayout.vue';
import { getPresentationTypeLabel } from '@/Support/programTypeStyles';

const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    participants: {
        type: Array,
        default: () => [],
    },
    total: {
        type: Number,
        default: 0,
    },
    activeTab: {
        type: String,
        default: 'speakers',
    },
});

const searchQuery = ref('');
const expandedParticipantId = ref(null);

const filteredParticipants = computed(() => {
    const query = searchQuery.value.trim().toLocaleLowerCase('tr');

    if (!query) {
        return props.participants;
    }

    return props.participants.filter((participant) => {
        const haystack = [
            participant.full_name,
            participant.title,
            participant.affiliation,
            ...(participant.role_labels ?? []),
        ]
            .filter(Boolean)
            .join(' ')
            .toLocaleLowerCase('tr');

        return haystack.includes(query);
    });
});

function toggleParticipant(participantId) {
    expandedParticipantId.value = expandedParticipantId.value === participantId
        ? null
        : participantId;
}

function isExpanded(participantId) {
    return expandedParticipantId.value === participantId;
}

function formatTimeRange(startTime, endTime) {
    if (!startTime && !endTime) {
        return '—';
    }

    if (startTime && endTime) {
        return `${startTime} – ${endTime}`;
    }

    return startTime || endTime;
}

function moderatorRoles(participant) {
    return participant.roles?.filter((role) => role.type === 'moderator') ?? [];
}

function speakerRoles(participant) {
    return participant.roles?.filter((role) => role.type === 'speaker') ?? [];
}

function programLink() {
    return route('events.program', props.event.slug);
}
</script>

<template>
    <Head :title="`${event.name} — Konuşmacılar`" />

    <PublicEventLayout :event="event" :active-tab="activeTab">
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/50 px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Konuşmacılar ve Görevliler</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ total }} kişi programda görev alıyor
                        </p>
                    </div>
                    <div class="relative w-full sm:max-w-xs">
                        <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="İsim veya kurum ara..."
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                    </div>
                </div>
            </div>

            <div
                v-if="!participants.length"
                class="flex flex-col items-center px-6 py-16 text-center"
            >
                <UserGroupIcon class="mb-3 h-10 w-10 text-slate-300" />
                <p class="font-medium text-slate-600">Bu kongre için henüz görevli listesi bulunmuyor</p>
            </div>

            <div
                v-else-if="!filteredParticipants.length"
                class="px-6 py-12 text-center text-sm text-slate-500"
            >
                Aramanızla eşleşen isim bulunamadı.
            </div>

            <ul v-else class="divide-y divide-slate-100">
                <li
                    v-for="participant in filteredParticipants"
                    :key="participant.id"
                >
                    <button
                        type="button"
                        class="flex w-full items-start gap-4 px-4 py-4 text-left transition-colors hover:bg-slate-50 sm:px-6"
                        @click="toggleParticipant(participant.id)"
                    >
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">
                            {{ participant.full_name?.charAt(0) || '?' }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ participant.full_name }}</p>
                                    <p v-if="participant.affiliation" class="mt-0.5 text-sm text-slate-500">
                                        {{ participant.affiliation }}
                                    </p>
                                </div>
                                <component
                                    :is="isExpanded(participant.id) ? ChevronUpIcon : ChevronDownIcon"
                                    class="mt-1 h-5 w-5 shrink-0 text-slate-400"
                                />
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2">
                                <span
                                    v-for="label in participant.role_labels"
                                    :key="label"
                                    class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700"
                                >
                                    {{ label }}
                                </span>
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                    {{ participant.participation_count }} görev
                                </span>
                            </div>
                        </div>
                    </button>

                    <div
                        v-if="isExpanded(participant.id)"
                        class="border-t border-slate-100 bg-slate-50/60 px-4 py-4 sm:px-6"
                    >
                        <div v-if="moderatorRoles(participant).length" class="mb-5">
                            <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-800">
                                <UserGroupIcon class="h-4 w-4 text-violet-600" />
                                Oturum Görevleri
                            </h3>
                            <div class="space-y-3">
                                <article
                                    v-for="(role, index) in moderatorRoles(participant)"
                                    :key="`moderator-${role.session_id}-${index}`"
                                    class="rounded-xl border border-violet-200/70 bg-white p-4 shadow-sm"
                                >
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-800">
                                            {{ role.role_label }}
                                        </span>
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                            {{ role.session_type_label }}
                                        </span>
                                    </div>
                                    <p class="mt-2 font-semibold text-slate-900">{{ role.session_title }}</p>
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1">
                                            <CalendarDaysIcon class="h-3.5 w-3.5" />
                                            {{ role.formatted_date }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <ClockIcon class="h-3.5 w-3.5" />
                                            {{ formatTimeRange(role.start_time, role.end_time) }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <MapPinIcon class="h-3.5 w-3.5" />
                                            {{ role.venue_name }}
                                        </span>
                                    </div>
                                </article>
                            </div>
                        </div>

                        <div v-if="speakerRoles(participant).length">
                            <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-800">
                                <MicrophoneIcon class="h-4 w-4 text-blue-600" />
                                Sunumlar
                            </h3>
                            <div class="space-y-3">
                                <article
                                    v-for="(role, index) in speakerRoles(participant)"
                                    :key="`speaker-${role.presentation_id}-${index}`"
                                    class="rounded-xl border border-blue-200/70 bg-white p-4 shadow-sm"
                                >
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                                            {{ role.speaker_role_label }}
                                        </span>
                                        <span
                                            v-if="role.presentation_type"
                                            class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700"
                                        >
                                            {{ getPresentationTypeLabel(role.presentation_type) }}
                                        </span>
                                    </div>
                                    <p class="mt-2 font-semibold text-slate-900">{{ role.presentation_title }}</p>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Oturum: {{ role.session_title }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1">
                                            <CalendarDaysIcon class="h-3.5 w-3.5" />
                                            {{ role.formatted_date }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <ClockIcon class="h-3.5 w-3.5" />
                                            {{ formatTimeRange(role.presentation_start_time || role.start_time, role.presentation_end_time || role.end_time) }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <MapPinIcon class="h-3.5 w-3.5" />
                                            {{ role.venue_name }}
                                        </span>
                                    </div>
                                </article>
                            </div>
                        </div>

                        <div class="mt-4">
                            <Link
                                :href="programLink()"
                                class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-800"
                            >
                                Programda görüntüle
                            </Link>
                        </div>
                    </div>
                </li>
            </ul>
        </section>
    </PublicEventLayout>
</template>
