<!-- Admin/EventDays/Index.vue - Premium Event Day Management -->
<template>
  <AdminLayout
    :page-title="`${event.name} - Günler`"
    page-subtitle="Etkinlik günlerini yönetin ve program oluşturun"
    :breadcrumbs="breadcrumbs"
  >
    <Head :title="`${event.name} - Günler`" />

    <div class="w-full space-y-8">
      <!-- Header Section -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0">
                <div class="h-12 w-12 bg-purple-600 rounded-xl flex items-center justify-center">
                  <CalendarDaysIcon class="h-8 w-8 text-white" />
                </div>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ event.name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Etkinlik Günleri Yönetimi</p>
              </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="flex items-center space-x-8 text-sm">
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.total_days }}</div>
                <div class="text-gray-500 dark:text-gray-400">Toplam Gün</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total_sessions }}</div>
                <div class="text-gray-500 dark:text-gray-400">Oturum</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.total_presentations }}</div>
                <div class="text-gray-500 dark:text-gray-400">Sunum</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation & Actions -->
        <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900">
          <div class="flex items-center space-x-3">
            <Link
              :href="route('admin.events.show', event.slug)"
              class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm"
            >
              <ArrowLeftIcon class="h-4 w-4 mr-2" />
              Etkinlik Detayı
            </Link>
          </div>

          <div class="flex items-center space-x-3">
            <!-- Auto Create Days -->
            <button
              @click="autoCreateDays"
              :disabled="processing"
              class="inline-flex items-center px-4 py-2 border border-purple-300 dark:border-purple-600 rounded-lg text-sm font-medium text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <SparklesIcon class="h-4 w-4 mr-2" />
              Otomatik Günler
            </button>

            <!-- Create New Day -->
            <Link
              :href="route('admin.events.days.create', event.slug)"
              class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              Yeni Gün Ekle
            </Link>
          </div>
        </div>
      </div>

      <!-- Event Days Grid -->
      <div v-if="eventDays.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="(day, index) in eventDays.data"
          :key="day.id"
          class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all duration-200 group"
        >
          <!-- Day Header -->
          <div class="relative">
            <div class="h-24 bg-gradient-to-br from-purple-500 to-blue-600 relative overflow-hidden">
              <div class="absolute inset-0 bg-black/10"></div>
              <div class="absolute top-4 left-4 text-white">
                <div class="text-2xl font-bold">{{ index + 1 }}</div>
                <div class="text-sm opacity-90">Gün</div>
              </div>
              <div class="absolute top-4 right-4 text-white">
                <div class="text-sm opacity-90">{{ formatDayName(day.date) }}</div>
                <div class="text-xs opacity-75">{{ formatDate(day.date) }}</div>
              </div>
            </div>
          </div>

          <!-- Day Content -->
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                  {{ day.title }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(day.date) }}</p>
              </div>
              
              <!-- Status Badge -->
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="day.is_active 
                  ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                  : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'"
              >
                {{ day.is_active ? 'Aktif' : 'Pasif' }}
              </span>
            </div>

            <!-- Description -->
            <p v-if="day.description" class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
              {{ day.description }}
            </p>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ day.program_sessions_count || 0 }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Oturum</div>
              </div>
              <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ day.presentations_count || 0 }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Sunum</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="flex items-center space-x-2">
                <!-- View -->
                <Link
                  :href="route('admin.events.days.show', [event.slug, day.id])"
                  class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/50 transition-colors"
                  title="Görüntüle"
                >
                  <EyeIcon class="h-4 w-4" />
                </Link>

                <!-- Edit -->
                <Link
                  :href="route('admin.events.days.edit', [event.slug, day.id])"
                  class="p-2 text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/50 transition-colors"
                  title="Düzenle"
                >
                  <PencilIcon class="h-4 w-4" />
                </Link>

                <!-- Program Management -->
                <button
                  @click="manageDayProgram(day)"
                  class="p-2 text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/50 transition-colors"
                  title="Program Yönetimi"
                >
                  <Cog6ToothIcon class="h-4 w-4" />
                </button>
              </div>

              <!-- More Actions -->
              <div class="relative">
                <button
                  @click="toggleActionsMenu(day.id)"
                  class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  <EllipsisVerticalIcon class="h-4 w-4" />
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
                    v-if="showActionsMenu === day.id"
                    class="absolute right-0 bottom-full mb-2 w-44 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-10 border dark:border-gray-700"
                  >
                    <div class="py-1">
                      <button
                        @click="duplicateDay(day)"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                      >
                        <DocumentDuplicateIcon class="h-4 w-4 mr-3" />
                        Kopyala
                      </button>
                      <button
                        @click="toggleDayStatus(day)"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                      >
                        <component :is="day.is_active ? EyeSlashIcon : EyeIcon" class="h-4 w-4 mr-3" />
                        {{ day.is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                      </button>
                      <hr class="my-1 border-gray-200 dark:border-gray-600" />
                      <button
                        @click="deleteDay(day)"
                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50 transition-colors"
                      >
                        <TrashIcon class="h-4 w-4 mr-3" />
                        Sil
                      </button>
                    </div>
                  </div>
                </transition>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-20">
        <div class="mx-auto h-24 w-24 text-gray-400 mb-6">
          <CalendarDaysIcon class="h-full w-full" />
        </div>
        <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2">Henüz gün eklenmemiş</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">
          {{ event.name }} etkinliği için günler ekleyerek program oluşturmaya başlayın.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <button
            @click="autoCreateDays"
            :disabled="processing"
            class="inline-flex items-center px-6 py-3 border border-purple-300 dark:border-purple-600 rounded-lg text-sm font-medium text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <SparklesIcon class="h-5 w-5 mr-2" />
            Otomatik Günler Oluştur
          </button>
          <Link
            :href="route('admin.events.days.create', event.slug)"
            class="inline-flex items-center px-6 py-3 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm"
          >
            <PlusIcon class="h-5 w-5 mr-2" />
            İlk Günü Ekle
          </Link>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="eventDays.last_page > 1" class="flex items-center justify-center">
        <nav class="flex items-center space-x-1">
          <button
            @click="goToPage(eventDays.current_page - 1)"
            :disabled="!eventDays.prev_page_url"
            class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <ChevronLeftIcon class="h-4 w-4" />
          </button>
          
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="typeof page === 'number' ? goToPage(page) : null"
            :disabled="typeof page !== 'number'"
            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="page === eventDays.current_page 
              ? 'bg-purple-600 text-white' 
              : typeof page === 'number' 
                ? 'border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' 
                : 'text-gray-400 cursor-default'"
          >
            {{ page }}
          </button>
          
          <button
            @click="goToPage(eventDays.current_page + 1)"
            :disabled="!eventDays.next_page_url"
            class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <ChevronRightIcon class="h-4 w-4" />
          </button>
        </nav>
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
  CalendarDaysIcon,
  ArrowLeftIcon,
  PlusIcon,
  SparklesIcon,
  EyeIcon,
  EyeSlashIcon,
  PencilIcon,
  Cog6ToothIcon,
  EllipsisVerticalIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  event: {
    type: Object,
    required: true
  },
  eventDays: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    default: () => ({})
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

// State
const processing = ref(false)
const showActionsMenu = ref(null)
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
  { label: 'Günler', href: null }
])

const visiblePages = computed(() => {
  const current = props.eventDays.current_page
  const total = props.eventDays.last_page
  const delta = 2
  
  const range = []
  const rangeWithDots = []
  
  for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
    range.push(i)
  }
  
  if (current - delta > 2) {
    rangeWithDots.push(1, '...')
  } else {
    rangeWithDots.push(1)
  }
  
  rangeWithDots.push(...range)
  
  if (current + delta < total - 1) {
    rangeWithDots.push('...', total)
  } else {
    rangeWithDots.push(total)
  }
  
  return rangeWithDots.filter((item, index, array) => array.indexOf(item) === index)
})

// Methods
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatDayName = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    weekday: 'long'
  })
}

const toggleActionsMenu = (dayId) => {
  showActionsMenu.value = showActionsMenu.value === dayId ? null : dayId
}

const autoCreateDays = () => {
  processing.value = true
  router.post(route('admin.events.days.auto-create', props.event.slug), {}, {
    onFinish: () => processing.value = false,
    onError: () => {
      alert('Otomatik gün oluşturma sırasında bir hata oluştu.')
    }
  })
}

const manageDayProgram = (day) => {
  // Navigate to program management for specific day
  router.visit(route('admin.events.days.show', [props.event.slug, day.id]))
}

const duplicateDay = (day) => {
  showActionsMenu.value = null
  router.post(route('admin.events.days.duplicate', [props.event.slug, day.id]), {}, {
    onError: () => {
      alert('Gün kopyalama sırasında bir hata oluştu.')
    }
  })
}

const toggleDayStatus = (day) => {
  showActionsMenu.value = null
  router.patch(route('admin.events.days.toggle-status', [props.event.slug, day.id]), {}, {
    onError: () => {
      alert('Durum değiştirme sırasında bir hata oluştu.')
    }
  })
}

const deleteDay = (day) => {
  showActionsMenu.value = null
  confirmDialog.value = {
    show: true,
    title: 'Günü Sil',
    message: `"${day.title}" gününü silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
    type: 'danger',
    callback: () => {
      router.delete(route('admin.events.days.destroy', [props.event.slug, day.id]), {
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

const goToPage = (page) => {
  if (page >= 1 && page <= props.eventDays.last_page) {
    router.get(route('admin.events.days.index', props.event.slug), {
      ...props.filters,
      page: page
    }, {
      preserveState: true,
      preserveScroll: true
    })
  }
}

// Click outside handler
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showActionsMenu.value = null
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>