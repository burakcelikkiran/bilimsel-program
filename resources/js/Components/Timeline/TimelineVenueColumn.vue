<!-- resources/js/Components/Timeline/TimelineVenueColumn.vue -->
<template>
    <div class="timeline-venue-column">
        <!-- Venue Header -->
        <div class="venue-header mb-4">
            <div
                class="rounded-lg shadow-sm border-2 p-4"
                :style="{
                    borderColor: venueData.color || '#3B82F6',
                    backgroundColor: `${venueData.color || '#3B82F6'}10`,
                }"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <!-- Venue Color Indicator -->
                        <div
                            class="w-4 h-4 rounded-full flex-shrink-0"
                            :style="{
                                backgroundColor: venueData.color || '#3B82F6',
                            }"
                        ></div>

                        <!-- Venue Info -->
                        <div>
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                {{ venueData.display_name || venueData.name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{
                                    venueData.capacity
                                        ? `Kapasite: ${venueData.capacity}`
                                        : "Salon"
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Venue Stats -->
                    <div class="text-right">
                        <div
                            class="text-lg font-bold text-gray-900 dark:text-white"
                        >
                            {{ venueData.sessions.length }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Oturum
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions Timeline -->
        <div class="venue-sessions space-y-3">
            <TimelineSessionCard
                v-for="session in sortedSessions"
                :key="session.id"
                :session-data="session"
                :venue-data="venueData"
                :day-data="dayData"
                :event="event"
                @session-click="$emit('session-click', $event)"
                @presentation-click="$emit('presentation-click', $event)"
            />
        </div>

        <!-- Empty Sessions State -->
        <div v-if="venueData.sessions.length === 0" class="empty-sessions">
            <div
                class="bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-8 text-center"
            >
                <div class="mx-auto h-12 w-12 text-gray-400 mb-3">
                    <ClockIcon class="h-full w-full" />
                </div>
                <h4
                    class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1"
                >
                    Bu salonda oturum yok
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ venueData.display_name || venueData.name }} salonu için
                    bu gün program bulunmuyor.
                </p>
            </div>
        </div>

        <!-- Time Conflicts Warning -->
        <div v-if="timeConflicts.length > 0" class="time-conflicts mt-4">
            <div
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3"
            >
                <div class="flex items-start space-x-2">
                    <ExclamationTriangleIcon
                        class="h-5 w-5 text-red-500 mt-0.5 flex-shrink-0"
                    />
                    <div>
                        <h4
                            class="text-sm font-medium text-red-800 dark:text-red-200"
                        >
                            Zaman Çakışması
                        </h4>
                        <p class="text-xs text-red-600 dark:text-red-300 mt-1">
                            Bu salonda {{ timeConflicts.length }} zaman
                            çakışması tespit edildi.
                        </p>
                        <div class="mt-2 space-y-1">
                            <div
                                v-for="conflict in timeConflicts"
                                :key="conflict.id"
                                class="text-xs text-red-600 dark:text-red-300"
                            >
                                • {{ conflict.session1_title }} ↔
                                {{ conflict.session2_title }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Venue Summary Footer -->
        <div v-if="venueData.sessions.length > 0" class="venue-summary mt-4">
            <div
                class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-3"
            >
                <div class="grid grid-cols-3 gap-3 text-center text-xs">
                    <!-- Total Duration -->
                    <div>
                        <div
                            class="font-semibold text-gray-900 dark:text-white"
                        >
                            {{ venueTotalDuration }}
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            Toplam Süre
                        </div>
                    </div>

                    <!-- Time Range -->
                    <div>
                        <div
                            class="font-semibold text-gray-900 dark:text-white"
                        >
                            {{ venueTimeRange }}
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            Zaman Aralığı
                        </div>
                    </div>

                    <!-- Total Presentations -->
                    <div>
                        <div
                            class="font-semibold text-gray-900 dark:text-white"
                        >
                            {{ totalPresentations }}
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            Sunum
                        </div>
                    </div>
                </div>

                <!-- Session Types Distribution -->
                <div
                    v-if="sessionTypeDistribution.length > 0"
                    class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700"
                >
                    <div class="flex items-center justify-center space-x-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400"
                            >Oturum Tipleri:</span
                        >
                        <div class="flex space-x-1">
                            <div
                                v-for="type in sessionTypeDistribution"
                                :key="type.type"
                                class="flex items-center space-x-1"
                            >
                                <div
                                    :class="getSessionTypeColor(type.type)"
                                    class="w-2 h-2 rounded-full"
                                ></div>
                                <span
                                    class="text-xs text-gray-600 dark:text-gray-400"
                                    >{{ type.count }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import TimelineSessionCard from "./TimelineSessionCard.vue";
import { ClockIcon, ExclamationTriangleIcon } from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    venueData: {
        type: Object,
        required: true,
    },
    dayData: {
        type: Object,
        required: true,
    },
    event: {
        type: Object,
        required: true,
    },
});

// Emits
const emit = defineEmits(["session-click", "presentation-click"]);

// Computed
const sortedSessions = computed(() => {
    return [...props.venueData.sessions].sort((a, b) => {
        // Primary sort by start_time
        if (a.start_time && b.start_time) {
            if (a.start_time !== b.start_time) {
                return a.start_time.localeCompare(b.start_time);
            }
        }

        // Secondary sort by sort_order
        const aOrder = a.sort_order || 0;
        const bOrder = b.sort_order || 0;

        return aOrder - bOrder;
    });
});

const venueTotalDuration = computed(() => {
    const totalMinutes = props.venueData.sessions.reduce((total, session) => {
        return total + (session.duration_in_minutes || 0);
    }, 0);

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    if (hours > 0 && minutes > 0) {
        return `${hours}s ${minutes}dk`;
    } else if (hours > 0) {
        return `${hours}s`;
    } else {
        return `${minutes}dk`;
    }
});

const venueTimeRange = computed(() => {
    const sessions = props.venueData.sessions;
    if (sessions.length === 0) return "-";

    let earliestTime = null;
    let latestTime = null;

    sessions.forEach((session) => {
        if (session.start_time) {
            if (!earliestTime || session.start_time < earliestTime) {
                earliestTime = session.start_time;
            }
        }
        if (session.end_time) {
            if (!latestTime || session.end_time > latestTime) {
                latestTime = session.end_time;
            }
        }
    });

    if (earliestTime && latestTime) {
        return `${earliestTime} - ${latestTime}`;
    }

    return "Belirsiz";
});

const totalPresentations = computed(() => {
    return props.venueData.sessions.reduce((total, session) => {
        return total + (session.presentations?.length || 0);
    }, 0);
});

const sessionTypeDistribution = computed(() => {
    const distribution = {};

    props.venueData.sessions.forEach((session) => {
        const type = session.session_type || "other";
        distribution[type] = (distribution[type] || 0) + 1;
    });

    return Object.entries(distribution).map(([type, count]) => ({
        type,
        count,
    }));
});

const timeConflicts = computed(() => {
    const conflicts = [];
    const sessions = sortedSessions.value;

    for (let i = 0; i < sessions.length - 1; i++) {
        const current = sessions[i];
        const next = sessions[i + 1];

        if (
            current.end_time &&
            next.start_time &&
            current.end_time > next.start_time
        ) {
            conflicts.push({
                id: `${current.id}-${next.id}`,
                session1_id: current.id,
                session1_title: current.title,
                session2_id: next.id,
                session2_title: next.title,
                overlap_minutes: calculateTimeDifference(
                    current.end_time,
                    next.start_time
                ),
            });
        }
    }

    return conflicts;
});

// Methods
const getSessionTypeColor = (sessionType) => {
    const colors = {
        plenary: "bg-purple-500",
        parallel: "bg-blue-500",
        workshop: "bg-green-500",
        poster: "bg-yellow-500",
        break: "bg-gray-400",
        lunch: "bg-orange-500",
        social: "bg-pink-500",
    };
    return colors[sessionType] || "bg-gray-500";
};

const calculateTimeDifference = (time1, time2) => {
    // Simple time difference calculation
    // This is a basic implementation - you might want to use a proper date library
    const [h1, m1] = time1.split(":").map(Number);
    const [h2, m2] = time2.split(":").map(Number);

    const minutes1 = h1 * 60 + m1;
    const minutes2 = h2 * 60 + m2;

    return Math.abs(minutes1 - minutes2);
};
</script>

<style scoped>
.timeline-venue-column {
    position: relative;
    min-height: 200px;
}

/* Venue header styling */
.venue-header {
    position: sticky;
    top: 0;
    z-index: 10;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    margin-bottom: 1rem;
    border-radius: 0.5rem;
}

.dark .venue-header {
    background: rgba(17, 24, 39, 0.9);
}

/* Venue sessions container */
.venue-sessions {
    position: relative;
    min-height: 100px;
}

/* Empty sessions styling */
.empty-sessions {
    transition: all 0.2s ease-in-out;
}

.empty-sessions:hover {
    transform: translateY(-1px);
}

/* Time conflicts warning */
.time-conflicts {
    position: relative;
    animation: pulse-warning 2s infinite;
}

@keyframes pulse-warning {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    50% {
        box-shadow: 0 0 0 0.5rem rgba(239, 68, 68, 0);
    }
}

/* Venue summary footer */
.venue-summary {
    position: sticky;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    border-radius: 0.5rem;
}

.dark .venue-summary {
    background: rgba(17, 24, 39, 0.95);
}

/* Session type distribution */
.session-type-distribution {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

/* Responsive behavior */
@media (max-width: 768px) {
    .venue-header {
        position: static;
        background: transparent;
        backdrop-filter: none;
    }

    .venue-summary {
        position: static;
        background: transparent;
        backdrop-filter: none;
    }
}

/* Venue color theme integration */
.venue-themed {
    border-left: 4px solid var(--venue-color);
}

/* Hover effects */
.venue-session-item {
    transition: all 0.2s ease-in-out;
}

.venue-session-item:hover {
    transform: translateX(2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dark .venue-session-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Loading states */
.venue-loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.dark .venue-loading {
    background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
    background-size: 200% 100%;
}
</style>
