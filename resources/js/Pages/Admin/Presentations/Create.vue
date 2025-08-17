<!-- resources/js/Pages/Admin/Presentations/Create.vue -->
<template>
  <AdminLayout
    page-title="Yeni Sunum Oluştur"
    page-subtitle="Etkinliğinize yeni bir sunum ekleyin"
    :breadcrumbs="breadcrumbs"
    :full-width="true"
  >
    <Head title="Yeni Sunum Oluştur" />

    <!-- Full Screen Container -->
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 -m-6 p-6">
      <!-- Header Section -->
      <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6">
        <div class="px-8 py-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Yeni Sunum Oluştur
              </h1>
              <p class="text-gray-600 dark:text-gray-400 mt-2">
                Etkinliğinize yeni bir sunum ekleyin ve konuşmacıları atayın
              </p>
            </div>
            
            <!-- Header Actions -->
            <div class="flex items-center space-x-3">
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
        <!-- Left Column - Form (3/4 width on xl screens) -->
        <div class="xl:col-span-3">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
              <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                  Temel Bilgiler
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                  Sunumun başlık, özet ve temel özelliklerini belirleyin
                </p>
              </div>
              
              <div class="p-8 space-y-6">
                <!-- Program Session Selection -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Program Oturumu <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.program_session_id"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                    :class="{ 'border-red-500': form.errors.program_session_id }"
                  >
                    <option value="">Oturum Seçin</option>
                    <optgroup 
                      v-for="event in groupedSessions" 
                      :key="event.id" 
                      :label="event.name"
                    >
                      <option 
                        v-for="session in event.sessions" 
                        :key="session.id" 
                        :value="session.id"
                      >
                        {{ session.title }} - {{ session.venue?.display_name }} ({{ formatTime(session.start_time) }})
                      </option>
                    </optgroup>
                  </select>
                  <p v-if="form.errors.program_session_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.program_session_id }}
                  </p>
                </div>

                <!-- Title -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Sunum Başlığı <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.title"
                    type="text"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 text-lg"
                    :class="{ 'border-red-500': form.errors.title }"
                    placeholder="Sunum başlığını girin"
                    maxlength="500"
                  />
                  <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                    {{ form.errors.title }}
                  </p>
                </div>

                <!-- Abstract -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Özet
                  </label>
                  <textarea
                    v-model="form.abstract"
                    rows="6"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                    :class="{ 'border-red-500': form.errors.abstract }"
                    placeholder="Sunum özetini girin"
                    maxlength="5000"
                  ></textarea>
                  <p v-if="form.errors.abstract" class="text-red-500 text-sm mt-1">
                    {{ form.errors.abstract }}
                  </p>
                  <p class="text-gray-500 text-sm mt-1">
                    {{ form.abstract?.length || 0 }}/5000 karakter
                  </p>
                </div>
              </div>
            </div>

            <!-- Details Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
              <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                  Detaylar
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                  Sunum türü, zamanlama ve dil bilgilerini belirleyin
                </p>
              </div>
              
              <div class="p-8 space-y-6">
                <!-- First Row: Type, Start Time, End Time -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <!-- Presentation Type -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Sunum Türü
                    </label>
                    <select
                      v-model="form.presentation_type"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                      :class="{ 'border-red-500': form.errors.presentation_type }"
                    >
                      <option value="">Tür Seçin</option>
                      <option value="keynote">Keynote</option>
                      <option value="oral">Sözlü Bildiri</option>
                      <option value="poster">Poster</option>
                      <option value="panel">Panel</option>
                      <option value="workshop">Workshop</option>
                    </select>
                    <p v-if="form.errors.presentation_type" class="text-red-500 text-sm mt-1">
                      {{ form.errors.presentation_type }}
                    </p>
                  </div>

                  <!-- Start Time -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Başlangıç Saati
                    </label>
                    <input
                      v-model="form.start_time"
                      type="time"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                      :class="{ 'border-red-500': form.errors.start_time }"
                    />
                    <p v-if="form.errors.start_time" class="text-red-500 text-sm mt-1">
                      {{ form.errors.start_time }}
                    </p>
                  </div>

                  <!-- End Time -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Bitiş Saati
                    </label>
                    <input
                      v-model="form.end_time"
                      type="time"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                      :class="{ 'border-red-500': form.errors.end_time }"
                    />
                    <p v-if="form.errors.end_time" class="text-red-500 text-sm mt-1">
                      {{ form.errors.end_time }}
                    </p>
                  </div>
                </div>

                <!-- Second Row: Duration and Language -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Duration -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Süre (Dakika)
                    </label>
                    <input
                      v-model.number="form.duration_minutes"
                      type="number"
                      min="1"
                      max="480"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                      :class="{ 'border-red-500': form.errors.duration_minutes }"
                      placeholder="60"
                    />
                    <p v-if="form.errors.duration_minutes" class="text-red-500 text-sm mt-1">
                      {{ form.errors.duration_minutes }}
                    </p>
                  </div>

                  <!-- Language -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Dil
                    </label>
                    <select
                      v-model="form.language"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                      :class="{ 'border-red-500': form.errors.language }"
                    >
                      <option value="">Dil Seçin</option>
                      <option value="tr">Türkçe</option>
                      <option value="en">İngilizce</option>
                      <option value="de">Almanca</option>
                      <option value="fr">Fransızca</option>
                    </select>
                    <p v-if="form.errors.language" class="text-red-500 text-sm mt-1">
                      {{ form.errors.language }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Speakers Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
              <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                  <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                      Konuşmacılar
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                      Sunumda yer alacak konuşmacıları ekleyin ve rollerini belirleyin
                    </p>
                  </div>
                  <button
                    type="button"
                    @click="addSpeaker"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors"
                  >
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Konuşmacı Ekle
                  </button>
                </div>
              </div>
              
              <div class="p-8">
                <!-- Speaker List -->
                <div v-if="form.speakers.length === 0" class="text-center py-12 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                  <UserGroupIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Henüz konuşmacı eklenmedi
                  </h3>
                  <p class="text-gray-500 dark:text-gray-400">
                    Sunuma konuşmacı eklemek için yukarıdaki butonu kullanın
                  </p>
                </div>

                <div v-else class="space-y-4">
                  <div
                    v-for="(speaker, index) in form.speakers"
                    :key="index"
                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600"
                  >
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                      <!-- Participant Selection -->
                      <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                          Katılımcı
                        </label>
                        <select
                          v-model="speaker.participant_id"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                          :class="{ 'border-red-500': form.errors[`speakers.${index}.participant_id`] }"
                        >
                          <option value="">Katılımcı Seçin</option>
                          <option 
                            v-for="participant in participants" 
                            :key="participant.id" 
                            :value="participant.id"
                          >
                            {{ participant.first_name }} {{ participant.last_name }}
                          </option>
                        </select>
                        <p v-if="form.errors[`speakers.${index}.participant_id`]" class="text-red-500 text-xs mt-1">
                          {{ form.errors[`speakers.${index}.participant_id`] }}
                        </p>
                      </div>

                      <!-- Role Selection -->
                      <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                          Rol
                        </label>
                        <select
                          v-model="speaker.role"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                          :class="{ 'border-red-500': form.errors[`speakers.${index}.role`] }"
                        >
                          <option value="primary">Ana Konuşmacı</option>
                          <option value="secondary">Ko-Konuşmacı</option>
                          <option value="moderator">Moderatör</option>
                        </select>
                        <p v-if="form.errors[`speakers.${index}.role`]" class="text-red-500 text-xs mt-1">
                          {{ form.errors[`speakers.${index}.role`] }}
                        </p>
                      </div>

                      <!-- Actions -->
                      <div class="flex items-end">
                        <button
                          type="button"
                          @click="removeSpeaker(index)"
                          class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors inline-flex items-center justify-center"
                        >
                          <TrashIcon class="h-4 w-4 mr-2" />
                          Kaldır
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
              <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                  Notlar
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                  İç notlar ve ek bilgiler ekleyin (opsiyonel)
                </p>
              </div>
              
              <div class="p-8">
                <textarea
                  v-model="form.notes"
                  rows="4"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                  :class="{ 'border-red-500': form.errors.notes }"
                  placeholder="İç notlar (opsiyonel)"
                  maxlength="1000"
                ></textarea>
                <p v-if="form.errors.notes" class="text-red-500 text-sm mt-1">
                  {{ form.errors.notes }}
                </p>
                <p class="text-gray-500 text-sm mt-2">
                  {{ form.notes?.length || 0 }}/1000 karakter
                </p>
              </div>
            </div>
          </form>
        </div>

        <!-- Right Column - Sidebar (1/4 width on xl screens) -->
        <div class="xl:col-span-1 space-y-6">
          <!-- Action Buttons -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              İşlemler
            </h3>
            
            <div class="space-y-3">
              <button
                @click="submit"
                :disabled="form.processing"
                class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-600 hover:bg-gray-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition-colors"
              >
                <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                <PlusIcon v-else class="h-4 w-4 mr-2" />
                {{ form.processing ? 'Oluşturuluyor...' : 'Sunumu Oluştur' }}
              </button>
              
              <Link
                :href="route('admin.presentations.index')"
                class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                İptal
              </Link>
            </div>
          </div>

          <!-- Quick Info -->
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Hızlı Bilgiler
            </h3>
            
            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Konuşmacı Sayısı</span>
                <span class="text-gray-900 dark:text-white font-medium">{{ form.speakers.length }}</span>
              </div>
              
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Karakter Sayısı</span>
                <span class="text-gray-900 dark:text-white font-medium">{{ (form.abstract || '').length }}/5000</span>
              </div>

              <div v-if="form.start_time && form.end_time" class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Süre</span>
                <span class="text-gray-900 dark:text-white font-medium">{{ calculateDuration() }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Tips -->
          <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-gray-600 rounded-lg flex items-center justify-center">
                  <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                  Sunum Oluşturma İpuçları
                </h3>
              </div>
            </div>
            <div class="mt-4">
              <div class="space-y-4">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 w-2 h-2 bg-gray-400 rounded-full mt-2"></div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">
                    Sunum başlığını açık ve anlaşılır tutun
                  </p>
                </div>
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 w-2 h-2 bg-gray-400 rounded-full mt-2"></div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">
                    Özet kısmında sunumun ana noktalarını belirtin
                  </p>
                </div>
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 w-2 h-2 bg-gray-400 rounded-full mt-2"></div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">
                    Konuşmacı rollerini doğru atayın
                  </p>
                </div>
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 w-2 h-2 bg-gray-400 rounded-full mt-2"></div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">
                    Başlangıç ve bitiş saatlerini kontrol edin
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  PlusIcon,
  TrashIcon,
  UserGroupIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  programSessions: {
    type: Array,
    default: () => []
  },
  participants: {
    type: Array,
    default: () => []
  },
  preselectedSession: {
    type: Object,
    default: null
  }
})

// Form
const form = useForm({
  program_session_id: props.preselectedSession?.id || '',
  title: '',
  abstract: '',
  start_time: '',
  end_time: '',
  duration_minutes: null,
  presentation_type: '',
  language: '',
  notes: '',
  sort_order: 0,
  speakers: []
})

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Sunumlar', href: route('admin.presentations.index') },
  { label: 'Yeni Sunum', href: null }
])

const groupedSessions = computed(() => {
  const grouped = {}
  
  props.programSessions.forEach(session => {
    const event = session.venue?.eventDay?.event
    if (!event) return
    
    if (!grouped[event.id]) {
      grouped[event.id] = {
        id: event.id,
        name: event.name,
        sessions: []
      }
    }
    
    grouped[event.id].sessions.push(session)
  })
  
  return Object.values(grouped)
})

// Methods
const formatTime = (timeString) => {
  if (!timeString) return ''
  try {
    return timeString.substring(0, 5) // HH:MM format
  } catch (error) {
    return timeString
  }
}

const calculateDuration = () => {
  if (!form.start_time || !form.end_time) return ''
  
  try {
    const [startHour, startMinute] = form.start_time.split(':').map(Number)
    const [endHour, endMinute] = form.end_time.split(':').map(Number)
    
    const startTotalMinutes = startHour * 60 + startMinute
    const endTotalMinutes = endHour * 60 + endMinute
    
    if (endTotalMinutes > startTotalMinutes) {
      const diffMinutes = endTotalMinutes - startTotalMinutes
      const hours = Math.floor(diffMinutes / 60)
      const minutes = diffMinutes % 60
      
      if (hours > 0) {
        return `${hours}s ${minutes}dk`
      } else {
        return `${minutes}dk`
      }
    }
    
    return ''
  } catch (error) {
    return ''
  }
}

const addSpeaker = () => {
  form.speakers.push({
    participant_id: '',
    role: 'primary',
    sort_order: form.speakers.length
  })
}

const removeSpeaker = (index) => {
  form.speakers.splice(index, 1)
  // Update sort orders
  form.speakers.forEach((speaker, idx) => {
    speaker.sort_order = idx
  })
}

const submit = () => {
  form.post(route('admin.presentations.store'), {
    onSuccess: () => {
      // Redirect handled by controller
    },
    onError: (errors) => {
      console.error('Form errors:', errors)
    }
  })
}
</script>

<style scoped>
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-gray-800;
}

::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-400 dark:bg-gray-500;
}
</style>