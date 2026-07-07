<script setup>
import { ref } from 'vue';
import {
    UserGroupIcon,
    MicrophoneIcon,
    ClockIcon,
    SparklesIcon,
    QueueListIcon,
    ChevronDownIcon,
    ChevronUpIcon,
} from '@heroicons/vue/24/outline';
import {
    getPresentationTypeLabel,
    getPresentationTypeStyle,
    getSessionTypeStyle,
    isCompactSession,
} from '@/Support/programTypeStyles';

defineProps({
    sessions: {
        type: Array,
        default: () => [],
    },
    venueColor: {
        type: String,
        default: '#2563eb',
    },
});

const expandedAbstracts = ref({});

function formatTimeRange(startTime, endTime) {
    if (!startTime && !endTime) {
        return '—';
    }

    if (startTime && endTime) {
        return `${startTime} – ${endTime}`;
    }

    return startTime || endTime;
}

function formatModerators(session) {
    if (!session.moderators?.length) {
        return '';
    }

    const prefix = session.moderator_title || 'Moderatör';
    const names = session.moderators
        .map((person) => {
            const parts = [person.title, person.full_name].filter(Boolean);
            return parts.join(' ');
        })
        .join(', ');

    return `${prefix}: ${names}`;
}

function sessionBorderStyle(session) {
    const style = getSessionTypeStyle(session);

    return { borderLeftColor: style.borderColor };
}

function presentationRowClass(presentation, index) {
    const style = getPresentationTypeStyle(presentation.presentation_type);

    return index % 2 === 0 ? style.rowBg : style.rowBgAlt;
}

function presentationBorderStyle(presentation) {
    const style = getPresentationTypeStyle(presentation.presentation_type);

    return { borderLeftColor: style.borderColor, borderLeftWidth: '4px' };
}

function abstractKey(presentation) {
    return `abstract-${presentation.id}`;
}

function isAbstractExpanded(presentation) {
    return !!expandedAbstracts.value[abstractKey(presentation)];
}

function toggleAbstract(presentation) {
    const key = abstractKey(presentation);
    expandedAbstracts.value = {
        ...expandedAbstracts.value,
        [key]: !expandedAbstracts.value[key],
    };
}

function sessionSpacingClass(session, index) {
    if (index === 0) {
        return '';
    }

    return isCompactSession(session) ? 'mt-2' : 'mt-5';
}
</script>

<template>
    <div
        v-if="!sessions.length"
        class="flex flex-col items-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-6 py-12 text-center"
    >
        <QueueListIcon class="mb-3 h-10 w-10 text-slate-300" />
        <p class="font-medium text-slate-600">Bu salonda henüz oturum bulunmuyor</p>
    </div>

    <div v-else>
        <article
            v-for="(session, sessionIndex) in sessions"
            :key="session.id"
            class="overflow-hidden rounded-xl border bg-white shadow-sm transition-shadow hover:shadow-md"
            :class="[
                getSessionTypeStyle(session).cardBorder,
                sessionSpacingClass(session, sessionIndex),
            ]"
        >
            <!-- Kompakt oturum (ara / öğle) -->
            <div
                v-if="isCompactSession(session)"
                class="flex flex-wrap items-center gap-x-3 gap-y-1 border-l-4 px-3 py-2 sm:px-4"
                :class="getSessionTypeStyle(session).headerBg"
                :style="sessionBorderStyle(session)"
            >
                <div
                    class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 font-mono text-xs font-semibold tabular-nums ring-1"
                    :class="getSessionTypeStyle(session).timeBadge"
                >
                    <ClockIcon class="h-3 w-3 opacity-60" />
                    {{ formatTimeRange(session.start_time, session.end_time) }}
                </div>
                <h4
                    class="text-sm font-semibold"
                    :class="getSessionTypeStyle(session).titleColor"
                >
                    {{ session.title }}
                </h4>
                <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="getSessionTypeStyle(session).badge"
                >
                    {{ session.type_label || session.session_type }}
                </span>
            </div>

            <!-- Normal oturum başlığı -->
            <div
                v-else
                class="border-l-4 px-4 py-4 sm:px-5"
                :class="getSessionTypeStyle(session).headerBg"
                :style="sessionBorderStyle(session)"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-5">
                    <div class="shrink-0">
                        <div
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-mono text-sm font-semibold tabular-nums shadow-sm ring-1"
                            :class="getSessionTypeStyle(session).timeBadge"
                        >
                            <ClockIcon class="h-3.5 w-3.5 opacity-60" />
                            {{ formatTimeRange(session.start_time, session.end_time) }}
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-start gap-2">
                            <h4
                                class="text-lg font-bold leading-snug"
                                :class="getSessionTypeStyle(session).titleColor"
                            >
                                {{ session.title }}
                            </h4>
                            <span
                                class="inline-flex shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="getSessionTypeStyle(session).badge"
                            >
                                {{ session.type_label || session.session_type }}
                            </span>
                        </div>

                        <p
                            v-if="session.description"
                            class="mt-2 text-sm leading-relaxed"
                            :class="getSessionTypeStyle(session).headerTextColor"
                        >
                            {{ session.description }}
                        </p>

                        <div
                            v-if="formatModerators(session)"
                            class="mt-2.5 flex items-start gap-2 text-sm"
                            :class="getSessionTypeStyle(session).headerTextColor"
                        >
                            <UserGroupIcon
                                class="mt-0.5 h-4 w-4 shrink-0"
                                :class="getSessionTypeStyle(session).iconColor"
                            />
                            <span>{{ formatModerators(session) }}</span>
                        </div>

                        <div
                            v-if="session.sponsor"
                            class="mt-2 flex items-center gap-2 text-xs"
                            :class="getSessionTypeStyle(session).headerTextColor"
                        >
                            <SparklesIcon class="h-3.5 w-3.5 text-amber-400" />
                            <span>
                                Sponsor:
                                <span class="font-medium">
                                    {{ session.sponsor.name }}
                                </span>
                            </span>
                        </div>

                        <div v-if="session.categories?.length" class="mt-3 flex flex-wrap gap-1.5">
                            <span
                                v-for="category in session.categories"
                                :key="category.id"
                                class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium"
                                :style="{
                                    backgroundColor: (category.color || '#64748b') + '18',
                                    color: category.color || '#475569',
                                    border: `1px solid ${(category.color || '#64748b')}33`,
                                }"
                            >
                                {{ category.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sunum listesi -->
            <div
                v-if="session.presentations?.length"
                class="divide-y divide-slate-100 border-t border-slate-100/80 p-2 sm:p-3"
                :class="getSessionTypeStyle(session).presentationContainerBg"
            >
                <div
                    v-for="(presentation, index) in session.presentations"
                    :key="presentation.id"
                    class="relative flex gap-4 rounded-lg border-l-4 px-3 py-3.5 sm:px-4 sm:py-4"
                    :class="presentationRowClass(presentation, index)"
                    :style="presentationBorderStyle(presentation)"
                >
                    <div class="hidden w-28 shrink-0 sm:block">
                        <span class="inline-flex items-center gap-1 font-mono text-xs tabular-nums text-slate-500">
                            <ClockIcon class="h-3 w-3 opacity-50" />
                            {{ formatTimeRange(presentation.start_time, presentation.end_time) }}
                        </span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                v-if="presentation.presentation_type"
                                class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold"
                                :class="getPresentationTypeStyle(presentation.presentation_type).badge"
                            >
                                {{ getPresentationTypeLabel(presentation.presentation_type) }}
                            </span>
                            <span
                                v-if="presentation.duration_minutes"
                                class="inline-flex items-center gap-1 text-xs text-slate-400"
                            >
                                <ClockIcon class="h-3 w-3" />
                                {{ presentation.duration_minutes }} dk
                            </span>
                        </div>

                        <div class="mt-1.5 flex items-start gap-2">
                            <MicrophoneIcon
                                class="mt-0.5 h-4 w-4 shrink-0"
                                :class="getPresentationTypeStyle(presentation.presentation_type).iconColor"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold leading-snug text-slate-900">
                                    {{ presentation.title }}
                                </p>

                                <p class="mt-1 font-mono text-xs text-slate-400 sm:hidden">
                                    {{ formatTimeRange(presentation.start_time, presentation.end_time) }}
                                </p>

                                <div
                                    v-if="presentation.speakers?.length"
                                    class="mt-2 space-y-1"
                                >
                                    <div
                                        v-for="speaker in presentation.speakers"
                                        :key="speaker.id"
                                    >
                                        <p class="text-sm font-semibold text-slate-800">
                                            {{ [speaker.title, speaker.full_name].filter(Boolean).join(' ') }}
                                        </p>
                                        <p
                                            v-if="speaker.affiliation"
                                            class="text-xs text-slate-500"
                                        >
                                            {{ speaker.affiliation }}
                                        </p>
                                    </div>
                                </div>

                                <div v-if="presentation.abstract" class="mt-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800"
                                        @click="toggleAbstract(presentation)"
                                    >
                                        <component
                                            :is="isAbstractExpanded(presentation) ? ChevronUpIcon : ChevronDownIcon"
                                            class="h-3.5 w-3.5"
                                        />
                                        {{ isAbstractExpanded(presentation) ? 'Özeti gizle' : 'Özet göster' }}
                                    </button>
                                    <p
                                        v-if="isAbstractExpanded(presentation)"
                                        class="mt-1.5 text-sm leading-relaxed text-slate-500"
                                    >
                                        {{ presentation.abstract }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </div>
</template>
