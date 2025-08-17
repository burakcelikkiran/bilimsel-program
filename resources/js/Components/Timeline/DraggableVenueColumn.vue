<!-- resources/js/Components/Timeline/DraggableVenueColumn.vue -->
<template>
  <div 
    class="draggable-venue-column"
    :class="{ 'edit-mode': editMode }"
  >
    <!-- Venue Header -->
    <div class="venue-header mb-4">
      <div 
        class="rounded-lg shadow-sm border-2 p-4"
        :style="{ 
          borderColor: venueData.color || '#3B82F6',
          backgroundColor: `${venueData.color || '#3B82F6'}10`
        }"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div 
              class="w-4 h-4 rounded-full flex-shrink-0"
              :style="{ backgroundColor: venueData.color || '#3B82F6' }"
            ></div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ venueData.display_name || venueData.name }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ venueData.capacity ? `Kapasite: ${venueData.capacity}` : 'Salon' }}
              </p>
            </div>
          </div>
          
          <div class="text-right">
            <div class="text-lg font-bold text-gray-900 dark:text-white">
              {{ venueData.sessions.length }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Oturum</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sessions Container -->
    <div 
      class="venue-sessions"
      :class="{ 'drop-zone-active': editMode }"
      @drop="handleDrop"
      @dragover.prevent
      @dragenter.prevent="handleDragEnter"
      @dragleave="handleDragLeave"
    >
      <!-- Time Slots (Background Grid) -->
      <div v-if="editMode" class="time-slots-background">
        <div
          v-for="slot in timeSlots"
          :key="slot.start"
          class="time-slot"
          :style="{ 
            height: slot.height + 'px',
            top: slot.top + 'px'
          }"
          :data-start-time="slot.start"
          :data-end-time="slot.end"
        >
          <div class="time-slot-label">{{ slot.start }} - {{ slot.end }}</div>
        </div>
      </div>

      <!-- Sessions List -->
      <div class="sessions-list space-y-3">
        <DraggableSessionCard
          v-for="(session, index) in sortedSessions"
          :key="session.id"
          :session-data="session"
          :venue-data="venueData"
          :day-data="dayData"
          :edit-mode="editMode"
          :session-index="index"
          @session-click="$emit('session-click', $event)"
          @presentation-click="$emit('presentation-click', $event)"
          @drag-start="handleSessionDragStart"
          @drag-end="handleSessionDragEnd"
        />
      </div>

      <!-- Drop Target Indicator -->
      <div
        v-if="editMode && dragState.isDragOver && dragState.canDrop"
        class="drop-target-indicator"
        :style="{ 
          top: dragState.dropPosition.y + 'px',
          left: '0',
          right: '0'
        }"
      >
        <div class="drop-line"></div>
        <div class="drop-message">Oturumu buraya bırak</div>
      </div>

      <!-- Invalid Drop Indicator -->
      <div
        v-if="editMode && dragState.isDragOver && !dragState.canDrop"
        class="invalid-drop-indicator"
      >
        <ExclamationTriangleIcon class="h-6 w-6 text-red-500" />
        <span class="text-sm text-red-600 dark:text-red-400">{{ dragState.errorMessage }}</span>
      </div>

      <!-- Empty State -->
      <div v-if="venueData.sessions.length === 0" class="empty-sessions-placeholder">
        <div 
          class="empty-drop-zone"
          :class="{ 
            'drag-over': editMode && dragState.isDragOver,
            'can-drop': editMode && dragState.isDragOver && dragState.canDrop
          }"
        >
          <ClockIcon class="h-8 w-8 text-gray-400 mx-auto mb-2" />
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ editMode ? 'Oturum sürükleyerek buraya bırakın' : 'Bu salonda oturum yok' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, ref, reactive } from 'vue'
import DraggableSessionCard from './DraggableSessionCard.vue'
import {
  ClockIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
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
  }
})

// Emits
const emit = defineEmits(['session-moved', 'session-click', 'presentation-click'])

// Inject drag context
const dragContext = inject('dragContext', null)

// Reactive data
const dragState = reactive({
  isDragOver: false,
  canDrop: false,
  dropPosition: { x: 0, y: 0 },
  targetIndex: -1,
  errorMessage: ''
})

// Computed
const sortedSessions = computed(() => {
  return [...props.venueData.sessions].sort((a, b) => {
    if (a.start_time && b.start_time) {
      return a.start_time.localeCompare(b.start_time)
    }
    return (a.sort_order || 0) - (b.sort_order || 0)
  })
})

const timeSlots = computed(() => {
  if (!props.editMode) return []
  
  const slots = []
  const startHour = 8
  const endHour = 18
  const slotDuration = 30 // minutes
  
  for (let hour = startHour; hour < endHour; hour++) {
    for (let minute = 0; minute < 60; minute += slotDuration) {
      const start = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`
      const endMinute = minute + slotDuration
      const endHourCalc = endMinute >= 60 ? hour + 1 : hour
      const endMinuteCalc = endMinute >= 60 ? 0 : endMinute
      const end = `${endHourCalc.toString().padStart(2, '0')}:${endMinuteCalc.toString().padStart(2, '0')}`
      
      slots.push({
        start,
        end,
        height: 40,
        top: ((hour - startHour) * 60 + minute) * (40 / slotDuration)
      })
    }
  }
  
  return slots
})

// Methods
const handleSessionDragStart = (session) => {
  if (dragContext) {
    dragContext.startDrag(session, props.venueData, props.dayData)
  }
}

const handleSessionDragEnd = () => {
  if (dragContext) {
    dragContext.endDrag()
  }
  resetDragState()
}

const handleDragEnter = (event) => {
  if (!props.editMode || !dragContext?.dragState.isDragging) return
  
  event.preventDefault()
  dragState.isDragOver = true
  
  updateDropValidation(event)
}

const handleDragLeave = (event) => {
  if (!event.currentTarget.contains(event.relatedTarget)) {
    resetDragState()
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  
  if (!props.editMode || !dragContext?.dragState.isDragging || !dragState.canDrop) {
    resetDragState()
    return
  }
  
  const session = dragContext.dragState.session
  const sourceVenue = dragContext.dragState.sourceVenue
  
  if (!session || !sourceVenue) {
    resetDragState()
    return
  }
  
  // Calculate new position and time
  const newPosition = calculateDropPosition(event)
  const newTime = calculateNewTime(event, session)
  
  // Emit session moved event
  emit('session-moved', {
    session: session,
    fromVenue: sourceVenue,
    toVenue: props.venueData,
    newPosition: newPosition,
    newTime: newTime
  })
  
  resetDragState()
  handleSessionDragEnd()
}

const updateDropValidation = (event) => {
  if (!dragContext?.dragState.isDragging) return
  
  const session = dragContext.dragState.session
  const sourceVenue = dragContext.dragState.sourceVenue
  
  // Update drop position
  const rect = event.currentTarget.getBoundingClientRect()
  dragState.dropPosition = {
    x: event.clientX - rect.left,
    y: event.clientY - rect.top
  }
  
  // Validate drop
  const validation = validateDrop(session, sourceVenue)
  dragState.canDrop = validation.valid
  dragState.errorMessage = validation.message
}

const validateDrop = (session, sourceVenue) => {
  if (!session) {
    return { valid: false, message: 'Geçersiz oturum' }
  }
  
  // Same venue check
  if (sourceVenue.id === props.venueData.id) {
    return { valid: true, message: '' }
  }
  
  // Time conflict check
  const hasConflict = props.venueData.sessions.some(existingSession => {
    if (existingSession.id === session.id) return false
    
    const sessionStart = new Date(`2000-01-01 ${session.start_time}`)
    const sessionEnd = new Date(`2000-01-01 ${session.end_time}`)
    const existingStart = new Date(`2000-01-01 ${existingSession.start_time}`)
    const existingEnd = new Date(`2000-01-01 ${existingSession.end_time}`)
    
    return (sessionStart < existingEnd && sessionEnd > existingStart)
  })
  
  if (hasConflict) {
    return { valid: false, message: 'Zaman çakışması var' }
  }
  
  // Venue capacity check (if applicable)
  if (props.venueData.capacity && props.venueData.sessions.length >= props.venueData.capacity) {
    return { valid: false, message: 'Salon kapasitesi dolu' }
  }
  
  return { valid: true, message: '' }
}

const calculateDropPosition = (event) => {
  const rect = event.currentTarget.querySelector('.sessions-list').getBoundingClientRect()
  const y = event.clientY - rect.top
  
  // Find the best position based on Y coordinate
  const sessionElements = event.currentTarget.querySelectorAll('.draggable-session-card')
  
  for (let i = 0; i < sessionElements.length; i++) {
    const element = sessionElements[i]
    const elementRect = element.getBoundingClientRect()
    const elementY = elementRect.top - rect.top
    
    if (y < elementY + (elementRect.height / 2)) {
      return i
    }
  }
  
  return props.venueData.sessions.length
}

const calculateNewTime = (event, session) => {
  // For now, keep the same time when moving between venues
  // This could be enhanced to snap to time slots
  return null
}

const resetDragState = () => {
  dragState.isDragOver = false
  dragState.canDrop = false
  dragState.dropPosition = { x: 0, y: 0 }
  dragState.targetIndex = -1
  dragState.errorMessage = ''
}
</script>

<style scoped>
.draggable-venue-column {
  position: relative;
  min-height: 200px;
}

/* Edit mode styling */
.edit-mode {
  background: rgba(59, 130, 246, 0.02);
  border-radius: 1rem;
  padding: 0.5rem;
}

.dark .edit-mode {
  background: rgba(59, 130, 246, 0.05);
}

/* Venue sessions container */
.venue-sessions {
  position: relative;
  min-height: 150px;
}

.drop-zone-active {
  border: 2px dashed transparent;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.drop-zone-active.drag-over {
  border-color: #3b82f6;
  background-color: rgba(59, 130, 246, 0.05);
}

/* Time slots background */
.time-slots-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 1;
}

.time-slot {
  position: absolute;
  left: 0;
  right: 0;
  border-top: 1px solid rgba(229, 231, 235, 0.5);
  background: linear-gradient(45deg, rgba(249, 250, 251, 0.3) 25%, transparent 25%, transparent 75%, rgba(249, 250, 251, 0.3) 75%);
  background-size: 10px 10px;
}

.dark .time-slot {
  border-color: rgba(75, 85, 99, 0.5);
  background: linear-gradient(45deg, rgba(31, 41, 55, 0.3) 25%, transparent 25%, transparent 75%, rgba(31, 41, 55, 0.3) 75%);
}

.time-slot-label {
  position: absolute;
  left: 4px;
  top: 2px;
  font-size: 0.625rem;
  color: rgba(156, 163, 175, 0.7);
  font-weight: 500;
}

/* Sessions list */
.sessions-list {
  position: relative;
  z-index: 2;
}

/* Drop indicators */
.drop-target-indicator {
  position: absolute;
  z-index: 10;
  pointer-events: none;
}

.drop-line {
  height: 3px;
  background: #22c55e;
  border-radius: 1.5px;
  box-shadow: 0 0 8px rgba(34, 197, 94, 0.5);
}

.drop-message {
  position: absolute;
  top: -25px;
  left: 50%;
  transform: translateX(-50%);
  background: #22c55e;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  white-space: nowrap;
}

.drop-message::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 4px solid transparent;
  border-top-color: #22c55e;
}

.invalid-drop-indicator {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  items-center;
  space-x: 2px;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  padding: 8px 12px;
  border-radius: 8px;
  z-index: 20;
  font-size: 0.875rem;
  backdrop-filter: blur(4px);
}

/* Empty state */
.empty-sessions-placeholder {
  padding: 2rem;
}

.empty-drop-zone {
  border: 2px dashed #e5e7eb;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  transition: all 0.2s ease;
}

.dark .empty-drop-zone {
  border-color: #374151;
}

.empty-drop-zone.drag-over {
  border-color: #3b82f6;
  background-color: rgba(59, 130, 246, 0.05);
}

.empty-drop-zone.can-drop {
  border-color: #22c55e;
  background-color: rgba(34, 197, 94, 0.05);
}

/* Responsive design */
@media (max-width: 768px) {
  .time-slots-background {
    display: none;
  }
  
  .edit-mode {
    padding: 0.25rem;
  }
}

/* Animation for smooth transitions */
.sessions-list > * {
  transition: transform 0.2s ease;
}

.venue-sessions.drag-over .sessions-list > * {
  transform: scale(0.98);
}
</style>