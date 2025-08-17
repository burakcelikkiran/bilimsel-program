<!-- Admin/Organizations/Show.vue - Gray Theme (Clean) -->
<template>
  <AdminLayout
    :page-title="safeOrganization.name"
    :page-subtitle="`${safeOrganization.description || 'Organizasyon detayları ve yönetimi'}`"
    :breadcrumbs="breadcrumbs"
  >
    <Head :title="safeOrganization.name" />

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
                        v-if="safeOrganization.logo_url" 
                        :src="safeOrganization.logo_url" 
                        :alt="safeOrganization.name"
                        class="h-full w-full object-contain bg-white/10 rounded-xl p-2"
                      />
                      <BuildingOfficeIcon v-else class="h-12 w-12 text-white/70" />
                    </div>
                    <div>
                      <h1 class="text-3xl font-bold mb-1">{{ safeOrganization.name }}</h1>
                      <div class="flex items-center space-x-3">
                        <span
                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white"
                        >
                          <BuildingOfficeIcon class="w-4 h-4 mr-2" />
                          Organizasyon
                        </span>
                        <span
                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                          :class="safeOrganization.is_active 
                            ? 'bg-green-500/20 text-green-100' 
                            : 'bg-red-500/20 text-red-100'"
                        >
                          <span class="w-2 h-2 mr-2 rounded-full bg-current"></span>
                          {{ safeOrganization.is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Quick Stats -->
                  <div class="flex items-center space-x-8 text-white/90">
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ statistics.total_events || 0 }}</div>
                      <div class="text-sm">Etkinlik</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ statistics.total_participants || 0 }}</div>
                      <div class="text-sm">Katılımcı</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ statistics.active_sponsors || 0 }}</div>
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
              <!-- Status Info -->
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Durum:</span>
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                  :class="safeOrganization.is_active 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                >
                  <span class="w-2 h-2 mr-2 rounded-full bg-current"></span>
                  {{ safeOrganization.is_active ? 'Aktif' : 'Pasif' }}
                </span>
              </div>

              <!-- Contact Info -->
              <div v-if="safeOrganization.contact_email" class="flex items-center space-x-2">
                <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <a :href="`mailto:${safeOrganization.contact_email}`" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeOrganization.contact_email }}
                  </a>
                  <div class="text-xs text-gray-500 dark:text-gray-400">E-posta</div>
                </div>
              </div>

              <div v-if="safeOrganization.contact_phone" class="flex items-center space-x-2">
                <PhoneIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <a :href="`tel:${safeOrganization.contact_phone}`" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeOrganization.contact_phone }}
                  </a>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Telefon</div>
                </div>
              </div>

              <!-- Creation Date -->
              <div class="flex items-center space-x-2">
                <CalendarIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(safeOrganization.created_at) }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Oluşturma Tarihi</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Bar -->
          <div class="px-8 py-4 flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900">
            <div class="flex items-center space-x-3">
              <!-- Back to List -->
              <Link
                :href="route('admin.organizations.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
              >
                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                Organizasyon Listesi
              </Link>
            </div>

            <div class="flex items-center space-x-3">
              <!-- Edit Button -->
              <Link
                :href="route('admin.organizations.edit', safeOrganization.id)"
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
          <!-- Recent Events -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Son Etkinlikler</h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ safeOrganization.recent_events?.length || 0 }} etkinlik
              </span>
            </div>

            <!-- Events List -->
            <div v-if="safeOrganization.recent_events?.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="event in safeOrganization.recent_events"
                :key="event.id"
                class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                      {{ event.name }}
                    </h4>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400 mb-3">
                      <div class="flex items-center">
                        <CalendarIcon class="h-4 w-4 mr-1" />
                        {{ event.formatted_date_range || 'Tarih belirtilmemiş' }}
                      </div>
                      <div class="flex items-center">
                        <span
                          :class="[
                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                            event.is_published
                              ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                              : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
                          ]"
                        >
                          {{ event.is_published ? 'Yayında' : 'Taslak' }}
                        </span>
                      </div>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-400">
                      <span class="font-medium">Durum:</span> {{ getStatusText(event.status) }}
                    </div>
                  </div>
                  
                  <!-- Event Actions -->
                  <div class="flex items-center space-x-2 ml-4">
                    <Link
                      :href="safeRoute('admin.events.show', `/admin/events/${event.id}`, event.id)"
                      class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                      title="Etkinliği Görüntüle"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </Link>
                    <Link
                      :href="safeRoute('admin.events.edit', `/admin/events/${event.id}/edit`, event.id)"
                      class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                      title="Etkinliği Düzenle"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </Link>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Events State -->
            <div v-else class="text-center py-16">
              <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                <CalendarIcon class="h-full w-full" />
              </div>
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Henüz etkinlik yok</h4>
              <p class="text-gray-500 dark:text-gray-400">Bu organizasyona ait henüz etkinlik oluşturulmamış.</p>
            </div>
          </div>

          <!-- Users Section -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kullanıcılar</h3>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ safeOrganization.users?.length || 0 }} kullanıcı
              </span>
            </div>

            <!-- Users List -->
            <div v-if="safeOrganization.users?.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="user in safeOrganization.users"
                :key="user.id"
                class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl flex items-center justify-center text-white text-lg font-semibold shadow-lg">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                      <p class="text-gray-600 dark:text-gray-400">{{ user.email }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <span
                      :class="[
                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                        user.role === 'organizer' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' :
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                      ]"
                    >
                      {{ getRoleText(user.role) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty Users State -->
            <div v-else class="text-center py-16">
              <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                <UserGroupIcon class="h-full w-full" />
              </div>
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Henüz kullanıcı yok</h4>
              <p class="text-gray-500 dark:text-gray-400">Bu organizasyona atanmış kullanıcı bulunmuyor.</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Organization Details -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Organizasyon Detayları</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <BuildingOfficeIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Organizasyon Adı</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ safeOrganization.name }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <CalendarIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Toplam Etkinlik</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ statistics.total_events || 0 }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <EyeIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Yayınlı Etkinlik</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ statistics.published_events || 0 }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <UserGroupIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Katılımcılar</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ statistics.total_participants || 0 }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <ClockIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Oluşturma Tarihi</span>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(safeOrganization.created_at) }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <CheckCircleIcon class="h-5 w-5 text-gray-500" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">Durum</span>
                </div>
                <span 
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="safeOrganization.is_active 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                >
                  {{ safeOrganization.is_active ? 'Aktif' : 'Pasif' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div v-if="safeOrganization.contact_email || safeOrganization.contact_phone" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İletişim Bilgileri</h3>
            <div class="space-y-3">
              <div v-if="safeOrganization.contact_email" class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <EnvelopeIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">E-posta</div>
                  <a :href="`mailto:${safeOrganization.contact_email}`" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeOrganization.contact_email }}
                  </a>
                </div>
              </div>
              
              <div v-if="safeOrganization.contact_phone" class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                  <PhoneIcon class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">Telefon</div>
                  <a :href="`tel:${safeOrganization.contact_phone}`" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    {{ safeOrganization.contact_phone }}
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div v-if="safeOrganization.description" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Açıklama</h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
              {{ safeOrganization.description }}
            </p>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hızlı İşlemler</h3>
            <div class="space-y-3">
              <Link
                :href="safeRoute('admin.events.create', '/admin/events/create') + `?organization_id=${safeOrganization.id}`"
                class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
              >
                <CalendarIcon class="h-5 w-5 mr-2" />
                Yeni Etkinlik
              </Link>
              
              <Link
                :href="safeRoute('admin.participants.create', '/admin/participants/create') + `?organization_id=${safeOrganization.id}`"
                class="w-full inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold rounded-xl shadow-sm hover:shadow-md transition-all duration-200"
              >
                <UserPlusIcon class="h-5 w-5 mr-2" />
                Katılımcı Ekle
              </Link>
              
              <Link
                :href="safeRoute('admin.sponsors.create', '/admin/sponsors/create') + `?organization_id=${safeOrganization.id}`"
                class="w-full inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold rounded-xl shadow-sm hover:shadow-md transition-all duration-200"
              >
                <StarIcon class="h-5 w-5 mr-2" />
                Sponsor Ekle
              </Link>
            </div>
          </div>

          <!-- Recent Participants -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Son Katılımcılar</h3>
              <Link
                :href="safeRoute('admin.participants.index', '/admin/participants') + `?organization_id=${safeOrganization.id}`"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 font-medium"
              >
                Tümünü Gör
              </Link>
            </div>

            <div v-if="safeOrganization.recent_participants?.length" class="space-y-4">
              <div
                v-for="participant in safeOrganization.recent_participants"
                :key="participant.id"
                class="flex items-center space-x-3"
              >
                <div class="flex-shrink-0">
                  <img
                    v-if="participant.photo_url"
                    :src="participant.photo_url"
                    :alt="participant.full_name"
                    class="w-10 h-10 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow-sm"
                  />
                  <div
                    v-else
                    class="w-10 h-10 bg-gradient-to-br from-gray-300 to-gray-400 dark:from-gray-600 dark:to-gray-700 rounded-full flex items-center justify-center shadow-sm"
                  >
                    <span class="text-sm font-semibold text-white">
                      {{ participant.initials }}
                    </span>
                  </div>
                </div>
                <div class="flex-grow min-w-0">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                    {{ participant.full_name }}
                  </p>
                  <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                    {{ participant.affiliation }}
                  </p>
                </div>
                <div class="flex-shrink-0 flex space-x-1">
                  <span
                    v-if="participant.is_speaker"
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200"
                  >
                    Konuşmacı
                  </span>
                  <span
                    v-if="participant.is_moderator"
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                  >
                    Moderatör
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-6">
              <div class="mx-auto h-12 w-12 text-gray-400 mb-3">
                <UserGroupIcon class="h-full w-full" />
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Henüz katılımcı bulunmuyor.</p>
            </div>
          </div>

          <!-- Sponsors -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sponsorlar</h3>
              <Link
                :href="safeRoute('admin.sponsors.index', '/admin/sponsors') + `?organization_id=${safeOrganization.id}`"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 font-medium"
              >
                Tümünü Gör
              </Link>
            </div>

            <div v-if="safeOrganization.sponsors?.length" class="space-y-4">
              <div
                v-for="sponsor in safeOrganization.sponsors"
                :key="sponsor.id"
                class="flex items-center space-x-3"
              >
                <div class="flex-shrink-0">
                  <img
                    v-if="sponsor.logo_url"
                    :src="sponsor.logo_url"
                    :alt="sponsor.name"
                    class="w-10 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-600 shadow-sm"
                  />
                  <div
                    v-else
                    class="w-10 h-10 bg-gradient-to-br from-gray-300 to-gray-400 dark:from-gray-600 dark:to-gray-700 rounded-lg flex items-center justify-center shadow-sm"
                  >
                    <BuildingOfficeIcon class="w-5 h-5 text-gray-500" />
                  </div>
                </div>
                <div class="flex-grow min-w-0">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                    {{ sponsor.name }}
                  </p>
                  <p class="text-xs text-gray-600 dark:text-gray-400">
                    {{ sponsor.formatted_level }}
                  </p>
                </div>
                <div class="flex-shrink-0">
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                      sponsor.is_active
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                    ]"
                  >
                    {{ sponsor.is_active ? 'Aktif' : 'Pasif' }}
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-6">
              <div class="mx-auto h-12 w-12 text-gray-400 mb-3">
                <StarIcon class="h-full w-full" />
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Henüz sponsor bulunmuyor.</p>
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
  BuildingOfficeIcon,
  EnvelopeIcon,
  PhoneIcon,
  ClockIcon,
  ArrowLeftIcon,
  PencilSquareIcon,
  EyeIcon,
  PencilIcon,
  CalendarIcon,
  CheckCircleIcon,
  UserGroupIcon,
  StarIcon,
  UserPlusIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  organization: {
    type: Object,
    required: false,
    default: () => ({
      id: null,
      name: 'Organizasyon',
      description: '',
      contact_email: '',
      contact_phone: '',
      logo_url: '',
      is_active: true,
      recent_events: [],
      users: [],
      recent_participants: [],
      sponsors: [],
      created_at: null
    })
  },
  statistics: {
    type: Object,
    default: () => ({
      total_events: 0,
      published_events: 0,
      total_participants: 0,
      active_sponsors: 0
    })
  },
  canEdit: {
    type: Boolean,
    default: false
  },
  canDelete: {
    type: Boolean,
    default: false
  }
})

// Helper function to safely create routes
const safeRoute = (routeName, fallback, param = null) => {
  try {
    if (param) {
      return route(routeName, param)
    }
    return route(routeName)
  } catch (error) {
    return fallback
  }
}

// Computed
const safeOrganization = computed(() => ({
  id: props.organization?.id || null,
  name: props.organization?.name || 'Organizasyon',
  description: props.organization?.description || '',
  contact_email: props.organization?.contact_email || '',
  contact_phone: props.organization?.contact_phone || '',
  logo_url: props.organization?.logo_url || '',
  is_active: props.organization?.is_active ?? true,
  recent_events: props.organization?.recent_events || [],
  users: props.organization?.users || [],
  recent_participants: props.organization?.recent_participants || [],
  sponsors: props.organization?.sponsors || [],
  created_at: props.organization?.created_at || null
}))

const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Organizasyonlar', href: route('admin.organizations.index') },
  { label: safeOrganization.value.name, href: null }
])

// Helper functions
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

const getStatusText = (status) => {
  const statusMap = {
    upcoming: 'Yaklaşan',
    ongoing: 'Devam Eden',
    past: 'Geçmiş'
  }
  return statusMap[status] || status
}

const getRoleText = (role) => {
  const roleMap = {
    organizer: 'Organizatör',
    editor: 'Editör',
    admin: 'Yönetici'
  }
  return roleMap[role] || role
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