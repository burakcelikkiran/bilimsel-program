<!-- resources/js/Components/Timeline/TimelineContainer.vue -->
<template>
  <div class="timeline-container">
    <!-- Timeline Header with Time Ruler -->
    <div class="timeline-header mb-6">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Time Scale Header -->
        <div class="timeline-time-scale px-6 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between text-sm font-medium text-gray-700 dark:text-gray-300">
            <span>Zaman Ölçeği</span>
            <div class="flex items-center space-x-4">
              <button
                @click="timeScale = 'hour'"
                :class="timeScale === 'hour' ? 'bg-purple-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                class="px-3 py-1 rounded-lg text-xs font-medium transition-colors"
              >
                Saatlik
              </button>
              <button
                @click="timeScale = 'session'"
                :class="timeScale === 'session' ? 'bg-purple-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                class="px-3 py-1 rounded-lg text-xs font-medium transition-colors"
              >
                Oturum
              </button>
            </div>
          </div>
        </div>

        <!-- Time Ruler -->
        <div v-if="timeScale === 'hour'" class="timeline-ruler px-6 py-2 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400 overflow-x-auto">
            <div v-for="hour in timeHours" :key="hour" class="flex-shrink-0 text-center min-w-[60px]">
              <div class="font-medium">{{ hour }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Timeline Days -->
    <div class="timeline-days space-y-8">
      <TimelineDaySection
        v-for="dayData in timelineData"
        :key="dayData.id"
        :day-data="dayData"
        :event="event"
        :time-scale="timeScale"
        :time-hours="timeHours"
        @session-click="$emit('session-click', $event)"
        @presentation-click="$emit('presentation-click', $event)"
      />
    </div>

    <!-- Timeline Legend -->
    <div class="timeline-legend mt-8">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Açıklamalar</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Session Types Legend -->
          <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Oturum Tipleri</h4>
            <div class="space-y-2">
              <div v-for="type in sessionTypeLegend" :key="type.value" class="flex items-center space-x-2">
                <div :class="type.colorClass" class="w-4 h-4 rounded"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ type.label }}</span>
              </div>
            </div>
          </div>

          <!-- Venue Colors Legend -->
          <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Salon Renkleri</h4>
            <div class="space-y-2">
              <div v-for="venue in venueColors" :key="venue.id" class="flex items-center space-x-2">
                <div 
                  :style="{ backgroundColor: venue.color }" 
                  class="w-4 h-4 rounded"
                ></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ venue.name }}</span>
              </div>
            </div>
          </div>

          <!-- Statistics -->
          <div>
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">İstatistikler</h4>
            <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
              <div>Toplam Gün: {{ stats.total_days }}</div>
              <div>Toplam Salon: {{ stats.total_venues }}</div>
              <div>Toplam Oturum: {{ stats.total_sessions }}</div>
              <div>Toplam Sunum: {{ stats.total_presentations }}</div>
              <div>Ortalama Oturum/Gün: {{ stats.average_sessions_per_day }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import TimelineDaySection from './TimelineDaySection.vue'

// Props
const props = defineProps({
  timelineData: {
    type: Array,
    required: true
  },
  event: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['session-click', 'presentation-click'])

// Reactive data
const timeScale = ref('session') // 'hour' or 'session'

// Computed
const timeHours = computed(() => {
  // Generate hour labels from 08:00 to 18:00
  const hours = []
  for (let hour = 8; hour <= 18; hour++) {
    hours.push(`${hour.toString().padStart(2, '0')}:00`)
  }
  return hours
})

const venueColors = computed(() => {
  const venues = []
  const seenVenues = new Set()
  
  props.timelineData.forEach(day => {
    day.venues.forEach(venue => {
      if (!seenVenues.has(venue.id)) {
        venues.push({
          id: venue.id,
          name: venue.display_name || venue.name,
          color: venue.color || '#3B82F6'
        })
        seenVenues.add(venue.id)
      }
    })
  })
  
  return venues
})

const sessionTypeLegend = computed(() => [
  { value: 'plenary', label: 'Genel Oturum', colorClass: 'bg-purple-500' },
  { value: 'parallel', label: 'Paralel Oturum', colorClass: 'bg-blue-500' },
  { value: 'workshop', label: 'Workshop', colorClass: 'bg-green-500' },
  { value: 'poster', label: 'Poster', colorClass: 'bg-yellow-500' },
  { value: 'break', label: 'Ara', colorClass: 'bg-gray-400' },
  { value: 'lunch', label: 'Öğle Arası', colorClass: 'bg-orange-500' },
  { value: 'social', label: 'Sosyal', colorClass: 'bg-pink-500' },
])

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

// Lifecycle
onMounted(() => {
  console.log('TimelineContainer mounted with data:', props.timelineData)
})
</script>

<style scoped>
.timeline-container {
  position: relative;
  width: 100%;
}

.timeline-ruler {
  position: relative;
  background: linear-gradient(to right, transparent 0%, transparent 100%);
}

.timeline-ruler::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(to right, 
    transparent 0%, 
    rgba(156, 163, 175, 0.3) 25%, 
    rgba(156, 163, 175, 0.5) 50%, 
    rgba(156, 163, 175, 0.3) 75%, 
    transparent 100%
  );
}

/* Time scale grid */
.timeline-hour-grid {
  background-image: repeating-linear-gradient(
    to right,
    rgba(156, 163, 175, 0.1) 0px,
    rgba(156, 163, 175, 0.1) 1px,
    transparent 1px,
    transparent 60px
  );
}

/* Session type colors */
.session-plenary {
  @apply bg-purple-100 border-purple-300 dark:bg-purple-900 dark:border-purple-700;
}

.session-parallel {
  @apply bg-blue-100 border-blue-300 dark:bg-blue-900 dark:border-blue-700;
}

.session-workshop {
  @apply bg-green-100 border-green-300 dark:bg-green-900 dark:border-green-700;
}

.session-poster {
  @apply bg-yellow-100 border-yellow-300 dark:bg-yellow-900 dark:border-yellow-700;
}

.session-break {
  @apply bg-gray-100 border-gray-300 dark:bg-gray-800 dark:border-gray-600;
}

.session-lunch {
  @apply bg-orange-100 border-orange-300 dark:bg-orange-900 dark:border-orange-700;
}

.session-social {
  @apply bg-pink-100 border-pink-300 dark:bg-pink-900 dark:border-pink-700;
}

/* Responsive design */
@media (max-width: 768px) {
  .timeline-ruler {
    overflow-x: auto;
  }
  
  .timeline-days {
    overflow-x: auto;
  }
}

/* Smooth animations */
.timeline-item {
  transition: all 0.2s ease-in-out;
}

.timeline-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Dark mode adjustments */
.dark .timeline-ruler::after {
  background: linear-gradient(to right, 
    transparent 0%, 
    rgba(75, 85, 99, 0.3) 25%, 
    rgba(75, 85, 99, 0.5) 50%, 
    rgba(75, 85, 99, 0.3) 75%, 
    transparent 100%
  );
}

.dark .timeline-hour-grid {
  background-image: repeating-linear-gradient(
    to right,
    rgba(75, 85, 99, 0.1) 0px,
    rgba(75, 85, 99, 0.1) 1px,
    transparent 1px,
    transparent 60px
  );
}
</style>