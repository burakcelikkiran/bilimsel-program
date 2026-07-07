<!-- Admin/Venues/Create.vue - Gray Theme -->
<template>
  <AdminLayout
    page-title="Yeni Salon"
    page-subtitle="Etkinlik günü için yeni bir salon oluşturun"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Yeni Salon" />

    <div class="w-full space-y-8">
      <!-- Header Card -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Yeni Salon</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Etkinlik günü için yeni bir salon oluşturun</p>
          </div>
          <Link
            :href="route('admin.venues.index')"
            class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Geri Dön
          </Link>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form @submit.prevent="createVenue" class="divide-y divide-slate-200 dark:divide-slate-700">
          
          <!-- Basic Information -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Temel Bilgiler</h3>
              
              <!-- Full width grid layout -->
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Event Day - Spans 2 columns -->
                <div class="lg:col-span-2">
                  <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Etkinlik Günü *
                  </label>
                  <select
                    v-model="form.event_day_id"
                    required
                    class="block w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-slate-500 focus:ring-slate-500"
                    :class="form.errors.event_day_id ? 'border-red-500' : ''"
                  >
                    <option value="">Etkinlik günü seçin</option>
                    <template v-for="group in groupedEventDays" :key="group.event_id">
                      <optgroup :label="group.event_name">
                        <option 
                          v-for="eventDay in group.options" 
                          :key="eventDay.id" 
                          :value="eventDay.id"
                        >
                          {{ eventDay.display_name }}
                        </option>
                      </optgroup>
                    </template>
                  </select>
                  <p v-if="form.errors.event_day_id" class="text-sm text-red-600 dark:text-red-400 mt-1">
                    {{ form.errors.event_day_id }}
                  </p>
                </div>

                <!-- Color Picker -->
                <div>
                  <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Renk
                  </label>
                  <div class="relative">
                    <input
                      v-model="form.color"
                      type="color"
                      class="h-10 w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 cursor-pointer"
                    />
                    <input
                      v-model="form.color"
                      type="text"
                      placeholder="#3B82F6"
                      class="mt-2 block w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-slate-500 focus:ring-slate-500"
                    />
                  </div>
                </div>

                <!-- Venue Name - Spans all columns -->
                <div class="lg:col-span-3">
                  <FormInput
                    v-model="form.name"
                    label="Salon Adı"
                    placeholder="Salon adını girin"
                    required
                    :error-message="form.errors.name"
                    :maxlength="255"
                    show-counter
                  />
                </div>

                <!-- Display Name - Spans 2 columns -->
                <div class="lg:col-span-2">
                  <FormInput
                    v-model="form.display_name"
                    label="Görünen Adı"
                    placeholder="Görünen adı girin (boş bırakılırsa salon adı kullanılır)"
                    :error-message="form.errors.display_name"
                    :maxlength="255"
                    show-counter
                  >
                    <template #helper>
                      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Programda görünecek isim. Boş bırakılırsa salon adı kullanılır.
                      </p>
                    </template>
                  </FormInput>
                </div>

                <!-- Capacity -->
                <div>
                  <FormInput
                    v-model="form.capacity"
                    type="number"
                    label="Kapasite"
                    placeholder="Maksimum kişi sayısı"
                    :error-message="form.errors.capacity"
                    min="1"
                    max="50000"
                  >
                    <template #helper>
                      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Maksimum kişi sayısı (opsiyonel)
                      </p>
                    </template>
                  </FormInput>
                </div>

                <!-- Sort Order -->
                <div class="lg:col-span-3">
                  <FormInput
                    v-model="form.sort_order"
                    type="number"
                    label="Sıralama"
                    placeholder="Sıralama numarası (0-9999)"
                    :error-message="form.errors.sort_order"
                    min="0"
                    max="9999"
                  >
                    <template #helper>
                      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Salonların sıralanmasında kullanılır. Boş bırakılırsa otomatik atanır.
                      </p>
                    </template>
                  </FormInput>
                </div>
              </div>
            </div>
          </div>

          <!-- Capacity & Color Templates -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Hızlı Şablonlar</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                Salon türüne göre önceden tanımlanmış ayarları kullanabilirsiniz.
              </p>
              
              <!-- Enhanced template cards with gray theme -->
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Auditorium Template -->
                <button
                  type="button"
                  @click="applyTemplate('auditorium')"
                  class="p-6 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-500 transition-all text-left group relative overflow-hidden"
                >
                  <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 rounded-bl-full opacity-50"></div>
                  <div class="relative">
                    <div class="flex items-center mb-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                        <BuildingOfficeIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                      </div>
                      <h4 class="text-lg font-semibold text-slate-900 dark:text-white">Auditorium</h4>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                      Büyük konferans salonu. 500+ kişi kapasiteli.
                    </p>
                  </div>
                </button>

                <!-- Conference Room Template -->
                <button
                  type="button"
                  @click="applyTemplate('conference')"
                  class="p-6 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-500 transition-all text-left group relative overflow-hidden"
                >
                  <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 rounded-bl-full opacity-50"></div>
                  <div class="relative">
                    <div class="flex items-center mb-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                        <UserGroupIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
                      </div>
                      <h4 class="text-lg font-semibold text-slate-900 dark:text-white">Konferans</h4>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                      Orta boy salon. 100-300 kişi kapasiteli.
                    </p>
                  </div>
                </button>

                <!-- Meeting Room Template -->
                <button
                  type="button"
                  @click="applyTemplate('meeting')"
                  class="p-6 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-500 transition-all text-left group relative overflow-hidden"
                >
                  <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800 rounded-bl-full opacity-50"></div>
                  <div class="relative">
                    <div class="flex items-center mb-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900 dark:to-purple-800 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                        <UsersIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                      </div>
                      <h4 class="text-lg font-semibold text-slate-900 dark:text-white">Toplantı</h4>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                      Küçük toplantı salonu. 20-100 kişi kapasiteli.
                    </p>
                  </div>
                </button>

                <!-- Workshop Template -->
                <button
                  type="button"
                  @click="applyTemplate('workshop')"
                  class="p-6 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-500 transition-all text-left group relative overflow-hidden"
                >
                  <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-900 dark:to-orange-800 rounded-bl-full opacity-50"></div>
                  <div class="relative">
                    <div class="flex items-center mb-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-900 dark:to-orange-800 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                        <WrenchScrewdriverIcon class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                      </div>
                      <h4 class="text-lg font-semibold text-slate-900 dark:text-white">Atölye</h4>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                      Workshop salonu. 10-50 kişi kapasiteli.
                    </p>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="px-6 py-6 bg-slate-50 dark:bg-slate-800">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <Link
                  :href="route('admin.venues.index')"
                  class="inline-flex items-center px-6 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors"
                >
                  İptal
                </Link>
                
                <button
                  type="button"
                  @click="resetForm"
                  class="inline-flex items-center px-6 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors"
                >
                  Temizle
                </button>
              </div>

              <div class="flex items-center space-x-4">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-8 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-900 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg hover:shadow-xl border border-slate-700"
                >
                  <span v-if="form.processing" class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Oluşturuluyor...
                  </span>
                  <span v-else>Salon Oluştur</span>
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/Forms/FormInput.vue'
import FormSelect from '@/Components/Forms/FormSelect.vue'
import { 
  ArrowLeftIcon, 
  BuildingOfficeIcon,
  UserGroupIcon,
  UsersIcon,
  WrenchScrewdriverIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  eventDays: {
    type: Array,
    default: () => []
  }
})

// Form
const form = useForm({
  event_day_id: '',
  name: '',
  display_name: '',
  capacity: '',
  color: '#3B82F6',
  sort_order: ''
})

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Salonlar', href: route('admin.venues.index') },
  { label: 'Yeni Salon', href: null }
])

// Group event days by event name for better UX
const groupedEventDays = computed(() => {
  if (!props.eventDays || props.eventDays.length === 0) return []
  
  // Create a map to group by event
  const eventGroups = new Map()
  
  props.eventDays.forEach(eventDay => {
    const eventName = eventDay.event?.name || 'Etkinlik Belirtilmemiş'
    const eventId = eventDay.event?.id || 'unknown'
    const groupKey = `${eventId}-${eventName}`
    
    if (!eventGroups.has(groupKey)) {
      eventGroups.set(groupKey, {
        event_name: eventName,
        event_id: eventId,
        options: []
      })
    }
    
    eventGroups.get(groupKey).options.push({
      id: eventDay.id,
      display_name: eventDay.display_name,
      date: eventDay.date,
      sort_order: eventDay.sort_order
    })
  })
  
  // Convert to array and sort
  const result = Array.from(eventGroups.values()).map(group => ({
    ...group,
    options: group.options.sort((a, b) => a.sort_order - b.sort_order)
  }))
  
  // Sort groups by event name
  return result.sort((a, b) => a.event_name.localeCompare(b.event_name))
})

// Templates
const templates = {
  auditorium: {
    capacity: 500,
    color: '#3B82F6'
  },
  conference: {
    capacity: 150,
    color: '#10B981'
  },
  meeting: {
    capacity: 50,
    color: '#8B5CF6'
  },
  workshop: {
    capacity: 25,
    color: '#F59E0B'
  }
}

// Methods
const createVenue = () => {
  // Route kontrolü
  try {
    const routeUrl = route('admin.venues.store')
    console.log('🔗 Route URL:', routeUrl)
  } catch (error) {
    console.error('❌ Route error:', error)
    alert('Route bulunamadı: admin.venues.store')
    return
  }
  
  console.log('🚀 Form submission started')
  console.log('📝 Form data:', {
    event_day_id: form.event_day_id,
    name: form.name,
    display_name: form.display_name,
    capacity: form.capacity,
    color: form.color,
    sort_order: form.sort_order
  })
  console.log('❌ Form errors before submit:', form.errors)
  console.log('⏳ Form processing state:', form.processing)
  
  form.post(route('admin.venues.store'), {
    onStart: () => {
      console.log('🟡 Request started')
    },
    onSuccess: (response) => {
      console.log('✅ Success response:', response)
    },
    onError: (errors) => {
      console.log('❌ Validation errors:', errors)
      console.log('📋 Full form errors:', form.errors)
    },
    onFinish: () => {
      console.log('🏁 Request finished, processing state:', form.processing)
    }
  })
}

const resetForm = () => {
  form.reset()
  form.color = '#3B82F6'
}

const applyTemplate = (templateKey) => {
  const template = templates[templateKey]
  if (!template) return
  
  form.capacity = template.capacity
  form.color = template.color
}
</script>

<style scoped>
/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Template button hover effects */
.group:hover .w-10 {
  transform: scale(1.1);
}

/* Enhanced shadow effects */
.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.hover\:shadow-xl:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>