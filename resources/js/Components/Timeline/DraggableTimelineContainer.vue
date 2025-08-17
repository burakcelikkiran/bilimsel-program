<!-- resources/js/Components/Timeline/DraggableTimelineContainer.vue -->
<template>
  <div class="draggable-timeline-container">
    <!-- Edit Mode Toggle -->
    <div class="timeline-toolbar mb-6">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <button
              @click="toggleEditMode"
              :class="[
                editMode 
                  ? 'bg-orange-600 hover:bg-orange-700 text-white' 
                  : 'bg-gray-200 hover:bg-gray-300 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300'
              ]"
              class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              <CursorArrowRaysIcon class="h-4 w-4 mr-2" />
              {{ editMode ? 'Düzenleme Modunu Kapat' : 'Düzenleme Modu' }}
            </button>
            
            <div v-if="editMode" class="text-sm text-orange-600 dark:text-orange-400">
              <ExclamationTriangleIcon class="h-4 w-4 inline mr-1" />
              Oturumları sürükleyerek yeniden düzenleyebilirsiniz
            </div>
          </div>

          <div v-if="editMode" class="flex items-center space-x-2">
            <button
              @click="saveChanges"
              :disabled="!hasChanges || saving"
              :class="[
                hasChanges && !saving
                  ? 'bg-green-600 hover:bg-green-700 text-white'
                  : 'bg-gray-300 text-gray-500 cursor-not-allowed dark:bg-gray-600 dark:text-gray-400'
              ]"
              class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              <CheckIcon v-if="!saving" class="h-4 w-4 mr-2" />
              <div v-else class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              {{ saving ? 'Kaydediliyor...' : 'Değişiklikleri Kaydet' }}
            </button>
            
            <button
              @click="resetChanges"
              :disabled="!hasChanges || saving"
              class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
            >
              <ArrowPathIcon class="h-4 w-4 mr-1" />
              Sıfırla
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Timeline Content -->
    <div class="timeline-days space-y-8">
      <DraggableDaySection
        v-for="dayData in timelineData"
        :key="dayData.id"
        :day-data="dayData"
        :event="event"
        :edit-mode="editMode"
        @session-moved="handleSessionMoved"
        @session-click="$emit('session-click', $event)"
        @presentation-click="$emit('presentation-click', $event)"
      />
    </div>

    <!-- Drag Overlay -->
    <div
      v-show="dragState.isDragging"
      class="fixed inset-0 bg-black bg-opacity-20 pointer-events-none z-50"
    >
      <div
        ref="dragPreview"
        class="absolute bg-white dark:bg-gray-800 rounded-lg shadow-lg border-2 border-orange-400 p-3 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none"
        :style="{ 
          left: dragState.x + 'px', 
          top: dragState.y + 'px',
          width: '300px'
        }"
      >
        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ dragState.session?.title }}</div>
        <div class="text-xs text-gray-500">{{ dragState.session?.formatted_time_range }}</div>
      </div>
    </div>

    <!-- Drop Zones Indicators -->
    <div v-if="editMode" class="fixed bottom-4 right-4 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 z-40">
      <div class="text-sm font-medium text-gray-900 dark:text-white mb-2">Sürükleme Rehberi</div>
      <div class="space-y-2 text-xs text-gray-600 dark:text-gray-400">
        <div class="flex items-center space-x-2">
          <div class="w-3 h-3 bg-green-400 rounded"></div>
          <span>Geçerli bırakma alanı</span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-3 h-3 bg-red-400 rounded"></div>
          <span>Çakışma var</span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-3 h-3 bg-blue-400 rounded"></div>
          <span>Mevcut konum</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, provide, reactive, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import DraggableDaySection from './DraggableDaySection.vue'
import {
  CursorArrowRaysIcon,
  ExclamationTriangleIcon,
  CheckIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  timelineData: {
    type: Array,
    required: true
  },
  event: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['session-click', 'presentation-click', 'timeline-updated'])

// Reactive data
const editMode = ref(false)
const saving = ref(false)
const originalData = ref(null)
const dragPreview = ref(null)

const dragState = reactive({
  isDragging: false,
  session: null,
  sourceVenue: null,
  sourceDay: null,
  x: 0,
  y: 0
})

const changes = ref([])

// Computed
const hasChanges = computed(() => changes.value.length > 0)

// Methods
const toggleEditMode = () => {
  if (editMode.value) {
    if (hasChanges.value) {
      if (confirm('Kaydedilmemiş değişiklikler var. Düzenleme modundan çıkmak istediğinizden emin misiniz?')) {
        resetChanges()
        editMode.value = false
      }
    } else {
      editMode.value = false
    }
  } else {
    editMode.value = true
    originalData.value = JSON.parse(JSON.stringify(props.timelineData))
  }
}

const handleSessionMoved = (moveData) => {
  const { session, fromVenue, toVenue, newPosition, newTime } = moveData
  
  // Find and update the session in the timeline data
  const fromDay = props.timelineData.find(day => 
    day.venues.some(venue => venue.id === fromVenue.id)
  )
  const toDay = props.timelineData.find(day => 
    day.venues.some(venue => venue.id === toVenue.id)
  )
  
  if (fromDay && toDay) {
    const fromVenueData = fromDay.venues.find(v => v.id === fromVenue.id)
    const toVenueData = toDay.venues.find(v => v.id === toVenue.id)
    
    if (fromVenueData && toVenueData) {
      // Remove session from source
      const sessionIndex = fromVenueData.sessions.findIndex(s => s.id === session.id)
      if (sessionIndex !== -1) {
        const [movedSession] = fromVenueData.sessions.splice(sessionIndex, 1)
        
        // Update session properties if moved to different time/venue
        if (newTime) {
          movedSession.start_time = newTime.start_time
          movedSession.end_time = newTime.end_time
        }
        
        // Add to target venue at new position
        toVenueData.sessions.splice(newPosition, 0, movedSession)
        
        // Record the change
        changes.value.push({
          type: 'session_moved',
          session_id: session.id,
          from_venue_id: fromVenue.id,
          to_venue_id: toVenue.id,
          from_day_id: fromDay.id,
          to_day_id: toDay.id,
          new_position: newPosition,
          new_time: newTime,
          timestamp: Date.now()
        })
      }
    }
  }
}

const saveChanges = async () => {
  if (!hasChanges.value || saving.value) return
  
  saving.value = true
  
  try {
    const response = await fetch(route('admin.timeline.update-order', props.event.slug), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        changes: changes.value,
        timeline_data: props.timelineData
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      changes.value = []
      originalData.value = JSON.parse(JSON.stringify(props.timelineData))
      emit('timeline-updated', result.data)
      
      // Show success message
      window.toast?.success('Değişiklikler başarıyla kaydedildi!')
    } else {
      throw new Error(result.message || 'Kaydetme sırasında hata oluştu')
    }
  } catch (error) {
    console.error('Save error:', error)
    window.toast?.error('Değişiklikler kaydedilemedi: ' + error.message)
  } finally {
    saving.value = false
  }
}

const resetChanges = () => {
  if (originalData.value) {
    // Reset timeline data to original state
    Object.assign(props.timelineData, JSON.parse(JSON.stringify(originalData.value)))
    changes.value = []
  }
}

const updateDragPreview = (e) => {
  if (dragState.isDragging) {
    dragState.x = e.clientX
    dragState.y = e.clientY
  }
}

// Provide drag context to child components
provide('dragContext', {
  editMode,
  dragState,
  startDrag: (session, venue, day) => {
    dragState.isDragging = true
    dragState.session = session
    dragState.sourceVenue = venue
    dragState.sourceDay = day
  },
  endDrag: () => {
    dragState.isDragging = false
    dragState.session = null
    dragState.sourceVenue = null
    dragState.sourceDay = null
  }
})

// Lifecycle
onMounted(() => {
  document.addEventListener('mousemove', updateDragPreview)
})

onUnmounted(() => {
  document.removeEventListener('mousemove', updateDragPreview)
})
</script>

<style scoped>
.draggable-timeline-container {
  position: relative;
}

/* Drag and drop visual effects */
.drag-source {
  opacity: 0.5;
  transform: rotate(5deg);
}

.drag-over-valid {
  background-color: rgba(34, 197, 94, 0.1);
  border: 2px dashed #22c55e;
}

.drag-over-invalid {
  background-color: rgba(239, 68, 68, 0.1);
  border: 2px dashed #ef4444;
}

.drop-zone {
  min-height: 60px;
  border: 2px dashed transparent;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.drop-zone.active {
  border-color: #3b82f6;
  background-color: rgba(59, 130, 246, 0.05);
}

.drop-zone.valid {
  border-color: #22c55e;
  background-color: rgba(34, 197, 94, 0.05);
}

.drop-zone.invalid {
  border-color: #ef4444;
  background-color: rgba(239, 68, 68, 0.05);
}

/* Session drag states */
.session-dragging {
  z-index: 1000;
  transform: rotate(5deg) scale(1.05);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.session-drop-target {
  border: 2px solid #3b82f6;
  background-color: rgba(59, 130, 246, 0.05);
}

/* Smooth transitions */
.session-item {
  transition: all 0.2s ease;
}

.session-item:not(.session-dragging) {
  cursor: grab;
}

.session-item:not(.session-dragging):active {
  cursor: grabbing;
}

/* Edit mode indicators */
.edit-mode .session-item {
  border-left: 4px solid #f59e0b;
}

.edit-mode .session-item:hover {
  border-left-color: #d97706;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

/* Drag preview styling */
.drag-preview {
  pointer-events: none;
  z-index: 1000;
  transform: rotate(5deg);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

/* Time slot indicators */
.time-slot {
  border: 1px dashed #e5e7eb;
  background: linear-gradient(45deg, #f9fafb 25%, transparent 25%, transparent 75%, #f9fafb 75%);
  background-size: 20px 20px;
}

.dark .time-slot {
  border-color: #374151;
  background: linear-gradient(45deg, #1f2937 25%, transparent 25%, transparent 75%, #1f2937 75%);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .timeline-toolbar {
    padding: 1rem;
  }
  
  .timeline-toolbar .flex {
    flex-direction: column;
    space-y: 2px;
  }
}
</style>