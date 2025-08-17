<!-- Admin/Organizations/Create.vue - Gray Theme -->
<template>
  <AdminLayout
    page-title="Yeni Organizasyon"
    page-subtitle="Sistem için yeni bir organizasyon oluşturun"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Yeni Organizasyon" />

    <div class="w-full space-y-8">
      <!-- Header Card -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Yeni Organizasyon</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Sistem için yeni bir organizasyon oluşturun</p>
          </div>
          <Link
            :href="route('admin.organizations.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Geri Dön
          </Link>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form @submit.prevent="createOrganization" class="divide-y divide-gray-200 dark:divide-gray-700">
          
          <!-- Basic Information -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Bilgiler</h3>
              
              <!-- Full width grid layout -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Organization Name - Spans 2 columns -->
                <div class="lg:col-span-2">
                  <FormInput
                    v-model="form.name"
                    label="Organizasyon Adı"
                    placeholder="Organizasyon adını girin"
                    required
                    :error-message="form.errors.name"
                    maxlength="255"
                    show-counter
                  />
                </div>

                <!-- Contact Email -->
                <div>
                  <FormInput
                    v-model="form.contact_email"
                    type="email"
                    label="İletişim E-postası"
                    placeholder="ornek@organizasyon.com"
                    :error-message="form.errors.contact_email"
                    maxlength="255"
                  >
                    <template #helper>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Ana iletişim e-posta adresi
                      </p>
                    </template>
                  </FormInput>
                </div>

                <!-- Contact Phone -->
                <div>
                  <FormInput
                    v-model="form.contact_phone"
                    type="tel"
                    label="İletişim Telefonu"
                    placeholder="+90 (555) 123 45 67"
                    :error-message="form.errors.contact_phone"
                    maxlength="50"
                  >
                    <template #helper>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        İletişim telefon numarası
                      </p>
                    </template>
                  </FormInput>
                </div>

                <!-- Description - Spans 2 columns -->
                <div class="lg:col-span-2">
                  <FormTextarea
                    v-model="form.description"
                    label="Açıklama"
                    placeholder="Organizasyonunuz hakkında kısa bir açıklama yazın..."
                    :error-message="form.errors.description"
                    :rows="6"
                    maxlength="1000"
                    show-counter
                  >
                    <template #helper>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Organizasyonunuzun misyonu, vizyonu ve faaliyet alanları hakkında bilgi verin
                      </p>
                    </template>
                  </FormTextarea>
                </div>

                <!-- Status -->
                <div class="lg:col-span-2">
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
                        Aktif Organizasyon
                      </label>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Aktif organizasyonlar sistem genelinde görünür ve kullanılabilir olur
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Logo Upload Section -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logo</h3>
              
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

          <!-- Form Actions -->
          <div class="px-6 py-6 bg-gray-50 dark:bg-gray-800">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <Link
                  :href="route('admin.organizations.index')"
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
                  <span v-else>Organizasyon Oluştur</span>
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
import FormTextarea from '@/Components/Forms/FormTextarea.vue'
import { 
  ArrowLeftIcon, 
  CheckCircleIcon,
  PhotoIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

// Form
const form = useForm({
  name: '',
  description: '',
  contact_email: '',
  contact_phone: '',
  logo: null,
  is_active: true
})

// State
const logoPreview = ref(null)

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Organizasyonlar', href: route('admin.organizations.index') },
  { label: 'Yeni Organizasyon', href: null }
])

// Methods
const createOrganization = () => {
  form.post(route('admin.organizations.store'), {
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

/* Enhanced shadow effects */
.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.hover\:shadow-xl:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>