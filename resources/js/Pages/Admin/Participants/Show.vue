<!-- Admin/Participants/Show.vue - Premium Participant Detail View with Gray Theme -->
<template>
  <AdminLayout
    :page-title="safeParticipant.full_name"
    :page-subtitle="`${safeParticipant.organization?.name || 'Organizasyon'} - ${safeParticipant.affiliation || 'Katılımcı'}`"
    :breadcrumbs="breadcrumbs"
  >
    <Head :title="safeParticipant.full_name" />

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
                    <!-- Profile Photo -->
                    <div class="h-20 w-20 bg-white/20 backdrop-blur-sm rounded-xl overflow-hidden flex items-center justify-center">
                      <img 
                        v-if="safeParticipant.photo_url" 
                        :src="safeParticipant.photo_url" 
                        :alt="safeParticipant.full_name"
                        class="h-full w-full object-cover"
                      />
                      <span v-else class="text-2xl font-bold">
                        {{ getInitials(safeParticipant.full_name) }}
                      </span>
                    </div>
                    <div>
                      <h1 class="text-3xl font-bold mb-1">{{ safeParticipant.full_name }}</h1>
                      <p v-if="safeParticipant.title" class="text-gray-200 text-lg">{{ safeParticipant.title }}</p>
                      <p v-if="safeParticipant.affiliation" class="text-gray-300 text-sm">{{ safeParticipant.affiliation }}</p>
                    </div>
                  </div>
                  
                  <!-- Quick Stats -->
                  <div class="flex items-center space-x-8 text-white/90">
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ safeParticipant.presentations?.length || 0 }}</div>
                      <div class="text-sm">Sunum</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ safeParticipant.moderated_sessions?.length || 0 }}</div>
                      <div class="text-sm">Moderasyon</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ calculateTotalParticipations() }}</div>
                      <div class="text-sm">Toplam Katılım</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Meta Bar -->
          <div class="px-8 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-6">
              <!-- Roles -->
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Roller:</span>
                <div class="flex space-x-2">
                  <span
                    v-if="safeParticipant.is_speaker"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                  >
                    <MicrophoneIcon class="w-4 h-4 mr-1" />
                    Konuşmacı
                  </span>
                  <span
                    v-if="safeParticipant.is_moderator"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-300 text-gray-900 dark:bg-gray-600 dark:text-gray-200"
                  >
                    <UserGroupIcon class="w-4 h-4 mr-1" />
                    Moderatör
                  </span>
                  <span
                    v-if="!safeParticipant.is_speaker && !safeParticipant.is_moderator"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                  >
                    <UserIcon class="w-4 h-4 mr-1" />
                    Katılımcı
                  </span>
                </div>
              </div>

              <!-- Contact Info -->
              <div v-if="safeParticipant.email" class="flex items-center space-x-2">
                <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <a :href="`mailto:${safeParticipant.email}`" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeParticipant.email }}
                  </a>
                  <div class="text-xs text-gray-500 dark:text-gray-400">E-posta</div>
                </div>
              </div>

              <div v-if="safeParticipant.phone" class="flex items-center space-x-2">
                <PhoneIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <a :href="`tel:${safeParticipant.phone}`" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeParticipant.phone }}
                  </a>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Telefon</div>
                </div>
              </div>

              <!-- Organization Info -->
              <div class="flex items-center space-x-2">
                <BuildingOfficeIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ safeParticipant.organization?.name || 'Organizasyon yok' }}</div>
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
                :href="route('admin.participants.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                Katılımcı Listesi
              </Link>
            </div>

            <div class="flex items-center space-x-3">
              <!-- Edit Button -->
              <Link
                :href="route('admin.participants.edit', safeParticipant.id)"
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
          <!-- Biography -->
          <div v-if="safeParticipant.bio" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Biyografi</h3>
            <div class="prose dark:prose-invert max-w-none">
              <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ safeParticipant.bio }}</p>
            </div>
          </div>

          <!-- Presentations -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sunumlar</h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ safeParticipant.presentations?.length || 0 }} sunum
              </span>
            </div>

            <!-- Presentations List -->
            <div v-if="safeParticipant.presentations?.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="presentation in safeParticipant.presentations"
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
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Presentations State -->
            <div v-else class="text-center py-16">
              <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                <DocumentTextIcon class="h-full w-full" />
              </div>
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Henüz sunum yok</h4>
              <p class="text-gray-500 dark:text-gray-400">Bu katılımcının henüz kayıtlı sunumu bulunmuyor.</p>
            </div>
          </div>

          <!-- Moderated Sessions -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Modere Ettiği Oturumlar</h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ safeParticipant.moderated_sessions?.length || 0 }} oturum
              </span>
            </div>

            <!-- Moderated Sessions List -->
            <div v-if="safeParticipant.moderated_sessions?.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="session in safeParticipant.moderated_sessions"
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
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Sessions State -->
            <div v-else class="text-center py-16">
              <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                <UserGroupIcon class="h-full w-full" />
              </div>
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Henüz moderasyon yok</h4>
              <p class="text-gray-500 dark:text-gray-400">Bu katılımcının henüz modere ettiği oturum bulunmuyor.</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Participant Details -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Katılımcı Detayları</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <UserIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Ad Soyad</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeParticipant.full_name }}</span>
              </div>
              
              <div v-if="safeParticipant.title" class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <AcademicCapIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Ünvan</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeParticipant.title }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <DocumentTextIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Sunum Sayısı</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeParticipant.presentations?.length || 0 }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <UserGroupIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Moderasyon Sayısı</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeParticipant.moderated_sessions?.length || 0 }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <CalendarIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Kayıt Tarihi</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(safeParticipant.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İletişim Bilgileri</h3>
            <div class="space-y-3">
              <div v-if="safeParticipant.email" class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <EnvelopeIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">E-posta</div>
                  <a :href="`mailto:${safeParticipant.email}`" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeParticipant.email }}
                  </a>
                </div>
              </div>
              
              <div v-if="safeParticipant.phone" class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <PhoneIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">Telefon</div>
                  <a :href="`tel:${safeParticipant.phone}`" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeParticipant.phone }}
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Organization Information -->
          <div v-if="safeParticipant.organization || safeParticipant.affiliation" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Organizasyon Bilgileri</h3>
            <div class="space-y-3">
              <div v-if="safeParticipant.organization" class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <BuildingOfficeIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">Organizasyon</div>
                  <div class="text-sm text-gray-600 dark:text-gray-400">{{ safeParticipant.organization.name }}</div>
                </div>
              </div>
              
              <div v-if="safeParticipant.affiliation" class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <AcademicCapIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">Kurum/Affiliation</div>
                  <div class="text-sm text-gray-600 dark:text-gray-400">{{ safeParticipant.affiliation }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Dialog -->
    <ConfirmDialog
      v-model="confirmDialog.show"
      :title="confirmDialog.title"
      :message="confirmDialog.message"
      :type="confirmDialog.type"
      @confirm="confirmDialog.callback"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue'
import {
  UserIcon,
  UserGroupIcon,
  MicrophoneIcon,
  BuildingOfficeIcon,
  DocumentTextIcon,
  EnvelopeIcon,
  PhoneIcon,
  ArrowLeftIcon,
  PencilSquareIcon,
  AcademicCapIcon,
  CalendarIcon,
  ClockIcon,
  EyeIcon
} from '@heroicons/vue/24/outline'

// Props - Backend'den "participant" key'i ile geliyor
const props = defineProps({
  participant: {
    type: Object,
    required: false,
    default: () => ({
      id: null,
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
      moderated_sessions: [],
      created_at: null,
      can_edit: false,
      can_delete: false
    })
  }
})

// State
const confirmDialog = ref({
  show: false,
  title: '',
  message: '',
  type: 'warning',
  callback: null
})

// Computed
const safeParticipant = computed(() => ({
  id: props.participant?.id || null,
  first_name: props.participant?.first_name || '',
  last_name: props.participant?.last_name || '',
  full_name: props.participant?.full_name || `${props.participant?.first_name || ''} ${props.participant?.last_name || ''}`.trim() || 'İsimsiz Katılımcı',
  title: props.participant?.title || '',
  email: props.participant?.email || '',
  phone: props.participant?.phone || '',
  affiliation: props.participant?.affiliation || '',
  bio: props.participant?.bio || '',
  photo_url: props.participant?.photo_url || '',
  is_speaker: props.participant?.is_speaker || false,
  is_moderator: props.participant?.is_moderator || false,
  organization: props.participant?.organization || null,
  presentations: props.participant?.presentations || [],
  moderated_sessions: props.participant?.moderated_sessions || [],
  created_at: props.participant?.created_at || null,
  can_edit: props.participant?.can_edit || false,
  can_delete: props.participant?.can_delete || false
}))

const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Katılımcılar', href: route('admin.participants.index') },
  { label: safeParticipant.value.full_name, href: null }
])

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

const formatPresentationInfo = (presentation) => {
  const sessionTitle = presentation.program_session?.title || 'Oturum'
  const eventInfo = presentation.program_session?.venue?.event_day?.event?.name || 'Etkinlik'
  return `${eventInfo} - ${sessionTitle}`
}

const formatSessionInfo = (session) => {
  const eventName = session.venue?.event_day?.event?.name || 'Etkinlik'
  const dayInfo = session.venue?.event_day?.display_name || ''
  return dayInfo ? `${eventName} - ${dayInfo}` : eventName
}

const calculateTotalParticipations = () => {
  const presentations = safeParticipant.value.presentations?.length || 0
  const sessions = safeParticipant.value.moderated_sessions?.length || 0
  return presentations + sessions
}
</script>

<style scoped>
.prose p {
  margin-bottom: 1rem;
  line-height: 1.7;
}

/* Ensure dropdown is above other elements */
.relative {
  position: relative;
}

/* Additional z-index utilities if needed */
.z-dropdown {
  z-index: 9999;
}
</style>