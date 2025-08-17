<!-- Admin/EventDays/Edit.vue - Premium Event Day Edit -->
<template>
  <AdminLayout
    :page-title="`${eventDay.display_name || 'Etkinlik Günü'} - Düzenle`"
    :page-subtitle="`${event.name} etkinliği günü düzenleyin`"
    :breadcrumbs="breadcrumbs"
  >
    <Head :title="`${eventDay.display_name || 'Etkinlik Günü'} - Düzenle`" />

    <div class="w-full">
      <!-- Header Section -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-purple-600 rounded-lg flex items-center justify-center">
                  <PencilSquareIcon class="h-6 w-6 text-white" />
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Etkinlik Günü Düzenle</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ eventDay.display_name || 'Başlıksız Gün' }} - {{ formatDate(eventDay.date) }}</p>
              </div>
            </div>
            
            <!-- Status Badge -->
            <div class="flex items-center space-x-3">
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                :class="eventDay.is_active 
                  ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                  : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'"
              >
                <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-current opacity-75"></span>
                {{ eventDay.is_active ? 'Aktif' : 'Pasif' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Form Section -->
        <form @submit.prevent="submitForm" class="p-6 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Display Name -->
            <div class="lg:col-span-2">
              <label for="display_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Gün Başlığı *
              </label>
              <input
                id="display_name"
                v-model="form.display_name"
                type="text"
                required
                class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                :class="form.errors.display_name ? 'border-red-300 focus:ring-red-500' : ''"
                placeholder="Örn: Kongre 1. Gün"
              />
              <p v-if="form.errors.display_name" class="mt-2 text-sm text-red-600">{{ form.errors.display_name }}</p>
            </div>

            <!-- Sort Order -->
            <div>
              <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Sıra No
              </label>
              <input
                id="sort_order"
                v-model.number="form.sort_order"
                type="number"
                min="0"
                class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                :class="form.errors.sort_order ? 'border-red-300 focus:ring-red-500' : ''"
                placeholder="0"
              />
              <p v-if="form.errors.sort_order" class="mt-2 text-sm text-red-600">{{ form.errors.sort_order }}</p>
            </div>
          </div>

          <!-- Date and Status -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Date -->
            <div>
              <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tarih *
              </label>
              <div class="relative">
                <CalendarDaysIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input
                  id="date"
                  v-model="form.date"
                  type="date"
                  required
                  class="block w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                  :class="form.errors.date ? 'border-red-300 focus:ring-red-500' : ''"
                  :min="formatDateForInput(event.start_date)"
                  :max="formatDateForInput(event.end_date)"
                />
              </div>
              <p v-if="form.errors.date" class="mt-2 text-sm text-red-600">{{ form.errors.date }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Tarih {{ formatDate(event.start_date) }} - {{ formatDate(event.end_date) }} arasında olmalıdır
              </p>
            </div>

            <!-- Day Preview -->
            <div v-if="form.date">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tarih Önizleme
              </label>
              <div class="px-4 py-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                <div class="flex items-center text-purple-700 dark:text-purple-300">
                  <CalendarIcon class="h-5 w-5 mr-2" />
                  <div>
                    <div class="font-semibold">{{ formatFullDate(form.date) }}</div>
                    <div class="text-sm opacity-75">{{ getDayOfWeek(form.date) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Status Card -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Durum
              </label>
              <div class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                <label class="flex items-center">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded transition-colors"
                  />
                  <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Günü aktif tut
                  </span>
                </label>
              </div>
            </div>
          </div>

          <!-- Change Summary -->
          <div v-if="hasChanges" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start">
              <ExclamationTriangleIcon class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
              <div class="ml-3">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Değişiklikler Tespit Edildi</h4>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-200">
                  <p>Aşağıdaki alanlar değiştirildi:</p>
                  <ul class="list-disc list-inside mt-1 space-y-1">
                    <li v-if="form.display_name !== originalForm.display_name">Gün başlığı</li>
                    <li v-if="form.date !== originalForm.date">Tarih</li>
                    <li v-if="form.sort_order !== originalForm.sort_order">Sıra numarası</li>
                    <li v-if="form.is_active !== originalForm.is_active">Durum</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3">
              <Link
                :href="route('admin.events.days.show', [event.slug, eventDay.id])"
                class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                Geri Dön
              </Link>
              
              <Link
                :href="route('admin.events.days.index', event.slug)"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
              >
                Listeye Dön
              </Link>
            </div>

            <div class="flex space-x-3">
              <!-- Reset Changes -->
              <button
                type="button"
                @click="resetForm"
                :disabled="form.processing || !hasChanges"
                class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <ArrowPathIcon class="h-4 w-4 mr-2" />
                Sıfırla
              </button>

              <!-- Update Day -->
              <button
                type="submit"
                :disabled="form.processing || !hasChanges"
                class="inline-flex items-center px-6 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <template v-if="form.processing">
                  <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  Güncelleniyor...
                </template>
                <template v-else>
                  <CheckIcon class="h-4 w-4 mr-2" />
                  Değişiklikleri Kaydet
                </template>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Warning for Sessions -->
      <div v-if="eventDay.program_sessions_count > 0" class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-6 border border-yellow-200 dark:border-yellow-800">
        <div class="flex items-start">
          <ExclamationTriangleIcon class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" />
          <div class="ml-3">
            <h4 class="text-sm font-medium text-yellow-900 dark:text-yellow-100">Dikkat: Program Oturumları Mevcut</h4>
            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-200">
              <p>Bu günde {{ eventDay.program_sessions_count }} adet program oturumu bulunmaktadır. Tarih değişikliği yaparsanız:</p>
              <ul class="list-disc list-inside mt-2 space-y-1">
                <li>Mevcut oturumlar yeni tarihe taşınacaktır</li>
                <li>Katılımcı bilgilendirmeleri gerekli olabilir</li>
                <li>Program çizelgesi güncellenmesi gerekebilir</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  PencilSquareIcon,
  CalendarDaysIcon,
  CalendarIcon,
  ArrowLeftIcon,
  CheckIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon
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
  }
})

// Helper function - önce tanımla
const formatDateForInput = (date) => {
  if (!date) return ''
  // Date input için YYYY-MM-DD formatında döndür
  const d = new Date(date)
  return d.toISOString().split('T')[0]
}

// Form - Gerçek veritabanı yapısına uygun
const form = useForm({
  display_name: props.eventDay.display_name || '',
  date: formatDateForInput(props.eventDay.date) || '',
  sort_order: props.eventDay.sort_order || 0,
  is_active: props.eventDay.is_active || false
})

// Store original values for comparison
const originalForm = ref({
  display_name: props.eventDay.display_name || '',
  date: formatDateForInput(props.eventDay.date) || '',
  sort_order: props.eventDay.sort_order || 0,
  is_active: props.eventDay.is_active || false
})

// Computed
const breadcrumbs = computed(() => [
  { label: 'Etkinlikler', href: route('admin.events.index') },
  { label: props.event.name, href: route('admin.events.show', props.event.slug) },
  { label: 'Günler', href: route('admin.events.days.index', props.event.slug) },
  { label: props.eventDay.display_name || 'Etkinlik Günü', href: route('admin.events.days.show', [props.event.slug, props.eventDay.id]) },
  { label: 'Düzenle', href: null }
])

const hasChanges = computed(() => {
  return (
    form.display_name !== originalForm.value.display_name ||
    form.date !== originalForm.value.date ||
    form.sort_order !== originalForm.value.sort_order ||
    form.is_active !== originalForm.value.is_active
  )
})

// Methods
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

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

const submitForm = () => {
  // Transform ile boolean conversion
  form.transform((data) => ({
    ...data,
    is_active: Boolean(data.is_active)
  })).put(route('admin.events.days.update', [props.event.slug, props.eventDay.id]), {
    onSuccess: () => {
      // Update original form values
      originalForm.value = {
        display_name: form.display_name,
        date: form.date,
        sort_order: form.sort_order,
        is_active: form.is_active
      }
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors)
    }
  })
}

const resetForm = () => {
  form.display_name = originalForm.value.display_name
  form.date = originalForm.value.date
  form.sort_order = originalForm.value.sort_order
  form.is_active = originalForm.value.is_active
}

// Warn user about unsaved changes
let isSubmitting = false

watch(() => form.data(), () => {
  // Add beforeunload listener if there are changes
  if (hasChanges.value && !isSubmitting) {
    window.addEventListener('beforeunload', handleBeforeUnload)
  } else {
    window.removeEventListener('beforeunload', handleBeforeUnload)
  }
}, { deep: true })

const handleBeforeUnload = (e) => {
  if (hasChanges.value && !isSubmitting) {
    e.preventDefault()
    e.returnValue = ''
  }
}

// Remove listener before form submission
watch(() => form.processing, (newVal) => {
  if (newVal) {
    isSubmitting = true
    window.removeEventListener('beforeunload', handleBeforeUnload)
  } else {
    isSubmitting = false
  }
})

onMounted(() => {
  console.log('Edit component mounted:', {
    eventDay: props.eventDay,
    form: form.data(),
    originalForm: originalForm.value
  })
})
</script>

<style scoped>
/* Enhanced form styling */
.form-input {
  transition: all 0.2s ease;
}

.form-input:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.form-input:focus {
  box-shadow: 
    0 0 0 2px rgb(147 51 234 / 0.1),
    0 0 0 4px rgb(147 51 234 / 0.1),
    0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Dark mode enhancements */
.dark .form-input:focus {
  box-shadow: 
    0 0 0 2px rgb(196 181 253 / 0.2),
    0 0 0 4px rgb(196 181 253 / 0.1),
    0 4px 6px -1px rgba(0, 0, 0, 0.3);
}
</style>