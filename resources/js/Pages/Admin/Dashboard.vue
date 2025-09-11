<!-- resources/js/Pages/Admin/Dashboard.vue -->
<template>
  <AdminLayout
    page-title="Dashboard"
    page-subtitle="Etkinlik yönetimi sisteminize genel bakış"
    :breadcrumbs="[]"
  >
    <Head title="Dashboard" />

    <!-- Welcome Banner -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-700 via-slate-800 to-slate-900 rounded-2xl p-8 mb-8">
      <div class="relative z-10">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">
              Hoş geldiniz, {{ currentUser?.name }}! 👋
            </h1>
            <p class="text-slate-300 text-lg">
              Bugün {{ new Date().toLocaleDateString('tr-TR') }} itibariyle sistemde 
              <span class="font-semibold text-white">{{ statistics.total_events }}</span> etkinlik bulunmaktadır.
            </p>
          </div>
          <div class="hidden lg:block">
            <div class="w-32 h-32 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm">
              <ChartBarIcon class="w-16 h-16 text-white/80" />
            </div>
          </div>
        </div>
      </div>
      <!-- Background Pattern -->
      <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
      <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/5 rounded-full"></div>
      <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-white/5 rounded-full"></div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Events -->
      <div class="group relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-xl hover:shadow-slate-500/10 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Toplam Etkinlik</p>
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ statistics.total_events }}</p>
            <div class="flex items-center mt-2">
              <ArrowUpIcon class="w-4 h-4 text-slate-500 mr-1" />
              <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">+12%</span>
              <span class="text-sm text-slate-500 dark:text-slate-400 ml-1">son aydan</span>
            </div>
          </div>
          <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <CalendarIcon class="w-6 h-6 text-slate-600 dark:text-slate-400" />
          </div>
        </div>
        <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-slate-400 to-slate-600"></div>
      </div>

      <!-- Active Organizations -->
      <div class="group relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-xl hover:shadow-slate-500/10 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Aktif Organizasyon</p>
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ statistics.total_organizations }}</p>
            <div class="flex items-center mt-2">
              <ArrowUpIcon class="w-4 h-4 text-slate-500 mr-1" />
              <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">+5%</span>
              <span class="text-sm text-slate-500 dark:text-slate-400 ml-1">son aydan</span>
            </div>
          </div>
          <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <BuildingOfficeIcon class="w-6 h-6 text-slate-600 dark:text-slate-400" />
          </div>
        </div>
        <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-slate-400 to-slate-600"></div>
      </div>

      <!-- Total Participants -->
      <div class="group relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-xl hover:shadow-slate-500/10 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Toplam Katılımcı</p>
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ statistics.total_participants?.toLocaleString() }}</p>
            <div class="flex items-center mt-2">
              <ArrowUpIcon class="w-4 h-4 text-slate-500 mr-1" />
              <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">+24%</span>
              <span class="text-sm text-slate-500 dark:text-slate-400 ml-1">son aydan</span>
            </div>
          </div>
          <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <UsersIcon class="w-6 h-6 text-slate-600 dark:text-slate-400" />
          </div>
        </div>
        <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-slate-400 to-slate-600"></div>
      </div>

      <!-- Pending Sessions -->
      <div class="group relative overflow-hidden bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-xl hover:shadow-slate-500/10 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Bekleyen Oturum</p>
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ statistics.pending_sessions }}</p>
            <div class="flex items-center mt-2">
              <ArrowDownIcon class="w-4 h-4 text-slate-500 mr-1" />
              <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">-8%</span>
              <span class="text-sm text-slate-500 dark:text-slate-400 ml-1">son aydan</span>
            </div>
          </div>
          <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <ClockIcon class="w-6 h-6 text-slate-600 dark:text-slate-400" />
          </div>
        </div>
        <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-slate-400 to-slate-600"></div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
      <!-- Recent Events -->
      <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Son Etkinlikler</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">En son oluşturulan etkinlikler</p>
            </div>
            <Link
              :href="route('admin.events.index')"
              class="inline-flex items-center px-4 py-2 bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors"
            >
              <span class="text-sm font-medium">Tümünü Gör</span>
              <ArrowRightIcon class="w-4 h-4 ml-2" />
            </Link>
          </div>
        </div>
        
        <div class="p-6">
          <div v-if="recentEvents.length === 0" class="text-center py-12">
            <CalendarIcon class="mx-auto h-16 w-16 text-slate-300 dark:text-slate-600 mb-4" />
            <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">Henüz etkinlik yok</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6">İlk etkinliğinizi oluşturarak başlayın.</p>
            <Link
              :href="route('admin.events.create')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-slate-600 to-slate-700 text-white rounded-xl hover:from-slate-700 hover:to-slate-800 transition-all duration-200 shadow-lg hover:shadow-xl"
            >
              <PlusIcon class="w-5 h-5 mr-2" />
              Yeni Etkinlik Oluştur
            </Link>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="event in recentEvents"
              :key="event.id"
              class="group flex items-center p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all duration-200 hover:shadow-md"
            >
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-slate-500 to-slate-600 rounded-xl flex items-center justify-center">
                  <CalendarIcon class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="ml-4 flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div class="flex-1 min-w-0">
                    <h4 class="text-base font-semibold text-slate-900 dark:text-white truncate group-hover:text-slate-700 dark:group-hover:text-slate-300 transition-colors">
                      {{ event.name }}
                    </h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                      {{ event.formatted_date_range }} • {{ event.location }}
                    </p>
                    <div class="flex items-center mt-2 space-x-4 text-xs text-slate-400">
                      <span class="flex items-center">
                        <DocumentTextIcon class="w-3 h-3 mr-1" />
                        {{ event.total_sessions }} oturum
                      </span>
                      <span class="flex items-center">
                        <SpeakerWaveIcon class="w-3 h-3 mr-1" />
                        {{ event.total_presentations }} sunum
                      </span>
                    </div>
                  </div>
                  <div class="flex items-center ml-4">
                    <span
                      class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium"
                      :class="getStatusClasses(event.status)"
                    >
                      {{ getStatusLabel(event.status) }}
                    </span>
                  </div>
                </div>
              </div>
              <Link
                :href="route('admin.events.show', event.slug)"
                class="ml-4 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
              >
                <ArrowRightIcon class="w-5 h-5" />
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions & Upcoming Events -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Hızlı İşlemler</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Sık kullanılan işlemler</p>
          </div>
          <div class="p-6 space-y-3">
            <Link
              v-for="action in quickActions"
              :key="action.name"
              :href="action.href"
              class="group flex items-center p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200"
            >
              <div class="flex-shrink-0">
                <div :class="action.iconBg" class="w-10 h-10 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                  <component :is="action.icon" :class="action.iconColor" class="w-5 h-5" />
                </div>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-slate-900 dark:text-white group-hover:text-slate-700 dark:group-hover:text-slate-300 transition-colors">
                  {{ action.name }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  {{ action.description }}
                </p>
              </div>
              <ArrowRightIcon class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors" />
            </Link>
          </div>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
          <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Yaklaşan Etkinlikler</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Bu hafta başlayacak etkinlikler</p>
          </div>
          <div class="p-6">
            <div v-if="upcomingEvents.length === 0" class="text-center py-8">
              <ClockIcon class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" />
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Yaklaşan etkinlik bulunmuyor.
              </p>
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="event in upcomingEvents"
                :key="event.id"
                class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-600 transition-all duration-200"
              >
                <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                    {{ event.name }}
                  </h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <ClockIcon class="w-3 h-3 inline mr-1" />
                    {{ event.days_until_start }} gün kaldı
                  </p>
                </div>
                <Link
                  :href="route('admin.events.show', event.slug)"
                  class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                >
                  <ArrowRightIcon class="w-4 h-4" />
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Activity Feed -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
      <div class="p-6 border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Son Aktiviteler</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Sistemdeki son hareketler</p>
          </div>
          <Link
            :href="route('admin.activities.index')"
            class="inline-flex items-center px-4 py-2 bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors"
          >
            <span class="text-sm font-medium">Tümünü Gör</span>
            <ArrowRightIcon class="w-4 h-4 ml-2" />
          </Link>
        </div>
      </div>
      <div class="p-6">
        <div v-if="recentActivities.length === 0" class="text-center py-8">
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Henüz aktivite bulunmuyor.
          </p>
        </div>
        <div v-else class="space-y-4">
          <div
            v-for="activity in recentActivities"
            :key="activity.id"
            class="flex items-start space-x-4 p-4 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
          >
            <div class="flex-shrink-0">
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center"
                :class="getActivityClasses(activity.type)"
              >
                <component :is="getActivityIcon(activity.type)" class="w-5 h-5" />
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-slate-900 dark:text-white">
                {{ activity.message }}
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ formatTime(activity.date) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  CalendarIcon,
  BuildingOfficeIcon,
  UsersIcon,
  ClockIcon,
  PlusIcon,
  ArrowRightIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ChartBarIcon,
  MapPinIcon,
  TagIcon,
  DocumentTextIcon,
  SpeakerWaveIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

// Page data
const page = usePage()
const currentUser = computed(() => page.props.auth?.user || null)

// Props with default values
const props = defineProps({
  statistics: {
    type: Object,
    required: false,
    default: () => ({
      total_events: 0,
      total_organizations: 0,
      total_participants: 0,
      pending_sessions: 0
    })
  },
  recentEvents: {
    type: Array,
    default: () => []
  },
  upcomingEvents: {
    type: Array,
    default: () => []
  },
  recentActivities: {
    type: Array,
    default: () => []
  }
})

// Quick actions configuration - corrected route names
const quickActions = [
  {
    name: 'Yeni Etkinlik',
    description: 'Yeni bir etkinlik oluşturun',
    href: route('admin.events.create'),
    icon: CalendarIcon,
    iconBg: 'bg-slate-100 dark:bg-slate-700',
    iconColor: 'text-slate-600 dark:text-slate-400'
  },
  {
    name: 'Katılımcı Ekle',
    description: 'Sisteme yeni katılımcı ekleyin',
    href: route('admin.participants.create'),
    icon: UsersIcon,
    iconBg: 'bg-slate-100 dark:bg-slate-700',
    iconColor: 'text-slate-600 dark:text-slate-400'
  },
  {
    name: 'Salon Yönetimi',
    description: 'Salonları düzenleyin',
    href: route('admin.venues.index'),
    icon: MapPinIcon,
    iconBg: 'bg-slate-100 dark:bg-slate-700',
    iconColor: 'text-slate-600 dark:text-slate-400'
  },
  {
    name: 'Sponsor Ekle',
    description: 'Yeni sponsor ekleyin',
    href: route('admin.sponsors.create'),
    icon: TagIcon,
    iconBg: 'bg-slate-100 dark:bg-slate-700',
    iconColor: 'text-slate-600 dark:text-slate-400'
  }
]

// Helper methods
const getStatusClasses = (status) => {
  const classes = {
    upcoming: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    ongoing: 'bg-slate-200 text-slate-800 dark:bg-slate-600 dark:text-slate-200',
    past: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    published: 'bg-slate-200 text-slate-800 dark:bg-slate-600 dark:text-slate-200',
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400'
  }
  return classes[status] || classes.draft
}

const getStatusLabel = (status) => {
  const labels = {
    upcoming: 'Yaklaşan',
    ongoing: 'Devam Ediyor',
    past: 'Tamamlandı',
    published: 'Yayında',
    draft: 'Taslak'
  }
  return labels[status] || status
}

const getActivityIcon = (type) => {
  const icons = {
    event_created: CalendarIcon,
    session_created: DocumentTextIcon,
    participant_added: UsersIcon,
    event_published: CheckCircleIcon,
    system_update: InformationCircleIcon
  }
  return icons[type] || InformationCircleIcon
}

const getActivityClasses = (type) => {
  const classes = {
    event_created: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
    session_created: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
    participant_added: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
    event_published: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
    system_update: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400'
  }
  return classes[type] || classes.system_update
}

const formatTime = (date) => {
  return new Date(date).toLocaleString('tr-TR', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.bg-grid-pattern {
  background-image: 
    linear-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
  background-size: 20px 20px;
}

/* Card hover animations */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Custom gradients */
.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}
</style>