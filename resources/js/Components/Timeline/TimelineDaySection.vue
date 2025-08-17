<!-- resources/js/Components/Timeline/TimelineDaySection.vue -->
<template>
  <div class="timeline-day-section">
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
          
          <!-- Day Stats -->
          <div class="flex items-center space-x-6 text-sm">
            <div class="text-center">
              <div class="text-2xl font-bold">{{ totalSessions }}</div>
              <div class="text-white text-opacity-80">Oturum</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold">{{ totalPresentations }}</div>
              <div class="text-white text-opacity-80">Sunum</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold">{{ dayData.venues.length }}</div>
              <div class="text-white text-opacity-80">Salon</div>
            </div>
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
        <TimelineVenueColumn
          v-for="venue in dayData.venues"
          :key="venue.id"
          :venue-data="venue"
          :day-data="dayData"
          :event="event"
          :time-scale="timeScale"
          :time-hours="timeHours"
          @session-click="$emit('session-click', $event)"
          @presentation-click="$emit('presentation-click', $event)"
        />
      </div>
    </div>

    <!-- Empty State for Day -->
    <div v-if="dayData.venues.length === 0" class="text-center py-12">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
          <CalendarDaysIcon class="h-full w-full" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Bu gün için program yok</h3>
        <p class="text-gray-500 dark:text-gray-400">{{ dayData.display_name || dayData.title }} günü için henüz program oluşturulmamış.</p>
      </div>
    </div>

    <!-- Day Summary Footer -->
    <div v-if="dayData.venues.length > 0" class="day-footer mt-6">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between">
          <!-- Time Range -->
          <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center space-x-1">
              <ClockIcon class="h-4 w-4" />
              <span>{{ dayTimeRange }}</span>
            </div>
            <div class="flex items-center space-x-1">
              <span>Toplam Süre:</span>
              <span class="font-medium">{{ dayTotalDuration }}</span>
            </div>
          </div>

          <!-- Session Types Summary -->
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600 dark:text-gray-400 mr-2">Oturum Tipleri:</span>
            <div v-for="(count, type) in sessionTypeCounts" :key="type" class="flex items-center space-x-1">
              <div 
                :class="getSessionTypeColor(type)"
                class="w-3 h-3 rounded-full"
              ></div>
              <span class="text-xs text-gray-600 dark:text-gray-400">{{ count }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import TimelineVenueColumn from './TimelineVenueColumn.vue'
import {
  CalendarDaysIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  dayData: {
    type: Object,
    required: true
  },
  event: {
    type: Object,
    required: true
  },
  timeScale: {
    type: String,
    default: 'session'
  },
  timeHours: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['session-click', 'presentation-click'])

// Computed
const gridCols = computed(() => {
  const venueCount = props.dayData.venues.length
  
  if (venueCount === 1) return 'grid-cols-1'
  if (venueCount === 2) return 'grid-cols-1 lg:grid-cols-2'
  if (venueCount === 3) return 'grid-cols-1 lg:grid-cols-2 xl:grid-cols-3'
  if (venueCount === 4) return 'grid-cols-1 lg:grid-cols-2 xl:grid-cols-4'
  
  return 'grid-cols-1 lg:grid-cols-2 xl:grid-cols-3' // Max 3 columns for better readability
})

const totalSessions = computed(() => {
  return props.dayData.venues.reduce((total, venue) => {
    return total + venue.sessions.length
  }, 0)
})

const totalPresentations = computed(() => {
  return props.dayData.venues.reduce((total, venue) => {
    return total + venue.sessions.reduce((sessionTotal, session) => {
      return sessionTotal + session.presentations.length
    }, 0)
  }, 0)
})

const dayTimeRange = computed(() => {
  let earliestTime = null
  let latestTime = null
  
  props.dayData.venues.forEach(venue => {
    venue.sessions.forEach(session => {
      if (session.start_time) {
        if (!earliestTime || session.start_time < earliestTime) {
          earliestTime = session.start_time
        }
      }
      if (session.end_time) {
        if (!latestTime || session.end_time > latestTime) {
          latestTime = session.end_time
        }
      }
    })
  })
  
  if (earliestTime && latestTime) {
    return `${earliestTime} - ${latestTime}`
  }
  
  return 'Zaman belirtilmemiş'
})

const dayTotalDuration = computed(() => {
  const totalMinutes = props.dayData.venues.reduce((total, venue) => {
    return total + venue.sessions.reduce((sessionTotal, session) => {
      return sessionTotal + (session.duration_in_minutes || 0)
    }, 0)
  }, 0)
  
  const hours = Math.floor(totalMinutes / 60)
  const minutes = totalMinutes % 60
  
  if (hours > 0 && minutes > 0) {
    return `${hours}s ${minutes}dk`
  } else if (hours > 0) {
    return `${hours}s`
  } else {
    return `${minutes}dk`
  }
})

const sessionTypeCounts = computed(() => {
  const counts = {}
  
  props.dayData.venues.forEach(venue => {
    venue.sessions.forEach(session => {
      const type = session.session_type || 'other'
      counts[type] = (counts[type] || 0) + 1
    })
  })
  
  return counts
})

// Methods
const getSessionTypeColor = (sessionType) => {
  const colors = {
    plenary: 'bg-purple-500',
    parallel: 'bg-blue-500',
    workshop: 'bg-green-500',
    poster: 'bg-yellow-500',
    break: 'bg-gray-400',
    lunch: 'bg-orange-500',
    social: 'bg-pink-500',
  }
  return colors[sessionType] || 'bg-gray-500'
}
</script>

<style scoped>
.timeline-day-section {
  position: relative;
}

/* Day header gradient animation */
.day-header {
  position: relative;
  overflow: hidden;
}

.day-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  0% {
    left: -100%;
  }
  100% {
    left: 100%;
  }
}

/* Venues grid responsive behavior */
.venues-grid {
  position: relative;
}

/* Session type indicator animations */
.session-type-indicator {
  transition: all 0.2s ease-in-out;
}

.session-type-indicator:hover {
  transform: scale(1.1);
}

/* Day footer styling */
.day-footer {
  border-top: 2px solid transparent;
  border-image: linear-gradient(to right, transparent, rgba(147, 51, 234, 0.3), transparent) 1;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .day-header .flex {
    flex-direction: column;
    text-align: center;
    space-y: 4px;
  }
  
  .day-header .flex .flex {
    justify-content: center;
  }
}

/* Dark mode adjustments */
.dark .day-footer {
  border-image: linear-gradient(to right, transparent, rgba(147, 51, 234, 0.5), transparent) 1;
}

/* Empty state styling */
.empty-state {
  background: radial-gradient(circle at center, rgba(147, 51, 234, 0.05), transparent);
}

.dark .empty-state {
  background: radial-gradient(circle at center, rgba(147, 51, 234, 0.1), transparent);
}
</style>