<!-- Admin/Participants/Edit.vue - Fixed Props and Form Data with Gray Theme -->
<template>
  <AdminLayout 
    :page-title="`${safeParticipant.full_name} - Düzenle`" 
    page-subtitle="Katılımcı bilgilerini güncelleyin"
    :breadcrumbs="breadcrumbs"
  >
    <Head :title="`${safeParticipant.full_name} - Düzenle`" />

    <div class="w-full">
      <!-- Header Section -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-gray-600 rounded-lg flex items-center justify-center">
                  <PencilSquareIcon class="h-6 w-6 text-white" />
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Katılımcı Düzenle</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ safeParticipant.full_name }}</p>
              </div>
            </div>
            
            <!-- Current Photo and Roles -->
            <div class="flex items-center space-x-4">
              <div v-if="safeParticipant.photo_url" class="h-12 w-12 rounded-lg overflow-hidden border-2 border-white shadow-md">
                <img :src="safeParticipant.photo_url" :alt="safeParticipant.full_name" class="h-full w-full object-cover" />
              </div>
              <div v-else class="h-12 w-12 rounded-lg bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center shadow-md">
                <span class="text-white font-semibold text-lg">{{ getInitials(safeParticipant.full_name) }}</span>
              </div>
              
              <div class="flex space-x-2">
                <span
                  v-if="safeParticipant.is_speaker"
                  class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                >
                  <MicrophoneIcon class="w-3 h-3 mr-1" />
                  Konuşmacı
                </span>
                <span
                  v-if="safeParticipant.is_moderator"
                  class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-300 text-gray-900 dark:bg-gray-600 dark:text-gray-200"
                >
                  <UserGroupIcon class="w-3 h-3 mr-1" />
                  Moderatör
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Section -->
        <form @submit.prevent="updateParticipant" class="p-6 space-y-8">
          <!-- Basic Information -->
          <div class="space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <UserIcon class="h-5 w-5 mr-2 text-gray-600" />
                Temel Bilgiler
              </h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <!-- Organization - Full Width -->
              <div class="lg:col-span-3 xl:col-span-4">
                <FormSelect
                  v-model="form.organization_id"
                  label="Organizasyon *"
                  :options="organizations"
                  option-value="id"
                  option-label="name"
                  required
                  :error-message="errors.organization_id"
                  placeholder="Organizasyon seçin"
                />
              </div>

              <!-- First Name -->
              <div class="lg:col-span-1 xl:col-span-2">
                <FormInput
                  v-model="form.first_name"
                  label="Ad *"
                  placeholder="Adı girin"
                  required
                  :error-message="errors.first_name"
                  :maxlength="255"
                  show-counter
                />
              </div>

              <!-- Last Name -->
              <div class="lg:col-span-1 xl:col-span-2">
                <FormInput
                  v-model="form.last_name"
                  label="Soyad *"
                  placeholder="Soyadı girin"
                  required
                  :error-message="errors.last_name"
                  :maxlength="255"
                  show-counter
                />
              </div>

              <!-- Title -->
              <div class="lg:col-span-1 xl:col-span-2">
                <FormInput
                  v-model="form.title"
                  label="Ünvan"
                  placeholder="Ünvanı girin (örn: Dr., Prof., vb.)"
                  :error-message="errors.title"
                  :maxlength="255"
                  show-counter
                />
              </div>

              <!-- Affiliation -->
              <div class="lg:col-span-1 xl:col-span-2">
                <FormInput
                  v-model="form.affiliation"
                  label="Kurum/Affiliation"
                  placeholder="Çalıştığı kurumu girin"
                  :error-message="errors.affiliation"
                  :maxlength="255"
                  show-counter
                />
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-8">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <EnvelopeIcon class="h-5 w-5 mr-2 text-gray-600" />
                İletişim Bilgileri
              </h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Email -->
              <div>
                <FormInput
                  v-model="form.email"
                  type="email"
                  label="E-posta"
                  placeholder="E-posta adresini girin"
                  :error-message="errors.email"
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
                  :error-message="errors.phone"
                  :maxlength="255"
                />
              </div>
            </div>
          </div>

          <!-- Roles -->
          <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-8">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <UserGroupIcon class="h-5 w-5 mr-2 text-gray-600" />
                Roller
              </h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Is Speaker -->
              <div class="flex items-start space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                <input
                  id="is_speaker"
                  v-model="form.is_speaker"
                  type="checkbox"
                  class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded mt-1 transition-colors"
                />
                <div class="flex-1">
                  <label for="is_speaker" class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                    <MicrophoneIcon class="h-4 w-4 mr-2 text-gray-600" />
                    Konuşmacı
                  </label>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Bu katılımcı sunumlar yapabilir
                  </p>
                  <div v-if="form.is_speaker" class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                    {{ safeParticipant.presentations?.length || 0 }} adet sunum var
                  </div>
                </div>
              </div>

              <!-- Is Moderator -->
              <div class="flex items-start space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                <input
                  id="is_moderator"
                  v-model="form.is_moderator"
                  type="checkbox"
                  class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded mt-1 transition-colors"
                />
                <div class="flex-1">
                  <label for="is_moderator" class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                    <UserGroupIcon class="h-4 w-4 mr-2 text-gray-600" />
                    Moderatör
                  </label>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Bu katılımcı oturum modere edebilir
                  </p>
                  <div v-if="form.is_moderator" class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                    {{ safeParticipant.moderated_sessions?.length || 0 }} adet oturum modere ediyor
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bio and Photo -->
          <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-8">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <DocumentTextIcon class="h-5 w-5 mr-2 text-gray-600" />
                Profil Bilgileri
              </h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <!-- Biography -->
              <div class="lg:col-span-2">
                <FormTextarea
                  v-model="form.bio"
                  label="Biyografi"
                  placeholder="Katılımcının biyografisini girin"
                  :rows="6"
                  :error-message="errors.bio"
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
              <div class="lg:col-span-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                  Profil Fotoğrafı
                </label>
                
                <div class="space-y-4">
                  <!-- Current Photo -->
                  <div v-if="safeParticipant.photo_url && !photoPreview" class="relative group">
                    <div class="h-32 w-32 rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-600 mx-auto">
                      <img :src="safeParticipant.photo_url" :alt="safeParticipant.full_name" class="h-full w-full object-cover" />
                    </div>
                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                      <button
                        type="button"
                        @click="removeCurrentPhoto"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 transition-colors"
                      >
                        <TrashIcon class="h-4 w-4 mr-2" />
                        Kaldır
                      </button>
                    </div>
                  </div>

                  <!-- New Photo Preview -->
                  <div v-if="photoPreview" class="relative">
                    <div class="h-32 w-32 rounded-lg overflow-hidden border-2 border-gray-300 dark:border-gray-600 mx-auto">
                      <img :src="photoPreview" alt="Yeni fotoğraf önizleme" class="h-full w-full object-cover" />
                    </div>
                    <div class="absolute -top-2 -right-2">
                      <button
                        type="button"
                        @click="removePhoto"
                        class="inline-flex items-center justify-center w-6 h-6 bg-gray-600 text-white rounded-full hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 transition-colors"
                      >
                        <XMarkIcon class="h-4 w-4" />
                      </button>
                    </div>
                  </div>

                  <!-- Photo Upload Input -->
                  <div class="flex items-center justify-center w-full">
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 dark:border-gray-600 transition-colors">
                      <div class="flex flex-col items-center justify-center pt-2 pb-3">
                        <PhotoIcon class="w-6 h-6 mb-1 text-gray-400" />
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          <span class="font-semibold">Yeni fotoğraf yükle</span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">JPEG, PNG (Max. 2MB)</p>
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
                
                <p v-if="errors.photo" class="text-sm text-red-600 dark:text-red-400 mt-2">
                  {{ errors.photo }}
                </p>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-between pt-8 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3">
              <Link
                :href="safeParticipant.id ? route('admin.participants.show', safeParticipant.id) : route('admin.participants.index')"
                class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                Geri Dön
              </Link>
              
              <Link
                :href="route('admin.participants.index')"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
              >
                Katılımcı Listesi
              </Link>
            </div>

            <div class="flex space-x-3">
              <!-- Reset Changes -->
              <button
                type="button"
                @click="resetForm"
                :disabled="processing || !hasChanges"
                class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <ArrowPathIcon class="h-4 w-4 mr-2" />
                Sıfırla
              </button>

              <!-- Update Participant -->
              <button
                type="submit"
                :disabled="processing || !hasChanges"
                class="inline-flex items-center px-6 py-2.5 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed border border-gray-700"
              >
                <template v-if="processing">
                  <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  Güncelleniyor...
                </template>
                <template v-else>
                  <CheckIcon class="h-4 w-4 mr-2" />
                  Katılımcıyı Güncelle
                </template>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Change Summary -->
      <div v-if="hasChanges" class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-start">
          <ExclamationTriangleIcon class="h-5 w-5 text-gray-600 dark:text-gray-400 mt-0.5 flex-shrink-0" />
          <div class="ml-3">
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Kaydedilmemiş Değişiklikler</h4>
            <div class="mt-2 text-sm text-gray-700 dark:text-gray-200">
              <p>Formu değiştirdiniz ancak henüz kaydetmediniz. Sayfadan ayrılmadan önce değişikliklerinizi kaydetmeyi unutmayın.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount, onMounted, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormInput from '@/Components/Forms/FormInput.vue'
import FormSelect from '@/Components/Forms/FormSelect.vue'
import FormTextarea from '@/Components/Forms/FormTextarea.vue'
import { 
  ArrowLeftIcon, 
  PencilSquareIcon,
  UserIcon,
  UserGroupIcon,
  MicrophoneIcon,
  EnvelopeIcon,
  DocumentTextIcon,
  PhotoIcon,
  TrashIcon,
  XMarkIcon,
  CheckIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Props - Backend'den gelen veriler
const props = defineProps({
  // Participant verisi
  participant: {
    type: Object,
    required: false,
    default: () => ({
      id: null,
      organization_id: null,
      first_name: '',
      last_name: '',
      title: '',
      email: '',
      phone: '',
      affiliation: '',
      bio: '',
      photo_url: '',
      is_speaker: false,
      is_moderator: false,
      organization: null,
      presentations: [],
      moderated_sessions: []
    })
  },
  
  // Form options
  organizations: {
    type: Array,
    default: () => []
  },
  
  // Errors
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Güvenli data access
const safeParticipant = computed(() => ({
  id: props.participant?.id || null,
  organization_id: props.participant?.organization_id || null,
  first_name: props.participant?.first_name || '',
  last_name: props.participant?.last_name || '',
  full_name: props.participant?.full_name || `${props.participant?.first_name || ''} ${props.participant?.last_name || ''}`.trim() || 'İsimsiz Katılımcı',
  title: props.participant?.title || '',
  email: props.participant?.email || '',
  phone: props.participant?.phone || '',
  affiliation: props.participant?.affiliation || '',
  bio: props.participant?.bio || '',
  photo_url: props.participant?.photo_url || '',
  is_speaker: Boolean(props.participant?.is_speaker),
  is_moderator: Boolean(props.participant?.is_moderator),
  organization: props.participant?.organization || null,
  presentations: props.participant?.presentations || [],
  moderated_sessions: props.participant?.moderated_sessions || []
}))

// Form setup - FIXED: Initialize after safeParticipant is ready
const form = reactive({
  organization_id: null,
  first_name: '',
  last_name: '',
  title: '',
  email: '',
  phone: '',
  affiliation: '',
  bio: '',
  photo: null,
  is_speaker: false,
  is_moderator: false
})

// Processing state for manual form handling
const processing = ref(false)

// Original form values for reset
const originalForm = ref({
  organization_id: null,
  first_name: '',
  last_name: '',
  title: '',
  email: '',
  phone: '',
  affiliation: '',
  bio: '',
  is_speaker: false,
  is_moderator: false
})

// State
const photoPreview = ref(null)

// Error handling - props.errors kullan
const errors = computed(() => props.errors || {})

// Initialize form data after component mount
onMounted(() => {
  // Set form values from safeParticipant
  form.organization_id = safeParticipant.value.organization_id
  form.first_name = safeParticipant.value.first_name
  form.last_name = safeParticipant.value.last_name
  form.title = safeParticipant.value.title
  form.email = safeParticipant.value.email
  form.phone = safeParticipant.value.phone
  form.affiliation = safeParticipant.value.affiliation
  form.bio = safeParticipant.value.bio
  form.is_speaker = safeParticipant.value.is_speaker
  form.is_moderator = safeParticipant.value.is_moderator
  
  // Update original form values too
  originalForm.value = {
    organization_id: safeParticipant.value.organization_id,
    first_name: safeParticipant.value.first_name,
    last_name: safeParticipant.value.last_name,
    title: safeParticipant.value.title,
    email: safeParticipant.value.email,
    phone: safeParticipant.value.phone,
    affiliation: safeParticipant.value.affiliation,
    bio: safeParticipant.value.bio,
    is_speaker: safeParticipant.value.is_speaker,
    is_moderator: safeParticipant.value.is_moderator
  }
})

// Breadcrumbs
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Katılımcılar', href: route('admin.participants.index') },
  { label: safeParticipant.value.full_name, href: safeParticipant.value.id ? route('admin.participants.show', safeParticipant.value.id) : null },
  { label: 'Düzenle', href: null }
])

// Computed - FIXED: hasChanges logic
const hasChanges = computed(() => {
  return Object.keys(originalForm.value).some(key => {
    return form[key] !== originalForm.value[key]
  }) || photoPreview.value !== null
})

// Helper functions
const getInitials = (name) => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Methods - FIXED: Manual form submission
const updateParticipant = async () => {
  if (!safeParticipant.value.id) {
    console.error('Participant ID missing')
    return
  }
  
  processing.value = true
  
  try {
    await router.put(route('admin.participants.update', safeParticipant.value.id), form, {
      onSuccess: () => {
        // Update original form values
        Object.keys(form).forEach(key => {
          if (key !== 'photo') {
            originalForm.value[key] = form[key]
          }
        })
        photoPreview.value = null
        processing.value = false
      },
      onError: (errors) => {
        console.error('Form submission errors:', errors)
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
  Object.keys(originalForm.value).forEach(key => {
    form[key] = originalForm.value[key]
  })
  form.photo = null
  photoPreview.value = null
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

const removeCurrentPhoto = () => {
  // This would set a flag to remove the current photo
  form.photo = 'remove'
  photoPreview.value = null
}

// Warn user about unsaved changes
let isSubmitting = false

watch(() => form, () => {
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
watch(() => processing, (newVal) => {
  if (newVal) {
    isSubmitting = true
    window.removeEventListener('beforeunload', handleBeforeUnload)
  } else {
    isSubmitting = false
  }
})

// Cleanup
onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', handleBeforeUnload)
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
    0 0 0 2px rgb(107 114 128 / 0.1),
    0 0 0 4px rgb(107 114 128 / 0.1),
    0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Dark mode enhancements */
.dark .form-input:focus {
  box-shadow: 
    0 0 0 2px rgb(107 114 128 / 0.2),
    0 0 0 4px rgb(107 114 128 / 0.1),
    0 4px 6px -1px rgba(0, 0, 0, 0.3);
}

/* Focus styles for checkboxes */
input[type="checkbox"]:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.1);
}

/* Custom file upload styling */
input[type="file"] {
  opacity: 0;
  position: absolute;
  pointer-events: none;
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>