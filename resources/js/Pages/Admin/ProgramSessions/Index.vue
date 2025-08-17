<!-- Admin/ProgramSessions/Index.vue - Gray Theme Version -->
<template>
  <AdminLayout
    page-title="Program Oturumları"
    page-subtitle="Etkinlik program oturumlarını yönetin ve düzenleyin"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Program Oturumları" />

    <!-- Hero Section with Quick Stats - Gray Theme -->
    <div class="mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 text-white shadow-lg border border-gray-700">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <SpeakerWaveIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Toplam Oturum</p>
              <p class="text-2xl font-bold">{{ stats.total || sessions?.total || 0 }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl p-6 text-white shadow-lg border border-gray-600">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <DocumentTextIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Sunumlu Oturumlar</p>
              <p class="text-2xl font-bold">{{ stats.with_presentations || 0 }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl p-6 text-white shadow-lg border border-gray-500">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <UsersIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Moderatörlü</p>
              <p class="text-2xl font-bold">{{ stats.with_moderators || 0 }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl p-6 text-white shadow-lg border border-gray-400">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <ClockIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Bugünkü Oturumlar</p>
              <p class="text-2xl font-bold">{{ stats.today || 0 }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modern Data Table -->
    <div class="bg-white dark:bg-gray-900 shadow-xl rounded-2xl overflow-hidden border border-gray-200/50 dark:border-gray-800/50">
      <!-- Enhanced Header -->
      <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 px-8 py-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
          <!-- Left: Title and Description -->
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
              Oturum Yönetimi
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              Program oturumlarını düzenleyin ve yönetin
            </p>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center space-x-3">
            <!-- Quick Filters -->
            <div class="flex items-center space-x-2">
              <button
                @click="quickFilter('all')"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  currentQuickFilter === 'all' 
                    ? 'bg-gray-800 text-white shadow-lg border border-gray-700' 
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Tümü
              </button>
              <button
                @click="quickFilter('main')"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  currentQuickFilter === 'main' 
                    ? 'bg-gray-700 text-white shadow-lg border border-gray-600' 
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Ana Oturum
              </button>
              <button
                @click="quickFilter('oral_presentation')"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  currentQuickFilter === 'oral_presentation' 
                    ? 'bg-gray-600 text-white shadow-lg border border-gray-500' 
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Sözlü Bildiri
              </button>
            </div>

            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
              <button
                @click="viewMode = 'list'"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                  viewMode === 'list' 
                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                ]"
              >
                <ListBulletIcon class="h-4 w-4" />
              </button>
              <button
                @click="viewMode = 'grid'"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                  viewMode === 'grid' 
                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                ]"
              >
                <Squares2X2Icon class="h-4 w-4" />
              </button>
            </div>

            <!-- Create Button -->
            <Link
              :href="safeRoute('admin.program-sessions.create', '/admin/program-sessions/create')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 border border-gray-700"
            >
              <PlusIcon class="h-5 w-5 mr-2" />
              Yeni Oturum
            </Link>
          </div>
        </div>

        <!-- Enhanced Search and Filters -->
        <div class="mt-6 flex flex-col lg:flex-row lg:items-center space-y-4 lg:space-y-0 lg:space-x-4">
          <!-- Search Bar -->
          <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
            </div>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Oturum başlığı, açıklama veya moderatör ile ara..."
              class="block w-full pl-12 pr-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent shadow-sm"
              @input="handleSearchDebounced"
            />
            <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-4 flex items-center">
              <button
                @click="clearSearch"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              >
                <XMarkIcon class="h-5 w-5" />
              </button>
            </div>
          </div>

          <!-- Advanced Filters -->
          <div class="flex items-center space-x-3">
            <!-- Event Filter -->
            <select
              v-model="activeFilters.event_id"
              @change="applyFilters"
              class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
            >
              <option value="">Tüm Etkinlikler</option>
              <option 
                v-for="event in filterOptions?.events || []" 
                :key="event.id" 
                :value="event.id"
              >
                {{ event.name }}
              </option>
            </select>

            <!-- Venue Filter -->
            <select
              v-model="activeFilters.venue_id"
              @change="applyFilters"
              class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
            >
              <option value="">Tüm Salonlar</option>
              <option 
                v-for="venue in filterOptions?.venues || []" 
                :key="venue.id" 
                :value="venue.id"
              >
                {{ venue.name }}
              </option>
            </select>

            <!-- Session Type Filter -->
            <select
              v-model="activeFilters.session_type"
              @change="applyFilters"
              class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
            >
              <option value="">Tüm Türler</option>
              <option value="main">Ana Oturum</option>
              <option value="satellite">Uydu Sempozyumu</option>
              <option value="oral_presentation">Sözlü Bildiri</option>
              <option value="special">Özel Oturum</option>
              <option value="break">Ara</option>
            </select>

            <!-- Filter Reset -->
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="px-4 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200"
              title="Filtreleri Temizle"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-16">
        <div class="flex flex-col items-center space-y-4">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-600"></div>
          <p class="text-gray-600 dark:text-gray-400 font-medium">Yükleniyor...</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="displayedSessions.length === 0" class="py-16">
        <div class="text-center">
          <div class="mx-auto h-24 w-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 rounded-2xl flex items-center justify-center">
            <SpeakerWaveIcon class="h-12 w-12 text-gray-400" />
          </div>
          <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
            {{ searchQuery || hasActiveFilters ? 'Sonuç bulunamadı' : 'Henüz oturum yok' }}
          </h3>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            {{ searchQuery || hasActiveFilters 
              ? 'Arama kriterlerinizi değiştirip tekrar deneyin.' 
              : 'İlk program oturumunu oluşturmak için başlayın.'
            }}
          </p>
          <div class="mt-8">
            <Link
              v-if="!searchQuery && !hasActiveFilters"
              :href="safeRoute('admin.program-sessions.create', '/admin/program-sessions/create')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
            >
              <PlusIcon class="h-5 w-5 mr-2" />
              İlk Oturumu Oluşturun
            </Link>
            <button
              v-else
              @click="clearAllFilters"
              class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold rounded-xl shadow-sm hover:shadow-md transition-all duration-200"
            >
              <XMarkIcon class="h-5 w-5 mr-2" />
              Filtreleri Temizle
            </button>
          </div>
        </div>
      </div>

      <!-- Sessions Content -->
      <div v-else>
        <!-- Bulk Actions -->
        <div 
          v-if="selectedSessions.length > 0" 
          class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-8 py-4"
        >
          <div class="flex items-center justify-between">
            <p class="text-gray-800 dark:text-gray-200 font-medium">
              {{ selectedSessions.length }} oturum seçildi
            </p>
            <div class="flex items-center space-x-3">
              <button
                @click="bulkDuplicate"
                class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition-all duration-200"
              >
                Kopyala
              </button>
              <button
                @click="bulkDelete"
                class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium transition-all duration-200"
              >
                Sil
              </button>
            </div>
          </div>
        </div>

        <!-- Sessions Table -->
        <div class="overflow-visible">
          <table class="w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
              <tr>
                <!-- Select All -->
                <th class="px-8 py-4 text-left w-12">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    :indeterminate="isIndeterminate"
                    @change="toggleSelectAll"
                    class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                  />
                </th>
                
                <!-- Session Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  <span>Oturum</span>
                </th>
                
                <!-- Type Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                  Tür
                </th>
                
                <!-- Time Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">
                  Zaman
                </th>
                
                <!-- Stats Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                  İstatistikler
                </th>
                
                <!-- Actions Header -->
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-60">
                  İşlemler
                </th>
              </tr>
            </thead>
            
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
              <tr 
                v-for="session in displayedSessions" 
                :key="session.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200"
              >
                <!-- Checkbox -->
                <td class="px-8 py-6 w-12">
                  <input
                    type="checkbox"
                    :value="session.id"
                    v-model="selectedSessions"
                    class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                  />
                </td>

                <!-- Session Info -->
                <td class="px-6 py-6">
                  <div class="flex items-start">
                    <div class="flex-shrink-0 h-12 w-12">
                      <div
                        class="h-12 w-12 rounded-xl bg-gradient-to-br flex items-center justify-center shadow-md"
                        :class="getSessionTypeGradient(session.session_type)"
                      >
                        <component :is="getSessionIcon(session.session_type)" class="h-6 w-6 text-white" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <Link
                        :href="safeRoute('admin.program-sessions.show', `/admin/program-sessions/${session.id}`, session.id)"
                        class="text-lg font-semibold text-gray-900 dark:text-white hover:text-gray-600 dark:hover:text-gray-400 transition-colors duration-200"
                      >
                        {{ session.title || 'Başlıksız Oturum' }}
                      </Link>
                      <p v-if="session.description" class="text-gray-600 dark:text-gray-400 mt-1 line-clamp-2 max-w-md">
                        {{ session.description }}
                      </p>
                      <div class="flex items-center mt-2 text-sm text-gray-500 dark:text-gray-400">
                        <BuildingOfficeIcon class="h-4 w-4 mr-1" />
                        {{ session.venue?.display_name || session.venue?.name || 'Salon belirtilmemiş' }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Type -->
                <td class="px-6 py-6 w-32">
                  <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                    :class="getSessionTypeClasses(session.session_type)"
                  >
                    <svg class="mr-2 h-2 w-2 fill-current" viewBox="0 0 8 8">
                      <circle cx="4" cy="4" r="3" />
                    </svg>
                    {{ session.session_type_display || session.session_type }}
                  </span>
                  <span
                    v-if="session.is_break"
                    class="block mt-1 inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200"
                  >
                    <PauseIcon class="w-3 h-3 mr-1" />
                    Ara
                  </span>
                </td>

                <!-- Time -->
                <td class="px-6 py-6 w-40">
                  <div class="text-sm">
                    <div class="font-medium text-gray-900 dark:text-white">
                      {{ session.formatted_time_range || 'Zaman belirtilmemiş' }}
                    </div>
                    <div class="text-gray-500 dark:text-gray-400 flex items-center mt-1">
                      <ClockIcon class="h-4 w-4 mr-1" />
                      {{ formatDuration(session) }}
                    </div>
                  </div>
                </td>

                <!-- Stats -->
                <td class="px-6 py-6 w-32">
                  <div class="text-sm space-y-1">
                    <div class="flex items-center text-gray-900 dark:text-white">
                      <DocumentTextIcon class="h-4 w-4 mr-1" />
                      <span class="font-semibold">{{ session.presentations_count || 0 }}</span>
                      <span class="text-gray-500 dark:text-gray-400 ml-1">sunum</span>
                    </div>
                    <div class="flex items-center text-gray-900 dark:text-white">
                      <UsersIcon class="h-4 w-4 mr-1" />
                      <span class="font-semibold">{{ session.moderators_count || 0 }}</span>
                      <span class="text-gray-500 dark:text-gray-400 ml-1">moderatör</span>
                    </div>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-6 w-60">
                  <div class="flex flex-wrap items-center gap-2">
                    <!-- View -->
                    <Link
                      :href="safeRoute('admin.program-sessions.show', `/admin/program-sessions/${session.id}`, session.id)"
                      class="inline-flex items-center px-2 py-1 bg-gray-700 hover:bg-gray-800 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                      title="Görüntüle"
                    >
                      <EyeIcon class="h-3 w-3 mr-1" />
                      Görüntüle
                    </Link>

                    <!-- Edit -->
                    <Link
                      v-if="session.can_edit"
                      :href="safeRoute('admin.program-sessions.edit', `/admin/program-sessions/${session.id}/edit`, session.id)"
                      class="inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                      title="Düzenle"
                    >
                      <PencilIcon class="h-3 w-3 mr-1" />
                      Düzenle
                    </Link>

                    <!-- More Actions Dropdown -->
                    <div class="relative">
                      <button
                        @click="toggleActionsMenu(session.id)"
                        class="inline-flex items-center px-2 py-1 bg-gray-800 hover:bg-gray-900 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                        title="Daha Fazla"
                      >
                        <EllipsisVerticalIcon class="h-3 w-3" />
                      </button>

                      <!-- Dropdown Menu -->
                      <div
                        v-if="showActionsMenu === session.id"
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-50 border border-gray-200 dark:border-gray-700"
                      >
                        <div class="py-1">
                          <!-- Duplicate -->
                          <button
                            @click="duplicateSession(session)"
                            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                          >
                            <DocumentDuplicateIcon class="h-4 w-4 mr-2" />
                            Kopyala
                          </button>

                          <!-- Delete -->
                          <button
                            v-if="session.can_delete"
                            @click="deleteSession(session)"
                            class="flex items-center w-full px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                          >
                            <TrashIcon class="h-4 w-4 mr-2" />
                            Sil
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Confirm Dialog -->
    <ConfirmDialog
      v-model="confirmDialog.show"
      :title="confirmDialog.title"
      :message="confirmDialog.message"
      :type="confirmDialog.type"
      @confirm="confirmDialog.callback"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  ListBulletIcon,
  Squares2X2Icon,
  CalendarIcon,
  ClockIcon,
  BuildingOfficeIcon,
  DocumentTextIcon,
  UsersIcon,
  EyeIcon,
  PencilIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  SpeakerWaveIcon,
  PauseIcon,
  EllipsisVerticalIcon,
  MicrophoneIcon,
  AcademicCapIcon,
  CogIcon
} from '@heroicons/vue/24/outline'

// Debounce function
const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Props
const props = defineProps({
  sessions: {
    type: Object,
    default: () => ({ data: [], total: 0 })
  },
  filter_options: {
    type: Object,
    default: () => ({})
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Helper function to safely create routes
const safeRoute = (routeName, fallback, param = null) => {
  try {
    if (param) {
      return route(routeName, param)
    }
    return route(routeName)
  } catch (error) {
    return fallback
  }
}

// State
const loading = ref(false)
const showActionsMenu = ref(null)
const selectedSessions = ref([])
const searchQuery = ref(props.filters.search || '')
const currentQuickFilter = ref('all')
const sortField = ref(props.filters.sort || 'start_time')
const sortDirection = ref(props.filters.direction || 'asc')
const pageSize = ref(props.filters.per_page || 15)
const viewMode = ref('list')

const activeFilters = ref({
  event_id: props.filters.event_id || '',
  venue_id: props.filters.venue_id || '',
  session_type: props.filters.session_type || '',
  category_id: props.filters.category_id || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

const confirmDialog = ref({
  show: false,
  title: '',
  message: '',
  type: 'warning',
  callback: null
})

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: safeRoute('admin.dashboard', '/admin/dashboard') },
  { label: 'Program Oturumları', href: null }
])

const displayedSessions = computed(() => props.sessions?.data || [])

const filterOptions = computed(() => props.filter_options || {})

const isAllSelected = computed(() => {
  return displayedSessions.value.length > 0 && selectedSessions.value.length === displayedSessions.value.length
})

const isIndeterminate = computed(() => {
  return selectedSessions.value.length > 0 && selectedSessions.value.length < displayedSessions.value.length
})

// Methods
const getSessionTypeClasses = (sessionType) => {
  const classes = {
    main: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    satellite: 'bg-gray-200 text-gray-900 dark:bg-gray-800 dark:text-gray-200',
    oral_presentation: 'bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-gray-200',
    special: 'bg-gray-400 text-white dark:bg-gray-600 dark:text-gray-200',
    break: 'bg-gray-500 text-white dark:bg-gray-500 dark:text-gray-100'
  }
  return classes[sessionType] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const getSessionTypeGradient = (sessionType) => {
  const gradients = {
    main: 'from-gray-500 to-gray-600',
    satellite: 'from-gray-600 to-gray-700',
    oral_presentation: 'from-gray-700 to-gray-800',
    special: 'from-gray-800 to-gray-900',
    break: 'from-gray-400 to-gray-500'
  }
  return gradients[sessionType] || 'from-gray-500 to-gray-600'
}

const getSessionIcon = (sessionType) => {
  const icons = {
    main: MicrophoneIcon,
    satellite: AcademicCapIcon,
    oral_presentation: DocumentTextIcon,
    special: CogIcon,
    break: PauseIcon
  }
  return icons[sessionType] || SpeakerWaveIcon
}

const formatDuration = (session) => {
  if (!session.start_time || !session.end_time) return 'Süre belirsiz'
  
  const start = new Date(`2000-01-01 ${session.start_time}`)
  const end = new Date(`2000-01-01 ${session.end_time}`)
  const diff = (end - start) / (1000 * 60) // minutes
  
  if (diff < 60) return `${diff} dk`
  const hours = Math.floor(diff / 60)
  const minutes = diff % 60
  return minutes > 0 ? `${hours}s ${minutes}dk` : `${hours}s`
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedSessions.value = []
  } else {
    selectedSessions.value = displayedSessions.value.map(session => session.id)
  }
}

const toggleActionsMenu = (sessionId) => {
  showActionsMenu.value = showActionsMenu.value === sessionId ? null : sessionId
}

// Session Actions
const duplicateSession = (session) => {
  showActionsMenu.value = null
  router.post(safeRoute('admin.program-sessions.duplicate', `/admin/program-sessions/${session.id}/duplicate`, session.id), {}, {
    onError: () => {
      alert('Kopyalama sırasında bir hata oluştu.')
    }
  })
}

const deleteSession = (session) => {
  showActionsMenu.value = null
  confirmDialog.value = {
    show: true,
    title: 'Oturumu Sil',
    message: `"${session.title}" oturumunu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
    type: 'danger',
    callback: () => {
      router.delete(safeRoute('admin.program-sessions.destroy', `/admin/program-sessions/${session.id}`, session.id), {
        onSuccess: () => {
          confirmDialog.value.show = false
        },
        onError: () => {
          alert('Silme işlemi sırasında bir hata oluştu.')
        }
      })
    }
  }
}

const hasActiveFilters = computed(() => {
  return Object.values(activeFilters.value).some(value => value !== '' && value !== null)
})

// Bulk Actions
const bulkDuplicate = () => {
  router.post(safeRoute('admin.program-sessions.bulk-duplicate', '/admin/program-sessions/bulk-duplicate'), { 
    session_ids: selectedSessions.value 
  }, {
    onSuccess: () => {
      selectedSessions.value = []
    },
    onError: () => {
      alert('Kopyalama işlemi sırasında bir hata oluştu.')
    }
  })
}

const bulkDelete = () => {
  confirmDialog.value = {
    show: true,
    title: 'Oturumları Sil',
    message: `Seçili ${selectedSessions.value.length} oturumu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
    type: 'danger',
    callback: () => {
      router.delete(safeRoute('admin.program-sessions.bulk-destroy', '/admin/program-sessions/bulk-destroy'), {
        data: { session_ids: selectedSessions.value },
        onSuccess: () => {
          confirmDialog.value.show = false
          selectedSessions.value = []
        },
        onError: () => {
          alert('Silme işlemi sırasında bir hata oluştu.')
        }
      })
    }
  }
}

// Click outside handler
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showActionsMenu.value = null
  }
}

// Debounced search handler
const handleSearchDebounced = debounce(() => {
  handleSearch()
}, 300)

const handleSearch = () => {
  updateUrl({ search: searchQuery.value, page: 1 })
}

const clearSearch = () => {
  searchQuery.value = ''
  handleSearch()
}

const quickFilter = (filter) => {
  currentQuickFilter.value = filter
  if (filter === 'all') {
    activeFilters.value.session_type = ''
  } else {
    activeFilters.value.session_type = filter
  }
  applyFilters()
}

const applyFilters = () => {
  updateUrl({ ...activeFilters.value, page: 1 })
}

const clearFilters = () => {
  activeFilters.value = {
    event_id: '',
    venue_id: '',
    session_type: '',
    category_id: '',
    date_from: '',
    date_to: ''
  }
  currentQuickFilter.value = 'all'
  applyFilters()
}

const clearAllFilters = () => {
  searchQuery.value = ''
  clearFilters()
}

const updateUrl = (params) => {
  const currentParams = new URLSearchParams(window.location.search)
  const existingParams = Object.fromEntries(currentParams)
  
  router.get(safeRoute('admin.program-sessions.index', '/admin/program-sessions'), {
    ...existingParams,
    ...params,
    search: searchQuery.value || undefined
  }, {
    preserveState: true,
    preserveScroll: true,
    onStart: () => loading.value = true,
    onFinish: () => loading.value = false
  })
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Focus styles */
input:focus, select:focus, button:focus {
  outline: none;
}

/* Custom checkbox indeterminate state */
input[type="checkbox"]:indeterminate {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M4 8h8v1H4z'/%3e%3c/svg%3e");
}
</style>