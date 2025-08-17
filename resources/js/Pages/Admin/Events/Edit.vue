<template>
  <AdminLayout 
    :page-title="pageTitle" 
    page-subtitle="Akademik etkinlik bilgilerini güncelleyin"
    :breadcrumbs="breadcrumbs">

    <Head :title="pageTitle" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-blue-950 dark:via-indigo-950 dark:to-purple-950 -mx-8 -my-6 px-8 py-6">
      <div class="max-w-7xl mx-auto">
        <!-- Corporate Header Section -->
        <div class="bg-white dark:bg-blue-900 rounded-3xl shadow-2xl border-2 border-blue-200 dark:border-blue-700 overflow-hidden mb-8">
          <!-- Professional Corporate Header with Status -->
          <div class="relative">
            <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-700 px-10 py-12">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                  <div class="flex-shrink-0">
                    <div class="h-24 w-24 bg-white/20 backdrop-blur-sm rounded-3xl flex items-center justify-center ring-4 ring-white/30 shadow-2xl">
                      <PencilSquareIcon class="h-12 w-12 text-white" />
                    </div>
                  </div>
                  <div class="flex-1">
                    <h1 class="text-4xl font-bold text-white mb-4">Bilimsel Etkinlik Düzenle</h1>
                    <p class="text-indigo-100 text-xl font-medium">{{ eventName }}</p>
                  </div>
                </div>

                <!-- Enhanced Corporate Status Badge -->
                <div class="flex items-center space-x-6">
                  <div class="text-right">
                    <div class="text-base text-indigo-200 mb-3 font-semibold">Durum</div>
                    <span class="inline-flex items-center px-8 py-4 rounded-2xl text-lg font-bold" :class="isPublished
                      ? 'bg-green-500/20 text-green-200 ring-3 ring-green-500/40'
                      : 'bg-orange-500/20 text-orange-200 ring-3 ring-orange-500/40'">
                      <span class="w-4 h-4 mr-4 rounded-full bg-current animate-pulse"></span>
                      {{ isPublished ? 'Yayında' : 'Taslak' }}
                    </span>
                  </div>
                </div>
              </div>
              
              <!-- Professional Progress -->
              <div class="mt-10 flex items-center space-x-4">
                <div class="h-3 w-3 bg-blue-300 rounded-full animate-pulse shadow-lg"></div>
                <span class="text-indigo-100 text-lg font-medium">Bilgileri düzenleme aşaması</span>
              </div>
            </div>
            
            <!-- Corporate Border -->
            <div class="h-2 bg-gradient-to-r from-blue-500 via-purple-500 to-green-500"></div>
          </div>

          <!-- Main Form Section -->
          <form @submit.prevent="submitForm" class="p-12 space-y-12">
            <!-- Event Information Section -->
            <div class="space-y-10">
              <div class="flex items-center space-x-6 mb-10">
                <div class="h-12 w-12 bg-blue-100 dark:bg-blue-800 rounded-2xl flex items-center justify-center">
                  <DocumentTextIcon class="h-7 w-7 text-blue-600 dark:text-blue-300" />
                </div>
                <h3 class="text-3xl font-bold text-blue-900 dark:text-blue-100">Etkinlik Bilgileri</h3>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Event Name with Slug Preview -->
                <div class="lg:col-span-2">
                  <label for="name" class="block text-lg font-bold text-blue-900 dark:text-blue-100 mb-4">
                    Etkinlik Adı *
                    <span class="text-base font-normal text-blue-600 ml-2">(Resmi kongre/konferans adı)</span>
                  </label>
                  <input 
                    id="name" 
                    v-model="form.name" 
                    type="text" 
                    required
                    class="block w-full px-6 py-6 border-3 border-blue-300 dark:border-blue-600 rounded-2xl bg-white dark:bg-blue-800 text-blue-900 dark:text-blue-100 placeholder-blue-400 focus:ring-4 focus:ring-blue-500/30 focus:border-blue-500 transition-all duration-300 shadow-lg hover:shadow-xl text-xl font-semibold"
                    :class="errors.name ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500' : ''"
                    placeholder="Örn: 25. Ulusal Pediatri Kongresi" 
                  />
                  <p v-if="errors.name" class="mt-3 text-base text-red-600 flex items-center">
                    <ExclamationCircleIcon class="h-5 w-5 mr-2" />
                    {{ errors.name }}
                  </p>

                  <!-- Enhanced Corporate Slug Preview -->
                  <div v-if="form.name" class="mt-6 p-6 bg-gradient-to-r from-blue-100 to-indigo-100 dark:from-blue-800 dark:to-indigo-800 rounded-2xl border-2 border-blue-300 dark:border-blue-600">
                    <div class="flex items-center text-lg">
                      <LinkIcon class="h-6 w-6 mr-4 text-blue-600" />
                      <span class="text-blue-800 dark:text-blue-200 mr-4 font-bold">URL Adı:</span>
                      <code class="bg-white dark:bg-blue-700 px-4 py-2 rounded-xl text-blue-700 dark:text-blue-200 font-mono text-lg font-semibold">
                        {{ generateSlug(form.name) }}
                      </code>
                    </div>
                  </div>
                </div>

                <!-- Organization -->
                <div class="lg:col-span-1">
                  <label for="organization_id" class="block text-lg font-bold text-blue-900 dark:text-blue-100 mb-4">
                    Organizasyon *
                    <span class="text-base font-normal text-blue-600 ml-2">(Düzenleyen kurum)</span>
                  </label>
                  <select 
                    id="organization_id" 
                    v-model="form.organization_id" 
                    required
                    class="block w-full px-6 py-6 border-3 border-blue-300 dark:border-blue-600 rounded-2xl bg-white dark:bg-blue-800 text-blue-900 dark:text-blue-100 focus:ring-4 focus:ring-blue-500/30 focus:border-blue-500 transition-all duration-300 shadow-lg hover:shadow-xl text-lg font-semibold"
                    :class="errors.organization_id ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500' : ''"
                  >
                    <option value="">Organizasyon Seçiniz</option>
                    <option v-for="org in organizations" :key="org.id" :value="org.id">
                      {{ org.name }}
                    </option>
                  </select>
                  <p v-if="errors.organization_id" class="mt-3 text-base text-red-600 flex items-center">
                    <ExclamationCircleIcon class="h-5 w-5 mr-2" />
                    {{ errors.organization_id }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Date and Location Section -->
            <div class="space-y-10 pt-10 border-t-2 border-blue-200 dark:border-blue-700">
              <div class="flex items-center space-x-6 mb-10">
                <div class="h-12 w-12 bg-purple-100 dark:bg-purple-800 rounded-2xl flex items-center justify-center">
                  <ClockIcon class="h-7 w-7 text-purple-600 dark:text-purple-300" />
                </div>
                <h3 class="text-3xl font-bold text-purple-900 dark:text-purple-100">Tarih ve Konum</h3>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Start Date -->
                <div class="lg:col-span-1">
                  <label for="start_date" class="block text-lg font-bold text-purple-900 dark:text-purple-100 mb-4">
                    Başlangıç Tarihi *
                  </label>
                  <div class="relative">
                    <CalendarDaysIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 h-7 w-7 text-purple-500" />
                    <input 
                      id="start_date" 
                      v-model="form.start_date" 
                      type="date" 
                      required
                      class="block w-full pl-16 pr-6 py-6 border-3 border-purple-300 dark:border-purple-600 rounded-2xl bg-white dark:bg-purple-800 text-purple-900 dark:text-purple-100 focus:ring-4 focus:ring-purple-500/30 focus:border-purple-500 transition-all duration-300 shadow-lg hover:shadow-xl text-lg font-semibold"
                      :class="errors.start_date ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500' : ''" 
                    />
                  </div>
                  <p v-if="errors.start_date" class="mt-3 text-base text-red-600">{{ errors.start_date }}</p>
                </div>

                <!-- End Date -->
                <div class="lg:col-span-1">
                  <label for="end_date" class="block text-lg font-bold text-purple-900 dark:text-purple-100 mb-4">
                    Bitiş Tarihi *
                  </label>
                  <div class="relative">
                    <CalendarDaysIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 h-7 w-7 text-purple-500" />
                    <input 
                      id="end_date" 
                      v-model="form.end_date" 
                      type="date" 
                      required
                      class="block w-full pl-16 pr-6 py-6 border-3 border-purple-300 dark:border-purple-600 rounded-2xl bg-white dark:bg-purple-800 text-purple-900 dark:text-purple-100 focus:ring-4 focus:ring-purple-500/30 focus:border-purple-500 transition-all duration-300 shadow-lg hover:shadow-xl text-lg font-semibold"
                      :class="errors.end_date ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500' : ''"
                      :min="form.start_date" 
                    />
                  </div>
                  <p v-if="errors.end_date" class="mt-3 text-base text-red-600">{{ errors.end_date }}</p>
                </div>

                <!-- Duration Display -->
                <div v-if="form.start_date && form.end_date" class="lg:col-span-1">
                  <label class="block text-lg font-bold text-purple-900 dark:text-purple-100 mb-4">
                    Süre
                  </label>
                  <div class="px-6 py-6 bg-gradient-to-r from-green-100 to-emerald-100 dark:from-green-800 dark:to-emerald-800 border-3 border-green-300 dark:border-green-600 rounded-2xl shadow-lg">
                    <div class="flex items-center justify-center text-green-800 dark:text-green-100">
                      <span class="text-4xl font-bold">{{ calculateDuration(form.start_date, form.end_date) }}</span>
                      <span class="text-xl font-bold ml-3">gün</span>
                    </div>
                  </div>
                </div>

                <!-- Location -->
                <div class="lg:col-span-2">
                  <label for="location" class="block text-lg font-bold text-purple-900 dark:text-purple-100 mb-4">
                    Etkinlik Konumu
                  </label>
                  <div class="relative">
                    <MapPinIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 h-7 w-7 text-purple-500" />
                    <input 
                      id="location" 
                      v-model="form.location" 
                      type="text"
                      class="block w-full pl-16 pr-6 py-6 border-3 border-purple-300 dark:border-purple-600 rounded-2xl bg-white dark:bg-purple-800 text-purple-900 dark:text-purple-100 placeholder-purple-400 focus:ring-4 focus:ring-purple-500/30 focus:border-purple-500 transition-all duration-300 shadow-lg hover:shadow-xl text-lg font-semibold"
                      :class="errors.location ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500' : ''"
                      placeholder="Örn: Antalya Convention Center" 
                    />
                  </div>
                  <p v-if="errors.location" class="mt-3 text-base text-red-600">{{ errors.location }}</p>
                </div>
              </div>
            </div>

            <!-- Description and Publication Section -->
            <div class="space-y-10 pt-10 border-t-2 border-purple-200 dark:border-purple-700">
              <div class="flex items-center space-x-6 mb-10">
                <div class="h-12 w-12 bg-indigo-100 dark:bg-indigo-800 rounded-2xl flex items-center justify-center">
                  <DocumentTextIcon class="h-7 w-7 text-indigo-600 dark:text-indigo-300" />
                </div>
                <h3 class="text-3xl font-bold text-indigo-900 dark:text-indigo-100">Akademik İçerik ve Yayın</h3>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
                <!-- Description -->
                <div class="lg:col-span-3">
                  <label for="description" class="block text-lg font-bold text-indigo-900 dark:text-indigo-100 mb-4">
                    Etkinlik Açıklaması
                    <span class="text-base font-normal text-indigo-600 ml-2">(Bilimsel amaç, hedef kitle ve program hakkında)</span>
                  </label>
                  <div class="relative">
                    <textarea 
                      id="description" 
                      v-model="form.description" 
                      rows="8"
                      class="block w-full px-6 py-6 border-3 border-indigo-300 dark:border-indigo-600 rounded-2xl bg-white dark:bg-indigo-800 text-indigo-900 dark:text-indigo-100 placeholder-indigo-400 focus:ring-4 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all duration-300 shadow-lg hover:shadow-xl resize-none text-lg leading-relaxed font-medium"
                      :class="errors.description ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500' : ''"
                      placeholder="Bu etkinlik hakkında detaylı bilgi verin. Bilimsel amaç, hedef kitle, ana konular ve beklenen katılımcı profili gibi akademik bilgileri içerebilir..."
                      maxlength="2000"
                    ></textarea>
                    <div class="absolute bottom-4 right-4 flex items-center space-x-2">
                      <span class="text-base text-indigo-500 bg-white dark:bg-indigo-800 px-3 py-2 rounded-lg font-semibold">{{ (form.description || '').length }}/2000</span>
                    </div>
                  </div>
                  <p v-if="errors.description" class="mt-3 text-base text-red-600">{{ errors.description }}</p>
                </div>

                <!-- Publication Status Enhanced Corporate Panel -->
                <div class="lg:col-span-1">
                  <label class="block text-lg font-bold text-indigo-900 dark:text-indigo-100 mb-4">
                    Yayın Durumu
                  </label>
                  <div class="bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-800 dark:to-purple-800 rounded-2xl p-8 border-3 border-indigo-300 dark:border-indigo-600 shadow-lg">
                    <label class="flex items-start space-x-4 cursor-pointer">
                      <input 
                        v-model="form.is_published" 
                        type="checkbox"
                        class="mt-2 h-7 w-7 text-indigo-600 focus:ring-indigo-500 border-indigo-300 rounded-xl transition-colors" 
                      />
                      <div>
                        <span class="text-lg font-bold text-indigo-900 dark:text-indigo-100">
                          Etkinliği yayınla
                        </span>
                        <p class="text-base text-indigo-700 dark:text-indigo-200 mt-3 leading-relaxed">
                          Yayınlandığında katılımcılar tarafından görülebilir olacak ve kayıt işlemleri başlayacak
                        </p>
                      </div>
                    </label>
                    
                    <!-- Corporate Status Preview -->
                    <div class="mt-8 pt-8 border-t-2 border-indigo-300 dark:border-indigo-600">
                      <div class="flex items-center space-x-4">
                        <div class="h-4 w-4 rounded-full" :class="form.is_published ? 'bg-green-500' : 'bg-orange-500'"></div>
                        <span class="text-lg font-bold" :class="form.is_published ? 'text-green-700 dark:text-green-300' : 'text-orange-700 dark:text-orange-300'">
                          {{ form.is_published ? 'Yayında' : 'Taslak' }}
                        </span>
                      </div>
                      <p class="text-base text-indigo-600 dark:text-indigo-300 mt-3">
                        {{ form.is_published ? 'Etkinlik aktif olarak yayında' : 'Etkinlik henüz yayınlanmadı' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-12 border-t-2 border-indigo-200 dark:border-indigo-700">
              <div class="flex items-center space-x-8">
                <Link 
                  :href="route('admin.events.show', eventSlug)"
                  class="inline-flex items-center px-10 py-5 border-3 border-red-300 dark:border-red-600 rounded-2xl text-lg font-bold text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900 hover:bg-red-100 dark:hover:bg-red-800 focus:ring-4 focus:ring-red-500/30 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl"
                >
                  <ArrowLeftIcon class="h-6 w-6 mr-3" />
                  Geri Dön
                </Link>

                <Link 
                  :href="route('admin.events.index')"
                  class="text-lg text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 transition-colors font-bold"
                >
                  Listeye Dön
                </Link>
              </div>

              <div class="flex space-x-8">
                <!-- Reset Changes -->
                <button 
                  type="button" 
                  @click="resetForm" 
                  :disabled="processing || !hasChanges"
                  class="inline-flex items-center px-10 py-5 border-3 border-orange-300 dark:border-orange-600 rounded-2xl text-lg font-bold text-orange-700 dark:text-orange-300 bg-orange-50 dark:bg-orange-900 hover:bg-orange-100 dark:hover:bg-orange-800 focus:ring-4 focus:ring-orange-500/30 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ArrowPathIcon class="h-6 w-6 mr-3" />
                  Sıfırla
                </button>

                <!-- Update Event -->
                <button 
                  type="submit" 
                  :disabled="processing || !hasChanges"
                  class="inline-flex items-center px-12 py-5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-lg font-bold rounded-2xl focus:ring-4 focus:ring-indigo-500/30 focus:ring-offset-2 transition-all duration-300 shadow-2xl hover:shadow-3xl disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105"
                >
                  <template v-if="processing">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-3 border-white mr-3"></div>
                    Güncelleniyor...
                  </template>
                  <template v-else>
                    <CheckIcon class="h-6 w-6 mr-3" />
                    Değişiklikleri Kaydet
                  </template>
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Change Summary - Enhanced Corporate Version -->
        <div v-if="hasChanges" class="bg-gradient-to-br from-orange-100 to-red-100 dark:from-orange-800 dark:to-red-800 rounded-3xl p-12 border-3 border-orange-300 dark:border-orange-600 shadow-2xl">
          <div class="flex items-start space-x-8">
            <div class="flex-shrink-0">
              <div class="h-16 w-16 bg-orange-200 dark:bg-orange-700 rounded-2xl flex items-center justify-center">
                <ExclamationTriangleIcon class="h-10 w-10 text-orange-700 dark:text-orange-200" />
              </div>
            </div>
            <div class="flex-1">
              <h4 class="text-2xl font-bold text-orange-900 dark:text-orange-100 mb-6">Kaydedilmemiş Akademik Değişiklikler</h4>
              <p class="text-lg text-orange-800 dark:text-orange-200 leading-relaxed mb-8">
                Etkinlik bilgilerinde değişiklikler yaptınız ancak henüz kaydetmediniz. Akademik program verilerinin kaybolmaması için değişikliklerinizi kaydetmeyi unutmayın.
              </p>
              <div class="flex items-center space-x-8 text-base text-orange-700 dark:text-orange-300">
                <div class="flex items-center space-x-3">
                  <div class="h-3 w-3 bg-orange-600 rounded-full"></div>
                  <span class="font-semibold">Otomatik kaydetme kapalı</span>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="h-3 w-3 bg-orange-600 rounded-full"></div>
                  <span class="font-semibold">Manuel kaydetme gerekli</span>
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
import { ref, computed, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  PencilSquareIcon,
  CalendarDaysIcon,
  MapPinIcon,
  ArrowLeftIcon,
  CheckIcon,
  ArrowPathIcon,
  LinkIcon,
  ExclamationTriangleIcon,
  ClockIcon,
  DocumentTextIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  event: {
    type: Object,
    required: true
  },
  organizations: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Computed properties
const pageTitle = computed(() => {
  return (props.event?.name || 'Event') + ' - Düzenle'
})

const eventName = computed(() => {
  return props.event?.name || 'Event'
})

const eventSlug = computed(() => {
  return props.event?.slug || 'unknown'
})

const isPublished = computed(() => {
  return Boolean(props.event?.is_published)
})

const breadcrumbs = computed(() => [
  { label: 'Etkinlikler', href: route('admin.events.index') },
  { label: eventName.value, href: route('admin.events.show', eventSlug.value) },
  { label: 'Düzenle', href: null }
])

// Form state
const form = reactive({
  name: props.event?.name || '',
  description: props.event?.description || '',
  start_date: props.event?.start_date || '',
  end_date: props.event?.end_date || '',
  location: props.event?.location || '',
  organization_id: props.event?.organization_id || null,
  is_published: Boolean(props.event?.is_published)
})

const processing = ref(false)

// Store original values
const originalForm = {
  name: props.event?.name || '',
  description: props.event?.description || '',
  start_date: props.event?.start_date || '',
  end_date: props.event?.end_date || '',
  location: props.event?.location || '',
  organization_id: props.event?.organization_id || null,
  is_published: Boolean(props.event?.is_published)
}

const hasChanges = computed(() => {
  return Object.keys(originalForm).some(key => {
    return form[key] !== originalForm[key]
  })
})

// Methods
const calculateDuration = (startDate, endDate) => {
  if (!startDate || !endDate) return 0
  const start = new Date(startDate)
  const end = new Date(endDate)
  const diffTime = Math.abs(end - start)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays + 1
}

const generateSlug = (name) => {
  if (!name) return ''
  return name
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim('-')
}

const submitForm = async () => {
  processing.value = true
  
  try {
    await router.put(route('admin.events.update', eventSlug.value), form, {
      onSuccess: () => {
        Object.keys(originalForm).forEach(key => {
          originalForm[key] = form[key]
        })
        processing.value = false
      },
      onError: () => {
        processing.value = false
      },
      onFinish: () => {
        processing.value = false
      }
    })
  } catch (error) {
    console.error('Submit error:', error)
    processing.value = false
  }
}

const resetForm = () => {
  Object.keys(originalForm).forEach(key => {
    form[key] = originalForm[key]
  })
}
</script>

<style scoped>
/* Enhanced corporate styling */
input:focus,
select:focus,
textarea:focus {
  transform: translateY(-2px);
}

.hover-lift:hover {
  transform: translateY(-3px);
}

input[type="checkbox"]:checked {
  background-color: rgb(79 70 229);
  border-color: rgb(79 70 229);
}

@keyframes corporateSpin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: corporateSpin 1s linear infinite;
}

.shadow-3xl {
  box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}

button:focus-visible,
input:focus-visible,
select:focus-visible,
textarea:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 3px;
}
</style>