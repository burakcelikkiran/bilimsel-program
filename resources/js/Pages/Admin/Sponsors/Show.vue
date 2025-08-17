<!-- Admin/Sponsors/Show.vue - Gray Theme (Clean) -->
<template>
  <AdminLayout
    :page-title="safeSponsor.name"
    :page-subtitle="`${safeSponsor.organization?.name || 'Organizasyon'} - ${getSponsorLevelDisplay(safeSponsor.sponsor_level)} Sponsor`"
    :breadcrumbs="breadcrumbs"
  >
    <Head :title="safeSponsor.name" />

    <div class="w-full space-y-8">
      <!-- Header Section -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="relative">
          <!-- Banner with gray gradient -->
          <div class="h-48 bg-gradient-to-r from-gray-800 to-gray-900 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute inset-0 flex items-end">
              <div class="p-8 text-white w-full">
                <div class="flex items-end justify-between">
                  <div class="flex items-center space-x-6">
                    <!-- Logo -->
                    <div class="h-20 w-20 bg-white/20 backdrop-blur-sm rounded-xl overflow-hidden flex items-center justify-center">
                      <img 
                        v-if="safeSponsor.logo_url" 
                        :src="safeSponsor.logo_url" 
                        :alt="safeSponsor.name"
                        class="h-full w-full object-contain bg-white/10 rounded-xl p-2"
                      />
                      <BuildingOffice2Icon v-else class="h-12 w-12 text-white/70" />
                    </div>
                    <div>
                      <h1 class="text-3xl font-bold mb-1">{{ safeSponsor.name }}</h1>
                      <div class="flex items-center space-x-3">
                        <span
                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white"
                        >
                          <component :is="getSponsorLevelIcon(safeSponsor.sponsor_level)" class="w-4 h-4 mr-2" />
                          {{ getSponsorLevelDisplay(safeSponsor.sponsor_level) }} Sponsor
                        </span>
                        <span
                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                          :class="safeSponsor.is_active 
                            ? 'bg-green-500/20 text-green-100' 
                            : 'bg-red-500/20 text-red-100'"
                        >
                          <span class="w-2 h-2 mr-2 rounded-full bg-current"></span>
                          {{ safeSponsor.is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Quick Stats -->
                  <div class="flex items-center space-x-8 text-white/90">
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ safeSponsor.program_sessions?.length || 0 }}</div>
                      <div class="text-sm">Oturum</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ safeSponsor.presentations?.length || 0 }}</div>
                      <div class="text-sm">Sunum</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ calculateTotalSponsorship() }}</div>
                      <div class="text-sm">Toplam Sponsorluk</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Meta Bar -->
          <div class="px-8 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-6">
              <!-- Level Info -->
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Sponsor Seviyesi:</span>
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                  :class="getSponsorLevelClasses(safeSponsor.sponsor_level)"
                >
                  <component :is="getSponsorLevelIcon(safeSponsor.sponsor_level)" class="w-4 h-4 mr-2" />
                  {{ getSponsorLevelDisplay(safeSponsor.sponsor_level) }}
                </span>
              </div>

              <!-- Contact Info -->
              <div v-if="safeSponsor.contact_email" class="flex items-center space-x-2">
                <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <a :href="`mailto:${safeSponsor.contact_email}`" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeSponsor.contact_email }}
                  </a>
                  <div class="text-xs text-gray-500 dark:text-gray-400">E-posta</div>
                </div>
              </div>

              <div v-if="safeSponsor.website" class="flex items-center space-x-2">
                <GlobeAltIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <a :href="safeSponsor.website" target="_blank" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                    {{ formatWebsite(safeSponsor.website) }}
                  </a>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Website</div>
                </div>
              </div>

              <!-- Organization Info -->
              <div class="flex items-center space-x-2">
                <BuildingOfficeIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ safeSponsor.organization?.name || 'Organizasyon yok' }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Organizasyon</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Bar -->
          <div class="px-8 py-4 flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900">
            <div class="flex items-center space-x-3">
              <!-- Back to List -->
              <Link
                :href="route('admin.sponsors.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                Sponsor Listesi
              </Link>
            </div>

            <div class="flex items-center space-x-3">
              <!-- Edit Button -->
              <Link
                v-if="safeSponsor.can_edit"
                :href="route('admin.sponsors.edit', safeSponsor.id)"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <PencilSquareIcon class="h-4 w-4 mr-2" />
                Düzenle
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="xl:col-span-3 space-y-8">
          <!-- Sponsored Program Sessions -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sponsorladığı Oturumlar</h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ safeSponsor.program_sessions?.length || 0 }} oturum
              </span>
            </div>

            <!-- Program Sessions List -->
            <div v-if="safeSponsor.program_sessions?.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="session in safeSponsor.program_sessions"
                :key="session.id"
                class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                      {{ session.title }}
                    </h4>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400 mb-3">
                      <div class="flex items-center">
                        <CalendarIcon class="h-4 w-4 mr-1" />
                        {{ formatSessionInfo(session) }}
                      </div>
                      <div class="flex items-center">
                        <ClockIcon class="h-4 w-4 mr-1" />
                        {{ session.formatted_time_range || 'Zaman belirtilmemiş' }}
                      </div>
                    </div>

                    <div v-if="session.venue" class="text-sm text-gray-600 dark:text-gray-400">
                      <span class="font-medium">Salon:</span> {{ session.venue.display_name || session.venue.name }}
                    </div>
                  </div>
                  
                  <!-- Session Actions -->
                  <div class="flex items-center space-x-2 ml-4">
                    <Link
                      :href="route('admin.program-sessions.show', session.id)"
                      class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                      title="Oturumu Görüntüle"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </Link>
                    <Link
                      :href="route('admin.program-sessions.edit', session.id)"
                      class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                      title="Oturumu Düzenle"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </Link>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Sessions State -->
            <div v-else class="text-center py-16">
              <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                <DocumentTextIcon class="h-full w-full" />
              </div>
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Henüz sponsorluk yok</h4>
              <p class="text-gray-500 dark:text-gray-400">Bu sponsor henüz hiçbir oturumu sponsorlamıyor.</p>
            </div>
          </div>

          <!-- Sponsored Presentations -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sponsorladığı Sunumlar</h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ safeSponsor.presentations?.length || 0 }} sunum
              </span>
            </div>

            <!-- Presentations List -->
            <div v-if="safeSponsor.presentations?.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="presentation in safeSponsor.presentations"
                :key="presentation.id"
                class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                      {{ presentation.title }}
                    </h4>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400 mb-3">
                      <div class="flex items-center">
                        <CalendarIcon class="h-4 w-4 mr-1" />
                        {{ formatPresentationInfo(presentation) }}
                      </div>
                      <div class="flex items-center">
                        <ClockIcon class="h-4 w-4 mr-1" />
                        {{ presentation.formatted_time_range || 'Zaman belirtilmemiş' }}
                      </div>
                    </div>

                    <div v-if="presentation.program_session" class="text-sm text-gray-600 dark:text-gray-400">
                      <span class="font-medium">Oturum:</span> {{ presentation.program_session.title }}
                    </div>
                  </div>
                  
                  <!-- Presentation Actions -->
                  <div class="flex items-center space-x-2 ml-4">
                    <Link
                      :href="route('admin.presentations.show', presentation.id)"
                      class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                      title="Sunumu Görüntüle"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </Link>
                    <Link
                      :href="route('admin.presentations.edit', presentation.id)"
                      class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                      title="Sunumu Düzenle"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </Link>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Presentations State -->
            <div v-else class="text-center py-16">
              <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                <PresentationChartLineIcon class="h-full w-full" />
              </div>
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Henüz sunum sponsorluğu yok</h4>
              <p class="text-gray-500 dark:text-gray-400">Bu sponsor henüz hiçbir sunumu sponsorlamıyor.</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Sponsor Details -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sponsor Detayları</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <BuildingOffice2Icon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Sponsor Adı</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeSponsor.name }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <StarIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Seviye</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ getSponsorLevelDisplay(safeSponsor.sponsor_level) }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <DocumentTextIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Oturum Sayısı</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeSponsor.program_sessions?.length || 0 }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <PresentationChartLineIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Sunum Sayısı</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeSponsor.presentations?.length || 0 }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <CalendarIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Kayıt Tarihi</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(safeSponsor.created_at) }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <CheckCircleIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Durum</span>
                </div>
                <span 
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="safeSponsor.is_active 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                >
                  {{ safeSponsor.is_active ? 'Aktif' : 'Pasif' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div v-if="safeSponsor.contact_email || safeSponsor.website" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İletişim Bilgileri</h3>
            <div class="space-y-3">
              <div v-if="safeSponsor.contact_email" class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <EnvelopeIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">E-posta</div>
                  <a :href="`mailto:${safeSponsor.contact_email}`" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeSponsor.contact_email }}
                  </a>
                </div>
              </div>
              
              <div v-if="safeSponsor.website" class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <GlobeAltIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">Website</div>
                  <a :href="safeSponsor.website" target="_blank" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    {{ formatWebsite(safeSponsor.website) }}
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Organization Information -->
          <div v-if="safeSponsor.organization" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Organizasyon Bilgileri</h3>
            <div class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
              <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                <BuildingOfficeIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
              </div>
              <div class="flex-1">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ safeSponsor.organization.name }}</div>
                <div v-if="safeSponsor.organization.description" class="text-sm text-gray-600 dark:text-gray-400">{{ safeSponsor.organization.description }}</div>
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
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  BuildingOffice2Icon,
  BuildingOfficeIcon,
  StarIcon,
  DocumentTextIcon,
  PresentationChartLineIcon,
  EnvelopeIcon,
  GlobeAltIcon,
  ArrowLeftIcon,
  PencilSquareIcon,
  EyeIcon,
  PencilIcon,
  CalendarIcon,
  ClockIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'
import {
  StarIcon as StarSolidIcon
} from '@heroicons/vue/24/solid'

// Props - Backend'den "sponsor" key'i ile geliyor
const props = defineProps({
  sponsor: {
    type: Object,
    required: false,
    default: () => ({
      id: null,
      name: 'Sponsor',
      sponsor_level: 'bronze',
      contact_email: '',
      website: '',
      logo_url: '',
      is_active: true,
      organization: null,
      program_sessions: [],
      presentations: [],
      created_at: null,
      can_edit: false,
      can_delete: false
    })
  }
})

// Computed
const safeSponsor = computed(() => ({
  id: props.sponsor?.id || null,
  name: props.sponsor?.name || 'Sponsor',
  sponsor_level: props.sponsor?.sponsor_level || 'bronze',
  contact_email: props.sponsor?.contact_email || '',
  website: props.sponsor?.website || '',
  logo_url: props.sponsor?.logo_url || '',
  is_active: props.sponsor?.is_active ?? true,
  organization: props.sponsor?.organization || null,
  program_sessions: props.sponsor?.program_sessions || [],
  presentations: props.sponsor?.presentations || [],
  created_at: props.sponsor?.created_at || null,
  can_edit: props.sponsor?.can_edit || false,
  can_delete: props.sponsor?.can_delete || false
}))

const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Sponsorlar', href: route('admin.sponsors.index') },
  { label: safeSponsor.value.name, href: null }
])

// Helper functions
const getSponsorLevelClasses = (level) => {
  const classes = {
    platinum: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    gold: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    silver: 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200',
    bronze: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
  }
  return classes[level] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const getSponsorLevelIcon = (level) => {
  return StarSolidIcon
}

const getSponsorLevelDisplay = (level) => {
  const displays = {
    platinum: 'Platinum',
    gold: 'Gold',
    silver: 'Silver',
    bronze: 'Bronze'
  }
  return displays[level] || level
}

const formatWebsite = (url) => {
  if (!url) return ''
  return url.replace(/^https?:\/\//, '').replace(/\/$/, '')
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

const formatSessionInfo = (session) => {
  const eventName = session.venue?.event_day?.event?.name || 'Etkinlik'
  const dayInfo = session.venue?.event_day?.display_name || ''
  return dayInfo ? `${eventName} - ${dayInfo}` : eventName
}

const formatPresentationInfo = (presentation) => {
  const sessionTitle = presentation.program_session?.title || 'Oturum'
  const eventInfo = presentation.program_session?.venue?.event_day?.event?.name || 'Etkinlik'
  return `${eventInfo} - ${sessionTitle}`
}

const calculateTotalSponsorship = () => {
  const sessions = safeSponsor.value.program_sessions?.length || 0
  const presentations = safeSponsor.value.presentations?.length || 0
  return sessions + presentations
}
</script>

<style scoped>
/* Ensure dropdown is above other elements */
.relative {
  position: relative;
}

/* Additional z-index utilities if needed */
.z-dropdown {
  z-index: 9999;
}
</style>