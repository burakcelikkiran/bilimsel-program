<!-- resources/js/Components/Charts/ParticipantChart.vue -->
<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          Katılımcı Analizi
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ totalParticipants }} toplam katılımcı
        </p>
      </div>
      
      <!-- Filter Options -->
      <div class="flex items-center space-x-3">
        <select
          v-model="selectedOrganization"
          class="text-sm border border-gray-300 rounded-md px-3 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
          @change="updateCharts"
        >
          <option value="">Tüm Organizasyonlar</option>
          <option
            v-for="org in organizations"
            :key="org.id"
            :value="org.id"
          >
            {{ org.name }}
          </option>
        </select>
        
        <div class="flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
          <button
            v-for="view in viewModes"
            :key="view.value"
            @click="selectedView = view.value"
            :class="[
              'px-3 py-1 text-sm font-medium rounded-md transition-all duration-200',
              selectedView === view.value
                ? 'bg-white dark:bg-gray-800 text-blue-600 shadow-sm'
                : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100'
            ]"
          >
            {{ view.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Chart Content -->
    <div class="grid grid-cols-12 gap-6">
      <!-- Main Chart Area -->
      <div class="col-span-8">
        <!-- Role Distribution Chart -->
        <div v-if="selectedView === 'roles'" class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 h-80">
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Rol Dağılımı
            </h4>
            <div class="flex space-x-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                <UserIcon class="w-3 h-3 mr-1" />
                {{ speakersCount }} konuşmacı
              </span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                <MicrophoneIcon class="w-3 h-3 mr-1" />
                {{ moderatorsCount }} moderatör
              </span>
            </div>
          </div>
          <div class="relative h-60">
            <canvas ref="rolesChart"></canvas>
          </div>
        </div>

        <!-- Participation Activity Chart -->
        <div v-if="selectedView === 'activity'" class="bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4 h-80">
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Katılım Aktivitesi
            </h4>
            <div class="text-xs text-gray-500 dark:text-gray-400">
              Son 30 gün
            </div>
          </div>
          <div class="relative h-60">
            <canvas ref="activityChart"></canvas>
          </div>
        </div>

        <!-- Affiliations Chart -->
        <div v-if="selectedView === 'affiliations'" class="bg-gradient-to-br from-purple-50 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg p-4 h-80">
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Kurum Dağılımı
            </h4>
            <div class="text-xs text-gray-500 dark:text-gray-400">
              En aktif {{ topAffiliations.length }} kurum
            </div>
          </div>
          <div class="relative h-60">
            <canvas ref="affiliationsChart"></canvas>
          </div>
        </div>

        <!-- Engagement Metrics -->
        <div v-if="selectedView === 'engagement'" class="bg-gradient-to-br from-orange-50 to-red-100 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg p-4 h-80">
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Katılım Metrikleri
            </h4>
            <div class="flex space-x-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                Ortalama: {{ averageParticipationsPerPerson.toFixed(1) }}
              </span>
            </div>
          </div>
          <div class="relative h-60">
            <canvas ref="engagementChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Statistics Sidebar -->
      <div class="col-span-4 space-y-4">
        <!-- Quick Stats -->
        <div class="bg-gradient-to-br from-cyan-50 to-blue-100 dark:from-cyan-900/20 dark:to-blue-900/20 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Hızlı İstatistikler
          </h4>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-600 dark:text-gray-400">Toplam Katılımcı:</span>
              <span class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ totalParticipants }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-600 dark:text-gray-400">Konuşmacılar:</span>
              <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ speakersCount }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-600 dark:text-gray-400">Moderatörler:</span>
              <span class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ moderatorsCount }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-600 dark:text-gray-400">Her İkisi:</span>
              <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ bothRolesCount }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-600 dark:text-gray-400">Fotoğrafı Olan:</span>
              <span class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ withPhotoCount }}</span>
            </div>
          </div>
        </div>

        <!-- Top Participants -->
        <div class="bg-gradient-to-br from-violet-50 to-purple-100 dark:from-violet-900/20 dark:to-purple-900/20 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            En Aktif Katılımcılar
          </h4>
          <div class="space-y-3">
            <div
              v-for="(participant, index) in topParticipants"
              :key="participant.id"
              class="flex items-center space-x-3"
            >
              <div class="flex-shrink-0">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white',
                    index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-amber-600' : 'bg-gray-300'
                  ]"
                >
                  {{ index + 1 }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">
                  {{ participant.full_name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {{ participant.affiliation }}
                </p>
              </div>
              <div class="text-xs font-bold text-violet-600 dark:text-violet-400">
                {{ participant.total_participations }}
              </div>
            </div>
          </div>
        </div>

        <!-- Top Affiliations -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-100 dark:from-emerald-900/20 dark:to-green-900/20 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            En Aktif Kurumlar
          </h4>
          <div class="space-y-2">
            <div
              v-for="affiliation in topAffiliations.slice(0, 5)"
              :key="affiliation.name"
              class="flex justify-between items-center"
            >
              <span class="text-xs text-gray-600 dark:text-gray-400 truncate flex-1 mr-2">
                {{ affiliation.name }}
              </span>
              <div class="flex items-center space-x-2">
                <div class="w-12 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                  <div
                    class="bg-emerald-500 h-1.5 rounded-full"
                    :style="{ width: `${(affiliation.count / topAffiliations[0].count) * 100}%` }"
                  ></div>
                </div>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 w-6">
                  {{ affiliation.count }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-gradient-to-br from-rose-50 to-pink-100 dark:from-rose-900/20 dark:to-pink-900/20 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Son Aktivite
          </h4>
          <div class="space-y-2">
            <div
              v-for="activity in recentActivities"
              :key="activity.id"
              class="flex items-start space-x-2"
            >
              <div class="flex-shrink-0 w-2 h-2 bg-rose-400 rounded-full mt-1.5"></div>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                  <span class="font-medium text-gray-900 dark:text-gray-100">
                    {{ activity.participant.full_name }}
                  </span>
                  {{ activity.type === 'speaker' ? 'konuşmacı olarak eklendi' : 'moderatör olarak atandı' }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500">
                  {{ formatDate(activity.created_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Export Actions -->
    <div class="mt-6 flex justify-between items-center">
      <div class="text-xs text-gray-500 dark:text-gray-400">
        Son güncellenme: {{ lastUpdated }}
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

    <!-- Loading Overlay -->
    <div v-if="loading" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-lg flex items-center justify-center">
      <div class="flex items-center space-x-2">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-sm text-gray-600 dark:text-gray-400">Veriler yükleniyor...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import {
  UserIcon,
  MicrophoneIcon,
  ArrowDownTrayIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'

Chart.register(...registerables)

const props = defineProps({
  participants: {
    type: Array,
    default: () => []
  },
  organizations: {
    type: Array,
    default: () => []
  },
  events: {
    type: Array,
    default: () => []
  }
})

// Refs for chart canvases
const rolesChart = ref(null)
const activityChart = ref(null)
const affiliationsChart = ref(null)
const engagementChart = ref(null)

// Chart instances
const chartInstances = ref({})

// State
const loading = ref(false)
const selectedView = ref('roles')
const selectedOrganization = ref('')

const viewModes = [
  { label: 'Roller', value: 'roles' },
  { label: 'Aktivite', value: 'activity' },
  { label: 'Kurumlar', value: 'affiliations' },
  { label: 'Katılım', value: 'engagement' }
]

// Computed properties
const filteredParticipants = computed(() => {
  if (!selectedOrganization.value) return props.participants
  return props.participants.filter(p => p.organization_id === selectedOrganization.value)
})

const totalParticipants = computed(() => filteredParticipants.value.length)

const speakersCount = computed(() => 
  filteredParticipants.value.filter(p => p.is_speaker).length
)

const moderatorsCount = computed(() => 
  filteredParticipants.value.filter(p => p.is_moderator).length
)

const bothRolesCount = computed(() => 
  filteredParticipants.value.filter(p => p.is_speaker && p.is_moderator).length
)

const withPhotoCount = computed(() => 
  filteredParticipants.value.filter(p => p.has_photo).length
)

const topParticipants = computed(() => {
  return filteredParticipants.value
    .map(p => ({
      ...p,
      total_participations: (p.total_sessions || 0) + (p.total_presentations || 0)
    }))
    .filter(p => p.total_participations > 0)
    .sort((a, b) => b.total_participations - a.total_participations)
    .slice(0, 5)
})

const topAffiliations = computed(() => {
  const affiliationMap = {}
  
  filteredParticipants.value.forEach(p => {
    if (p.affiliation) {
      affiliationMap[p.affiliation] = (affiliationMap[p.affiliation] || 0) + 1
    }
  })
  
  return Object.entries(affiliationMap)
    .map(([name, count]) => ({ name, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 10)
})

const averageParticipationsPerPerson = computed(() => {
  const totalParticipations = filteredParticipants.value.reduce((sum, p) => 
    sum + (p.total_sessions || 0) + (p.total_presentations || 0), 0
  )
  return totalParticipants.value > 0 ? totalParticipations / totalParticipants.value : 0
})

const recentActivities = computed(() => {
  // Mock recent activities - in real app, this would come from actual data
  return filteredParticipants.value
    .slice(0, 5)
    .map(p => ({
      id: p.id,
      participant: p,
      type: p.is_speaker ? 'speaker' : 'moderator',
      created_at: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000)
    }))
    .sort((a, b) => b.created_at - a.created_at)
})

const lastUpdated = computed(() => {
  return new Date().toLocaleTimeString('tr-TR', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
})

// Methods
const formatDate = (date) => {
  const now = new Date()
  const diff = now - new Date(date)
  const hours = Math.floor(diff / (1000 * 60 * 60))
  
  if (hours < 1) return 'Az önce'
  if (hours < 24) return `${hours} saat önce`
  
  const days = Math.floor(hours / 24)
  return `${days} gün önce`
}

const createRolesChart = () => {
  if (!rolesChart.value) return

  const ctx = rolesChart.value.getContext('2d')
  
  if (chartInstances.value.roles) {
    chartInstances.value.roles.destroy()
  }

  const speakersOnly = speakersCount.value - bothRolesCount.value
  const moderatorsOnly = moderatorsCount.value - bothRolesCount.value
  const both = bothRolesCount.value
  const noRole = totalParticipants.value - speakersCount.value - moderatorsOnly

  chartInstances.value.roles = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Sadece Konuşmacı', 'Sadece Moderatör', 'Her İkisi', 'Katılımcı'],
      datasets: [{
        data: [speakersOnly, moderatorsOnly, both, noRole],
        backgroundColor: ['#10B981', '#8B5CF6', '#F59E0B', '#6B7280'],
        borderWidth: 0,
        cutout: '60%'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            usePointStyle: true,
            padding: 20,
            font: {
              size: 11
            }
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0)
              const percentage = ((context.parsed / total) * 100).toFixed(1)
              return `${context.label}: ${context.parsed} (${percentage}%)`
            }
          }
        }
      }
    }
  })
}

const createActivityChart = () => {
  if (!activityChart.value) return

  const ctx = activityChart.value.getContext('2d')
  
  if (chartInstances.value.activity) {
    chartInstances.value.activity.destroy()
  }

  // Generate mock activity data for last 30 days
  const days = Array.from({ length: 30 }, (_, i) => {
    const date = new Date()
    date.setDate(date.getDate() - (29 - i))
    return date.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' })
  })

  const speakerData = days.map(() => Math.floor(Math.random() * 5))
  const moderatorData = days.map(() => Math.floor(Math.random() * 3))

  chartInstances.value.activity = new Chart(ctx, {
    type: 'line',
    data: {
      labels: days,
      datasets: [
        {
          label: 'Yeni Konuşmacılar',
          data: speakerData,
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4
        },
        {
          label: 'Yeni Moderatörler',
          data: moderatorData,
          borderColor: '#8B5CF6',
          backgroundColor: 'rgba(139, 92, 246, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
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
          },
          ticks: {
            maxTicksLimit: 7
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.1)'
          }
        }
      }
    }
  })
}

const createAffiliationsChart = () => {
  if (!affiliationsChart.value) return

  const ctx = affiliationsChart.value.getContext('2d')
  
  if (chartInstances.value.affiliations) {
    chartInstances.value.affiliations.destroy()
  }

  chartInstances.value.affiliations = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: topAffiliations.value.map(a => 
        a.name.length > 20 ? a.name.substring(0, 20) + '...' : a.name
      ),
      datasets: [{
        label: 'Katılımcı Sayısı',
        data: topAffiliations.value.map(a => a.count),
        backgroundColor: '#8B5CF6',
        borderRadius: 6,
        borderSkipped: false
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
          beginAtZero: true,
          grid: {
            display: false
          }
        },
        y: {
          grid: {
            display: false
          },
          ticks: {
            font: {
              size: 10
            }
          }
        }
      }
    }
  })
}

const createEngagementChart = () => {
  if (!engagementChart.value) return

  const ctx = engagementChart.value.getContext('2d')
  
  if (chartInstances.value.engagement) {
    chartInstances.value.engagement.destroy()
  }

  // Create engagement buckets
  const engagementBuckets = {
    '0': 0,
    '1-2': 0,
    '3-5': 0,
    '6-10': 0,
    '10+': 0
  }

  filteredParticipants.value.forEach(p => {
    const total = (p.total_sessions || 0) + (p.total_presentations || 0)
    if (total === 0) engagementBuckets['0']++
    else if (total <= 2) engagementBuckets['1-2']++
    else if (total <= 5) engagementBuckets['3-5']++
    else if (total <= 10) engagementBuckets['6-10']++
    else engagementBuckets['10+']++
  })

  chartInstances.value.engagement = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: Object.keys(engagementBuckets),
      datasets: [{
        label: 'Katılımcı Sayısı',
        data: Object.values(engagementBuckets),
        backgroundColor: [
          '#EF4444',
          '#F59E0B', 
          '#10B981',
          '#3B82F6',
          '#8B5CF6'
        ],
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
        },
        tooltip: {
          callbacks: {
            title: function(context) {
              return `${context[0].label} katılım`
            },
            label: function(context) {
              return `${context.parsed.y} katılımcı`
            }
          }
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: 'Katılım Sayısı'
          },
          grid: {
            display: false
          }
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Katılımcı Sayısı'
          }
        }
      }
    }
  })
}

const updateCharts = async () => {
  await nextTick()
  
  if (selectedView.value === 'roles') {
    createRolesChart()
  } else if (selectedView.value === 'activity') {
    createActivityChart()
  } else if (selectedView.value === 'affiliations') {
    createAffiliationsChart()
  } else if (selectedView.value === 'engagement') {
    createEngagementChart()
  }
}

const exportChart = () => {
  console.log('Exporting participant chart...')
}

const refreshData = () => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
    updateCharts()
  }, 1000)
}

// Watchers
watch(selectedView, () => {
  updateCharts()
})

watch(selectedOrganization, () => {
  updateCharts()
})

// Lifecycle
onMounted(() => {
  updateCharts()
})
</script>

<style scoped>
canvas {
  max-height: 100%;
}
</style>