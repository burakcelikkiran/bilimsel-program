<template>
  <AdminLayout 
    page-title="Yeni Bilimsel Etkinlik" 
    page-subtitle="Akademik etkinlik oluşturun ve programlayın" 
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Yeni Etkinlik" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-blue-950 dark:via-indigo-950 dark:to-purple-950 -mx-8 -my-6 px-8 py-6">
      <div class="max-w-7xl mx-auto">
        <!-- Corporate Header Section -->
        <div class="bg-white dark:bg-blue-900 rounded-3xl shadow-2xl border-2 border-blue-200 dark:border-blue-700 overflow-hidden mb-8">
          <div class="relative">
            <!-- Professional Corporate Header -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 px-10 py-12">
              <div class="flex items-center space-x-8">
                <div class="flex-shrink-0">
                  <div class="h-24 w-24 bg-white/20 backdrop-blur-sm rounded-3xl flex items-center justify-center ring-4 ring-white/30 shadow-2xl">
                    <CalendarIcon class="h-12 w-12 text-white" />
                  </div>
                </div>
                <div class="flex-1">
                  <h1 class="text-4xl font-bold text-white mb-4">Bilimsel Etkinlik Oluştur</h1>
                  <p class="text-blue-100 text-xl font-medium">Kongre, sempozyum veya konferans programınızı profesyonelce organize edin</p>
                </div>
              </div>
              
              <!-- Professional Progress -->
              <div class="mt-10 flex items-center space-x-4">
                <div class="h-3 w-3 bg-green-400 rounded-full animate-pulse shadow-lg"></div>
                <span class="text-blue-100 text-lg font-medium">Temel bilgiler aşaması</span>
              </div>
            </div>
            
            <!-- Corporate Border -->
            <div class="h-2 bg-gradient-to-r from-green-500 via-blue-500 to-purple-500"></div>
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
                <!-- Event Name -->
                <div class="lg:col-span-2">
                  <label for="title" class="block text-lg font-bold text-blue-900 dark:text-blue-100 mb-4">
                    Etkinlik Adı *
                    <span class="text-base font-normal text-blue-600 ml-2">(Resmi kongre/konferans adı)</span>
                  </label>
                  <input 
                    id="title" 
                    v-model="form.title" 
                    type="text" 
                    required
                    class="block w-full px-6 py-6 border-3 border-blue-300 dark:border-blue-600 rounded-2xl bg-white dark:bg-blue-800 text-blue-900 dark:text-blue-100 placeholder-blue-400 focus:ring-4 focus:ring-blue-500/30 focus:border-blue-500 transition-all duration-300 shadow-lg hover:shadow-xl text-xl font-semibold"
                    :class="errors.title ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500' : ''"
                    placeholder="Örn: 25. Ulusal Pediatri Kongresi" 
                  />
                  <p v-if="errors.title" class="mt-3 text-base text-red-600 flex items-center">
                    <ExclamationCircleIcon class="h-5 w-5 mr-2" />
                    {{ errors.title }}
                  </p>
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
                      :min="minDate" 
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
                      :min="form.start_date || minDate" 
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

            <!-- Description Section -->
            <div class="space-y-10 pt-10 border-t-2 border-purple-200 dark:border-purple-700">
              <div class="flex items-center space-x-6 mb-10">
                <div class="h-12 w-12 bg-indigo-100 dark:bg-indigo-800 rounded-2xl flex items-center justify-center">
                  <DocumentTextIcon class="h-7 w-7 text-indigo-600 dark:text-indigo-300" />
                </div>
                <h3 class="text-3xl font-bold text-indigo-900 dark:text-indigo-100">Akademik İçerik</h3>
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

                <!-- Settings Panel -->
                <div class="lg:col-span-1">
                  <label class="block text-lg font-bold text-indigo-900 dark:text-indigo-100 mb-4">
                    Program Ayarları
                  </label>
                  <div class="bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-800 dark:to-indigo-800 rounded-2xl p-8 border-3 border-blue-300 dark:border-blue-600 shadow-lg">
                    <h4 class="text-xl font-bold text-blue-900 dark:text-blue-100 mb-6 flex items-center">
                      <Cog6ToothIcon class="h-6 w-6 mr-3 text-blue-600" />
                      Otomatik İşlemler
                    </h4>
                    <div class="space-y-6">
                      <label class="flex items-start space-x-4 cursor-pointer">
                        <input 
                          v-model="form.auto_create_days" 
                          type="checkbox"
                          class="mt-1 h-6 w-6 text-blue-600 focus:ring-blue-500 border-blue-300 rounded-lg transition-colors" 
                        />
                        <div>
                          <span class="text-lg font-bold text-blue-900 dark:text-blue-100">
                            Günleri otomatik oluştur
                          </span>
                          <p class="text-base text-blue-700 dark:text-blue-200 mt-2 leading-relaxed">
                            Belirlenen tarih aralığında her gün için otomatik program günleri oluşturulur
                          </p>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-12 border-t-2 border-indigo-200 dark:border-indigo-700">
              <Link 
                :href="route('admin.events.index')"
                class="inline-flex items-center px-10 py-5 border-3 border-red-300 dark:border-red-600 rounded-2xl text-lg font-bold text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900 hover:bg-red-100 dark:hover:bg-red-800 focus:ring-4 focus:ring-red-500/30 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl"
              >
                <ArrowLeftIcon class="h-6 w-6 mr-3" />
                Vazgeç
              </Link>

              <div class="flex space-x-8">
                <!-- Save as Draft -->
                <button 
                  type="button" 
                  @click="submitForm(false)" 
                  :disabled="processing"
                  class="inline-flex items-center px-10 py-5 border-3 border-orange-300 dark:border-orange-600 rounded-2xl text-lg font-bold text-orange-700 dark:text-orange-300 bg-orange-50 dark:bg-orange-900 hover:bg-orange-100 dark:hover:bg-orange-800 focus:ring-4 focus:ring-orange-500/30 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <DocumentIcon class="h-6 w-6 mr-3" />
                  Taslak Kaydet
                </button>

                <!-- Create Event -->
                <button 
                  type="submit" 
                  :disabled="processing"
                  class="inline-flex items-center px-12 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-lg font-bold rounded-2xl focus:ring-4 focus:ring-blue-500/30 focus:ring-offset-2 transition-all duration-300 shadow-2xl hover:shadow-3xl disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105"
                >
                  <template v-if="processing">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-3 border-white mr-3"></div>
                    Oluşturuluyor...
                  </template>
                  <template v-else>
                    <PlusIcon class="h-6 w-6 mr-3" />
                    Bilimsel Etkinlik Oluştur
                  </template>
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Professional Tips Section -->
        <div class="bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-800 dark:to-emerald-800 rounded-3xl p-12 border-3 border-green-300 dark:border-green-600 shadow-2xl">
          <div class="flex items-start space-x-8">
            <div class="flex-shrink-0">
              <div class="h-16 w-16 bg-green-200 dark:bg-green-700 rounded-2xl flex items-center justify-center">
                <InformationCircleIcon class="h-10 w-10 text-green-700 dark:text-green-200" />
              </div>
            </div>
            <div class="flex-1">
              <h4 class="text-2xl font-bold text-green-900 dark:text-green-100 mb-6">
                Akademik Etkinlik Oluşturma Rehberi
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-10 text-lg text-green-800 dark:text-green-200">
                <div class="space-y-6">
                  <div class="flex items-start space-x-4">
                    <div class="h-3 w-3 bg-green-600 rounded-full mt-3 flex-shrink-0"></div>
                    <span><strong>Resmi Ad:</strong> Kongre/konferans tam adını kullanın</span>
                  </div>
                  <div class="flex items-start space-x-4">
                    <div class="h-3 w-3 bg-green-600 rounded-full mt-3 flex-shrink-0"></div>
                    <span><strong>Organizasyon:</strong> Ana düzenleyen kurumu seçin</span>
                  </div>
                  <div class="flex items-start space-x-4">
                    <div class="h-3 w-3 bg-green-600 rounded-full mt-3 flex-shrink-0"></div>
                    <span><strong>Tarih Seçimi:</strong> Resmi etkinlik tarihlerini girin</span>
                  </div>
                </div>
                <div class="space-y-6">
                  <div class="flex items-start space-x-4">
                    <div class="h-3 w-3 bg-green-600 rounded-full mt-3 flex-shrink-0"></div>
                    <span><strong>Açıklama:</strong> Bilimsel amaç ve hedefleri belirtin</span>
                  </div>
                  <div class="flex items-start space-x-4">
                    <div class="h-3 w-3 bg-green-600 rounded-full mt-3 flex-shrink-0"></div>
                    <span><strong>Konum:</strong> Detaylı adres bilgisi ekleyin</span>
                  </div>
                  <div class="flex items-start space-x-4">
                    <div class="h-3 w-3 bg-green-600 rounded-full mt-3 flex-shrink-0"></div>
                    <span><strong>Otomatik Gün:</strong> Program günlerini hızlı oluştur</span>
                  </div>
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
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  CalendarIcon,
  CalendarDaysIcon,
  MapPinIcon,
  PlusIcon,
  ArrowLeftIcon,
  DocumentIcon,
  InformationCircleIcon,
  ClockIcon,
  DocumentTextIcon,
  ExclamationCircleIcon,
  Cog6ToothIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  organizations: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Form
const form = useForm({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  location: '',
  organization_id: '',
  auto_create_days: true
})

const processing = ref(false)

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Etkinlikler', href: route('admin.events.index') },
  { label: 'Yeni Etkinlik', href: null }
])

const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
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

const submitForm = (publish = true) => {
  processing.value = true

  if (!form.title.trim() || !form.organization_id || !form.start_date || !form.end_date) {
    processing.value = false
    return
  }

  if (new Date(form.start_date) > new Date(form.end_date)) {
    processing.value = false
    alert('Bitiş tarihi başlangıç tarihinden önce olamaz.')
    return
  }

  form.transform((data) => ({
    ...data,
    is_published: false
  }))

  form.post(route('admin.events.store'), {
    onSuccess: () => {
      processing.value = false
    },
    onError: (errors) => {
      processing.value = false
      console.error('Form submission errors:', errors)
      
      const firstError = Object.values(errors)[0]
      if (firstError && firstError[0]) {
        alert(firstError[0])
      }
    },
    onFinish: () => {
      processing.value = false
    }
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
  background-color: rgb(37 99 235);
  border-color: rgb(37 99 235);
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