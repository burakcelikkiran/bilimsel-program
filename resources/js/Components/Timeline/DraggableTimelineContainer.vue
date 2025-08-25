<!-- resources/js/Components/Timeline/DraggableTimelineContainer.vue -->
<template>
    <div class="draggable-timeline-container">
        <!-- Timeline Header (Time Scale section hidden) -->

        <!-- Timeline Days -->
        <div class="timeline-days space-y-8">
            <DraggableTimelineDaySection
                v-for="dayData in timelineData"
                :key="dayData.id"
                :day-data="dayData"
                :event="event"
                :is-dragging="isDragging"
                @session-click="$emit('session-click', $event)"
                @presentation-click="$emit('presentation-click', $event)"
                @session-moved="handleSessionMoved"
                @session-updated="handleSessionUpdated"
                @drag-start="handleDragStart"
                @drag-end="handleDragEnd"
            />
        </div>

        <!-- Timeline Legend -->
        <div class="timeline-legend mt-8">
            <div
                class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
            >
                <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                >
                    Açıklamalar
                </h3>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <!-- Session Types Legend -->
                    <div>
                        <h4
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
                        >
                            Oturum Tipleri
                        </h4>
                        <div class="space-y-2">
                            <div
                                v-for="type in sessionTypeLegend"
                                :key="type.value"
                                class="flex items-center space-x-2"
                            >
                                <div
                                    :class="type.colorClass"
                                    class="w-4 h-4 rounded"
                                ></div>
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                    >{{ type.label }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Venue Colors Legend -->
                    <div>
                        <h4
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
                        >
                            Salon Renkleri
                        </h4>
                        <div class="space-y-2">
                            <div
                                v-for="venue in venueColors"
                                :key="venue.id"
                                class="flex items-center space-x-2"
                            >
                                <div
                                    :style="{ backgroundColor: venue.color }"
                                    class="w-4 h-4 rounded"
                                ></div>
                                <span
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                    >{{ venue.name }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Drag & Drop Guide -->
                    <div>
                        <h4
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
                        >
                            Sürükle & Bırak Rehberi
                        </h4>
                        <div
                            class="space-y-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded"></div>
                                <span>Geçerli bırakma alanı</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-red-500 rounded"></div>
                                <span>Çakışma var</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-blue-500 rounded"></div>
                                <span>Aktif sürükleme</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">
                                Oturumları farklı salon ve günlere
                                taşıyabilirsiniz
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Changes Summary -->
        <div v-if="pendingChanges.length > 0" class="mt-8">
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6"
            >
                <div class="flex items-start space-x-3">
                    <div
                        class="h-6 w-6 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0"
                    >
                        <span class="text-white text-xs font-bold">{{
                            pendingChanges.length
                        }}</span>
                    </div>
                    <div class="flex-1">
                        <h3
                            class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2"
                        >
                            {{ pendingChanges.length }} Bekleyen Değişiklik
                        </h3>
                        <div
                            class="space-y-1 text-sm text-yellow-700 dark:text-yellow-300 max-h-32 overflow-y-auto"
                        >
                            <div
                                v-for="change in pendingChanges"
                                :key="change.id"
                                class="flex items-center justify-between"
                            >
                                <span>{{ change.description }}</span>
                                <span
                                    class="text-xs text-yellow-600 dark:text-yellow-400"
                                    >{{ change.type }}</span
                                >
                            </div>
                        </div>
                        <div
                            class="mt-3 text-xs text-yellow-600 dark:text-yellow-400"
                        >
                            Değişiklikleri kaydetmek için "Kaydet" butonuna
                            basın
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, provide } from "vue";
import DraggableTimelineDaySection from "./DraggableTimelineDaySection.vue";

// Props
const props = defineProps({
    timelineData: {
        type: Array,
        required: true,
    },
    event: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
});

// Emits
const emit = defineEmits([
    "session-click",
    "presentation-click",
    "timeline-updated",
    "changes-pending",
]);

// Reactive data
const isDragging = ref(false);
const pendingChanges = ref([]);

// Provide timeline data to child components
provide("timelineData", props.timelineData);

// Computed
const venueColors = computed(() => {
    const venues = [];
    const seenVenues = new Set();

    props.timelineData.forEach((day) => {
        day.venues.forEach((venue) => {
            if (!seenVenues.has(venue.id)) {
                venues.push({
                    id: venue.id,
                    name: venue.display_name || venue.name,
                    color: venue.color || "#3B82F6",
                });
                seenVenues.add(venue.id);
            }
        });
    });

    return venues;
});

const sessionTypeLegend = computed(() => [
    { value: "plenary", label: "Genel Oturum", colorClass: "bg-purple-500" },
    { value: "parallel", label: "Paralel Oturum", colorClass: "bg-blue-500" },
    { value: "workshop", label: "Workshop", colorClass: "bg-green-500" },
    { value: "poster", label: "Poster", colorClass: "bg-yellow-500" },
    { value: "break", label: "Ara", colorClass: "bg-gray-400" },
    { value: "lunch", label: "Öğle Arası", colorClass: "bg-orange-500" },
    { value: "social", label: "Sosyal", colorClass: "bg-pink-500" },
]);

// Methods
const handleDragStart = () => {
    isDragging.value = true;
};

const handleDragEnd = () => {
    isDragging.value = false;
};

const handleSessionMoved = (moveData) => {
    // Add to pending changes
    const change = {
        id: Date.now() + Math.random(),
        type: "session_moved",
        description: `"${moveData.session.title}" oturumu taşındı`,
        sessionId: moveData.session.id,
        fromVenueId: moveData.fromVenueId,
        toVenueId: moveData.toVenueId,
        fromDayId: moveData.fromDayId,
        toDayId: moveData.toDayId,
        newSortOrder: moveData.newSortOrder,
        newStartTime: moveData.newStartTime,
        newEndTime: moveData.newEndTime,
        timestamp: new Date().toISOString(),
    };

    pendingChanges.value.push(change);

    // Emit updated timeline and pending changes
    emit("timeline-updated", {
        timeline: props.timelineData,
        changes: pendingChanges.value,
    });

    emit("changes-pending", pendingChanges.value);
};

const handleSessionUpdated = (updateData) => {
    // Remove existing changes for this venue to avoid duplicates
    const existingChangeIndex = pendingChanges.value.findIndex(
        change => change.type === "session_reordered" && 
                 change.venue_id === updateData.venue_id &&
                 change.day_id === updateData.day_id
    );
    
    if (existingChangeIndex !== -1) {
        pendingChanges.value.splice(existingChangeIndex, 1);
    }
    
    // Add new change for this venue
    const change = {
        id: Date.now() + Math.random(),
        type: "session_reordered",
        description: `Oturumlar yeniden sıralandı`,
        venue_id: updateData.venue_id,
        day_id: updateData.day_id,
        session_order: updateData.session_order,
        timestamp: new Date().toISOString(),
    };
    pendingChanges.value.push(change);
    
    // Emit updated timeline and pending changes
    emit("timeline-updated", {
        timeline: props.timelineData,
        changes: pendingChanges.value,
    });
    emit("changes-pending", pendingChanges.value);
};

// Watch for external changes
watch(
    () => props.timelineData,
    () => {
        // Reset pending changes if timeline data changes externally
        // This would happen after a successful save
        if (pendingChanges.value.length > 0) {
            pendingChanges.value = [];
            emit("changes-pending", []);
        }
    },
    { deep: true }
);

// Expose methods for parent component
defineExpose({
    getPendingChanges: () => pendingChanges.value,
    clearPendingChanges: () => {
        pendingChanges.value = [];
        emit("changes-pending", []);
    },
});
</script>

<style scoped>
.draggable-timeline-container {
    position: relative;
    width: 100%;
}

/* Drag states */
.is-dragging {
    pointer-events: none;
}

.drag-source {
    opacity: 0.5;
    transform: rotate(2deg);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

.valid-drop-zone {
    background-color: rgba(34, 197, 94, 0.1);
    border: 2px dashed #22c55e;
}

.invalid-drop-zone {
    background-color: rgba(239, 68, 68, 0.1);
    border: 2px dashed #ef4444;
}

.drop-preview {
    background-color: rgba(59, 130, 246, 0.2);
    border: 2px dashed #3b82f6;
    min-height: 60px;
    border-radius: 8px;
    margin: 4px 0;
}

/* Animations */
.timeline-item {
    transition: all 0.2s ease-in-out;
}

.timeline-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Dark mode adjustments */
.dark .valid-drop-zone {
    background-color: rgba(34, 197, 94, 0.05);
}

.dark .invalid-drop-zone {
    background-color: rgba(239, 68, 68, 0.05);
}

.dark .drop-preview {
    background-color: rgba(59, 130, 246, 0.1);
}

/* Responsive behavior */
@media (max-width: 768px) {
    .timeline-days {
        overflow-x: auto;
        padding-bottom: 1rem;
    }
}

/* Smooth transitions for reordering */
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

.list-leave-active {
    position: absolute;
}
</style>
