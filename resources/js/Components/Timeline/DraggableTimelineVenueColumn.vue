<!-- resources/js/Components/Timeline/DraggableTimelineVenueColumnNew.vue -->
<template>
    <div class="draggable-timeline-venue-column">
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
                            {{ sessionCount }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Oturum
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Draggable Sessions Timeline with FormKit -->
        <div v-if="hasSessions" class="venue-sessions">
            <ul
                ref="sessionsList"
                class="space-y-3 min-h-[100px] p-2 rounded-lg transition-colors bg-gray-50 dark:bg-gray-800/50"
            >
                <li
                    v-for="(session, index) in sessions"
                    :key="session.id"
                    class="session-item"
                >
                    <DraggableTimelineSessionCard
                        :session-data="session"
                        :venue-data="venueData"
                        :day-data="dayData"
                        :event="event"
                        :sort-order="index"
                        :is-last-session="index === sessions.length - 1"
                        @session-click="$emit('session-click', $event)"
                        @presentation-click="$emit('presentation-click', $event)"
                        @move-up="handleMoveUp"
                        @move-down="handleMoveDown"
                    />
                </li>
            </ul>
        </div>

        <!-- Empty Sessions State -->
        <div v-else class="empty-sessions">
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
        <div v-if="hasSessions" class="venue-summary mt-4">
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
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useDragAndDrop } from "@formkit/drag-and-drop/vue";
import DraggableTimelineSessionCard from "./DraggableTimelineSessionCard.vue";
import {
    ClockIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

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
    isDragging: {
        type: Boolean,
        default: false,
    },
    allVenues: {
        type: Array,
        default: () => [],
    },
});

// Emits
const emit = defineEmits([
    "session-click",
    "presentation-click",
    "session-moved",
    "session-updated",
    "drag-start",
    "drag-end",
]);

// Reactive data
const sessionsList = ref(null);
const sessions = ref([]);

// Initialize sessions from props
onMounted(() => {
    if (props.venueData.sessions && Array.isArray(props.venueData.sessions)) {
        // Deep clone to avoid mutating original data
        sessions.value = JSON.parse(JSON.stringify(props.venueData.sessions));
        initializeDragAndDrop();
    }
});

// Initialize drag and drop
const initializeDragAndDrop = () => {
    if (!sessionsList.value) return;
    
    useDragAndDrop(sessionsList, {
        values: sessions,
        group: `venue-${props.venueData.id}`,
        dragHandle: '.drag-handle',
        animations: {
            duration: 150,
        },
    });
};

// Watch for prop changes - only update if parent explicitly changes the data
watch(
    () => props.venueData.sessions,
    (newSessions, oldSessions) => {
        // Only update if sessions actually changed from parent
        if (newSessions && Array.isArray(newSessions) && 
            JSON.stringify(newSessions) !== JSON.stringify(oldSessions)) {
            // Deep clone to avoid mutating original data
            sessions.value = JSON.parse(JSON.stringify(newSessions));
        }
    },
    { deep: true }
);

// Watch for session changes
watch(sessions, (newSessions) => {
    if (!newSessions || !Array.isArray(newSessions)) return;
    
    // Don't update if we're just initializing
    if (JSON.stringify(newSessions) === JSON.stringify(props.venueData.sessions)) {
        return;
    }
    
    // Update sort orders
    newSessions.forEach((session, index) => {
        session.sort_order = index;
    });
    
    // Recalculate times
    calculateNewTimes();
    
    // Emit update
    emit("session-updated", {
        session_order: newSessions.map(s => s.id),
        venue_id: props.venueData.id,
        day_id: props.dayData.id,
    });
}, { deep: true });

// Computed
const hasSessions = computed(() => {
    return sessions.value && sessions.value.length > 0;
});

const sessionCount = computed(() => {
    return sessions.value ? sessions.value.length : 0;
});

const timeConflicts = computed(() => {
    const conflicts = [];
    const list = sessions.value;
    if (!list || !Array.isArray(list)) return conflicts;
    
    for (let i = 0; i < list.length; i++) {
        for (let j = i + 1; j < list.length; j++) {
            const session1 = list[i];
            const session2 = list[j];
            
            if (session1.start_time && session1.end_time && 
                session2.start_time && session2.end_time) {
                if (checkTimeOverlap(session1, session2)) {
                    conflicts.push({
                        id: `${session1.id}-${session2.id}`,
                        session1_title: session1.title,
                        session2_title: session2.title,
                    });
                }
            }
        }
    }
    return conflicts;
});

const venueTotalDuration = computed(() => {
    let totalMinutes = 0;
    const list = sessions.value;
    if (!list || !Array.isArray(list)) return "0dk";
    
    list.forEach(session => {
        if (session.duration_in_minutes) {
            totalMinutes += session.duration_in_minutes;
        }
    });
    
    if (totalMinutes === 0) return "0dk";
    
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
    const list = sessions.value;
    if (!list || !Array.isArray(list) || list.length === 0) return "-";
    
    const times = list
        .filter(s => s.start_time)
        .map(s => s.start_time)
        .sort();
    
    const endTimes = list
        .filter(s => s.end_time)
        .map(s => s.end_time)
        .sort();
    
    if (times.length > 0 && endTimes.length > 0) {
        return `${times[0]} - ${endTimes[endTimes.length - 1]}`;
    }
    
    return "-";
});

const totalPresentations = computed(() => {
    let total = 0;
    const list = sessions.value;
    if (!list || !Array.isArray(list)) return 0;
    
    list.forEach(session => {
        if (session.presentations) {
            total += session.presentations.length;
        }
    });
    return total;
});

// Methods
const checkTimeOverlap = (session1, session2) => {
    const start1 = timeToMinutes(session1.start_time);
    const end1 = timeToMinutes(session1.end_time);
    const start2 = timeToMinutes(session2.start_time);
    const end2 = timeToMinutes(session2.end_time);
    
    return (start1 < end2 && end1 > start2);
};

const timeToMinutes = (time) => {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
};

const calculateNewTimes = () => {
    const list = sessions.value;
    if (!list || !Array.isArray(list)) return;
    
    let currentTime = "09:00";
    list.forEach((session, index) => {
        if (index === 0) {
            currentTime = session.start_time || "09:00";
        } else {
            const prevSession = list[index - 1];
            currentTime = prevSession.end_time || currentTime;
        }
        
        session.start_time = currentTime;
        
        if (session.duration_in_minutes) {
            const [hours, minutes] = currentTime.split(":").map(Number);
            const totalMinutes = hours * 60 + minutes + session.duration_in_minutes;
            const newHours = Math.floor(totalMinutes / 60);
            const newMinutes = totalMinutes % 60;
            session.end_time = `${newHours.toString().padStart(2, "0")}:${newMinutes.toString().padStart(2, "0")}`;
            currentTime = session.end_time;
        }
    });
};

const handleMoveUp = (session, currentIndex) => {
    if (!sessions.value || currentIndex <= 0) return;
    
    if (currentIndex > 0) {
        const newList = [...sessions.value];
        [newList[currentIndex - 1], newList[currentIndex]] = [
            newList[currentIndex],
            newList[currentIndex - 1],
        ];
        sessions.value = newList;
    }
};

const handleMoveDown = (session, currentIndex) => {
    const list = sessions.value;
    if (!list || !Array.isArray(list)) return;
    
    if (currentIndex < list.length - 1) {
        const newList = [...list];
        [newList[currentIndex], newList[currentIndex + 1]] = [
            newList[currentIndex + 1],
            newList[currentIndex],
        ];
        sessions.value = newList;
    }
};
</script>

<style scoped>
/* FormKit drag and drop classes */
.session-item {
    list-style: none;
}

.dragging {
    opacity: 0.5;
}

.drag-over {
    background-color: rgba(59, 130, 246, 0.1);
    border-color: rgb(59, 130, 246);
}
</style>