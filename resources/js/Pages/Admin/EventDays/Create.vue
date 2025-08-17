<!-- Admin/EventDays/Create.vue - Premium Event Day Creation -->
<template>
  <AdminLayout
    page-title="Yeni Gün Ekle"
    :page-subtitle="`${event.name} etkinliği için yeni gün oluşturun`"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Yeni Gün Ekle" />

    <div class="w-full">
      <!-- Header Section -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-10 w-10 bg-purple-600 rounded-lg flex items-center justify-center">
                <CalendarIcon class="h-6 w-6 text-white" />
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Yeni Etkinlik Günü</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.name }} - {{ formatEventDateRange(event.start_date, event.end_date) }}</p>
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

          <!-- Date Selection -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                  :min="event.start_date"
                  :max="event.end_date"
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
          </div>

          <!-- Status Options -->
          <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 space-y-4">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Durum Ayarları</h4>
            
            <div class="flex items-start space-x-3">
              <label class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded transition-colors"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                  Günü aktif yap
                </span>
              </label>
            </div>
            
            <div class="text-xs text-gray-500 dark:text-gray-400">
              <p>• Aktif günler program oluşturma için kullanılabilir</p>
              <p>• Pasif günler gizli kalır ve programa dahil edilmez</p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3">
              <Link
                :href="route('admin.events.days.index', event.slug)"
                class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                İptal
              </Link>
            </div>

            <div class="flex space-x-3">
              <!-- Save as Inactive -->
              <button
                type="button"
                @click="submitForm(false)"
                :disabled="form.processing"
                class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <EyeSlashIcon class="h-4 w-4 mr-2" />
                Pasif Kaydet
              </button>

              <!-- Save as Active -->
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-6 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <template v-if="form.processing">
                  <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  Oluşturuluyor...
                </template>
                <template v-else>
                  <CalendarIcon class="h-4 w-4 mr-2" />
                  Gün Oluştur
                </template>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Hızlı İpuçları -->
      <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-800">
        <div class="flex items-start">
          <InformationCircleIcon class="h-5 w-5 text-purple-600 dark:text-purple-400 mt-0.5 flex-shrink-0" />
          <div class="ml-3">
            <h4 class="text-sm font-medium text-purple-900 dark:text-purple-100">Gün Oluşturma İpuçları</h4>
            <div class="mt-2 text-sm text-purple-700 dark:text-purple-200">
              <ul class="list-disc list-inside space-y-1">
                <li>Gün başlığını açık ve anlaşılır tutun</li>
                <li>Tarih etkinlik tarih aralığında olmalıdır</li>
                <li>Sıra numarası günlerin görüntülenme sırasını belirler</li>
                <li>Pasif günler program oluşturmada görünmez</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  CalendarIcon,
  CalendarDaysIcon,
  ArrowLeftIcon,
  EyeSlashIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  event: {
    type: Object,
    required: true
  }
})

// Form - Gerçek veritabanı yapısına göre
const form = useForm({
  display_name: '',  // Zorunlu alan
  date: '',
  sort_order: 0,
  is_active: true
})

// Computed
const breadcrumbs = computed(() => [
  { label: 'Etkinlikler', href: route('admin.events.index') },
  { label: props.event.name, href: route('admin.events.show', props.event.slug) },
  { label: 'Günler', href: route('admin.events.days.index', props.event.slug) },
  { label: 'Yeni Gün', href: null }
])

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

const formatEventDateRange = (startDate, endDate) => {
  if (!startDate) return '-'
  const start = formatDate(startDate)
  if (!endDate || startDate === endDate) return start
  return `${start} - ${formatDate(endDate)}`
}

const getDayOfWeek = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    weekday: 'long'
  })
}

// Fixed submitForm method
const submitForm = (active = true) => {
  // Use transform to ensure proper boolean conversion
  form.transform((data) => ({
    ...data,
    is_active: Boolean(active)
  })).post(route('admin.events.days.store', props.event.slug), {
    onSuccess: () => {
      // Handle success in controller redirect
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors)
    }
  })
}
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