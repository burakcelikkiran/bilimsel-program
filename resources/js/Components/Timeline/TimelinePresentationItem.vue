<!-- resources/js/Components/Timeline/TimelinePresentationItem.vue -->
<template>
  <div 
    class="timeline-presentation-item group cursor-pointer"
    :class="presentationTypeClass"
    @click="handlePresentationClick"
  >
    <!-- Presentation Header -->
    <div class="presentation-header flex items-start justify-between">
      <!-- Presentation Info -->
      <div class="flex-1 min-w-0">
        <!-- Title -->
        <h5 class="presentation-title text-xs font-medium text-gray-800 dark:text-gray-200 group-hover:text-purple-600 transition-colors leading-tight">
          {{ presentationData.title }}
        </h5>
        
        <!-- Time -->
        <div v-if="presentationTimeRange" class="presentation-time flex items-center space-x-1 mt-1">
          <ClockIcon class="h-3 w-3 text-gray-400" />
          <span class="text-xs text-gray-500 dark:text-gray-400">{{ presentationTimeRange }}</span>
          <span v-if="presentationData.duration_minutes" class="text-xs text-gray-400">
            ({{ formatDuration(presentationData.duration_minutes) }})
          </span>
        </div>
      </div>

      <!-- Presentation Type Badge -->
      <div class="presentation-type ml-2">
        <span 
          :class="presentationTypeBadgeClass"
          class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
        >
          <component 
            :is="presentationTypeIcon" 
            class="h-3 w-3 mr-1" 
          />
          {{ presentationTypeLabel }}
        </span>
      </div>
    </div>

    <!-- Abstract (if available) -->
    <div v-if="presentationData.abstract" class="presentation-abstract mt-2">
      <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
        {{ presentationData.abstract }}
      </p>
    </div>

    <!-- Speakers -->
    <div v-if="presentationData.speakers && presentationData.speakers.length > 0" class="presentation-speakers mt-2">
      <div class="flex items-start space-x-1">
        <UserIcon class="h-3 w-3 text-gray-400 mt-0.5 flex-shrink-0" />
        <div class="flex flex-col space-y-0.5 min-w-0">
          <div 
            v-for="speaker in presentationData.speakers" 
            :key="speaker.id"
            class="flex items-center space-x-1"
          >
            <span class="text-xs text-gray-700 dark:text-gray-300 font-medium truncate">
              {{ speaker.full_name }}
            </span>
            <span v-if="speaker.speaker_role && speaker.speaker_role !== 'speaker'" 
                  class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
              ({{ getSpeakerRoleLabel(speaker.speaker_role) }})
            </span>
            <span v-if="speaker.affiliation" 
                  class="text-xs text-gray-500 dark:text-gray-400 truncate">
              - {{ speaker.affiliation }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Language (if not default) -->
    <div v-if="presentationData.language && presentationData.language !== 'tr'" class="presentation-language mt-2">
      <div class="flex items-center space-x-1">
        <GlobeAltIcon class="h-3 w-3 text-gray-400" />
        <span class="text-xs text-gray-500 dark:text-gray-400">
          {{ getLanguageLabel(presentationData.language) }}
        </span>
      </div>
    </div>

    <!-- Presentation Actions -->
    <div class="presentation-actions mt-2 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
      <div class="flex items-center space-x-1">
        <button
          @click.stop="handlePresentationClick"
          class="p-1 text-gray-400 hover:text-purple-600 transition-colors"
          title="Sunumu Görüntüle"
        >
          <EyeIcon class="h-3 w-3" />
        </button>
        <button
          v-if="canEdit"
          @click.stop="handleEditClick"
          class="p-1 text-gray-400 hover:text-orange-600 transition-colors"
          title="Sunumu Düzenle"
        >
          <PencilSquareIcon class="h-3 w-3" />
        </button>
      </div>
      
      <!-- Presentation ID -->
      <div class="text-xs text-gray-400">
        #{{ presentationData.id }}
      </div>
    </div>

    <!-- Bottom Border for Visual Separation -->
    <div class="presentation-border mt-2 border-b border-gray-200 dark:border-gray-700 opacity-50"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  ClockIcon,
  EyeIcon,
  PencilSquareIcon,
  UserIcon,
  GlobeAltIcon,
  MicrophoneIcon,
  DocumentTextIcon,
  PresentationChartLineIcon,
  UserGroupIcon,
  AcademicCapIcon,
  StarIcon,
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  presentationData: {
    type: Object,
    required: true
  },
  sessionData: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['presentation-click'])

// Computed
const presentationTimeRange = computed(() => {
  if (props.presentationData.formatted_time_range) {
    return props.presentationData.formatted_time_range
  }
  
  if (props.presentationData.start_time && props.presentationData.end_time) {
    return `${props.presentationData.start_time} - ${props.presentationData.end_time}`
  }
  
  return null
})

const presentationTypeClass = computed(() => {
  const baseClass = 'presentation-item'
  const typeClasses = {
    keynote: 'presentation-keynote',
    oral: 'presentation-oral',
    poster: 'presentation-poster',
    panel: 'presentation-panel',
    workshop: 'presentation-workshop',
  }
  
  return [baseClass, typeClasses[props.presentationData.presentation_type] || 'presentation-default']
})

const presentationTypeBadgeClass = computed(() => {
  const badgeClasses = {
    keynote: 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-200',
    oral: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
    poster: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200',
    panel: 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200',
    workshop: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200',
  }
  
  return badgeClasses[props.presentationData.presentation_type] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'
})

const presentationTypeIcon = computed(() => {
  const icons = {
    keynote: StarIcon,
    oral: MicrophoneIcon,
    poster: PresentationChartLineIcon,
    panel: UserGroupIcon,
    workshop: AcademicCapIcon,
  }
  
  return icons[props.presentationData.presentation_type] || DocumentTextIcon
})

const presentationTypeLabel = computed(() => {
  const labels = {
    keynote: 'Keynote',
    oral: 'Sözlü',
    poster: 'Poster',
    panel: 'Panel',
    workshop: 'Workshop',
  }
  
  return labels[props.presentationData.presentation_type] || 'Sunum'
})

const canEdit = computed(() => {
  // Check if user can edit this presentation
  return props.presentationData.can_edit || 
         props.sessionData.can_edit || 
         false // Add proper permission check here
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

const getSpeakerRoleLabel = (role) => {
  const labels = {
    primary: 'Ana',
    co_speaker: 'Ko',
    discussant: 'Tartışmacı',
    moderator: 'Moderatör',
  }
  
  return labels[role] || role
}

const getLanguageLabel = (language) => {
  const labels = {
    en: 'İngilizce',
    tr: 'Türkçe',
    fr: 'Fransızca',
    de: 'Almanca',
    es: 'İspanyolca',
  }
  
  return labels[language] || language.toUpperCase()
}

const handlePresentationClick = () => {
  emit('presentation-click', props.presentationData)
}

const handleEditClick = () => {
  router.visit(route('admin.presentations.edit', props.presentationData.id))
}
</script>

<style scoped>
.timeline-presentation-item {
  @apply bg-gray-50 dark:bg-gray-800 rounded-md p-3 border border-gray-200 dark:border-gray-700 transition-all duration-200;
}

.timeline-presentation-item:hover {
  @apply bg-white dark:bg-gray-700 shadow-sm border-purple-300 dark:border-purple-600;
  transform: translateX(2px);
}

/* Presentation type specific styling */
.presentation-keynote {
  @apply border-l-2 border-l-purple-500;
}

.presentation-oral {
  @apply border-l-2 border-l-blue-500;
}

.presentation-poster {
  @apply border-l-2 border-l-green-500;
}

.presentation-panel {
  @apply border-l-2 border-l-orange-500;
}

.presentation-workshop {
  @apply border-l-2 border-l-indigo-500;
}

.presentation-default {
  @apply border-l-2 border-l-gray-500;
}

/* Presentation header */
.presentation-header {
  @apply mb-1;
}

.presentation-title {
  @apply mb-0 leading-tight;
  line-height: 1.2;
}

/* Speakers styling */
.presentation-speakers {
  @apply bg-white dark:bg-gray-900 rounded-sm p-2 border border-gray-100 dark:border-gray-600;
}

/* Actions */
.presentation-actions {
  @apply pt-2 border-t border-gray-200 dark:border-gray-600;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .timeline-presentation-item {
    @apply p-2;
  }
  
  .presentation-title {
    @apply text-xs;
  }
  
  .presentation-actions {
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
.timeline-presentation-item:focus-within {
  @apply ring-1 ring-purple-500 ring-offset-1 dark:ring-offset-gray-800;
}

/* Loading state */
.presentation-loading {
  @apply animate-pulse bg-gray-300 dark:bg-gray-600;
}

.presentation-loading .presentation-title,
.presentation-loading .presentation-time,
.presentation-loading .presentation-speakers {
  @apply bg-gray-400 dark:bg-gray-500 rounded;
  height: 0.75rem;
  margin-bottom: 0.25rem;
}

/* Print styles */
@media print {
  .timeline-presentation-item {
    @apply shadow-none border border-gray-400 break-inside-avoid;
  }
  
  .presentation-actions {
    @apply hidden;
  }
}

/* Accessibility */
.timeline-presentation-item:focus {
  @apply outline-none ring-2 ring-purple-500 ring-offset-2 dark:ring-offset-gray-900;
}

/* Last item styling */
.timeline-presentation-item:last-child .presentation-border {
  @apply hidden;
}

/* Responsive text sizing */
@media (min-width: 1024px) {
  .presentation-title {
    @apply text-sm;
  }
}

/* Hover animations */
.presentation-type {
  transition: transform 0.2s ease;
}

.timeline-presentation-item:hover .presentation-type {
  transform: scale(1.05);
}

/* Speaker role styling */
.speaker-role-primary {
  @apply font-semibold;
}

.speaker-role-co {
  @apply font-medium;
}

.speaker-role-discussant {
  @apply italic;
}
</style>