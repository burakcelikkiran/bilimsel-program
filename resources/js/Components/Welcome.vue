<!-- resources/js/Pages/Dashboard.vue -->
<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Custom Navigation (without AdminLayout) -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Left side: Logo and Navigation -->
          <div class="flex items-center">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">EPS</span>
              </div>
              <span class="ml-3 text-xl font-semibold text-gray-900 dark:text-white">
                Etkinlik Programı Sistemi
              </span>
            </div>

            <!-- Main Navigation -->
            <div class="hidden md:ml-10 md:flex md:space-x-8">
              <Link
                :href="route('admin.dashboard')"
                class="border-blue-500 text-gray-900 dark:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Dashboard
              </Link>
              <Link
                :href="route('admin.events.index')"
                class="border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
              >
                Etkinlikler
              </Link>
              <Link
                :href="route('admin.participants.index')"
                class="border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
              >
                Katılımcılar
              </Link>
            </div>
          </div>

          <!-- Right side: User menu -->
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 relative">
              <BellIcon class="h-6 w-6" />
              <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
            </button>

            <!-- User Menu -->
            <div class="relative">
              <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <img
                  class="h-8 w-8 rounded-full object-cover"
                  :src="$page.props.auth.user.profile_photo_url || '/images/default-avatar.png'"
                  :alt="$page.props.auth.user.name"
                />
                <span class="ml-3 text-gray-700 dark:text-gray-300 hidden md:block">
                  {{ $page.props.auth.user.name }}
                </span>
                <ChevronDownIcon class="ml-2 h-4 w-4 text-gray-400 hidden md:block" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 dark:from-blue-800 dark:to-blue-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
          <!-- Welcome Message -->
          <h1 class="text-4xl font-bold text-white mb-4">
            Hoş Geldiniz, {{ $page.props.auth.user.name }}
          </h1>
          <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">
            Etkinlik programlarınızı yönetin, katılımcıları organize edin ve profesyonel sunumlar hazırlayın.
          </p>

          <!-- Quick Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-center">
              <div class="text-3xl font-bold text-white mb-2">
                {{ dashboardData.stats.total_events || 0 }}
              </div>
              <div class="text-blue-100 text-sm">Toplam Etkinlik</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-center">
              <div class="text-3xl font-bold text-white mb-2">
                {{ dashboardData.stats.active_events || 0 }}
              </div>
              <div class="text-blue-100 text-sm">Aktif Etkinlik</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-center">
              <div class="text-3xl font-bold text-white mb-2">
                {{ dashboardData.stats.total_participants || 0 }}
              </div>
              <div class="text-blue-100 text-sm">Toplam Katılımcı</div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 text-center">
              <div class="text-3xl font-bold text-white mb-2">
                {{ dashboardData.stats.total_sessions || 0 }}
              </div>
              <div class="text-blue-100 text-sm">Program Oturumu</div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              :href="route('admin.events.create')"
              class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-50 transition-colors font-semibold shadow-lg"
            >
              <PlusIcon class="h-5 w-5 inline mr-2" />
              Yeni Etkinlik Oluştur
            </Link>
            <Link
              :href="route('admin.participants.create')"
              class="border-2 border-white text-white px-8 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition-colors font-semibold"
            >
              <UsersIcon class="h-5 w-5 inline mr-2" />
              Katılımcı Ekle
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Recent Events Section -->
      <div class="mb-12">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            Son Etkinlikler
          </h2>
          <Link
            :href="route('admin.events.index')"
            class="text-blue-600 hover:text-blue-700 font-medium"
          >
            Tümünü Gör →
          </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="event in dashboardData.recent_events"
            :key="event.id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow p-6"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                  {{ event.name }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                  {{ event.formatted_date_range }}
                </p>
                
                <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                  <span class="flex items-center">
                    <CalendarIcon class="h-4 w-4 mr-1" />
                    {{ event.total_sessions }} Oturum
                  </span>
                  <span class="flex items-center">
                    <UsersIcon class="h-4 w-4 mr-1" />
                    {{ event.total_participants }} Katılımcı
                  </span>
                </div>
              </div>
              
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="getEventStatusClasses(event.status)"
              >
                {{ getEventStatusLabel(event.status) }}
              </span>
            </div>

            <div class="mt-4 flex justify-end">
              <Link
                :href="route('admin.events.show', event.id)"
                class="text-blue-600 hover:text-blue-700 text-sm font-medium"
              >
                Detayları Gör →
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <Link
          :href="route('admin.events.index')"
          class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition-shadow group"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CalendarIcon class="h-8 w-8 text-blue-600 group-hover:text-blue-700" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Etkinlikler</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ dashboardData.stats.total_events || 0 }}
              </p>
            </div>
          </div>
        </Link>

        <Link
          :href="route('admin.participants.index')"
          class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition-shadow group"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UsersIcon class="h-8 w-8 text-green-600 group-hover:text-green-700" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Katılımcılar</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ dashboardData.stats.total_participants || 0 }}
              </p>
            </div>
          </div>
        </Link>

        <Link
          :href="route('admin.venues.index')"
          class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition-shadow group"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <MapPinIcon class="h-8 w-8 text-purple-600 group-hover:text-purple-700" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Salonlar</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ dashboardData.stats.total_venues || 0 }}
              </p>
            </div>
          </div>
        </Link>

        <Link
          :href="route('admin.organizations.index')"
          class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition-shadow group"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <BuildingOfficeIcon class="h-8 w-8 text-orange-600 group-hover:text-orange-700" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Organizasyonlar</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ dashboardData.stats.total_organizations || 0 }}
              </p>
            </div>
          </div>
        </Link>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Son Aktiviteler
          </h3>
        </div>
        <div class="p-6">
          <div v-if="dashboardData.recent_activities && dashboardData.recent_activities.length > 0" class="space-y-4">
            <div
              v-for="activity in dashboardData.recent_activities"
              :key="activity.id"
              class="flex items-start space-x-3"
            >
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                  <CalendarIcon class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900 dark:text-white">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatDate(activity.created_at) }}
                </p>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-12">
            <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Henüz aktivite yok</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              İlk etkinliğinizi oluşturarak başlayın.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import {
  BellIcon,
  ChevronDownIcon,
  CalendarIcon,
  UsersIcon,
  MapPinIcon,
  BuildingOfficeIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

defineProps({
  dashboardData: {
    type: Object,
    default: () => ({
      stats: {},
      recent_events: [],
      recent_activities: []
    })
  }
})

// Helper functions
const getEventStatusClasses = (status) => {
  const statusClasses = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    upcoming: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    ongoing: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    completed: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
  }
  return statusClasses[status] || statusClasses.draft
}

const getEventStatusLabel = (status) => {
  const statusLabels = {
    draft: 'Taslak',
    upcoming: 'Yaklaşan',
    ongoing: 'Devam Ediyor',
    completed: 'Tamamlandı'
  }
  return statusLabels[status] || 'Bilinmeyen'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('tr-TR')
}
</script>

<style scoped>
/* Custom hover effects */
.hover-lift:hover {
  transform: translateY(-2px);
  transition: transform 0.2s ease;
}
</style>