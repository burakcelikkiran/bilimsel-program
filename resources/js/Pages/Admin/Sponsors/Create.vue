<!-- Admin/Sponsors/Create.vue - Gray Theme -->
<template>
  <AdminLayout
    page-title="Yeni Sponsor"
    page-subtitle="Etkinlik için yeni bir sponsor oluşturun"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Yeni Sponsor" />

    <div class="w-full space-y-8">
      <!-- Header Card -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Yeni Sponsor</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Etkinlik için yeni bir sponsor oluşturun</p>
          </div>
          <Link
            :href="route('admin.sponsors.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Geri Dön
          </Link>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form @submit.prevent="createSponsor" class="divide-y divide-gray-200 dark:divide-gray-700">
          
          <!-- Basic Information -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Bilgiler</h3>
              
              <!-- Full width grid layout -->
              <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Organization - Spans 2 columns -->
                <div class="lg:col-span-2">
                  <FormSelect
                    v-model="form.organization_id"
                    label="Organizasyon"
                    :options="organizations"
                    option-value="id"
                    option-label="name"
                    required
                    :error-message="form.errors.organization_id"
                    placeholder="Organizasyon seçin"
                  />
                </div>

                <!-- Sponsor Level -->
                <div>
                  <FormSelect
                    v-model="form.sponsor_level"
                    label="Sponsor Seviyesi"
                    :options="sponsorLevels"
                    required
                    :error-message="form.errors.sponsor_level"
                    placeholder="Sponsor seviyesi seçin"
                  />
                </div>

                <!-- Status -->
                <div class="flex items-center space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                  <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                  />
                  <div class="flex-1">
                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                      <CheckCircleIcon class="h-4 w-4 mr-2 text-gray-600" />
                      Aktif Sponsor
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      Aktif sponsorlar listede görünür
                    </p>
                  </div>
                </div>

                <!-- Name - Spans all 4 columns -->
                <div class="lg:col-span-4">
                  <FormInput
                    v-model="form.name"
                    label="Sponsor Adı"
                    placeholder="Sponsor adını girin"
                    required
                    :error-message="form.errors.name"
                    maxlength="255"
                    show-counter
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Information & Logo Side by Side -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İletişim Bilgileri ve Logo</h3>
              
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Contact Information Section -->
                <div class="space-y-6">
                  <h4 class="text-md font-medium text-gray-700 dark:text-gray-300">İletişim Bilgileri</h4>
                  
                  <div class="space-y-4">
                    <!-- Contact Email -->
                    <div>
                      <FormInput
                        v-model="form.contact_email"
                        type="email"
                        label="İletişim E-postası"
                        placeholder="E-posta adresini girin"
                        :error-message="form.errors.contact_email"
                        maxlength="255"
                      >
                        <template #helper>
                          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Sponsor ile iletişim için kullanılacak e-posta adresi
                          </p>
                        </template>
                      </FormInput>
                    </div>

                    <!-- Website -->
                    <div>
                      <FormInput
                        v-model="form.website"
                        type="url"
                        label="Website"
                        placeholder="https://example.com"
                        :error-message="form.errors.website"
                        maxlength="500"
                      >
                        <template #helper>
                          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Sponsor'un resmi website adresi
                          </p>
                        </template>
                      </FormInput>
                    </div>
                  </div>
                </div>

                <!-- Logo Upload Section -->
                <div class="space-y-6">
                  <h4 class="text-md font-medium text-gray-700 dark:text-gray-300">Logo</h4>
                  
                  <div class="space-y-4">
                    <!-- Logo Preview -->
                    <div v-if="logoPreview" class="flex items-center space-x-4">
                      <div class="h-24 w-24 rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-600 bg-white">
                        <img :src="logoPreview" alt="Logo önizleme" class="h-full w-full object-contain p-2" />
                      </div>
                      <button
                        type="button"
                        @click="removeLogo"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:ring-2 focus:ring-gray-500 transition-colors"
                      >
                        <TrashIcon class="h-4 w-4 mr-2" />
                        Kaldır
                      </button>
                    </div>

                    <!-- Logo Upload Input -->
                    <div class="flex items-center justify-center w-full">
                      <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 dark:border-gray-600 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                          <PhotoIcon class="w-10 h-10 mb-3 text-gray-400" />
                          <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                            <span class="font-semibold">Logo yüklemek için tıklayın</span>
                          </p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF veya SVG (Max. 2MB)</p>
                        </div>
                        <input
                          type="file"
                          accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
                          @change="handleLogoUpload"
                          class="hidden"
                        />
                      </label>
                    </div>
                    
                    <p v-if="form.errors.logo" class="text-sm text-red-600 dark:text-red-400 mt-1">
                      {{ form.errors.logo }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sponsor Level Templates -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hızlı Şablonlar</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Sponsor seviyesine göre önceden tanımlanmış ayarları kullanabilirsiniz.
              </p>
              
              <!-- Enhanced template cards with gray theme -->
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Platinum Template -->
                <button
                  type="button"
                  @click="applyTemplate('platinum')"
                  class="p-6 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-500 transition-all text-left group relative overflow-hidden"
                >
                  <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-bl-full opacity-50"></div>
                  <div class="relative">
                    <div class="flex items-center mb-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                        <StarIcon class="h-6 w-6 text-gray-600 dark:text-gray-300" />
                      </div>
                      <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Platinum</h4>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                      En üst seviye sponsor paketi. Maksimum görünürlük ve avantajlar.
                    </p>
                  </div>
                </button>

                <!-- Gold Template -->
                <button
                  type="button"
                  @click="applyTemplate('gold')"
                  class="p-6 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-500 transition-all text-left group relative overflow-hidden"
                >
                  <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-yellow-100 to-yellow-200 dark:from-yellow-900 dark:to-yellow-800 rounded-bl-full opacity-50"></div>
                  <div class="relative">
                    <div class="flex items-center mb-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-yellow-100 to-yellow-200 dark:from-yellow-900 dark:to-yellow-800 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                        <StarIcon class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                      </div>
                      <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Gold</h4>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                      Yüksek seviye sponsor paketi. Premium görünürlük imkanları.
                    </p>
                  </div>
                </button>

                <!-- Silver Template -->
                <button
                  type="button"
                  @click="applyTemplate('silver')"
                  class="p-6 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-500 transition-all text-left group relative overflow-hidden"
                >
                  <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 rounded-bl-full opacity-50"></div>
                  <div class="relative">
                    <div class="flex items-center mb-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                        <StarIcon class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                      </div>
                      <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Silver</h4>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                      Orta seviye sponsor paketi. İyi görünürlük ve faydalar.
                    </p>
                  </div>
                </button>

                <!-- Bronze Template -->
                <button
                  type="button"
                  @click="applyTemplate('bronze')"
                  class="p-6 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-500 transition-all text-left group relative overflow-hidden"
                >
                  <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-900 dark:to-orange-800 rounded-bl-full opacity-50"></div>
                  <div class="relative">
                    <div class="flex items-center mb-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-900 dark:to-orange-800 rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                        <StarIcon class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                      </div>
                      <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Bronze</h4>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                      Temel seviye sponsor paketi. Maliyet etkin çözüm.
                    </p>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="px-6 py-6 bg-gray-50 dark:bg-gray-800">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <Link
                  :href="route('admin.sponsors.index')"
                  class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                >
                  İptal
                </Link>
                
                <button
                  type="button"
                  @click="resetForm"
                  class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                >
                  Temizle
                </button>
              </div>

              <div class="flex items-center space-x-4">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-8 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg hover:shadow-xl border border-gray-700"
                >
                  <span v-if="form.processing" class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Oluşturuluyor...
                  </span>
                  <span v-else>Sponsor Oluştur</span>
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
  StarIcon,
  CheckCircleIcon,
  PhotoIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  organizations: {
    type: Array,
    default: () => []
  }
})

// Form
const form = useForm({
  organization_id: '',
  name: '',
  sponsor_level: 'bronze',
  contact_email: '',
  website: '',
  logo: null,
  is_active: true
})

// State
const logoPreview = ref(null)

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Sponsorlar', href: route('admin.sponsors.index') },
  { label: 'Yeni Sponsor', href: null }
])

// Sponsor Levels
const sponsorLevels = [
  { value: 'platinum', label: 'Platinum' },
  { value: 'gold', label: 'Gold' },
  { value: 'silver', label: 'Silver' },
  { value: 'bronze', label: 'Bronze' }
]

// Templates
const templates = {
  platinum: {
    sponsor_level: 'platinum',
    is_active: true
  },
  gold: {
    sponsor_level: 'gold',
    is_active: true
  },
  silver: {
    sponsor_level: 'silver',
    is_active: true
  },
  bronze: {
    sponsor_level: 'bronze',
    is_active: true
  }
}

// Methods
const createSponsor = () => {
  form.post(route('admin.sponsors.store'), {
    onSuccess: () => {
      // Success message will be handled by the backend
    },
    onError: () => {
      // Errors will be handled by the form validation
    }
  })
}

const resetForm = () => {
  form.reset()
  logoPreview.value = null
}

const applyTemplate = (templateKey) => {
  const template = templates[templateKey]
  if (!template) return
  
  form.sponsor_level = template.sponsor_level
  form.is_active = template.is_active
}

const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml']
  if (!allowedTypes.includes(file.type)) {
    alert('Sadece JPEG, PNG, JPG, GIF veya SVG dosyaları yüklenebilir.')
    return
  }

  // Validate file size (2MB)
  if (file.size > 2 * 1024 * 1024) {
    alert('Dosya boyutu 2MB\'dan küçük olmalıdır.')
    return
  }

  form.logo = file

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    logoPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const removeLogo = () => {
  form.logo = null
  logoPreview.value = null
}
</script>

<style scoped>
/* Custom file upload styling */
input[type="file"] {
  opacity: 0;
  position: absolute;
  pointer-events: none;
}

/* Focus styles for checkboxes */
input[type="checkbox"]:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.1);
}

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