<!-- resources/js/Components/Timeline/DraggableTimelineSessionCard.vue -->
<template>
    <div
        class="draggable-timeline-session-card group"
        :class="[
            sessionTypeClass,
            { 'session-featured': sessionData.is_featured },
            { 'session-break': sessionData.is_break },
            { 'session-dragging': isDragging },
        ]"
        @click="handleSessionClick"
        :data-session-id="sessionData.id"
    >
        <!-- Drag Handle and Move Buttons -->
        <div
            class="drag-handle absolute top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity flex items-center space-x-1"
        >
            <div
                class="w-6 h-6 bg-gray-500 bg-opacity-20 rounded flex items-center justify-center cursor-move"
                :draggable="true"
                @dragstart="handleDragStart"
                @dragend="handleDragEnd"
            >
                <svg
                    class="w-3 h-3 text-gray-600 dark:text-gray-400"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"
                    />
                </svg>
            </div>
            
            <!-- Move Up Button -->
            <button
                v-if="sortOrder > 0"
                @click.stop="$emit('move-up', sessionData, sortOrder)"
                class="w-6 h-6 bg-gray-500 bg-opacity-20 rounded flex items-center justify-center hover:bg-opacity-30 transition-all"
                title="Yukarı taşı"
            >
                <ChevronUpIcon class="w-3 h-3 text-gray-600 dark:text-gray-400" />
            </button>
            
            <!-- Move Down Button -->
            <button
                v-if="!isLastSession"
                @click.stop="$emit('move-down', sessionData, sortOrder)"
                class="w-6 h-6 bg-gray-500 bg-opacity-20 rounded flex items-center justify-center hover:bg-opacity-30 transition-all"
                title="Aşağı taşı"
            >
                <ChevronDownIcon class="w-3 h-3 text-gray-600 dark:text-gray-400" />
            </button>
        </div>

        <!-- Session Header -->
        <div class="session-header">
            <div class="flex items-start justify-between">
                <!-- Session Info -->
                <div class="flex-1 min-w-0 pl-20">
                    <!-- Session Title -->
                    <h4
                        class="session-title text-sm font-semibold text-gray-900 dark:text-white group-hover:text-purple-600 transition-colors"
                    >
                        {{ sessionData.title }}
                    </h4>

                    <!-- Time and Duration -->
                    <div class="session-meta flex items-center space-x-3 mt-1">
                        <div
                            class="flex items-center space-x-1 text-xs text-gray-600 dark:text-gray-400"
                        >
                            <ClockIcon class="h-3 w-3" />
                            <span>{{
                                sessionData.formatted_time_range || timeRange
                            }}</span>
                        </div>
                        <div
                            v-if="sessionData.duration_in_minutes"
                            class="text-xs text-gray-500 dark:text-gray-500"
                        >
                            {{
                                formatDuration(sessionData.duration_in_minutes)
                            }}
                        </div>
                        <div
                            class="text-xs text-purple-600 dark:text-purple-400 font-medium"
                        >
                            #{{ sortOrder + 1 }}
                        </div>
                    </div>

                    <!-- Session Type Badge -->
                    <div class="flex items-center space-x-2 mt-2">
                        <span
                            :class="sessionTypeBadgeClass"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                        >
                            {{
                                sessionData.session_type_display ||
                                sessionData.session_type
                            }}
                        </span>

                        <!-- Featured Badge -->
                        <span
                            v-if="sessionData.is_featured"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
                        >
                            <StarIcon class="h-3 w-3 mr-1" />
                            Öne Çıkan
                        </span>

                        <!-- Break Badge -->
                        <span
                            v-if="sessionData.is_break"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
                        >
                            Ara
                        </span>

                        <!-- Moved Badge -->
                        <span
                            v-if="isModified"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                        >
                            Taşındı
                        </span>
                    </div>
                </div>

                <!-- Session Actions -->
                <div
                    class="session-actions flex items-start space-x-1 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                    <button
                        @click.stop="handleSessionClick"
                        class="p-1 text-gray-400 hover:text-purple-600 transition-colors"
                        title="Oturumu Görüntüle"
                    >
                        <EyeIcon class="h-4 w-4" />
                    </button>
                    <button
                        v-if="sessionData.can_edit"
                        @click.stop="handleEditClick"
                        class="p-1 text-gray-400 hover:text-orange-600 transition-colors"
                        title="Oturumu Düzenle"
                    >
                        <PencilSquareIcon class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Session Description -->
        <div
            v-if="sessionData.description"
            class="session-description mt-2 pl-20"
        >
            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                {{ sessionData.description }}
            </p>
        </div>

        <!-- Moderators -->
        <div
            v-if="sessionData.moderators && sessionData.moderators.length > 0"
            class="session-moderators mt-3 pl-20"
        >
            <div class="flex items-center space-x-1 text-xs">
                <UserGroupIcon class="h-3 w-3 text-gray-400" />
                <span class="text-gray-500 dark:text-gray-500"
                    >{{ sessionData.moderator_title || "Moderatör" }}:</span
                >
                <div class="flex flex-wrap items-center gap-1">
                    <span
                        v-for="(moderator, index) in sessionData.moderators"
                        :key="moderator.id"
                        class="text-gray-700 dark:text-gray-300 font-medium"
                    >
                        {{ moderator.full_name
                        }}<span v-if="index < sessionData.moderators.length - 1"
                            >,</span
                        >
                    </span>
                </div>
            </div>
        </div>

        <!-- Category -->
        <div v-if="sessionData.category" class="session-category mt-2 pl-20">
            <div class="flex items-center space-x-1">
                <div
                    class="w-2 h-2 rounded-full"
                    :style="{ backgroundColor: sessionData.category.color }"
                ></div>
                <span class="text-xs text-gray-600 dark:text-gray-400">{{
                    sessionData.category.name
                }}</span>
            </div>
        </div>

        <!-- Sponsor -->
        <div v-if="sessionData.sponsor" class="session-sponsor mt-2 pl-20">
            <div class="flex items-center space-x-1">
                <BuildingOfficeIcon class="h-3 w-3 text-gray-400" />
                <span class="text-xs text-gray-600 dark:text-gray-400"
                    >Sponsor:</span
                >
                <span
                    class="text-xs text-gray-700 dark:text-gray-300 font-medium"
                    >{{ sessionData.sponsor.name }}</span
                >
            </div>
        </div>

        <!-- Presentations Count -->
        <div
            v-if="
                sessionData.presentations &&
                sessionData.presentations.length > 0
            "
            class="session-presentations mt-3 pl-20"
        >
            <div class="flex items-center justify-between">
                <div
                    class="flex items-center space-x-1 text-xs text-gray-600 dark:text-gray-400"
                >
                    <DocumentTextIcon class="h-3 w-3" />
                    <span>{{ sessionData.presentations.length }} Sunum</span>
                </div>

                <!-- Toggle Presentations -->
                <button
                    @click.stop="togglePresentations"
                    class="text-xs text-purple-600 hover:text-purple-700 transition-colors"
                >
                    {{ showPresentations ? "Gizle" : "Göster" }}
                    <ChevronDownIcon
                        :class="{ 'rotate-180': showPresentations }"
                        class="h-3 w-3 inline ml-1 transition-transform"
                    />
                </button>
            </div>

            <!-- Presentations List -->
            <div
                v-show="showPresentations"
                class="presentations-list mt-2 space-y-1"
            >
                <TimelinePresentationItem
                    v-for="presentation in sessionData.presentations"
                    :key="presentation.id"
                    :presentation-data="presentation"
                    :session-data="sessionData"
                    @presentation-click="$emit('presentation-click', $event)"
                />
            </div>
        </div>

        <!-- Session Footer -->
        <div
            v-if="!sessionData.is_break"
            class="session-footer mt-3 pt-2 border-t border-gray-200 dark:border-gray-700 pl-20"
        >
            <div class="flex items-center justify-between text-xs">
                <!-- Duration -->
                <div class="text-gray-500 dark:text-gray-500">
                    {{
                        sessionData.duration_in_minutes
                            ? formatDuration(sessionData.duration_in_minutes)
                            : "Süre belirsiz"
                    }}
                </div>

                <!-- Session ID -->
                <div class="text-gray-400 dark:text-gray-600">
                    #{{ sessionData.id }}
                </div>
            </div>
        </div>

        <!-- Drag feedback overlay -->
        <div
            v-if="isDragging"
            class="absolute inset-0 bg-blue-500 bg-opacity-20 border-2 border-blue-500 border-dashed rounded-lg pointer-events-none"
        ></div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import TimelinePresentationItem from "./TimelinePresentationItem.vue";
import {
    ClockIcon,
    EyeIcon,
    PencilSquareIcon,
    UserGroupIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    ChevronDownIcon,
    ChevronUpIcon,
    StarIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    sessionData: {
        type: Object,
        required: true,
    },
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
    sortOrder: {
        type: Number,
        default: 0,
    },
    isDragging: {
        type: Boolean,
        default: false,
    },
    isLastSession: {
        type: Boolean,
        default: false,
    },
});

// Emits
const emit = defineEmits(["session-click", "presentation-click", "move-up", "move-down", "dragstart", "dragend"]);

// Reactive data
const showPresentations = ref(false);

// Computed
const timeRange = computed(() => {
    if (props.sessionData.start_time && props.sessionData.end_time) {
        return `${props.sessionData.start_time} - ${props.sessionData.end_time}`;
    }
    return "Zaman belirtilmemiş";
});

const sessionTypeClass = computed(() => {
    const baseClass = "session-card";
    const typeClasses = {
        plenary: "session-plenary",
        parallel: "session-parallel",
        workshop: "session-workshop",
        poster: "session-poster",
        break: "session-break",
        lunch: "session-lunch",
        social: "session-social",
    };

    return [
        baseClass,
        typeClasses[props.sessionData.session_type] || "session-default",
    ];
});

const sessionTypeBadgeClass = computed(() => {
    const badgeClasses = {
        plenary:
            "bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200",
        parallel:
            "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
        workshop:
            "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
        poster: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200",
        break: "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200",
        lunch: "bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200",
        social: "bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200",
    };

    return (
        badgeClasses[props.sessionData.session_type] ||
        "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
    );
});

const isModified = computed(() => {
    // Session'ın taşınıp taşınmadığını kontrol et
    return (
        props.sessionData.original_venue_id &&
        props.sessionData.original_venue_id !== props.venueData.id
    );
});

// Methods
const formatDuration = (minutes) => {
    if (!minutes) return "";

    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (hours > 0 && remainingMinutes > 0) {
        return `${hours}s ${remainingMinutes}dk`;
    } else if (hours > 0) {
        return `${hours}s`;
    } else {
        return `${remainingMinutes}dk`;
    }
};

const handleSessionClick = () => {
    emit("session-click", props.sessionData);
};

const handleEditClick = () => {
    router.visit(route("admin.program-sessions.edit", props.sessionData.id));
};

const togglePresentations = () => {
    showPresentations.value = !showPresentations.value;
};

const handleDragStart = (event) => {
    // Emit dragstart event with session data and sort order
    emit("dragstart", event, props.sessionData, props.sortOrder);
    event.stopPropagation();
};

const handleDragEnd = (event) => {
    // Emit dragend event
    emit("dragend", event);
    event.stopPropagation();
};
</script>

<style scoped>
.draggable-timeline-session-card {
    @apply relative bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm transition-all duration-200;
}

.draggable-timeline-session-card:hover {
    @apply shadow-md border-purple-300 dark:border-purple-600;
    transform: translateY(-1px);
}

/* Drag handle */
.drag-handle {
    z-index: 10;
}

/* Session type specific styling */
.session-plenary {
    @apply border-l-4 border-l-purple-500;
}

.session-parallel {
    @apply border-l-4 border-l-blue-500;
}

.session-workshop {
    @apply border-l-4 border-l-green-500;
}

.session-poster {
    @apply border-l-4 border-l-yellow-500;
}

.session-break {
    @apply border-l-4 border-l-gray-400 bg-gray-50 dark:bg-gray-800;
}

.session-lunch {
    @apply border-l-4 border-l-orange-500;
}

.session-social {
    @apply border-l-4 border-l-pink-500;
}

.session-default {
    @apply border-l-4 border-l-gray-500;
}

/* Featured session styling */
.session-featured {
    @apply ring-2 ring-yellow-300 dark:ring-yellow-600;
    box-shadow: 0 0 0 1px rgb(253 224 71 / 0.3);
}

.session-featured:hover {
    @apply ring-yellow-400 dark:ring-yellow-500;
}

/* Dragging state */
.session-dragging {
    @apply opacity-50 transform rotate-2 shadow-2xl;
    cursor: grabbing;
}

.session-dragging .drag-handle {
    @apply opacity-100;
}

/* Session header */
.session-header {
    @apply relative;
}

.session-title {
    @apply truncate;
    line-height: 1.3;
}

.session-meta {
    @apply flex items-center flex-wrap gap-2;
}

/* Actions */
.session-actions {
    @apply flex-shrink-0;
}

/* Presentations toggle */
.presentations-list {
    @apply bg-gray-50 dark:bg-gray-800 rounded-md p-2 border border-gray-200 dark:border-gray-700;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .draggable-timeline-session-card {
        @apply p-3;
    }

    .session-actions {
        @apply opacity-100;
    }

    .session-title {
        @apply text-sm;
    }

    .session-meta {
        @apply text-xs;
    }

    .drag-handle {
        @apply opacity-100;
    }
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Focus states */
.draggable-timeline-session-card:focus-within {
    @apply ring-2 ring-purple-500 ring-offset-2 dark:ring-offset-gray-900;
}

/* Break session specific styling */
.session-break .session-title {
    @apply text-gray-600 dark:text-gray-400;
}

.session-break .session-meta {
    @apply text-gray-500 dark:text-gray-500;
}

/* Loading state */
.session-loading {
    @apply animate-pulse bg-gray-200 dark:bg-gray-700;
}

.session-loading .session-title,
.session-loading .session-meta,
.session-loading .session-description {
    @apply bg-gray-300 dark:bg-gray-600 rounded;
    height: 1rem;
    margin-bottom: 0.5rem;
}

/* Drag states for better UX */
.session-card.sortable-chosen {
    @apply shadow-2xl ring-2 ring-blue-500;
    transform: rotate(2deg);
}

.session-card.sortable-drag {
    @apply opacity-50;
}

.session-card.sortable-ghost {
    @apply opacity-30 bg-blue-100 dark:bg-blue-900;
}

/* Print styles */
@media print {
    .draggable-timeline-session-card {
        @apply shadow-none border border-gray-300 break-inside-avoid;
    }

    .session-actions {
        @apply hidden;
    }

    .drag-handle {
        @apply hidden;
    }
}
</style>


