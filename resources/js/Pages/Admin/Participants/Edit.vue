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
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-10 w-10 bg-slate-600 rounded-lg flex items-center justify-center">
                  <PencilSquareIcon class="h-6 w-6 text-white" />
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Katılımcı Düzenle</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ safeParticipant.full_name }}</p>
              </div>
            </div>
            
            <!-- Current Photo and Roles -->
            <div class="flex items-center space-x-4">
              <div v-if="safeParticipant.photo_url" class="h-12 w-12 rounded-lg overflow-hidden border-2 border-white shadow-md">
                <img :src="safeParticipant.photo_url" :alt="safeParticipant.full_name" class="h-full w-full object-cover" />
              </div>
              <div v-else class="h-12 w-12 rounded-lg bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center shadow-md">
                <span class="text-white font-semibold text-lg">{{ getInitials(safeParticipant.full_name) }}</span>
              </div>
              
              <div class="flex space-x-2">
                <span
                  v-if="safeParticipant.is_speaker"
                  class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-300"
                >
                  <MicrophoneIcon class="w-3 h-3 mr-1" />
                  Konuşmacı
                </span>
                <span
                  v-if="safeParticipant.is_moderator"
                  class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-300 text-slate-900 dark:bg-slate-600 dark:text-slate-200"
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
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center">
                <UserIcon class="h-5 w-5 mr-2 text-slate-600" />
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
                  :error-message="form.errors.organization_id"
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
                  :error-message="form.errors.first_name"
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
                  :error-message="form.errors.last_name"
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
                  :error-message="form.errors.title"
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
                  :error-message="form.errors.affiliation"
                  :maxlength="255"
                  show-counter
                />
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="space-y-6 border-t border-slate-200 dark:border-slate-700 pt-8">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center">
                <EnvelopeIcon class="h-5 w-5 mr-2 text-slate-600" />
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
                  :error-message="form.errors.email"
                  :maxlength="255"
                >
                  <template #helper>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
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

          <!-- Roles -->
          <div class="space-y-6 border-t border-slate-200 dark:border-slate-700 pt-8">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center">
                <UserGroupIcon class="h-5 w-5 mr-2 text-slate-600" />
                Roller
              </h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Is Speaker -->
              <div class="flex items-start space-x-3 p-4 border border-slate-200 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                <input
                  id="is_speaker"
                  v-model="form.is_speaker"
                  type="checkbox"
                  class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded mt-1 transition-colors"
                />
                <div class="flex-1">
                  <label for="is_speaker" class="text-sm font-medium text-slate-700 dark:text-slate-300 flex items-center">
                    <MicrophoneIcon class="h-4 w-4 mr-2 text-slate-600" />
                    Konuşmacı
                  </label>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Bu katılımcı sunumlar yapabilir
                  </p>
                  <div v-if="form.is_speaker" class="mt-2 text-xs text-slate-600 dark:text-slate-400">
                    {{ safeParticipant.presentations?.length || 0 }} adet sunum var
                  </div>
                </div>
              </div>

              <!-- Is Moderator -->
              <div class="flex items-start space-x-3 p-4 border border-slate-200 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                <input
                  id="is_moderator"
                  v-model="form.is_moderator"
                  type="checkbox"
                  class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded mt-1 transition-colors"
                />
                <div class="flex-1">
                  <label for="is_moderator" class="text-sm font-medium text-slate-700 dark:text-slate-300 flex items-center">
                    <UserGroupIcon class="h-4 w-4 mr-2 text-slate-600" />
                    Moderatör
                  </label>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Bu katılımcı oturum modere edebilir
                  </p>
                  <div v-if="form.is_moderator" class="mt-2 text-xs text-slate-600 dark:text-slate-400">
                    {{ safeParticipant.moderated_sessions?.length || 0 }} adet oturum modere ediyor
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bio and Photo -->
          <div class="space-y-6 border-t border-slate-200 dark:border-slate-700 pt-8">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center">
                <DocumentTextIcon class="h-5 w-5 mr-2 text-slate-600" />
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
                  :error-message="form.errors.bio"
                  :maxlength="1000"
                  show-counter
                >
                  <template #helper>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                      Bu bilgi katılımcının profil sayfasında gösterilecektir
                    </p>
                  </template>
                </FormTextarea>
              </div>

              <!-- Photo Upload -->
              <div class="lg:col-span-1">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                  Profil Fotoğrafı
                </label>
                
                <div class="space-y-4">
                  <!-- Current Photo -->
                  <div v-if="safeParticipant.photo_url && !photoPreview" class="relative group">
                    <div class="h-32 w-32 rounded-lg overflow-hidden border-2 border-slate-200 dark:border-slate-600 mx-auto">
                      <img :src="safeParticipant.photo_url" :alt="safeParticipant.full_name" class="h-full w-full object-cover" />
                    </div>
                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                      <button
                        type="button"
                        @click="removeCurrentPhoto"
                        class="inline-flex items-center px-3 py-2 border border-slate-300 text-sm font-medium rounded-md text-white bg-slate-600 hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 transition-colors"
                      >
                        <TrashIcon class="h-4 w-4 mr-2" />
                        Kaldır
                      </button>
                    </div>
                  </div>

                  <!-- New Photo Preview -->
                  <div v-if="photoPreview" class="relative">
                    <div class="h-32 w-32 rounded-lg overflow-hidden border-2 border-slate-300 dark:border-slate-600 mx-auto">
                      <img :src="photoPreview" alt="Yeni fotoğraf önizleme" class="h-full w-full object-cover" />
                    </div>
                    <div class="absolute -top-2 -right-2">
                      <button
                        type="button"
                        @click="removePhoto"
                        class="inline-flex items-center justify-center w-6 h-6 bg-slate-600 text-white rounded-full hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 transition-colors"
                      >
                        <XMarkIcon class="h-4 w-4" />
                      </button>
                    </div>
                  </div>

                  <!-- Photo Upload Input -->
                  <div class="flex items-center justify-center w-full">
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 dark:border-slate-600 transition-colors">
                      <div class="flex flex-col items-center justify-center pt-2 pb-3">
                        <PhotoIcon class="w-6 h-6 mb-1 text-slate-400" />
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                          <span class="font-semibold">Yeni fotoğraf yükle</span>
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">JPEG, PNG (Max. 2MB)</p>
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
                
                <p v-if="form.errors.photo" class="text-sm text-red-600 dark:text-red-400 mt-2">
                  {{ form.errors.photo }}
                </p>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-between pt-8 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center space-x-3">
              <Link
                :href="safeParticipant.id ? route('admin.participants.show', safeParticipant.id) : route('admin.participants.index')"
                class="inline-flex items-center px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                Geri Dön
              </Link>
              
              <Link
                :href="route('admin.participants.index')"
                class="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
              >
                Katılımcı Listesi
              </Link>
            </div>

            <div class="flex space-x-3">
              <!-- Reset Changes -->
              <button
                type="button"
                @click="resetForm"
                :disabled="form.processing || !hasChanges"
                class="inline-flex items-center px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <ArrowPathIcon class="h-4 w-4 mr-2" />
                Sıfırla
              </button>

              <!-- Update Participant -->
              <button
                type="submit"
                :disabled="form.processing || !hasChanges"
                class="inline-flex items-center px-6 py-2.5 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-900 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed border border-slate-700"
              >
                <template v-if="form.processing">
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
      <div v-if="hasChanges" class="bg-slate-50 dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
        <div class="flex items-start">
          <ExclamationTriangleIcon class="h-5 w-5 text-slate-600 dark:text-slate-400 mt-0.5 flex-shrink-0" />
          <div class="ml-3">
            <h4 class="text-sm font-medium text-slate-900 dark:text-slate-100">Kaydedilmemiş Değişiklikler</h4>
            <div class="mt-2 text-sm text-slate-700 dark:text-slate-200">
              <p>Formu değiştirdiniz ancak henüz kaydetmediniz. Sayfadan ayrılmadan önce değişikliklerinizi kaydetmeyi unutmayın.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
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

const props = defineProps({
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
  organizations: {
    type: Array,
    default: () => []
  }
})

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

const form = useForm({
  organization_id: safeParticipant.value.organization_id,
  first_name: safeParticipant.value.first_name,
  last_name: safeParticipant.value.last_name,
  title: safeParticipant.value.title,
  email: safeParticipant.value.email,
  phone: safeParticipant.value.phone,
  affiliation: safeParticipant.value.affiliation,
  bio: safeParticipant.value.bio,
  photo: null,
  is_speaker: safeParticipant.value.is_speaker,
  is_moderator: safeParticipant.value.is_moderator
})

const originalForm = ref({
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
})

const photoPreview = ref(null)

const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Katılımcılar', href: route('admin.participants.index') },
  { label: safeParticipant.value.full_name, href: safeParticipant.value.id ? route('admin.participants.show', safeParticipant.value.id) : null },
  { label: 'Düzenle', href: null }
])

const hasChanges = computed(() => {
  return Object.keys(originalForm.value).some(key => {
    return form[key] !== originalForm.value[key]
  }) || photoPreview.value !== null
})

const getInitials = (name) => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const updateParticipant = () => {
  if (!safeParticipant.value.id) {
    return
  }

  isSubmitting.value = true

  form.put(route('admin.participants.update', safeParticipant.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      originalForm.value = {
        organization_id: form.organization_id,
        first_name: form.first_name,
        last_name: form.last_name,
        title: form.title,
        email: form.email,
        phone: form.phone,
        affiliation: form.affiliation,
        bio: form.bio,
        is_speaker: form.is_speaker,
        is_moderator: form.is_moderator
      }
      form.photo = null
      photoPreview.value = null
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}

const resetForm = () => {
  form.organization_id = originalForm.value.organization_id
  form.first_name = originalForm.value.first_name
  form.last_name = originalForm.value.last_name
  form.title = originalForm.value.title
  form.email = originalForm.value.email
  form.phone = originalForm.value.phone
  form.affiliation = originalForm.value.affiliation
  form.bio = originalForm.value.bio
  form.is_speaker = originalForm.value.is_speaker
  form.is_moderator = originalForm.value.is_moderator
  form.photo = null
  photoPreview.value = null
  form.clearErrors()
}

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg']
  if (!allowedTypes.includes(file.type)) {
    alert('Sadece JPEG, PNG veya JPG dosyaları yüklenebilir.')
    return
  }

  if (file.size > 2 * 1024 * 1024) {
    alert('Dosya boyutu 2MB\'dan küçük olmalıdır.')
    return
  }

  form.photo = file

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
  form.photo = 'remove'
  photoPreview.value = null
}

const isSubmitting = ref(false)

watch(form, () => {
  if (hasChanges.value && !isSubmitting.value) {
    window.addEventListener('beforeunload', handleBeforeUnload)
  } else {
    window.removeEventListener('beforeunload', handleBeforeUnload)
  }
}, { deep: true })

const handleBeforeUnload = (e) => {
  if (hasChanges.value && !isSubmitting.value) {
    e.preventDefault()
    e.returnValue = ''
  }
}

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