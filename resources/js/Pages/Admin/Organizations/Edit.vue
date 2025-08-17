<!-- Admin/Organizations/Edit.vue - Gray Theme -->
<template>
  <AdminLayout page-title="Organizasyon Düzenle"
    :page-subtitle="`${organization.name} organizasyon bilgilerini düzenleyin`" :breadcrumbs="breadcrumbs">

    <Head :title="`${organization.name} - Düzenle`" />

    <div class="w-full space-y-8">
      <!-- Header Section -->
      <div
        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="relative">
          <!-- Banner with gray gradient -->
          <div class="h-48 bg-gradient-to-r from-gray-800 to-gray-900 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute inset-0 flex items-end">
              <div class="p-8 text-white w-full">
                <div class="flex items-end justify-between">
                  <div class="flex items-center space-x-6">
                    <!-- Logo -->
                    <div
                      class="h-20 w-20 bg-white/20 backdrop-blur-sm rounded-xl overflow-hidden flex items-center justify-center">
                      <img v-if="organization.logo_url && !logoPreview" :src="organization.logo_url"
                        :alt="organization.name" class="h-full w-full object-contain bg-white/10 rounded-xl p-2" />
                      <img v-else-if="logoPreview" :src="logoPreview" :alt="form.name"
                        class="h-full w-full object-contain bg-white/10 rounded-xl p-2" />
                      <BuildingOfficeIcon v-else class="h-12 w-12 text-white/70" />
                    </div>
                    <div>
                      <h1 class="text-3xl font-bold mb-1">{{ form.name || organization.name }}</h1>
                      <div class="flex items-center space-x-3">
                        <span
                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                          <BuildingOfficeIcon class="w-4 h-4 mr-2" />
                          Organizasyon
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" :class="form.is_active
                          ? 'bg-green-500/20 text-green-100'
                          : 'bg-red-500/20 text-red-100'">
                          <span class="w-2 h-2 mr-2 rounded-full bg-current"></span>
                          {{ form.is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                        <span
                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-500/20 text-blue-100">
                          <PencilIcon class="w-4 h-4 mr-2" />
                          Düzenleniyor
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Edit Stats -->
                  <div class="flex items-center space-x-8 text-white/90">
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ organization.events_count || 0 }}</div>
                      <div class="text-sm">Etkinlik</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ organization.participants_count || 0 }}</div>
                      <div class="text-sm">Katılımcı</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ organization.sponsors_count || 0 }}</div>
                      <div class="text-sm">Sponsor</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Meta Bar -->
          <div class="px-8 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-6">
              <!-- Contact Info Preview -->
              <div v-if="form.contact_email" class="flex items-center space-x-2">
                <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ form.contact_email }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">E-posta (Önizleme)</div>
                </div>
              </div>

              <div v-if="form.contact_phone" class="flex items-center space-x-2">
                <PhoneIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ form.contact_phone }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Telefon (Önizleme)</div>
                </div>
              </div>

              <!-- Creation Date -->
              <div class="flex items-center space-x-2">
                <CalendarIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(organization.created_at)
                    }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Oluşturma Tarihi</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Bar -->
          <div class="px-8 py-4 flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900">
            <div class="flex items-center space-x-3">
              <!-- Back to List -->
              <Link :href="route('admin.organizations.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm">
              <ArrowLeftIcon class="h-4 w-4 mr-2" />
              Organizasyon Listesi
              </Link>

              <!-- View Original -->
              <Link :href="route('admin.organizations.show', organization.id)"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm">
              <EyeIcon class="h-4 w-4 mr-2" />
              Orijinali Görüntüle
              </Link>
            </div>

            <div class="flex items-center space-x-3">
              <!-- Reset Button -->
              <button type="button" @click="resetForm"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm">
                <ArrowPathIcon class="h-4 w-4 mr-2" />
                Sıfırla
              </button>

              <!-- Save Button -->
              <button type="submit" form="edit-organization-form" :disabled="form.processing"
                class="inline-flex items-center px-6 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm border border-gray-700">
                <span v-if="form.processing" class="inline-flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  Kaydediliyor...
                </span>
                <span v-else>
                  <CheckIcon class="h-4 w-4 mr-2" />
                  Değişiklikleri Kaydet
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="xl:col-span-3 space-y-8">
          <!-- Form Card -->
          <div
            class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <form id="edit-organization-form" @submit.prevent="updateOrganization"
              class="divide-y divide-gray-200 dark:divide-gray-700">

              <!-- Basic Information -->
              <div class="p-6 space-y-6">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Bilgiler</h3>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Organization Name -->
                    <div class="md:col-span-2">
                      <FormInput v-model="form.name" label="Organizasyon Adı" placeholder="Organizasyon adını girin"
                        required :error-message="form.errors.name" :maxlength="255" show-counter />
                    </div>

                    <!-- Contact Email -->
                    <div>
                      <FormInput v-model="form.contact_email" type="email" label="İletişim E-postası"
                        placeholder="ornek@organizasyon.com" :error-message="form.errors.contact_email"
                        :maxlength="255">
                        <template #helper>
                          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Ana iletişim e-posta adresi
                          </p>
                        </template>
                      </FormInput>
                    </div>

                    <!-- Contact Phone -->
                    <div>
                      <FormInput v-model="form.contact_phone" type="tel" label="İletişim Telefonu"
                        placeholder="+90 (555) 123 45 67" :error-message="form.errors.contact_phone" :maxlength="50">
                        <template #helper>
                          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            İletişim telefon numarası
                          </p>
                        </template>
                      </FormInput>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                      <FormTextarea v-model="form.description" label="Açıklama"
                        placeholder="Organizasyonunuz hakkında kısa bir açıklama yazın..."
                        :error-message="form.errors.description" :rows="6" :maxlength="2000" show-counter>
                        <template #helper>
                          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Organizasyonunuzun misyonu, vizyonu ve faaliyet alanları hakkında bilgi verin
                          </p>
                        </template>
                      </FormTextarea>
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-2">
                      <div
                        class="flex items-center space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                        <input id="is_active" v-model="form.is_active" type="checkbox"
                          class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded" />
                        <div class="flex-1">
                          <label for="is_active"
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
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

              <!-- Logo Upload -->
              <div class="p-6 space-y-6">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logo</h3>

                  <div class="space-y-6">
                    <!-- Current Logo -->
                    <div v-if="organization.logo_url && !logoPreview && !form._remove_logo"
                      class="flex items-center space-x-4">
                      <div
                        class="h-20 w-20 rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-600 bg-white">
                        <img :src="organization.logo_url" :alt="organization.name"
                          class="h-full w-full object-contain p-2" />
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Mevcut Logo</p>
                        <button type="button" @click="removeCurrentLogo"
                          class="inline-flex items-center px-3 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:ring-2 focus:ring-red-500 transition-colors mt-2">
                          <TrashIcon class="h-4 w-4 mr-2" />
                          Mevcut Logoyu Kaldır
                        </button>
                      </div>
                    </div>

                    <!-- Logo Upload -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ (organization.logo_url && !logoPreview && !form._remove_logo) ? 'Yeni Logo Yükle' :
                        'Organizasyon Logosu'
                        }}
                      </label>

                      <div class="space-y-4">
                        <!-- Logo Preview -->
                        <div v-if="logoPreview" class="flex items-center space-x-4">
                          <div
                            class="h-20 w-20 rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-600 bg-white">
                            <img :src="logoPreview" alt="Logo önizleme" class="h-full w-full object-contain p-2" />
                          </div>
                          <button type="button" @click="removeLogo"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:ring-2 focus:ring-gray-500 transition-colors">
                            <TrashIcon class="h-4 w-4 mr-2" />
                            Kaldır
                          </button>
                        </div>

                        <!-- Logo Upload Input -->
                        <div class="flex items-center justify-center w-full">
                          <label
                            class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 dark:border-gray-600 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                              <PhotoIcon class="w-8 h-8 mb-2 text-gray-400" />
                              <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-semibold">Logo yüklemek için tıklayın</span>
                              </p>
                              <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF veya SVG (Max. 2MB)</p>
                            </div>
                            <input type="file" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
                              @change="handleLogoUpload" class="hidden" />
                          </label>
                        </div>
                      </div>

                      <p v-if="form.errors.logo" class="text-sm text-red-600 dark:text-red-400 mt-1">
                        {{ form.errors.logo }}
                      </p>
                      <p v-if="form.errors._remove_logo" class="text-sm text-red-600 dark:text-red-400 mt-1">
                        {{ form.errors._remove_logo }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <Link :href="route('admin.organizations.index')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                  İptal
                  </Link>

                  <button type="button" @click="resetForm"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    Sıfırla
                  </button>
                </div>

                <div class="flex items-center space-x-4">
                  <!-- View Organization -->
                  <Link :href="route('admin.organizations.show', organization.id)"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                  <EyeIcon class="h-4 w-4 mr-2" />
                  Görüntüle
                  </Link>

                  <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center px-6 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors border border-gray-700">
                    <span v-if="form.processing" class="inline-flex items-center">
                      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                      </svg>
                      Güncelleniyor...
                    </span>
                    <span v-else>Organizasyon Güncelle</span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Live Preview -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Önizleme</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <BuildingOfficeIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Organizasyon Adı</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ form.name || 'Belirtilmemiş'
                  }}</span>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <EnvelopeIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">E-posta</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ form.contact_email ||
                  'Belirtilmemiş'
                  }}</span>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <PhoneIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Telefon</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ form.contact_phone ||
                  'Belirtilmemiş'
                  }}</span>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <CheckCircleIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Durum</span>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="form.is_active
                  ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                  : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                  {{ form.is_active ? 'Aktif' : 'Pasif' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Changes Summary -->
          <div v-if="hasChanges"
            class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-start">
              <ExclamationTriangleIcon class="h-5 w-5 text-gray-600 dark:text-gray-400 mt-0.5 flex-shrink-0" />
              <div class="ml-3">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Kaydedilmemiş Değişiklikler</h4>
                <div class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                  <p>Formu değiştirdiniz ancak henüz kaydetmediniz. Sayfadan ayrılmadan önce değişikliklerinizi
                    kaydetmeyi
                    unutmayın.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Current Organization Details -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Mevcut Organizasyon Bilgileri</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <CalendarIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Kayıt Tarihi</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(organization.created_at)
                  }}</span>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <ClockIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Son Güncelleme</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(organization.updated_at)
                  }}</span>
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
import FormInput from '@/Components/Forms/FormInput.vue'
import FormTextarea from '@/Components/Forms/FormTextarea.vue'
import {
  ArrowLeftIcon,
  CheckCircleIcon,
  PhotoIcon,
  TrashIcon,
  EyeIcon,
  PencilIcon,
  BuildingOfficeIcon,
  CalendarIcon,
  ClockIcon,
  EnvelopeIcon,
  PhoneIcon,
  CheckIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  organization: {
    type: Object,
    required: true
  }
})

// Form - Model alanlarına göre güncellendi
const form = useForm({
  name: props.organization.name,
  description: props.organization.description || '',
  contact_email: props.organization.contact_email || '',
  contact_phone: props.organization.contact_phone || '',
  logo: null,
  is_active: props.organization.is_active,
  _remove_logo: false
})

// State
const logoPreview = ref(null)

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Organizasyonlar', href: route('admin.organizations.index') },
  { label: props.organization.name, href: route('admin.organizations.show', props.organization.id) },
  { label: 'Düzenle', href: null }
])

// Track changes
const hasChanges = computed(() => {
  return form.name !== props.organization.name ||
    form.description !== (props.organization.description || '') ||
    form.contact_email !== (props.organization.contact_email || '') ||
    form.contact_phone !== (props.organization.contact_phone || '') ||
    form.is_active !== props.organization.is_active ||
    form.logo !== null ||
    form._remove_logo
})

// Methods
const updateOrganization = () => {
  // Debug için console log
  console.log('Form Data:', {
    name: form.name,
    description: form.description,
    contact_email: form.contact_email,
    contact_phone: form.contact_phone,
    is_active: form.is_active,
    logo: form.logo,
    _remove_logo: form._remove_logo
  });

  // Transform kullanarak veriyi hazırlayın
  form.transform((data) => ({
    ...data,
    _method: 'PUT'
  })).post(route('admin.organizations.update', props.organization.id), {
    forceFormData: true,
    onSuccess: () => {
      console.log('Organization updated successfully');
      form.reset('_method'); // _method'u temizle
    },
    onError: (errors) => {
      console.log('Update errors:', errors);
      form.reset('_method'); // _method'u temizle
    }
  })
}

const resetForm = () => {
  form.reset()
  form.name = props.organization.name
  form.description = props.organization.description || ''
  form.contact_email = props.organization.contact_email || ''
  form.contact_phone = props.organization.contact_phone || ''
  form.is_active = props.organization.is_active
  form._remove_logo = false
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
  form._remove_logo = false

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

const removeCurrentLogo = () => {
  form._remove_logo = true
  form.logo = null
  logoPreview.value = null
}

const formatDate = (dateString) => {
  if (!dateString) return '-'

  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return '-'

    const day = date.getDate().toString().padStart(2, '0')
    const month = (date.getMonth() + 1).toString().padStart(2, '0')
    const year = date.getFullYear()

    return `${day}/${month}/${year}`
  } catch (error) {
    console.error('Date formatting error:', error)
    return '-'
  }
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