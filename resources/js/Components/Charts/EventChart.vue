<!-- resources/js/Components/Charts/EventChart.vue -->
<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          Etkinlik Analizi
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Son {{ timeRange }} günlük veriler
        </p>
      </div>
      
      <!-- Time Range Selector -->
      <div class="flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
        <button
          v-for="range in timeRanges"
          :key="range.value"
          @click="selectedTimeRange = range.value"
          :class="[
            'px-3 py-1 text-sm font-medium rounded-md transition-all duration-200',
            selectedTimeRange === range.value
              ? 'bg-white dark:bg-gray-800 text-blue-600 shadow-sm'
              : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100'
          ]"
        >
          {{ range.label }}
        </button>
      </div>
    </div>

    <!-- Chart Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in chartTabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
            activeTab === tab.key
              ? 'border-blue-500 text-blue-600 dark:text-blue-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
          ]"
        >
          <component :is="tab.icon" class="inline h-4 w-4 mr-2" />
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- Chart Content -->
    <div class="h-80 relative">
      <!-- Overview Chart -->
      <div v-if="activeTab === 'overview'" class="grid grid-cols-2 gap-6 h-full">
        <!-- Status Distribution -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Etkinlik Durumları
          </h4>
          <div class="relative h-40">
            <canvas ref="statusChart"></canvas>
          </div>
          <div class="mt-3 space-y-2">
            <div v-for="status in statusData" :key="status.label" class="flex items-center justify-between text-xs">
              <div class="flex items-center">
                <div :class="['w-3 h-3 rounded-full mr-2', status.color]"></div>
                <span class="text-gray-600 dark:text-gray-400">{{ status.label }}</span>
              </div>
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ status.count }}</span>
            </div>
          </div>
        </div>

        <!-- Monthly Trend -->
        <div class="bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Aylık Trend
          </h4>
          <div class="relative h-40">
            <canvas ref="trendChart"></canvas>
          </div>
          <div class="mt-3 flex justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>Bu ay: {{ currentMonthEvents }} etkinlik</span>
            <span class="text-green-600 dark:text-green-400">+{{ monthlyGrowth }}% artış</span>
          </div>
        </div>
      </div>

      <!-- Timeline Chart -->
      <div v-if="activeTab === 'timeline'" class="h-full">
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg p-4 h-full">
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Etkinlik Zaman Çizelgesi
            </h4>
            <div class="flex space-x-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                <CalendarIcon class="w-3 h-3 mr-1" />
                {{ upcomingEvents }} yaklaşan
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                <PlayIcon class="w-3 h-3 mr-1" />
                {{ ongoingEvents }} devam ediyor
              </span>
            </div>
          </div>
          <div class="relative h-56">
            <canvas ref="timelineChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Statistics Chart -->
      <div v-if="activeTab === 'statistics'" class="h-full">
        <div class="grid grid-cols-3 gap-4 h-full">
          <!-- Sessions per Event -->
          <div class="bg-gradient-to-br from-orange-50 to-red-100 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
              Oturum Dağılımı
            </h4>
            <div class="relative h-32">
              <canvas ref="sessionsChart"></canvas>
            </div>
            <div class="mt-2 text-center">
              <div class="text-lg font-bold text-orange-600 dark:text-orange-400">
                {{ averageSessionsPerEvent.toFixed(1) }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                ortalama oturum/etkinlik
              </div>
            </div>
          </div>

          <!-- Participants -->
          <div class="bg-gradient-to-br from-cyan-50 to-blue-100 dark:from-cyan-900/20 dark:to-blue-900/20 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
              Katılımcı Analizi
            </h4>
            <div class="relative h-32">
              <canvas ref="participantsChart"></canvas>
            </div>
            <div class="mt-2 text-center">
              <div class="text-lg font-bold text-cyan-600 dark:text-cyan-400">
                {{ totalParticipants }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                toplam katılımcı
              </div>
            </div>
          </div>

          <!-- Organizations -->
          <div class="bg-gradient-to-br from-violet-50 to-purple-100 dark:from-violet-900/20 dark:to-purple-900/20 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
              Organizasyon Bazlı
            </h4>
            <div class="relative h-32">
              <canvas ref="organizationsChart"></canvas>
            </div>
            <div class="mt-2 text-center">
              <div class="text-lg font-bold text-violet-600 dark:text-violet-400">
                {{ activeOrganizations }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                aktif organizasyon
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Chart -->
      <div v-if="activeTab === 'performance'" class="h-full">
        <div class="bg-gradient-to-br from-indigo-50 to-blue-100 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-lg p-4 h-full">
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Performans Metrikleri
            </h4>
            <div class="text-xs text-gray-500 dark:text-gray-400">
              Son güncellenme: {{ lastUpdated }}
            </div>
          </div>
          <div class="relative h-56">
            <canvas ref="performanceChart"></canvas>
          </div>
          <div class="mt-4 grid grid-cols-4 gap-4">
            <div class="text-center">
              <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                {{ completionRate }}%
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                Tamamlanma Oranı
              </div>
            </div>
            <div class="text-center">
              <div class="text-lg font-bold text-green-600 dark:text-green-400">
                {{ avgEventDuration }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                Ort. Etkinlik Süresi (gün)
              </div>
            </div>
            <div class="text-center">
              <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
                {{ totalPresentations }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                Toplam Sunum
              </div>
            </div>
            <div class="text-center">
              <div class="text-lg font-bold text-orange-600 dark:text-orange-400">
                {{ sponsorshipRate }}%
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                Sponsorluk Oranı
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-lg flex items-center justify-center">
        <div class="flex items-center space-x-2">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
          <span class="text-sm text-gray-600 dark:text-gray-400">Veriler yükleniyor...</span>
        </div>
      </div>
    </div>

    <!-- Export Actions -->
    <div class="mt-6 flex justify-between items-center">
      <div class="text-xs text-gray-500 dark:text-gray-400">
        {{ events.length }} etkinlik analiz edildi
      </div>
      <div class="flex space-x-2">
        <button
          @click="exportChart"
          class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
        >
          <ArrowDownTrayIcon class="w-3 h-3 mr-1" />
          Dışa Aktar
        </button>
        <button
          @click="refreshData"
          class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
        >
          <ArrowPathIcon class="w-3 h-3 mr-1" />
          Yenile
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import {
  CalendarIcon,
  ChartBarIcon,
  ClockIcon,
  PresentationChartLineIcon,
  PlayIcon,
  ArrowDownTrayIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'

Chart.register(...registerables)

const props = defineProps({
  events: {
    type: Array,
    default: () => []
  },
  organizations: {
    type: Array,
    default: () => []
  },
  participants: {
    type: Array,
    default: () => []
  },
  statistics: {
    type: Object,
    default: () => ({})
  }
})

// Refs for chart canvases
const statusChart = ref(null)
const trendChart = ref(null)
const timelineChart = ref(null)
const sessionsChart = ref(null)
const participantsChart = ref(null)
const organizationsChart = ref(null)
const performanceChart = ref(null)

// Chart instances
const chartInstances = ref({})

// State
const loading = ref(false)
const activeTab = ref('overview')
const selectedTimeRange = ref(30)

const timeRanges = [
  { label: '7G', value: 7 },
  { label: '30G', value: 30 },
  { label: '90G', value: 90 },
  { label: '1Y', value: 365 }
]

const chartTabs = [
  { key: 'overview', label: 'Genel Bakış', icon: ChartBarIcon },
  { key: 'timeline', label: 'Zaman Çizelgesi', icon: ClockIcon },
  { key: 'statistics', label: 'İstatistikler', icon: PresentationChartLineIcon },
  { key: 'performance', label: 'Performans', icon: CalendarIcon }
]

// Computed properties
const timeRange = computed(() => selectedTimeRange.value)

const statusData = computed(() => [
  { label: 'Yayında', count: props.events.filter(e => e.is_published && e.status === 'upcoming').length, color: 'bg-green-500' },
  { label: 'Taslak', count: props.events.filter(e => !e.is_published).length, color: 'bg-yellow-500' },
  { label: 'Devam Ediyor', count: props.events.filter(e => e.status === 'ongoing').length, color: 'bg-blue-500' },
  { label: 'Tamamlandı', count: props.events.filter(e => e.status === 'past').length, color: 'bg-gray-500' }
])

const upcomingEvents = computed(() => props.events.filter(e => e.status === 'upcoming').length)
const ongoingEvents = computed(() => props.events.filter(e => e.status === 'ongoing').length)
const currentMonthEvents = computed(() => {
  const currentMonth = new Date().getMonth()
  return props.events.filter(e => new Date(e.start_date).getMonth() === currentMonth).length
})

const monthlyGrowth = computed(() => {
  // Mock calculation - in real app, calculate based on previous month data
  return Math.floor(Math.random() * 20) + 5
})

const averageSessionsPerEvent = computed(() => {
  const totalSessions = props.events.reduce((sum, event) => sum + (event.total_sessions || 0), 0)
  return props.events.length > 0 ? totalSessions / props.events.length : 0
})

const totalParticipants = computed(() => props.participants.length || 0)
const activeOrganizations = computed(() => props.organizations.filter(o => o.is_active).length || 0)

const completionRate = computed(() => {
  const completed = props.events.filter(e => e.status === 'past').length
  return props.events.length > 0 ? Math.round((completed / props.events.length) * 100) : 0
})

const avgEventDuration = computed(() => {
  const totalDuration = props.events.reduce((sum, event) => sum + (event.duration || 0), 0)
  return props.events.length > 0 ? (totalDuration / props.events.length).toFixed(1) : 0
})

const totalPresentations = computed(() => {
  return props.events.reduce((sum, event) => sum + (event.total_presentations || 0), 0)
})

const sponsorshipRate = computed(() => {
  // Mock calculation - count events with sponsors
  const sponsoredEvents = props.events.filter(e => e.sponsors && e.sponsors.length > 0).length
  return props.events.length > 0 ? Math.round((sponsoredEvents / props.events.length) * 100) : 0
})

const lastUpdated = computed(() => {
  return new Date().toLocaleTimeString('tr-TR', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
})

// Methods
const createStatusChart = () => {
  if (!statusChart.value) return

  const ctx = statusChart.value.getContext('2d')
  
  if (chartInstances.value.status) {
    chartInstances.value.status.destroy()
  }

  chartInstances.value.status = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: statusData.value.map(s => s.label),
      datasets: [{
        data: statusData.value.map(s => s.count),
        backgroundColor: ['#10B981', '#F59E0B', '#3B82F6', '#6B7280'],
        borderWidth: 0,
        cutout: '60%'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      }
    }
  })
}

const createTrendChart = () => {
  if (!trendChart.value) return

  const ctx = trendChart.value.getContext('2d')
  
  if (chartInstances.value.trend) {
    chartInstances.value.trend.destroy()
  }

  // Generate mock monthly data
  const months = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz']
  const data = months.map(() => Math.floor(Math.random() * 20) + 5)

  chartInstances.value.trend = new Chart(ctx, {
    type: 'line',
    data: {
      labels: months,
      datasets: [{
        data: data,
        borderColor: '#10B981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#10B981',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        x: {
          display: false
        },
        y: {
          display: false
        }
      },
      elements: {
        point: {
          hoverRadius: 6
        }
      }
    }
  })
}

const createTimelineChart = () => {
  if (!timelineChart.value) return

  const ctx = timelineChart.value.getContext('2d')
  
  if (chartInstances.value.timeline) {
    chartInstances.value.timeline.destroy()
  }

  // Create timeline data based on events
  const timelineData = props.events.slice(0, 10).map(event => ({
    x: new Date(event.start_date),
    y: Math.random() * 5 + 1,
    event: event
  }))

  chartInstances.value.timeline = new Chart(ctx, {
    type: 'scatter',
    data: {
      datasets: [{
        label: 'Etkinlikler',
        data: timelineData,
        backgroundColor: '#8B5CF6',
        borderColor: '#8B5CF6',
        pointRadius: 6,
        pointHoverRadius: 8
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            title: function(context) {
              return context[0].raw.event.name
            },
            label: function(context) {
              return `Tarih: ${new Date(context.raw.x).toLocaleDateString('tr-TR')}`
            }
          }
        }
      },
      scales: {
        x: {
          type: 'time',
          time: {
            unit: 'day'
          },
          grid: {
            display: false
          }
        },
        y: {
          display: false
        }
      }
    }
  })
}

const createSessionsChart = () => {
  if (!sessionsChart.value) return

  const ctx = sessionsChart.value.getContext('2d')
  
  if (chartInstances.value.sessions) {
    chartInstances.value.sessions.destroy()
  }

  const sessionData = props.events.map(e => e.total_sessions || 0).slice(0, 6)

  chartInstances.value.sessions = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.events.slice(0, 6).map(e => e.name.substring(0, 10) + '...'),
      datasets: [{
        data: sessionData,
        backgroundColor: '#F97316',
        borderRadius: 4,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        x: {
          display: false
        },
        y: {
          display: false
        }
      }
    }
  })
}

const createParticipantsChart = () => {
  if (!participantsChart.value) return

  const ctx = participantsChart.value.getContext('2d')
  
  if (chartInstances.value.participants) {
    chartInstances.value.participants.destroy()
  }

  const speakerCount = props.participants.filter(p => p.is_speaker).length
  const moderatorCount = props.participants.filter(p => p.is_moderator).length
  const bothCount = props.participants.filter(p => p.is_speaker && p.is_moderator).length

  chartInstances.value.participants = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Konuşmacı', 'Moderatör', 'Her İkisi'],
      datasets: [{
        data: [speakerCount - bothCount, moderatorCount - bothCount, bothCount],
        backgroundColor: ['#06B6D4', '#8B5CF6', '#10B981'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      }
    }
  })
}

const createOrganizationsChart = () => {
  if (!organizationsChart.value) return

  const ctx = organizationsChart.value.getContext('2d')
  
  if (chartInstances.value.organizations) {
    chartInstances.value.organizations.destroy()
  }

  const orgData = props.organizations.slice(0, 5).map(org => ({
    name: org.name,
    events: props.events.filter(e => e.organization_id === org.id).length
  }))

  chartInstances.value.organizations = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
      labels: orgData.map(o => o.name.substring(0, 8) + '...'),
      datasets: [{
        data: orgData.map(o => o.events),
        backgroundColor: '#8B5CF6',
        borderRadius: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        x: {
          display: false
        },
        y: {
          display: false
        }
      }
    }
  })
}

const createPerformanceChart = () => {
  if (!performanceChart.value) return

  const ctx = performanceChart.value.getContext('2d')
  
  if (chartInstances.value.performance) {
    chartInstances.value.performance.destroy()
  }

  const weeks = ['1. Hafta', '2. Hafta', '3. Hafta', '4. Hafta']
  const eventsData = weeks.map(() => Math.floor(Math.random() * 10) + 2)
  const sessionsData = weeks.map(() => Math.floor(Math.random() * 50) + 20)

  chartInstances.value.performance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: weeks,
      datasets: [
        {
          label: 'Etkinlikler',
          data: eventsData,
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          yAxisID: 'y'
        },
        {
          label: 'Oturumlar',
          data: sessionsData,
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false
      },
      plugins: {
        legend: {
          position: 'top',
          labels: {
            usePointStyle: true,
            padding: 20
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          }
        },
        y: {
          type: 'linear',
          display: true,
          position: 'left',
          grid: {
            color: 'rgba(0, 0, 0, 0.1)'
          }
        },
        y1: {
          type: 'linear',
          display: true,
          position: 'right',
          grid: {
            drawOnChartArea: false
          }
        }
      }
    }
  })
}

const initializeCharts = async () => {
  await nextTick()
  
  if (activeTab.value === 'overview') {
    createStatusChart()
    createTrendChart()
  } else if (activeTab.value === 'timeline') {
    createTimelineChart()
  } else if (activeTab.value === 'statistics') {
    createSessionsChart()
    createParticipantsChart()
    createOrganizationsChart()
  } else if (activeTab.value === 'performance') {
    createPerformanceChart()
  }
}

const exportChart = () => {
  // Implementation for chart export
  console.log('Exporting chart...')
}

const refreshData = () => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
    initializeCharts()
  }, 1000)
}

// Watchers
watch(activeTab, () => {
  nextTick(() => {
    initializeCharts()
  })
})

watch(selectedTimeRange, () => {
  refreshData()
})

// Lifecycle
onMounted(() => {
  initializeCharts()
})
</script>

<style scoped>
canvas {
  max-height: 100%;
}

.chart-container {
  position: relative;
  height: 200px;
}
</style>