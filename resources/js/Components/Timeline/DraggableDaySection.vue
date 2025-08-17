<!-- resources/js/Components/Timeline/DraggableDaySection.vue -->
<template>
  <div class="draggable-day-section">
    <!-- Day Header -->
    <div class="day-header mb-4">
      <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl shadow-sm text-white p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="h-12 w-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
              <CalendarDaysIcon class="h-8 w-8 text-white" />
            </div>
            <div>
              <h2 class="text-2xl font-bold">{{ dayData.display_name || dayData.title }}</h2>
              <p class="text-white text-opacity-90">{{ dayData.formatted_date }}</p>
            </div>
          </div>
          
          <!-- Edit Mode Indicator -->
          <div v-if="editMode" class="flex items-center space-x-2 bg-white bg-opacity-20 rounded-lg px-3 py-2">
            <CursorArrowRaysIcon class="h-5 w-5 text-white" />
            <span class="text-sm font-medium">Düzenleme Modu</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Venues Grid -->
    <div class="venues-grid">
      <div 
        class="grid gap-6"
        :class="gridCols"
      >
        <DraggableVenueColumn
          v-for="venue in dayData.venues"
          :key="venue.id"
          :venue-data="venue"
          :day-data="dayData"
          :edit-mode="editMode"
          @session-moved="$emit('session-moved', $event)"
          @session-click="$emit('session-click', $event)"
          @presentation-click="$emit('presentation-click', $event)"
        />
      </div>
    </div>

    <!-- Cross-Venue Drop Zones (for moving between venues) -->
    <div v-if="editMode && dragContext?.dragState.isDragging" class="cross-venue-zones mt-6">
      <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-3">
          Salon Arası Taşıma Alanları
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="venue in dayData.venues"
            :key="'cross-' + venue.id"
            @drop="handleCrossVenueDrop($event, venue)"
            @dragover.prevent
            @dragenter.prevent="handleDragEnter($event, venue)"
            @dragleave="handleDragLeave($event, venue)"
            :class="[
              'cross-venue-drop-zone p-3 border-2 border-dashed rounded-lg text-center transition-all',
              getCrossZoneClass(venue)
            ]"
          >
            <div class="flex items-center justify-center space-x-2">
              <div 
                class="w-3 h-3 rounded-full"
                :style="{ backgroundColor: venue.color || '#3B82F6' }"
              ></div>
              <span class="text-sm font-medium">{{ venue.display_name || venue.name }}</span>
            </div>
            <div class="text-xs text-gray-500 mt-1">
              {{ venue.sessions.length }} oturum
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import DraggableVenueColumn from './DraggableVenueColumn.vue'
import {
  CalendarDaysIcon,
  CursorArrowRaysIcon,
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  dayData: {
    type: Object,
    required: true
  },
  editMode: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['session-moved', 'session-click', 'presentation-click'])

// Inject drag context
const dragContext = inject('dragContext', null)

// Reactive data
const dragOverVenue = ref(null)

// Computed
const gridCols = computed(() => {
  const venueCount = props.dayData.venues.length
  
  if (venueCount === 1) return 'grid-cols-1'
  if (venueCount === 2) return 'grid-cols-1 lg:grid-cols-2'
  if (venueCount === 3) return 'grid-cols-1 lg:grid-cols-2 xl:grid-cols-3'
  if (venueCount === 4) return 'grid-cols-1 lg:grid-cols-2 xl:grid-cols-4'
  
  return 'grid-cols-1 lg:grid-cols-2 xl:grid-cols-3'
})

// Methods
const getCrossZoneClass = (venue) => {
  if (!dragContext?.dragState.isDragging) {
    return 'border-gray-300 dark:border-gray-600 text-gray-500'
  }
  
  const sourceVenueId = dragContext.dragState.sourceVenue?.id
  
  if (venue.id === sourceVenueId) {
    return 'border-blue-400 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
  }
  
  if (dragOverVenue.value === venue.id) {
    // Check for time conflicts
    if (hasTimeConflict(venue, dragContext.dragState.session)) {
      return 'border-red-400 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300'
    } else {
      return 'border-green-400 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300'
    }
  }
  
  return 'border-gray-300 dark:border-gray-600 text-gray-500 hover:border-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20'
}

const hasTimeConflict = (venue, session) => {
  if (!session || !venue.sessions) return false
  
  return venue.sessions.some(existingSession => {
    if (existingSession.id === session.id) return false
    
    const sessionStart = new Date(`2000-01-01 ${session.start_time}`)
    const sessionEnd = new Date(`2000-01-01 ${session.end_time}`)
    const existingStart = new Date(`2000-01-01 ${existingSession.start_time}`)
    const existingEnd = new Date(`2000-01-01 ${existingSession.end_time}`)
    
    return (sessionStart < existingEnd && sessionEnd > existingStart)
  })
}

const handleDragEnter = (event, venue) => {
  event.preventDefault()
  dragOverVenue.value = venue.id
}

const handleDragLeave = (event, venue) => {
  // Only clear if we're actually leaving the venue zone
  if (!event.currentTarget.contains(event.relatedTarget)) {
    dragOverVenue.value = null
  }
}

const handleCrossVenueDrop = (event, venue) => {
  event.preventDefault()
  dragOverVenue.value = null
  
  if (!dragContext?.dragState.isDragging) return
  
  const session = dragContext.dragState.session
  const sourceVenue = dragContext.dragState.sourceVenue
  
  if (!session || !sourceVenue || venue.id === sourceVenue.id) return
  
  // Check for time conflicts
  if (hasTimeConflict(venue, session)) {
    window.toast?.error('Seçilen salonda zaman çakışması var!')
    return
  }
  
  // Emit session moved event
  emit('session-moved', {
    session: session,
    fromVenue: sourceVenue,
    toVenue: venue,
    newPosition: venue.sessions.length, // Add to end
    newTime: null // Keep same time
  })
  
  dragContext.endDrag()
}
</script>

<style scoped>
.draggable-day-section {
  position: relative;
}

/* Cross venue zones styling */
.cross-venue-zones {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.cross-venue-drop-zone {
  cursor: pointer;
  transition: all 0.2s ease;
}

.cross-venue-drop-zone:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Edit mode styling */
.edit-mode .venues-grid {
  padding: 1rem;
  border: 2px dashed #f59e0b;
  border-radius: 1rem;
  background: linear-gradient(45deg, #fef3c7 25%, transparent 25%, transparent 75%, #fef3c7 75%);
  background-size: 20px 20px;
}

.dark .edit-mode .venues-grid {
  border-color: #d97706;
  background: linear-gradient(45deg, #451a03 25%, transparent 25%, transparent 75%, #451a03 75%);
}

/* Responsive behavior */
@media (max-width: 768px) {
  .cross-venue-zones .grid {
    grid-template-columns: 1fr;
  }
}
</style>