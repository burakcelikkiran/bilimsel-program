<!-- Admin/Venues/Show.vue - Gray Theme (Clean) -->
<template>
  <AdminLayout
    :page-title="safeVenue.name"
    :page-subtitle="`${safeVenue.event_day?.display_name || 'Etkinlik Günü'} - ${formatCapacity(safeVenue.capacity)}`"
    :breadcrumbs="breadcrumbs"
  >
    <Head :title="safeVenue.name" />

    <div class="w-full space-y-8">
      <!-- Header Section -->
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="relative">
          <!-- Banner with venue color -->
          <div class="h-48 relative overflow-hidden" :style="{ backgroundColor: safeVenue.color || '#3B82F6' }">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute inset-0 flex items-end">
              <div class="p-8 text-white w-full">
                <div class="flex items-end justify-between">
                  <div class="flex items-center space-x-6">
                    <!-- Icon -->
                    <div class="h-20 w-20 bg-white/20 backdrop-blur-sm rounded-xl overflow-hidden flex items-center justify-center">
                      <BuildingOffice2Icon class="h-12 w-12 text-white/90" />
                    </div>
                    <div>
                      <h1 class="text-3xl font-bold mb-1">{{ safeVenue.display_name || safeVenue.name }}</h1>
                      <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                          <MapPinIcon class="w-4 h-4 mr-2" />
                          {{ safeVenue.event_day?.display_name || 'Etkinlik Günü' }}
                        </span>
                        <span v-if="safeVenue.capacity" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                          <UserGroupIcon class="w-4 h-4 mr-2" />
                          {{ formatCapacity(safeVenue.capacity) }}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Quick Stats -->
                  <div class="flex items-center space-x-8 text-white/90">
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ safeVenue.program_sessions?.length || 0 }}</div>
                      <div class="text-sm">Oturum</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ safeVenue.sort_order || 0 }}</div>
                      <div class="text-sm">Sıra</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ calculateTotalTime() }}</div>
                      <div class="text-sm">Toplam Süre</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Meta Bar -->
          <div class="px-8 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-6">
              <!-- Event Day Info -->
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Etkinlik Günü:</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ safeVenue.event_day?.display_name || 'Belirtilmemiş' }}
                </span>
              </div>

              <!-- Event Info -->
              <div class="flex items-center space-x-2">
                <CalendarIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ safeVenue.event_day?.event?.name || 'Etkinlik yok' }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Etkinlik</div>
                </div>
              </div>

              <!-- Capacity Info -->
              <div v-if="safeVenue.capacity" class="flex items-center space-x-2">
                <UserGroupIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCapacity(safeVenue.capacity) }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Kapasite</div>
                </div>
              </div>

              <!-- Color Info -->
              <div class="flex items-center space-x-2">
                <div class="h-5 w-5 rounded border border-gray-300" :style="{ backgroundColor: safeVenue.color }"></div>
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ safeVenue.color }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Renk Kodu</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Bar -->
          <div class="px-8 py-4 flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900">
            <div class="flex items-center space-x-3">
              <!-- Back to List -->
              <Link
                :href="route('admin.venues.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                Salon Listesi
              </Link>
            </div>

            <div class="flex items-center space-x-3">
              <!-- Edit Button -->
              <Link
                v-if="safeVenue.can_edit"
                :href="route('admin.venues.edit', safeVenue.id)"
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
          <!-- Program Sessions -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Program Oturumları</h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ safeVenue.program_sessions?.length || 0 }} oturum
              </span>
            </div>

            <!-- Program Sessions List -->
            <div v-if="safeVenue.program_sessions?.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="session in safeVenue.program_sessions"
                :key="session.id"
                class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                      <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ session.title }}
                      </h4>
                      <span v-if="session.is_break" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                        Ara
                      </span>
                      <span v-if="session.sponsor" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                        Sponsorlu
                      </span>
                    </div>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400 mb-3">
                      <div class="flex items-center">
                        <ClockIcon class="h-4 w-4 mr-1" />
                        {{ session.formatted_time_range || 'Zaman belirtilmemiş' }}
                      </div>
                      <div v-if="session.session_type && session.session_type !== 'main'" class="flex items-center">
                        <TagIcon class="h-4 w-4 mr-1" />
                        {{ getSessionTypeLabel(session.session_type) }}
                      </div>
                    </div>

                    <div v-if="session.description" class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                      {{ session.description }}
                    </div>

                    <!-- Moderators -->
                    <div v-if="session.moderators?.length > 0" class="flex items-center space-x-2 mb-2">
                      <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ session.moderator_title || 'Moderatör' }}:</span>
                      <div class="flex flex-wrap gap-1">
                        <span
                          v-for="moderator in session.moderators"
                          :key="moderator.id"
                          class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                        >
                          {{ moderator.first_name }} {{ moderator.last_name }}
                        </span>
                      </div>
                    </div>

                    <!-- Presentations count -->
                    <div v-if="session.presentations_count > 0" class="text-xs text-gray-500 dark:text-gray-400">
                      {{ session.presentations_count }} sunum
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
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Henüz oturum yok</h4>
              <p class="text-gray-500 dark:text-gray-400 mb-4">Bu salonda henüz hiçbir program oturumu bulunmuyor.</p>
              <Link
                :href="route('admin.program-sessions.create', { venue_id: safeVenue.id })"
                class="inline-flex items-center px-4 py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-800 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                <PlusIcon class="h-4 w-4 mr-2" />
                İlk Oturumu Oluştur
              </Link>
            </div>
          </div>

          <!-- Venue Timeline (if sessions exist) -->
          <div v-if="safeVenue.program_sessions?.length > 0" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Günlük Program</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Salondaki oturumların zaman çizelgesi</p>
            </div>

            <!-- Timeline -->
            <div class="p-6">
              <div class="relative">
                <!-- Timeline line -->
                <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                
                <div class="space-y-6">
                  <div
                    v-for="(session, index) in sortedSessions"
                    :key="session.id"
                    class="relative flex items-start space-x-4"
                  >
                    <!-- Timeline dot -->
                    <div class="relative z-10 flex items-center justify-center w-12 h-12 rounded-full border-4 border-white dark:border-gray-900 shadow-sm"
                         :style="{ backgroundColor: session.is_break ? '#F59E0B' : safeVenue.color }"
                    >
                      <ClockIcon v-if="!session.is_break" class="h-5 w-5 text-white" />
                      <PauseIcon v-else class="h-5 w-5 text-white" />
                    </div>
                    
                    <!-- Timeline content -->
                    <div class="flex-1 min-w-0 pb-8">
                      <div class="flex items-center justify-between">
                        <div class="flex-1">
                          <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ session.title }}
                          </h4>
                          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ session.formatted_time_range || 'Zaman belirtilmemiş' }}
                          </p>
                        </div>
                        <div class="flex items-center space-x-2">
                          <span v-if="session.is_break" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                            Ara
                          </span>
                          <span v-if="session.sponsor" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                            {{ session.sponsor.name }}
                          </span>
                        </div>
                      </div>
                      
                      <div v-if="session.description && !session.is_break" class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                        {{ session.description }}
                      </div>
                      
                      <div v-if="session.moderators?.length > 0" class="mt-2 flex items-center space-x-2">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ session.moderator_title || 'Moderatör' }}:</span>
                        <div class="flex flex-wrap gap-1">
                          <span
                            v-for="moderator in session.moderators"
                            :key="moderator.id"
                            class="text-xs text-gray-600 dark:text-gray-400"
                          >
                            {{ moderator.first_name }} {{ moderator.last_name }}{{ session.moderators.length > 1 && session.moderators.indexOf(moderator) < session.moderators.length - 1 ? ',' : '' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Venue Details -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Salon Detayları</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <BuildingOffice2Icon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Salon Adı</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeVenue.name }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <EyeIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Görünen Ad</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeVenue.display_name || safeVenue.name }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <UserGroupIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Kapasite</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatCapacity(safeVenue.capacity) }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <DocumentTextIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Oturum Sayısı</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeVenue.program_sessions?.length || 0 }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <HashtagIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Sıra</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeVenue.sort_order || 'Belirtilmemiş' }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <div class="h-5 w-5 rounded border border-gray-300" :style="{ backgroundColor: safeVenue.color }"></div>
                  <span class="text-sm text-gray-600 dark:text-gray-400">Renk</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeVenue.color }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <CalendarIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Kayıt Tarihi</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(safeVenue.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Event Day Information -->
          <div v-if="safeVenue.event_day" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Etkinlik Günü Bilgileri</h3>
            <div class="space-y-3">
              <div class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <CalendarIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ safeVenue.event_day.display_name }}</div>
                  <div v-if="safeVenue.event_day.date" class="text-sm text-gray-600 dark:text-gray-400">{{ formatDate(safeVenue.event_day.date) }}</div>
                </div>
              </div>
              
              <div v-if="safeVenue.event_day.event" class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <BuildingOfficeIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ safeVenue.event_day.event.name }}</div>
                  <div v-if="safeVenue.event_day.event.description" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ safeVenue.event_day.event.description }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hızlı İşlemler</h3>
            <div class="space-y-3">
              <Link
                :href="route('admin.program-sessions.create', { venue_id: safeVenue.id })"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-800 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                <PlusIcon class="h-4 w-4 mr-2" />
                Yeni Oturum Ekle
              </Link>
              
              <Link
                v-if="safeVenue.program_sessions?.length > 0"
                :href="route('admin.program-sessions.index', { venue_id: safeVenue.id })"
                class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                <DocumentTextIcon class="h-4 w-4 mr-2" />
                Tüm Oturumları Görüntüle
              </Link>
              
              <Link
                :href="route('admin.venues.edit', safeVenue.id)"
                class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                <PencilIcon class="h-4 w-4 mr-2" />
                Salon Bilgilerini Düzenle
              </Link>
            </div>
          </div>

          <!-- Statistics Summary -->
          <div v-if="safeVenue.program_sessions?.length > 0" class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İstatistikler</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Toplam Oturum</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeVenue.program_sessions.length }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Ara Sayısı</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ getBreakCount() }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Sponsorlu Oturum</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ getSponsoredCount() }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Toplam Süre</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ calculateTotalTime() }}</span>
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
  DocumentTextIcon,
  ArrowLeftIcon,
  PencilSquareIcon,
  EyeIcon,
  PencilIcon,
  CalendarIcon,
  ClockIcon,
  UserGroupIcon,
  MapPinIcon,
  TagIcon,
  HashtagIcon,
  PlusIcon,
  PauseIcon
} from '@heroicons/vue/24/outline'

// Props - Backend'den "venue" key'i ile geliyor
const props = defineProps({
  venue: {
    type: Object,
    required: false,
    default: () => ({
      id: null,
      name: 'Salon',
      display_name: '',
      capacity: null,
      color: '#3B82F6',
      sort_order: 0,
      event_day_id: null,
      event_day: null,
      program_sessions: [],
      created_at: null,
      can_edit: false,
      can_delete: false
    })
  }
})

// Computed
const safeVenue = computed(() => ({
  id: props.venue?.id || null,
  name: props.venue?.name || 'Salon',
  display_name: props.venue?.display_name || '',
  capacity: props.venue?.capacity || null,
  color: props.venue?.color || '#3B82F6',
  sort_order: props.venue?.sort_order || 0,
  event_day_id: props.venue?.event_day_id || null,
  event_day: props.venue?.event_day || null,
  program_sessions: props.venue?.program_sessions || [],
  created_at: props.venue?.created_at || null,
  can_edit: props.venue?.can_edit || false,
  can_delete: props.venue?.can_delete || false
}))

const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Salonlar', href: route('admin.venues.index') },
  { label: safeVenue.value.name, href: null }
])

const sortedSessions = computed(() => {
  if (!safeVenue.value.program_sessions) return []
  
  return [...safeVenue.value.program_sessions].sort((a, b) => {
    // First sort by start_time
    if (a.start_time && b.start_time) {
      if (a.start_time !== b.start_time) {
        return a.start_time.localeCompare(b.start_time)
      }
    }
    
    // Then by sort_order
    return (a.sort_order || 0) - (b.sort_order || 0)
  })
})

// Helper functions
const formatCapacity = (capacity) => {
  if (!capacity) return 'Belirtilmemiş'
  return `${capacity.toLocaleString()} kişi`
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

const getSessionTypeLabel = (sessionType) => {
  const labels = {
    main: 'Ana Oturum',
    keynote: 'Açılış Konuşması',
    panel: 'Panel',
    workshop: 'Atölye',
    break: 'Ara',
    lunch: 'Öğle Yemeği',
    networking: 'Networking'
  }
  return labels[sessionType] || sessionType
}

const calculateTotalTime = () => {
  if (!safeVenue.value.program_sessions?.length) return '0 dk'
  
  let totalMinutes = 0
  
  safeVenue.value.program_sessions.forEach(session => {
    if (session.start_time && session.end_time) {
      const start = new Date(`1970-01-01T${session.start_time}`)
      const end = new Date(`1970-01-01T${session.end_time}`)
      const diffMs = end - start
      totalMinutes += Math.max(0, diffMs / (1000 * 60))
    }
  })
  
  const hours = Math.floor(totalMinutes / 60)
  const minutes = totalMinutes % 60
  
  if (hours > 0) {
    return `${hours}sa ${minutes}dk`
  }
  return `${minutes}dk`
}

const getBreakCount = () => {
  return safeVenue.value.program_sessions?.filter(session => session.is_break).length || 0
}

const getSponsoredCount = () => {
  return safeVenue.value.program_sessions?.filter(session => session.sponsor_id).length || 0
}
</script>

<style scoped>
/* Line clamp utility */
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
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