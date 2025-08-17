<!-- resources/js/Pages/Admin/Presentations/Show.vue -->
<template>
  <AdminLayout
    :page-title="cleanTitle"
    page-subtitle="Sunum detayları ve bilgileri"
    :breadcrumbs="breadcrumbs"
    :full-width="true"
  >
    <Head :title="cleanTitle" />

    <!-- Full Screen Container -->
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 -m-6 p-6">
      <!-- Header Section -->
      <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6">
        <div class="px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ presentation.title }}
              </h1>
              <div class="flex items-center space-x-4 mt-2">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                  :class="getPresentationTypeClasses(presentation.presentation_type)"
                >
                  <component :is="getPresentationIcon(presentation.presentation_type)" class="h-4 w-4 mr-2" />
                  {{ getPresentationTypeLabel(presentation.presentation_type) }}
                </span>
                <span v-if="presentation.language" class="text-sm text-gray-500 dark:text-gray-400">
                  {{ getLanguageLabel(presentation.language) }}
                </span>
                <span v-if="presentation.duration_minutes" class="text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDuration(presentation.duration_minutes) }}
                </span>
              </div>
            </div>
            
            <!-- Header Actions -->
            <div class="flex items-center space-x-3">
              <Link
                :href="route('admin.presentations.edit', presentation.id)"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors"
              >
                <PencilIcon class="h-4 w-4 mr-2" />
                Düzenle
              </Link>
              
              <button
                @click="duplicatePresentation"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors"
              >
                <DocumentDuplicateIcon class="h-4 w-4 mr-2" />
                Kopyala
              </button>

              <Link
                :href="route('admin.presentations.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                Geri Dön
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <!-- Left Column - Main Content (3/4 width on xl screens) -->
        <div class="xl:col-span-3 space-y-6">
          <!-- Abstract Card -->
          <div v-if="presentation.abstract" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Özet
              </h2>
            </div>
            <div class="p-8">
              <div class="prose dark:prose-invert max-w-none">
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                  {{ presentation.abstract }}
                </p>
              </div>
            </div>
          </div>

          <!-- Session Information Card -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Program Oturumu
              </h2>
            </div>
            
            <div class="p-8">
              <div v-if="presentation.programSession" class="space-y-6">
                <!-- Session Title -->
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ presentation.programSession.title }}
                  </h3>
                </div>

                <!-- Event and Venue Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                      Etkinlik
                    </label>
                    <p class="text-gray-900 dark:text-white">
                      {{ presentation.programSession.venue?.eventDay?.event?.name || 'Belirtilmemiş' }}
                    </p>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                      Salon
                    </label>
                    <p class="text-gray-900 dark:text-white">
                      {{ presentation.programSession.venue?.display_name || 'Belirtilmemiş' }}
                    </p>
                  </div>
                </div>

                <!-- Session Time -->
                <div v-if="presentation.programSession.start_time">
                  <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                    Oturum Zamanı
                  </label>
                  <div class="flex items-center text-gray-900 dark:text-white">
                    <ClockIcon class="h-5 w-5 mr-2 text-gray-400" />
                    {{ formatTimeRange(presentation.programSession) }}
                  </div>
                </div>

                <!-- Session Categories -->
                <div v-if="presentation.programSession.categories && presentation.programSession.categories.length > 0">
                  <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                    Kategoriler
                  </label>
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="category in presentation.programSession.categories"
                      :key="category.id"
                      class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                    >
                      {{ category.name }}
                    </span>
                  </div>
                </div>
              </div>

              <div v-else class="text-gray-500 dark:text-gray-400 italic">
                Program oturumu bilgisi bulunamadı
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="presentation.notes" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Notlar
              </h2>
            </div>
            <div class="p-8">
              <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                {{ presentation.notes }}
              </p>
            </div>
          </div>
        </div>

        <!-- Right Column - Sidebar (1/4 width on xl screens) -->
        <div class="xl:col-span-1 space-y-6">
          <!-- Speakers -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Konuşmacılar
              </h2>
            </div>
            
            <div class="p-6">
              <div v-if="presentation.speakers && presentation.speakers.length > 0" class="space-y-4">
                <div
                  v-for="speaker in presentation.speakers"
                  :key="speaker.id"
                  class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                >
                  <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-600 flex items-center justify-center">
                    <UserIcon class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                      {{ speaker.first_name }} {{ speaker.last_name }}
                    </h4>
                    <p v-if="speaker.pivot?.speaker_role" class="text-xs text-gray-500 dark:text-gray-400">
                      {{ getSpeakerRoleLabel(speaker.pivot.speaker_role) }}
                    </p>
                    <p v-if="speaker.email" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      {{ speaker.email }}
                    </p>
                    <p v-if="speaker.affiliation" class="text-xs text-gray-500 dark:text-gray-400">
                      {{ speaker.affiliation }}
                    </p>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-6">
                <UserGroupIcon class="h-12 w-12 text-gray-400 mx-auto mb-3" />
                <p class="text-gray-500 dark:text-gray-400">
                  Henüz konuşmacı atanmamış
                </p>
              </div>
            </div>
          </div>

          <!-- Technical Details -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Teknik Detaylar
              </h2>
            </div>
            
            <div class="p-6 space-y-3">
              <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</span>
                <span class="text-sm text-gray-900 dark:text-white">#{{ presentation.id }}</span>
              </div>
              
              <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Oluşturulma</span>
                <span class="text-sm text-gray-900 dark:text-white">
                  {{ formatDate(presentation.created_at) }}
                </span>
              </div>
              
              <div v-if="presentation.updated_at !== presentation.created_at" class="flex justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Son Güncelleme</span>
                <span class="text-sm text-gray-900 dark:text-white">
                  {{ formatDate(presentation.updated_at) }}
                </span>
              </div>
              
              <div v-if="presentation.sort_order" class="flex justify-between">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Sıra</span>
                <span class="text-sm text-gray-900 dark:text-white">{{ presentation.sort_order }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Hızlı İşlemler
              </h2>
            </div>
            
            <div class="p-6 space-y-3">
              <Link
                :href="route('admin.presentations.edit', presentation.id)"
                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors"
              >
                <PencilIcon class="h-4 w-4 mr-2" />
                Sunumu Düzenle
              </Link>
              
              <button
                @click="duplicatePresentation"
                class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors"
              >
                <DocumentDuplicateIcon class="h-4 w-4 mr-2" />
                Kopyala
              </button>
              
              <button
                @click="deletePresentation"
                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors"
              >
                <TrashIcon class="h-4 w-4 mr-2" />
                Sil
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Sunumu Sil
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          "{{ presentation.title }}" sunumunu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.
        </p>
        <div class="flex items-center justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            İptal
          </button>
          <button
            @click="confirmDelete"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg"
          >
            Sil
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  PencilIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  UserIcon,
  UserGroupIcon,
  ClockIcon,
  ArrowLeftIcon,
  StarIcon,
  MicrophoneIcon,
  AcademicCapIcon,
  PresentationChartLineIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  presentation: {
    type: Object,
    required: true
  }
})

// State
const showDeleteModal = ref(false)

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Sunumlar', href: route('admin.presentations.index') },
  { label: props.presentation.title, href: null }
])

const cleanTitle = computed(() => {
  return props.presentation.title || 'Sunum'
})

// Methods
const getPresentationTypeClasses = (presentationType) => {
  const classes = {
    keynote: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    oral: 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-200',
    poster: 'bg-gray-300 text-gray-900 dark:bg-gray-600 dark:text-gray-200',
    panel: 'bg-gray-400 text-white dark:bg-gray-500 dark:text-gray-200',
    workshop: 'bg-gray-500 text-white dark:bg-gray-400 dark:text-gray-900'
  }
  return classes[presentationType] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const getPresentationIcon = (presentationType) => {
  const icons = {
    keynote: StarIcon,
    oral: MicrophoneIcon,
    poster: PresentationChartLineIcon,
    panel: UserGroupIcon,
    workshop: AcademicCapIcon
  }
  return icons[presentationType] || DocumentTextIcon
}

const getPresentationTypeLabel = (presentationType) => {
  const labels = {
    keynote: 'Keynote',
    oral: 'Sözlü Bildiri',
    poster: 'Poster',
    panel: 'Panel',
    workshop: 'Workshop'
  }
  return labels[presentationType] || presentationType || 'Belirtilmemiş'
}

const getSpeakerRoleLabel = (role) => {
  const labels = {
    primary: 'Ana Konuşmacı',
    secondary: 'Ko-Konuşmacı',
    moderator: 'Moderatör'
  }
  return labels[role] || role || ''
}

const getLanguageLabel = (language) => {
  const labels = {
    tr: 'Türkçe',
    en: 'İngilizce',
    de: 'Almanca',
    fr: 'Fransızca'
  }
  return labels[language] || language
}

const formatDuration = (minutes) => {
  if (!minutes) return ''
  if (minutes < 60) return `${minutes} dakika`
  const hours = Math.floor(minutes / 60)
  const remainingMinutes = minutes % 60
  return remainingMinutes > 0 ? `${hours} saat ${remainingMinutes} dakika` : `${hours} saat`
}

const formatTimeRange = (session) => {
  if (!session?.start_time || !session?.end_time) return 'Zaman belirtilmemiş'
  
  try {
    const start = session.start_time.substring(0, 5) // HH:MM
    const end = session.end_time.substring(0, 5) // HH:MM
    return `${start} - ${end}`
  } catch (error) {
    return 'Zaman belirsiz'
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  try {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    return dateString
  }
}

const duplicatePresentation = () => {
  router.post(route('admin.presentations.duplicate', props.presentation.id), {}, {
    onSuccess: () => {
      // Redirect handled by controller
    }
  })
}

const deletePresentation = () => {
  showDeleteModal.value = true
}

const confirmDelete = () => {
  router.delete(route('admin.presentations.destroy', props.presentation.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      // Redirect handled by controller
    }
  })
}
</script>

<style scoped>
.prose {
  max-width: none;
}

.prose p {
  margin-bottom: 1rem;
}

.prose:last-child p {
  margin-bottom: 0;
}
</style>