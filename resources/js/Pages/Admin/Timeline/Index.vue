<!-- resources/js/Pages/Admin/Timeline/Index.vue -->
<template>
  <AdminLayout :page-title="`${event.name} - Timeline Görünümü`"
    page-subtitle="Etkinlik programının zaman çizelgesi görünümü" :breadcrumbs="breadcrumbs">

    <Head :title="`${event.name} - Timeline`" />

    <!-- Header Section -->
    <div class="mb-6">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <!-- Title & Stats -->
            <div class="flex items-center space-x-4">
              <div class="h-12 w-12 bg-purple-600 rounded-xl flex items-center justify-center">
                <ClockIcon class="h-8 w-8 text-white" />
              </div>
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ event.name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Timeline Görünümü</p>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-3">
              <!-- Filters Toggle -->
              <button @click="showFilters = !showFilters"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <FunnelIcon class="h-4 w-4 mr-2" />
                Filtreler
              </button>

              <!-- Export Dropdown -->
              <div class="relative">
                <button @click="showExportMenu = !showExportMenu"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                  <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                  Dışa Aktar
                  <ChevronDownIcon class="h-4 w-4 ml-1" />
                </button>

                <!-- Export Menu -->
                <div v-show="showExportMenu"
                  class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                  <div class="py-1">
                    <button @click="exportTimeline('pdf')"
                      class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                      PDF olarak dışa aktar
                    </button>
                    <button @click="exportTimeline('excel')"
                      class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                      Excel olarak dışa aktar
                    </button>
                    <button @click="exportTimeline('json')"
                      class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                      JSON olarak dışa aktar
                    </button>
                  </div>
                </div>
              </div>

              <!-- Edit Mode Toggle -->
              <Link v-if="event.can_edit" :href="route('admin.timeline.edit', event.slug)"
                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors">
              <PencilSquareIcon class="h-4 w-4 mr-2" />
              Düzenle
              </Link>
            </div>
          </div>
        </div>

        <!-- Stats Bar -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800">
          <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.total_days }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Gün</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total_venues }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Salon</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.total_sessions }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Oturum</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.total_presentations }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Sunum</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.total_duration_hours }}s</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Toplam Süre</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Panel -->
    <div v-show="showFilters" class="mb-6">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Day Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gün</label>
            <select v-model="activeFilters.day_id" @change="applyFilters"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
              <option value="">Tüm günler</option>
              <option v-for="day in filters.days" :key="day.value" :value="day.value">
                {{ day.label }}
              </option>
            </select>
          </div>

          <!-- Venue Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Salon</label>
            <select v-model="activeFilters.venue_id" @change="applyFilters"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
              <option value="">Tüm salonlar</option>
              <option v-for="venue in filters.venues" :key="venue.value" :value="venue.value">
                {{ venue.label }}
              </option>
            </select>
          </div>

          <!-- Session Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Oturum Tipi</label>
            <select v-model="activeFilters.session_type" @change="applyFilters"
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
              <option value="">Tüm tipler</option>
              <option v-for="type in filters.session_types" :key="type.value" :value="type.value">
                {{ type.label }}
              </option>
            </select>
          </div>

          <!-- Clear Filters -->
          <div class="flex items-end">
            <button @click="clearFilters"
              class="w-full px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
              Filtreleri Temizle
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Timeline Content -->
    <div class="space-y-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
        <span class="ml-3 text-gray-500 dark:text-gray-400">Timeline yükleniyor...</span>
      </div>

      <!-- Timeline Component -->
      <TimelineContainer v-else :timeline-data="filteredTimelineData" :event="event" :stats="stats"
        @session-click="handleSessionClick" @presentation-click="handlePresentationClick" />

      <!-- Empty State -->
      <div v-if="!loading && filteredTimelineData.length === 0" class="text-center py-12">
        <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
          <ClockIcon class="h-full w-full" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Program bulunamadı</h3>
        <p class="text-gray-500 dark:text-gray-400">Seçili filtreler için program bulunamadı.</p>
        <button @click="clearFilters"
          class="mt-4 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">
          Filtreleri Temizle
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TimelineContainer from '@/Components/Timeline/TimelineContainer.vue'
import {
  ClockIcon,
  PencilSquareIcon,
  DocumentArrowDownIcon,
  FunnelIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  event: {
    type: Object,
    required: true
  },
  timelineData: {
    type: Array,
    required: true
  },
  stats: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    required: true
  }
})

// Reactive data
const loading = ref(false)
const showFilters = ref(false)
const showExportMenu = ref(false)
const activeFilters = ref({
  day_id: '',
  venue_id: '',
  category_id: '',
  session_type: ''
})

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Etkinlikler', href: route('admin.events.index') },
  { label: props.event.name, href: route('admin.events.show', props.event.slug) },
  { label: 'Timeline', href: null }
])

const filteredTimelineData = ref(props.timelineData)

// Methods
const applyFilters = async () => {
  loading.value = true

  try {
    const response = await fetch(route('admin.timeline.data', props.event.slug), {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(activeFilters.value)
    })

    const data = await response.json()

    if (data.success) {
      filteredTimelineData.value = data.data
    }
  } catch (error) {
    console.error('Filter error:', error)
  } finally {
    loading.value = false
  }
}

const clearFilters = () => {
  activeFilters.value = {
    day_id: '',
    venue_id: '',
    category_id: '',
    session_type: ''
  }
  filteredTimelineData.value = props.timelineData
}

const exportTimeline = (format) => {
  showExportMenu.value = false
  
  if (format === 'pdf') {
    window.location.href = route('admin.export.events.program-pdf', props.event.slug)
  } else if (format === 'excel') {
    window.location.href = route('admin.export.events.program-excel', props.event.slug)
  } else {
    // JSON export için Inertia router kullan
    router.post(route('admin.timeline.export', props.event.slug), {
      format: format,
      filters: activeFilters.value
    }, {
      onSuccess: () => {
        console.log('JSON export başarılı')
      },
      onError: (errors) => {
        console.error('Export error:', errors)
      }
    })
  }
}


const handleSessionClick = (session) => {
  router.visit(route('admin.program-sessions.show', session.id))
}

const handlePresentationClick = (presentation) => {
  router.visit(route('admin.presentations.show', presentation.id))
}

// Close dropdowns when clicking outside
const closeDropdowns = (event) => {
  if (!event.target.closest('.relative')) {
    showExportMenu.value = false
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', closeDropdowns)
})
</script>

<style scoped>
/* Custom timeline styling */
.timeline-container {
  position: relative;
}

/* Smooth transitions */
.timeline-item {
  transition: all 0.2s ease-in-out;
}

.timeline-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Filter panel animations */
.filters-enter-active,
.filters-leave-active {
  transition: all 0.3s ease;
}

.filters-enter-from,
.filters-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>