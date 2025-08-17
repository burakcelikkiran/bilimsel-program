<!-- resources/js/Components/Timeline/DraggableSessionCard.vue -->
<template>
  <div 
    ref="sessionCard"
    class="draggable-session-card"
    :class="[
      sessionTypeClass,
      {
        'edit-mode': editMode,
        'dragging': isDragging,
        'drag-source': isDragSource,
        'session-featured': sessionData.is_featured,
        'session-break': sessionData.is_break
      }
    ]"
    :draggable="editMode"
    @dragstart="handleDragStart"
    @dragend="handleDragEnd"
    @click="handleSessionClick"
  >
    <!-- Drag Handle (only visible in edit mode) -->
    <div v-if="editMode" class="drag-handle">
      <div class="drag-handle-dots">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
      </div>
    </div>

    <!-- Session Content -->
    <div class="session-content">
      <!-- Session Header -->
      <div class="session-header">
        <div class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <!-- Session Title -->
            <h4 class="session-title text-sm font-semibold text-gray-900 dark:text-white group-hover:text-purple-600 transition-colors">
              {{ sessionData.title }}
            </h4>
            
            <!-- Time and Duration -->
            <div class="session-meta flex items-center space-x-3 mt-1">
              <div class="flex items-center space-x-1 text-xs text-gray-600 dark:text-gray-400">
                <ClockIcon class="h-3 w-3" />
                <span>{{ sessionData.formatted_time_range || timeRange }}</span>
              </div>
              <div v-if="sessionData.duration_in_minutes" class="text-xs text-gray-500 dark:text-gray-500">
                {{ formatDuration(sessionData.duration_in_minutes) }}
              </div>
            </div>
            
            <!-- Session Type Badge -->
            <div class="flex items-center space-x-2 mt-2">
              <span 
                :class="sessionTypeBadgeClass"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
              >
                {{ sessionData.session_type_display || sessionData.session_type }}
              </span>
              
              <!-- Featured Badge -->
              <span v-if="sessionData.is_featured" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                <StarIcon class="h-3 w-3 mr-1" />
                Öne Çıkan
              </span>
            </div>
          </div>

          <!-- Edit Mode Actions -->
          <div v-if="editMode" class="session-actions flex items-start space-x-1">
            <div class="text-xs text-gray-400 font-mono">#{{ sessionData.id }}</div>
          </div>
        </div>
      </div>

      <!-- Session Description -->
      <div v-if="sessionData.description" class="session-description mt-2">
        <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
          {{ sessionData.description }}
        </p>
      </div>

      <!-- Moderators -->
      <div v-if="sessionData.moderators && sessionData.moderators.length > 0" class="session-moderators mt-3">
        <div class="flex items-center space-x-1 text-xs">
          <UserGroupIcon class="h-3 w-3 text-gray-400" />
          <span class="text-gray-500 dark:text-gray-500">{{ sessionData.moderator_title || 'Moderatör' }}:</span>
          <div class="flex flex-wrap items-center gap-1">
            <span 
              v-for="(moderator, index) in sessionData.moderators" 
              :key="moderator.id"
              class="text-gray-700 dark:text-gray-300 font-medium"
            >
              {{ moderator.full_name }}<span v-if="index < sessionData.moderators.length - 1">,</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Presentations Count -->
      <div v-if="sessionData.presentations && sessionData.presentations.length > 0" class="session-presentations mt-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-1 text-xs text-gray-600 dark:text-gray-400">
            <DocumentTextIcon class="h-3 w-3" />
            <span>{{ sessionData.presentations.length }} Sunum</span>
          </div>
          
          <!-- Toggle Presentations (disabled in edit mode) -->
          <button
            v-if="!editMode"
            @click.stop="togglePresentations"
            class="text-xs text-purple-600 hover:text-purple-700 transition-colors"
          >
            {{ showPresentations ? 'Gizle' : 'Göster' }}
            <ChevronDownIcon 
              :class="{ 'rotate-180': showPresentations }"
              class="h-3 w-3 inline ml-1 transition-transform"
            />
          </button>
        </div>

        <!-- Presentations List (collapsed in edit mode) -->
        <div v-show="showPresentations && !editMode" class="presentations-list mt-2 space-y-1">
          <div
            v-for="presentation in sessionData.presentations"
            :key="presentation.id"
            class="bg-gray-50 dark:bg-gray-800 rounded p-2 text-xs"
          >
            <div class="font-medium text-gray-900 dark:text-white">{{ presentation.title }}</div>
            <div v-if="presentation.speakers" class="text-gray-600 dark:text-gray-400 mt-1">
              {{ presentation.speakers.map(s => s.full_name).join(', ') }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Drag Preview Ghost (hidden) -->
    <div ref="dragGhost" class="drag-ghost" style="position: absolute; left: -9999px; top: -9999px;">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border-2 border-purple-400 p-3" style="width: 250px;">
        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ sessionData.title }}</div>
        <div class="text-xs text-gray-500">{{ timeRange }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue'
import {
  ClockIcon,
  UserGroupIcon,
  DocumentTextIcon,
  ChevronDownIcon,
  StarIcon,
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  sessionData: {
    type: Object,
    required: true
  },
  venueData: {
    type: Object,
    required: true
  },
  dayData: {
    type: Object,
    required: true
  },
  editMode: {
    type: Boolean,
    default: false
  },
  sessionIndex: {
    type: Number,
    default: 0
  }
})

// Emits
const emit = defineEmits(['session-click', 'presentation-click', 'drag-start', 'drag-end'])

// Inject drag context
const dragContext = inject('dragContext', null)

// Reactive data
const sessionCard = ref(null)
const dragGhost = ref(null)
const showPresentations = ref(false)
const isDragging = ref(false)

// Computed
const timeRange = computed(() => {
  if (props.sessionData.start_time && props.sessionData.end_time) {
    return `${props.sessionData.start_time} - ${props.sessionData.end_time}`
  }
  return 'Zaman belirtilmemiş'
})

const sessionTypeClass = computed(() => {
  const baseClass = 'session-card'
  const typeClasses = {
    plenary: 'session-plenary',
    parallel: 'session-parallel',
    workshop: 'session-workshop',
    poster: 'session-poster',
    break: 'session-break',
    lunch: 'session-lunch',
    social: 'session-social',
  }
  
  return [baseClass, typeClasses[props.sessionData.session_type] || 'session-default']
})

const sessionTypeBadgeClass = computed(() => {
  const badgeClasses = {
    plenary: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    parallel: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    workshop: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    poster: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    break: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    lunch: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    social: 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
  }
  
  return badgeClasses[props.sessionData.session_type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
})

const isDragSource = computed(() => {
  return dragContext?.dragState.isDragging && 
         dragContext?.dragState.session?.id === props.sessionData.id
})

// Methods
const formatDuration = (minutes) => {
  if (!minutes) return ''
  
  const hours = Math.floor(minutes / 60)
  const remainingMinutes = minutes % 60
  
  if (hours > 0 && remainingMinutes > 0) {
    return `${hours}s ${remainingMinutes}dk`
  } else if (hours > 0) {
    return `${hours}s`
  } else {
    return `${remainingMinutes}dk`
  }
}

const handleSessionClick = () => {
  if (!props.editMode) {
    emit('session-click', props.sessionData)
  }
}

const togglePresentations = () => {
  showPresentations.value = !showPresentations.value
}

const handleDragStart = (event) => {
  if (!props.editMode) {
    event.preventDefault()
    return
  }
  
  isDragging.value = true
  
  // Set custom drag image
  if (dragGhost.value) {
    event.dataTransfer.setDragImage(dragGhost.value.firstElementChild, 125, 30)
  }
  
  // Set drag data
  event.dataTransfer.setData('application/json', JSON.stringify({
    sessionId: props.sessionData.id,
    venueId: props.venueData.id,
    dayId: props.dayData.id
  }))
  
  event.dataTransfer.effectAllowed = 'move'
  
  // Notify parent and context
  emit('drag-start', props.sessionData)
  
  // Add visual feedback
  setTimeout(() => {
    if (sessionCard.value) {
      sessionCard.value.classList.add('drag-source')
    }
  }, 0)
}

const handleDragEnd = (event) => {
  isDragging.value = false
  
  // Remove visual feedback
  if (sessionCard.value) {
    sessionCard.value.classList.remove('drag-source')
  }
  
  // Notify parent
  emit('drag-end')
}
</script>

<style scoped>
.draggable-session-card {
  @apply relative bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 group;
}

.draggable-session-card:hover {
  @apply shadow-md border-purple-300 dark:border-purple-600;
  transform: translateY(-1px);
}

/* Edit mode styling */
.edit-mode {
  @apply cursor-grab border-l-4 border-l-orange-400;
  position: relative;
}

.edit-mode:hover {
  @apply border-l-orange-500 shadow-lg;
}

.edit-mode:active {
  @apply cursor-grabbing;
}

/* Drag handle */
.drag-handle {
  @apply absolute left-2 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity;
  cursor: grab;
}

.edit-mode .drag-handle {
  @apply opacity-70;
}

.drag-handle-dots {
  @apply grid grid-cols-2 gap-1;
}

.dot {
  @apply w-1.5 h-1.5 bg-gray-400 rounded-full;
}

.edit-mode:hover .dot {
  @apply bg-orange-500;
}

/* Session content */
.session-content {
  @apply p-4;
  margin-left: 0;
}

.edit-mode .session-content {
  margin-left: 1.5rem;
}

/* Drag states */
.dragging {
  @apply opacity-70 transform rotate-2 scale-105 shadow-2xl z-50;
}

.drag-source {
  @apply opacity-30;
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

/* Presentations list */
.presentations-list {
  @apply bg-gray-50 dark:bg-gray-800 rounded-md p-2 border border-gray-200 dark:border-gray-700;
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Drag ghost (hidden) */
.drag-ghost {
  pointer-events: none;
  opacity: 0;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .draggable-session-card {
    @apply p-3;
  }
  
  .edit-mode .session-content {
    margin-left: 1rem;
  }
  
  .drag-handle {
    @apply opacity-100;
  }
  
  .session-title {
    @apply text-sm;
  }
  
  .session-meta {
    @apply text-xs;
  }
}

/* Focus states */
.draggable-session-card:focus-within {
  @apply ring-2 ring-purple-500 ring-offset-2 dark:ring-offset-gray-900;
}

/* Break session specific styling */
.session-break .session-title {
  @apply text-gray-600 dark:text-gray-400;
}

.session-break .session-meta {
  @apply text-gray-500 dark:text-gray-500;
}

/* Smooth animations */
.session-item {
  transition: all 0.2s ease-in-out;
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
</style>