<!-- Admin/EventDays/Show.vue - Premium Event Day Detail View -->
<template>
  <AdminLayout
    :page-title="eventDay.title"
    :page-subtitle="`${event.name} - ${formatFullDate(eventDay.date)}`"
    :breadcrumbs="breadcrumbs"
  >
    <Head :title="eventDay.title" />

    <div class="w-full space-y-8">
      <!-- Header Section -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="relative">
          <!-- Banner -->
          <div class="h-48 bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-600 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute inset-0 flex items-end">
              <div class="p-8 text-white w-full">
                <div class="flex items-end justify-between">
                  <div class="flex items-center space-x-4">
                    <div class="h-16 w-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                      <CalendarIcon class="h-10 w-10" />
                    </div>
                    <div>
                      <h1 class="text-3xl font-bold mb-1">{{ eventDay.title }}</h1>
                      <p class="text-blue-100 text-lg">{{ formatFullDate(eventDay.date) }} - {{ getDayOfWeek(eventDay.date) }}</p>
                    </div>
                  </div>
                  
                  <!-- Quick Stats -->
                  <div class="flex items-center space-x-8 text-white/90">
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ timeSlots ? Object.keys(timeSlots).length : 0 }}</div>
                      <div class="text-sm">Zaman Dilimi</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ venues.length }}</div>
                      <div class="text-sm">Salon</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ totalSessions }}</div>
                      <div class="text-sm">Oturum</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Meta Bar -->
          <div class="px-8 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-6">
              <!-- Status -->
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Durum:</span>
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                  :class="eventDay.is_active 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                    : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'"
                >
                  <component :is="eventDay.is_active ? CheckCircleIcon : XCircleIcon" class="w-4 h-4 mr-1" />
                  {{ eventDay.is_active ? 'Aktif' : 'Pasif' }}
                </span>
              </div>

              <!-- Event Info -->
              <div class="flex items-center space-x-2">
                <CalendarDaysIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ event.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Ana Etkinlik</div>
                </div>
              </div>

              <!-- Date -->
              <div class="flex items-center space-x-2">
                <ClockIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatFullDate(eventDay.date) }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ getDayOfWeek(eventDay.date) }}</div>
                </div>
              </div>

              <!-- Sort Order -->
              <div v-if="eventDay.sort_order !== null" class="flex items-center space-x-2">
                <HashtagIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ eventDay.sort_order + 1 }}. Gün</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Sıra</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Bar -->
          <div class="px-8 py-4 flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900">
            <div class="flex items-center space-x-3">
              <!-- Back to List -->
              <Link
                :href="route('admin.events.days.index', event.slug)"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                Günler Listesi
              </Link>
            </div>

            <div class="flex items-center space-x-3">
              <!-- Edit Button -->
              <Link
                :href="route('admin.events.days.edit', [event.slug, eventDay.id])"
                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <PencilSquareIcon class="h-4 w-4 mr-2" />
                Düzenle
              </Link>

              <!-- Program Management -->
              <button
                @click="manageSessions"
                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <Cog6ToothIcon class="h-4 w-4 mr-2" />
                Program Yönetimi
              </button>

              <!-- More Actions -->
              <div class="dropdown-container relative">
                <button
                  @click="showActionsMenu = !showActionsMenu"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm"
                >
                  <EllipsisVerticalIcon class="h-4 w-4 mr-2" />
                  Diğer
                  <ChevronDownIcon class="h-4 w-4 ml-2" />
                </button>

                <!-- Dropdown Menu -->
                <transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <div
                    v-if="showActionsMenu"
                    class="dropdown-menu absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 border dark:border-gray-700"
                    style="z-index: 9999 !important;"
                  >
                    <div class="py-1">
                      <button
                        @click="duplicateDay"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                      >
                        <DocumentDuplicateIcon class="h-4 w-4 mr-3" />
                        Günü Kopyala
                      </button>
                      <button
                        @click="exportSchedule"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                      >
                        <ArrowDownTrayIcon class="h-4 w-4 mr-3" />
                        Program Dışa Aktar
                      </button>
                      <button
                        @click="toggleStatus"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                      >
                        <component :is="eventDay.is_active ? EyeSlashIcon : EyeIcon" class="h-4 w-4 mr-3" />
                        {{ eventDay.is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                      </button>
                      <hr class="my-1 border-gray-200 dark:border-gray-600" />
                      <button
                        @click="deleteDay"
                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50 transition-colors"
                      >
                        <TrashIcon class="h-4 w-4 mr-3" />
                        Günü Sil
                      </button>
                    </div>
                  </div>
                </transition>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="xl:col-span-3 space-y-8">
          <!-- Description -->
          <div v-if="eventDay.description" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Açıklama</h3>
            <div class="prose dark:prose-invert max-w-none">
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ eventDay.description }}</p>
            </div>
          </div>

          <!-- Program Schedule -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Günlük Program</h3>
              <button
                @click="addSession"
                class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors"
              >
                <PlusIcon class="h-4 w-4 mr-1" />
                Oturum Ekle
              </button>
            </div>

            <!-- Time Slots -->
            <div v-if="timeSlots && Object.keys(timeSlots).length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="(sessions, timeSlot) in timeSlots"
                :key="timeSlot"
                class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <div class="flex items-start space-x-6">
                  <!-- Time -->
                  <div class="flex-shrink-0 w-20">
                    <div class="text-lg font-semibold text-purple-600 dark:text-purple-400">
                      {{ formatTime(timeSlot) }}
                    </div>
                  </div>

                  <!-- Sessions -->
                  <div class="flex-1 space-y-3">
                    <div
                      v-for="session in sessions"
                      :key="session.id"
                      class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-purple-300 dark:hover:border-purple-600 transition-colors"
                    >
                      <div class="flex items-start justify-between">
                        <div class="flex-1">
                          <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                            {{ session.title || 'Başlıksız Oturum' }}
                          </h4>
                          <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <div class="flex items-center">
                              <BuildingOfficeIcon class="h-4 w-4 mr-1" />
                              {{ session.venue?.name || 'Salon Yok' }}
                            </div>
                            <div v-if="session.category" class="flex items-center">
                              <TagIcon class="h-4 w-4 mr-1" />
                              {{ session.category.name }}
                            </div>
                            <div class="flex items-center">
                              <DocumentTextIcon class="h-4 w-4 mr-1" />
                              {{ session.presentations?.length || 0 }} sunum
                            </div>
                          </div>
                          <div v-if="session.moderators?.length > 0" class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <UserIcon class="h-4 w-4 mr-1" />
                            Moderatör: {{ session.moderators.map(m => m.name).join(', ') }}
                          </div>
                        </div>
                        
                        <!-- Session Actions -->
                        <div class="flex items-center space-x-2 ml-4">
                          <button
                            @click="viewSession(session)"
                            class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/50 transition-colors"
                            title="Görüntüle"
                          >
                            <EyeIcon class="h-4 w-4" />
                          </button>
                          <button
                            @click="editSession(session)"
                            class="p-1.5 text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/50 transition-colors"
                            title="Düzenle"
                          >
                            <PencilIcon class="h-4 w-4" />
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Program State -->
            <div v-else class="text-center py-16">
              <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                <ClockIcon class="h-full w-full" />
              </div>
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Henüz program yok</h4>
              <p class="text-gray-500 dark:text-gray-400 mb-6">Bu gün için program oturumları oluşturmaya başlayın.</p>
              <button
                @click="addSession"
                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors"
              >
                <PlusIcon class="h-5 w-5 mr-2" />
                İlk Oturumu Ekle
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Quick Stats -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İstatistikler</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <ClockIcon class="h-5 w-5 text-blue-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Zaman Dilimi</span>
                </div>
                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ timeSlots ? Object.keys(timeSlots).length : 0 }}</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <PresentationChartLineIcon class="h-5 w-5 text-green-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Toplam Oturum</span>
                </div>
                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ totalSessions }}</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <DocumentTextIcon class="h-5 w-5 text-purple-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Toplam Sunum</span>
                </div>
                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ totalPresentations }}</span>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <BuildingOfficeIcon class="h-5 w-5 text-indigo-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Kullanılan Salon</span>
                </div>
                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ usedVenues.length }}</span>
              </div>
            </div>
          </div>

          <!-- Available Venues -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Mevcut Salonlar</h3>
            <div class="space-y-3">
              <div
                v-for="venue in venues"
                :key="venue.id"
                class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ venue.name }}</span>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ venue.capacity || '∞' }} kişi</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hızlı İşlemler</h3>
            <div class="space-y-3">
              <button
                @click="addSession"
                class="flex items-center space-x-3 w-full p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
              >
                <PlusIcon class="h-5 w-5 text-purple-500" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">Oturum Ekle</span>
              </button>
              <Link
                :href="route('admin.venues.index')"
                class="flex items-center space-x-3 w-full p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
              >
                <BuildingOfficeIcon class="h-5 w-5 text-blue-500" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">Salon Yönetimi</span>
              </Link>
              <Link
                :href="route('admin.categories.index')"
                class="flex items-center space-x-3 w-full p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
              >
                <TagIcon class="h-5 w-5 text-green-500" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">Kategori Yönetimi</span>
              </Link>
            </div>
          </div>
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
  CalendarIcon,
  CalendarDaysIcon,
  ClockIcon,
  HashtagIcon,
  ArrowLeftIcon,
  PencilSquareIcon,
  Cog6ToothIcon,
  EllipsisVerticalIcon,
  ChevronDownIcon,
  DocumentDuplicateIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  EyeSlashIcon,
  TrashIcon,
  CheckCircleIcon,
  XCircleIcon,
  PlusIcon,
  BuildingOfficeIcon,
  TagIcon,
  DocumentTextIcon,
  UserIcon,
  PencilIcon,
  PresentationChartLineIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  event: {
    type: Object,
    required: true
  },
  eventDay: {
    type: Object,
    required: true
  },
  timeSlots: {
    type: Object,
    default: () => ({})
  },
  venues: {
    type: Array,
    default: () => []
  }
})

// State
const showActionsMenu = ref(false)
const confirmDialog = ref({
  show: false,
  title: '',
  message: '',
  type: 'warning',
  callback: null
})

// Computed
const breadcrumbs = computed(() => [
  { label: 'Etkinlikler', href: route('admin.events.index') },
  { label: props.event.name, href: route('admin.events.show', props.event.slug) },
  { label: 'Günler', href: route('admin.events.days.index', props.event.slug) },
  { label: props.eventDay.title, href: null }
])

const totalSessions = computed(() => {
  if (!props.timeSlots) return 0
  return Object.values(props.timeSlots).flat().length
})

const totalPresentations = computed(() => {
  if (!props.timeSlots) return 0
  return Object.values(props.timeSlots).flat().reduce((total, session) => {
    return total + (session.presentations?.length || 0)
  }, 0)
})

const usedVenues = computed(() => {
  if (!props.timeSlots) return []
  const venueIds = new Set()
  Object.values(props.timeSlots).flat().forEach(session => {
    if (session.venue?.id) {
      venueIds.add(session.venue.id)
    }
  })
  return props.venues.filter(venue => venueIds.has(venue.id))
})

// Click outside handler for dropdown menu
const handleClickOutside = (event) => {
  if (!event.target.closest('.dropdown-container')) {
    showActionsMenu.value = false
  }
}

// Keyboard shortcuts
const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    showActionsMenu.value = false
    confirmDialog.value.show = false
  }
}

// Methods
const formatFullDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const getDayOfWeek = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    weekday: 'long'
  })
}

const formatTime = (time) => {
  if (!time) return '-'
  return time.substring(0, 5) // HH:MM format
}

// Actions
const manageSessions = () => {
  router.visit(route('admin.program-sessions.index', { event_day_id: props.eventDay.id }))
}

const addSession = () => {
  router.visit(route('admin.program-sessions.create', { event_day_id: props.eventDay.id }))
}

const viewSession = (session) => {
  router.visit(route('admin.program-sessions.show', session.id))
}

const editSession = (session) => {
  router.visit(route('admin.program-sessions.edit', session.id))
}

const duplicateDay = () => {
  showActionsMenu.value = false
  router.post(route('admin.events.days.duplicate', [props.event.slug, props.eventDay.id]), {}, {
    onSuccess: () => {
      // Success message will be handled by the backend
    },
    onError: () => {
      alert('Gün kopyalama sırasında bir hata oluştu.')
    }
  })
}

const exportSchedule = () => {
  showActionsMenu.value = false
  
  // Create form for file download
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = route('admin.events.days.export')
  
  // Add CSRF token
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (csrfToken) {
    const csrfInput = document.createElement('input')
    csrfInput.type = 'hidden'
    csrfInput.name = '_token'
    csrfInput.value = csrfToken
    form.appendChild(csrfInput)
  }
  
  // Add event day ID
  const dayInput = document.createElement('input')
  dayInput.type = 'hidden'
  dayInput.name = 'event_day_id'
  dayInput.value = props.eventDay.id
  form.appendChild(dayInput)
  
  document.body.appendChild(form)
  form.submit()
  document.body.removeChild(form)
}

const toggleStatus = () => {
  showActionsMenu.value = false
  router.patch(route('admin.events.days.toggle-status', [props.event.slug, props.eventDay.id]), {}, {
    onError: () => {
      alert('Durum değiştirme sırasında bir hata oluştu.')
    }
  })
}

const deleteDay = () => {
  showActionsMenu.value = false
  confirmDialog.value = {
    show: true,
    title: 'Günü Sil',
    message: `"${props.eventDay.title}" gününü silmek istediğinize emin misiniz? Bu işlem geri alınamaz ve tüm oturumlar silinecektir.`,
    type: 'danger',
    callback: () => {
      router.delete(route('admin.events.days.destroy', [props.event.slug, props.eventDay.id]), {
        onSuccess: () => {
          confirmDialog.value.show = false
          // Redirect to days list after successful deletion
          router.visit(route('admin.events.days.index', props.event.slug))
        },
        onError: () => {
          alert('Silme işlemi sırasında bir hata oluştu.')
        }
      })
    }
  }
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
  
  // Debug log
  console.log('EventDay Show mounted with data:', {
    event: props.event,
    eventDay: props.eventDay,
    timeSlots: props.timeSlots,
    venues: props.venues
  })
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
/* Z-Index ve Dropdown Düzeltmeleri */
.dropdown-container {
  position: relative;
  z-index: 10;
}

.dropdown-menu {
  position: absolute !important;
  z-index: 9999 !important;
  right: 0;
  top: 100%;
  min-width: 14rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Ensure parent containers don't interfere */
.bg-white,
.bg-gray-900 {
  position: relative;
}

/* Override any overflow hidden that might clip the dropdown */
.overflow-hidden {
  overflow: visible !important;
}

/* Prose styles */
.prose p {
  margin-bottom: 1rem;
  line-height: 1.7;
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Focus styles */
button:focus-visible,
a:focus-visible {
  outline: none;
  box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.5);
  border-radius: 0.5rem;
}

/* Dark mode dropdown shadow */
.dark .dropdown-menu {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .dropdown-menu {
    right: 0;
    left: auto;
    min-width: 12rem;
  }
}

/* Session card hover effects */
.session-card {
  transition: all 0.2s ease;
}

.session-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px -2px rgba(0, 0, 0, 0.1);
}

/* Timeline styles */
.timeline-time {
  position: relative;
}

.timeline-time::after {
  content: '';
  position: absolute;
  right: -1rem;
  top: 50%;
  width: 0.5rem;
  height: 0.5rem;
  background: currentColor;
  border-radius: 50%;
  transform: translateY(-50%);
  opacity: 0.3;
}

/* Status badges */
.status-active {
  animation: pulse-green 2s infinite;
}

@keyframes pulse-green {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
  }
  50% {
    box-shadow: 0 0 0 0.5rem rgba(34, 197, 94, 0);
  }
}

/* Venue color indicators */
.venue-indicator {
  position: relative;
}

.venue-indicator::before {
  content: '';
  position: absolute;
  left: -0.5rem;
  top: 50%;
  width: 0.25rem;
  height: 1rem;
  background: currentColor;
  border-radius: 0.125rem;
  transform: translateY(-50%);
}

/* Grid layout improvements */
@media (min-width: 1280px) {
  .grid-responsive {
    grid-template-columns: 3fr 1fr;
  }
}

/* Button animations */
.btn-animate {
  transition: all 0.2s ease;
}

.btn-animate:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px -2px rgba(0, 0, 0, 0.1);
}

/* Custom scrollbar */
.scroll-area::-webkit-scrollbar {
  width: 6px;
}

.scroll-area::-webkit-scrollbar-track {
  background: rgb(243 244 246);
  border-radius: 3px;
}

.scroll-area::-webkit-scrollbar-thumb {
  background: rgb(156 163 175);
  border-radius: 3px;
}

.scroll-area::-webkit-scrollbar-thumb:hover {
  background: rgb(107 114 128);
}

.dark .scroll-area::-webkit-scrollbar-track {
  background: rgb(55 65 81);
}

.dark .scroll-area::-webkit-scrollbar-thumb {
  background: rgb(75 85 99);
}

.dark .scroll-area::-webkit-scrollbar-thumb:hover {
  background: rgb(107 114 128);
}

/* Print styles */
@media print {
  .no-print {
    display: none !important;
  }
  
  .bg-gradient-to-r {
    background: #7c3aed !important;
    color: white !important;
  }
}
</style>