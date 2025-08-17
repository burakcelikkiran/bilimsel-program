<!-- Admin/Participants/Create.vue - Premium Participant Create Form with Gray Theme -->
<template>
  <AdminLayout
    page-title="Yeni Katılımcı"
    page-subtitle="Etkinlik için yeni bir katılımcı oluşturun"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Yeni Katılımcı" />

    <div class="w-full space-y-8">
      <!-- Header Card -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Yeni Katılımcı</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Etkinlik için yeni bir katılımcı oluşturun</p>
          </div>
          <Link
            :href="route('admin.participants.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Geri Dön
          </Link>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form @submit.prevent="createParticipant" class="divide-y divide-gray-200 dark:divide-gray-700">
          <!-- Basic Information -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Bilgiler</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Organization -->
                <div class="md:col-span-2">
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

                <!-- First Name -->
                <div>
                  <FormInput
                    v-model="form.first_name"
                    label="Ad"
                    placeholder="Adı girin"
                    required
                    :error-message="form.errors.first_name"
                    :maxlength="255"
                    show-counter
                  />
                </div>

                <!-- Last Name -->
                <div>
                  <FormInput
                    v-model="form.last_name"
                    label="Soyad"
                    placeholder="Soyadı girin"
                    required
                    :error-message="form.errors.last_name"
                    :maxlength="255"
                    show-counter
                  />
                </div>

                <!-- Title -->
                <div>
                  <FormInput
                    v-model="form.title"
                    label="Ünvan"
                    placeholder="Ünvanı girin (örn: Dr., Prof., vb.)"
                    :error-message="form.errors.title"
                    :maxlength="255"
                    show-counter
                  />
                </div>

                <!-- Affiliation -->
                <div>
                  <FormInput
                    v-model="form.affiliation"
                    label="Kurum/Affiliation"
                    placeholder="Çalıştığı kurumu girin"
                    :error-message="form.errors.affiliation"
                    :maxlength="255"
                    show-counter
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İletişim Bilgileri</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Email -->
                <div>
                  <FormInput
                    v-model="form.email"
                    type="email"
                    label="E-posta"
                    placeholder="E-posta adresini girin"
                    :error-message="form.errors.email"
                    :maxlength="255"
                  >
                    <template #helper>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Katılımcıya bildirimler bu adrese gönderilecektir
                      </p>
                    </template>
                  </FormInput>
                </div>

                <!-- Phone -->
                <div>
                  <FormInput
                    v-model="form.phone"
                    type="tel"
                    label="Telefon"
                    placeholder="Telefon numarasını girin"
                    :error-message="form.errors.phone"
                    :maxlength="255"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Roles -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Roller</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Is Speaker -->
                <div class="flex items-start space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                  <input
                    id="is_speaker"
                    v-model="form.is_speaker"
                    type="checkbox"
                    class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded mt-1"
                  />
                  <div class="flex-1">
                    <label for="is_speaker" class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                      <MicrophoneIcon class="h-4 w-4 mr-2 text-gray-600" />
                      Konuşmacı
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      Bu katılımcı sunumlar yapabilir
                    </p>
                  </div>
                </div>

                <!-- Is Moderator -->
                <div class="flex items-start space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                  <input
                    id="is_moderator"
                    v-model="form.is_moderator"
                    type="checkbox"
                    class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded mt-1"
                  />
                  <div class="flex-1">
                    <label for="is_moderator" class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                      <UserGroupIcon class="h-4 w-4 mr-2 text-gray-600" />
                      Moderatör
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      Bu katılımcı oturum modere edebilir
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bio and Photo -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profil Bilgileri</h3>
              
              <div class="space-y-6">
                <!-- Biography -->
                <div>
                  <FormTextarea
                    v-model="form.bio"
                    label="Biyografi"
                    placeholder="Katılımcının biyografisini girin"
                    :rows="4"
                    :error-message="form.errors.bio"
                    :maxlength="1000"
                    show-counter
                  >
                    <template #helper>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Bu bilgi katılımcının profil sayfasında gösterilecektir
                      </p>
                    </template>
                  </FormTextarea>
                </div>

                <!-- Photo Upload -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Profil Fotoğrafı
                  </label>
                  
                  <div class="space-y-4">
                    <!-- Photo Preview -->
                    <div v-if="photoPreview" class="flex items-center space-x-4">
                      <div class="h-20 w-20 rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-600">
                        <img :src="photoPreview" alt="Önizleme" class="h-full w-full object-cover" />
                      </div>
                      <button
                        type="button"
                        @click="removePhoto"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:ring-2 focus:ring-gray-500 transition-colors"
                      >
                        <TrashIcon class="h-4 w-4 mr-2" />
                        Kaldır
                      </button>
                    </div>

                    <!-- Photo Upload Input -->
                    <div class="flex items-center justify-center w-full">
                      <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 dark:border-gray-600 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                          <PhotoIcon class="w-8 h-8 mb-2 text-gray-400" />
                          <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                            <span class="font-semibold">Fotoğraf yüklemek için tıklayın</span>
                          </p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">JPEG, PNG veya JPG (Max. 2MB)</p>
                        </div>
                        <input
                          type="file"
                          accept="image/jpeg,image/png,image/jpg"
                          @change="handlePhotoUpload"
                          class="hidden"
                        />
                      </label>
                    </div>
                  </div>
                  
                  <p v-if="form.errors.photo" class="text-sm text-red-600 dark:text-red-400 mt-1">
                    {{ form.errors.photo }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Setup Templates -->
          <div class="p-6 space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hızlı Şablonlar</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Yaygın katılımcı türleri için önceden tanımlanmış şablonları kullanabilirsiniz.
              </p>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Speaker Template -->
                <button
                  type="button"
                  @click="applyTemplate('speaker')"
                  class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-left"
                >
                  <div class="flex items-center mb-2">
                    <MicrophoneIcon class="h-5 w-5 text-gray-600 mr-2" />
                    <h4 class="font-medium text-gray-900 dark:text-white">Konuşmacı</h4>
                  </div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Sadece konuşmacı rolü olan katılımcı
                  </p>
                </button>

                <!-- Moderator Template -->
                <button
                  type="button"
                  @click="applyTemplate('moderator')"
                  class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-left"
                >
                  <div class="flex items-center mb-2">
                    <UserGroupIcon class="h-5 w-5 text-gray-600 mr-2" />
                    <h4 class="font-medium text-gray-900 dark:text-white">Moderatör</h4>
                  </div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Sadece moderatör rolü olan katılımcı
                  </p>
                </button>

                <!-- Both Roles Template -->
                <button
                  type="button"
                  @click="applyTemplate('both')"
                  class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-left"
                >
                  <div class="flex items-center mb-2">
                    <UserIcon class="h-5 w-5 text-gray-600 mr-2" />
                    <h4 class="font-medium text-gray-900 dark:text-white">Her İkisi</h4>
                  </div>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Hem konuşmacı hem moderatör
                  </p>
                </button>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <Link
                :href="route('admin.participants.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                İptal
              </Link>
              
              <button
                type="button"
                @click="resetForm"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                Temizle
              </button>
            </div>

            <div class="flex items-center space-x-4">
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-6 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors border border-gray-700"
              >
                <span v-if="form.processing" class="inline-flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Oluşturuluyor...
                </span>
                <span v-else>Katılımcı Oluştur</span>
              </button>
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
import FormTextarea from '@/Components/Forms/FormTextarea.vue'
import { 
  ArrowLeftIcon, 
  UserIcon,
  UserGroupIcon,
  MicrophoneIcon,
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
  first_name: '',
  last_name: '',
  title: '',
  affiliation: '',
  email: '',
  phone: '',
  bio: '',
  photo: null,
  is_speaker: false,
  is_moderator: false
})

// State
const photoPreview = ref(null)

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Katılımcılar', href: route('admin.participants.index') },
  { label: 'Yeni Katılımcı', href: null }
])

// Templates
const templates = {
  speaker: {
    is_speaker: true,
    is_moderator: false
  },
  moderator: {
    is_speaker: false,
    is_moderator: true
  },
  both: {
    is_speaker: true,
    is_moderator: true
  }
}

// Methods
const createParticipant = () => {
  form.post(route('admin.participants.store'), {
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
  photoPreview.value = null
}

const applyTemplate = (templateKey) => {
  const template = templates[templateKey]
  if (!template) return
  
  form.is_speaker = template.is_speaker
  form.is_moderator = template.is_moderator
}

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg']
  if (!allowedTypes.includes(file.type)) {
    alert('Sadece JPEG, PNG veya JPG dosyaları yüklenebilir.')
    return
  }

  // Validate file size (2MB)
  if (file.size > 2 * 1024 * 1024) {
    alert('Dosya boyutu 2MB\'dan küçük olmalıdır.')
    return
  }

  form.photo = file

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    photoPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const removePhoto = () => {
  form.photo = null
  photoPreview.value = null
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
</style>