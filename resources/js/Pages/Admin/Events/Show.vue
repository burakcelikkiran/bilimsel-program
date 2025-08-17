<template>
  <AdminLayout :page-title="event?.name || 'Etkinlik Detayı'" :page-subtitle="eventSubtitle" :breadcrumbs="breadcrumbs">

    <Head :title="event?.name || 'Etkinlik Detayı'" />

    <div class="w-full space-y-8">
      <!-- Header Section -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="relative">
          <!-- Banner with gray gradient -->
          <div class="h-48 bg-gradient-to-r from-gray-800 to-gray-900 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute inset-0 flex items-end">
              <div class="p-8 text-white w-full">
                <div class="flex items-end justify-between">
                  <div class="flex items-center space-x-4">
                    <div class="h-16 w-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                      <CalendarIcon class="h-10 w-10" />
                    </div>
                    <div>
                      <h1 class="text-3xl font-bold mb-1">{{ event?.name || 'Etkinlik Detayı' }}</h1>
                      <p class="text-gray-300 text-lg">{{ eventDateRange || 'Tarih belirtilmemiş' }}</p>
                    </div>
                  </div>

                  <!-- Quick Stats -->
                  <div class="flex items-center space-x-8 text-white/90">
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ event?.event_days_count || 0 }}</div>
                      <div class="text-sm">Gün</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ event?.total_sessions || 0 }}</div>
                      <div class="text-sm">Oturum</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold">{{ event?.total_presentations || 0 }}</div>
                      <div class="text-sm">Sunum</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Meta Bar -->
          <div class="px-8 py-6 bg-gray-50 border-b border-gray-200">
            <div class="flex flex-wrap items-center gap-6">
              <!-- Status -->
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-500">Durum:</span>
                <span :class="[
                  'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                  statusClasses
                ]">
                  <svg class="mr-2 h-2 w-2 fill-current" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                  </svg>
                  {{ statusLabel }}
                </span>
              </div>

              <!-- Organization Info -->
              <div class="flex items-center space-x-2">
                <BuildingOfficeIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ event?.organization?.name || 'Organizasyon Yok' }}
                  </div>
                  <div class="text-xs text-gray-500">Organizasyon</div>
                </div>
              </div>

              <!-- Location Info -->
              <div v-if="event?.location" class="flex items-center space-x-2">
                <MapPinIcon class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ event.location }}</div>
                  <div class="text-xs text-gray-500">Konum</div>
                </div>
              </div>

              <!-- Published Status -->
              <div class="flex items-center space-x-2">
                <component :is="event?.is_published ? EyeIcon : EyeSlashIcon" class="h-5 w-5 text-gray-400" />
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ event?.is_published ? 'Yayında' : 'Taslak' }}</div>
                  <div class="text-xs text-gray-500">Yayın Durumu</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Bar -->
          <div class="px-8 py-4 flex flex-wrap items-center justify-between gap-4 bg-white">
            <div class="flex items-center space-x-3">
              <!-- Back to List -->
              <Link :href="route('admin.events.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm">
              <ArrowLeftIcon class="h-4 w-4 mr-2" />
              Etkinlik Listesi
              </Link>

            </div>

            <div class="flex items-center space-x-3">
              <!-- ✅ DÜZELTME: Timeline Butonları -->
              <div class="flex items-center space-x-3 border-l border-gray-200 pl-4">
                <!-- Timeline View Button -->
                <Link :href="route('admin.timeline.show', event?.slug)"
                  class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-blue-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                <ClockIcon class="h-4 w-4 mr-2" />
                Timeline Görünümü
                </Link>

                <!-- Drag & Drop Editor Button -->
                <Link v-if="event?.can_edit" :href="route('admin.drag-drop.event-editor', event?.slug)"
                  class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-600 to-red-600 text-white text-sm font-medium rounded-lg hover:from-orange-700 hover:to-red-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                <CursorArrowRaysIcon class="h-4 w-4 mr-2" />
                <span>Editör</span>
                <span class="ml-1 px-1.5 py-0.5 bg-white/20 text-xs rounded-full">Beta</span>
                </Link>
              </div>

              <!-- Timeline Export Dropdown -->
              <div class="relative">
                <button @click="showTimelineExportMenu = !showTimelineExportMenu"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors shadow-sm">
                  <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                  Timeline Dışa Aktar
                  <ChevronDownIcon class="h-4 w-4 ml-1" />
                </button>

                <!-- Export Menu -->
                <teleport to="body">
                  <div v-if="showTimelineExportMenu"
                    class="fixed bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-[99999] min-w-[16rem]"
                    :style="timelineExportStyle" @click.stop>
                    <div class="px-4 py-2 border-b border-gray-200">
                      <h3 class="text-sm font-medium text-gray-900">Timeline Dışa Aktarım</h3>
                    </div>

                    <div class="py-1">
                      <button @click="exportTimeline('pdf')"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center transition-colors">
                        <DocumentIcon class="h-4 w-4 mr-3 text-red-500" />
                        <div>
                          <div class="font-medium">PDF Timeline Raporu</div>
                          <div class="text-xs text-gray-500">Yazdırılabilir program çizelgesi</div>
                        </div>
                      </button>

                      <button @click="exportTimeline('excel')"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center transition-colors">
                        <TableCellsIcon class="h-4 w-4 mr-3 text-green-500" />
                        <div>
                          <div class="font-medium">Excel Timeline Tablosu</div>
                          <div class="text-xs text-gray-500">Düzenlenebilir veri tablosu</div>
                        </div>
                      </button>

                      <button @click="exportTimeline('image')"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center transition-colors">
                        <PhotoIcon class="h-4 w-4 mr-3 text-blue-500" />
                        <div>
                          <div class="font-medium">Timeline Görüntüsü</div>
                          <div class="text-xs text-gray-500">PNG/JPEG format</div>
                        </div>
                      </button>

                      <div class="border-t border-gray-200 my-1"></div>

                      <button @click="shareTimeline"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center transition-colors">
                        <ShareIcon class="h-4 w-4 mr-3 text-purple-500" />
                        <div>
                          <div class="font-medium">Timeline Linkini Paylaş</div>
                          <div class="text-xs text-gray-500">Bağlantıyı kopyala</div>
                        </div>
                      </button>
                    </div>
                  </div>
                </teleport>
              </div>

              <!-- Publish/Unpublish -->
              <button v-if="event?.can_publish" @click="togglePublishStatus" :disabled="processing" :class="[
                'inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-lg focus:ring-2 focus:ring-offset-2 transition-colors shadow-sm',
                event.is_published
                  ? 'bg-gray-700 hover:bg-gray-800 focus:ring-gray-500'
                  : 'bg-gray-800 hover:bg-gray-900 focus:ring-gray-600',
                processing && 'opacity-50 cursor-not-allowed'
              ]">
                <component :is="event.is_published ? EyeSlashIcon : EyeIcon" class="h-4 w-4 mr-2" />
                {{ event.is_published ? 'Yayından Kaldır' : 'Yayınla' }}
              </button>

              <!-- Edit Button -->
              <Link :href="route('admin.events.edit', event?.slug || 'default')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm">
              <PencilSquareIcon class="h-4 w-4 mr-2" />
              Düzenle
              </Link>

              <!-- More Actions -->
              <div class="relative" ref="actionsMenuRef">
                <button @click="showActionsMenu = !showActionsMenu"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm">
                  <EllipsisVerticalIcon class="h-4 w-4 mr-2" />
                  Diğer
                  <ChevronDownIcon class="h-4 w-4 ml-2" />
                </button>

                <!-- Dropdown Menu -->
                <teleport to="body">
                  <div v-if="showActionsMenu"
                    class="fixed bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 border border-gray-200 min-w-[14rem]"
                    :style="dropdownStyle" style="z-index: 99999 !important;">
                    <div class="py-1">
                      <button @click="duplicateEvent"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <DocumentDuplicateIcon class="h-4 w-4 mr-3" />
                        Etkinliği Kopyala
                      </button>

                      <button @click="exportEvent"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <DocumentArrowDownIcon class="h-4 w-4 mr-3" />
                        Dışa Aktar
                      </button>

                      <hr class="my-1 border-gray-200" />

                      <button v-if="event?.can_delete" @click="confirmDelete"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                        <TrashIcon class="h-4 w-4 mr-3" />
                        Etkinliği Sil
                      </button>
                    </div>
                  </div>
                </teleport>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ Tab Navigation -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Tab Headers -->
        <div class="border-b border-gray-200">
          <nav class="px-8 flex space-x-8" aria-label="Tabs">
            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === tab.id
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]">
              <component :is="tab.icon" class="h-5 w-5 mr-2 inline" />
              {{ tab.name }}
              <span v-if="tab.count !== undefined" :class="[
                'ml-2 py-0.5 px-2 rounded-full text-xs',
                activeTab === tab.id
                  ? 'bg-indigo-100 text-indigo-600'
                  : 'bg-gray-100 text-gray-600'
              ]">
                {{ tab.count }}
              </span>
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-8">
          <!-- Overview Tab -->
          <div v-if="activeTab === 'overview'" class="space-y-8">
            <!-- Description -->
            <div v-if="event?.description">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Açıklama</h3>
              <div class="prose max-w-none">
                <p class="text-gray-700 leading-relaxed" v-html="event.description"></p>
              </div>
            </div>

            <!-- Quick Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center">
                  <CalendarDaysIcon class="h-8 w-8 text-blue-600" />
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Toplam Gün</p>
                    <p class="text-2xl font-bold text-gray-900">{{ event?.event_days_count || 0 }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center">
                  <SpeakerWaveIcon class="h-8 w-8 text-green-600" />
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Toplam Oturum</p>
                    <p class="text-2xl font-bold text-gray-900">{{ event?.total_sessions || 0 }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center">
                  <MapPinIcon class="h-8 w-8 text-purple-600" />
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Toplam Salon</p>
                    <p class="text-2xl font-bold text-gray-900">{{ event?.total_venues || 0 }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center">
                  <UsersIcon class="h-8 w-8 text-orange-600" />
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Toplam Sunum</p>
                    <p class="text-2xl font-bold text-gray-900">{{ event?.total_presentations || 0 }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Timeline Tab - Düzeltilmiş Versiyon -->
          <div v-if="activeTab === 'timeline'">
            <div class="mb-6">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Program Zaman Çizelgesi</h3>
                  <p class="text-sm text-gray-600 mt-1">Etkinlik programını görsel zaman çizelgesi üzerinde görüntüleyin
                  </p>
                </div>

                <div class="flex items-center space-x-3">
                  <!-- Timeline Export Button -->
                  <button @click="showTimelineExportModal = true"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                    Dışa Aktar
                  </button>

                  <!-- Full Timeline View -->
                  <Link :href="route('admin.timeline.show', event?.slug)"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors">
                  <ArrowTopRightOnSquareIcon class="h-4 w-4 mr-2" />
                  Tam Ekran
                  </Link>
                </div>
              </div>
            </div>

            <!-- Timeline Component (Embedded) -->
            <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
              <!-- ✅ ÇÖZÜM: Timeline preview component'i -->
              <div v-if="timelineData?.sessions?.length > 0" class="p-6">
                <div class="text-center">
                  <ClockIcon class="h-12 w-12 text-indigo-600 mx-auto mb-4" />
                  <h3 class="text-lg font-medium text-gray-900 mb-2">Timeline Verileri Mevcut</h3>
                  <p class="text-gray-500 mb-4">{{ timelineData.sessions.length }} oturum bulundu</p>

                  <!-- Timeline preview -->
                  <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Oturum Özeti:</h4>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                      <div v-for="session in timelineData.sessions.slice(0, 5)" :key="session.id"
                        class="text-left p-3 bg-white border border-gray-200 rounded text-sm hover:shadow-sm transition-shadow cursor-pointer"
                        @click="handleTimelineSessionClick(session)">
                        <div class="font-medium text-gray-900">{{ session.title }}</div>
                        <div class="text-gray-500 text-xs mt-1 flex items-center space-x-4">
                          <span>{{ session.venue?.display_name || 'Salon belirtilmemiş' }}</span>
                          <span>{{ session.start_time || 'Zaman belirtilmemiş' }}</span>
                          <span>{{ session.presentations?.length || 0 }} sunum</span>
                        </div>
                      </div>
                    </div>
                    <p v-if="timelineData.sessions.length > 5" class="text-xs text-gray-500 mt-2">
                      ... ve {{ timelineData.sessions.length - 5 }} oturum daha
                    </p>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-16">
                <ClockIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz program yok</h3>
                <p class="text-gray-500 mb-6">Bu etkinlik için program oturumları oluşturmaya başlayın.</p>

                <Link :href="route('admin.program-sessions.create') + '?event=' + event?.slug"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <SpeakerWaveIcon class="h-5 w-5 mr-2" />
                İlk Oturumu Ekle
                </Link>
              </div>
            </div>
          </div>

          <!-- Days Tab -->
          <div v-if="activeTab === 'days'">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Etkinlik Günleri</h3>
                <p class="text-sm text-gray-600 mt-1">Etkinlik günlerini yönetin ve organize edin</p>
              </div>
              <Link v-if="event?.can_manage_days" :href="route('admin.events.days.create', event?.slug)"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
              <CalendarDaysIcon class="h-4 w-4 mr-2" />
              Yeni Gün Ekle
              </Link>
            </div>

            <!-- Days List -->
            <div v-if="event?.event_days?.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="day in event.event_days" :key="day.id"
                class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">
                      {{ day.title }}
                    </h4>
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                      <ClockIcon class="h-4 w-4 mr-1" />
                      {{ formatDate(day.date) }}
                    </div>
                    <div class="flex items-center text-sm text-gray-500">
                      <SpeakerWaveIcon class="h-4 w-4 mr-1" />
                      {{ day.sessions_count || 0 }} oturum
                    </div>
                  </div>
                </div>

                <!-- Day Actions -->
                <div class="flex items-center space-x-2 pt-4 border-t border-gray-200">
                  <Link :href="route('admin.events.days.show', [event?.slug, day.id])"
                    class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                  <EyeIcon class="h-4 w-4 mr-1" />
                  Görüntüle
                  </Link>
                  <Link :href="route('admin.events.days.edit', [event?.slug, day.id])"
                    class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-lg transition-colors">
                  <PencilIcon class="h-4 w-4 mr-1" />
                  Düzenle
                  </Link>
                </div>
              </div>
            </div>

            <!-- Empty Days State -->
            <div v-else class="text-center py-16">
              <CalendarDaysIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz gün yok</h3>
              <p class="text-gray-500 mb-6">Bu etkinlik için günler eklemeye başlayın.</p>
              <Link v-if="event?.can_manage_days" :href="route('admin.events.days.create', event?.slug)"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
              <CalendarDaysIcon class="h-5 w-5 mr-2" />
              İlk Günü Ekle
              </Link>
            </div>
          </div>

          <!-- Management Tab -->
          <div v-if="activeTab === 'management'">
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Yönetim Araçları</h3>
              <p class="text-sm text-gray-600 mt-1">Etkinlik bileşenlerini yönetin</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Days Management -->
              <Link :href="route('admin.events.days.index', event?.slug)"
                class="group p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md hover:border-indigo-300 transition-all">
              <div class="flex items-center justify-between mb-4">
                <CalendarDaysIcon class="h-8 w-8 text-blue-600" />
                <ArrowTopRightOnSquareIcon
                  class="h-5 w-5 text-gray-400 group-hover:text-indigo-600 transition-colors" />
              </div>
              <h4 class="text-lg font-semibold text-gray-900 mb-2">Günleri Yönet</h4>
              <p class="text-sm text-gray-600 mb-4">Etkinlik günlerini ekleyin ve düzenleyin</p>
              <div class="text-sm font-medium text-indigo-600">
                {{ event?.event_days_count || 0 }} gün
              </div>
              </Link>

              <!-- Venues Management -->
              <Link :href="route('admin.venues.index') + '?event=' + event?.slug"
                class="group p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md hover:border-indigo-300 transition-all">
              <div class="flex items-center justify-between mb-4">
                <MapPinIcon class="h-8 w-8 text-purple-600" />
                <ArrowTopRightOnSquareIcon
                  class="h-5 w-5 text-gray-400 group-hover:text-indigo-600 transition-colors" />
              </div>
              <h4 class="text-lg font-semibold text-gray-900 mb-2">Salonları Yönet</h4>
              <p class="text-sm text-gray-600 mb-4">Etkinlik salonlarını organize edin</p>
              <div class="text-sm font-medium text-indigo-600">
                {{ event?.total_venues || 0 }} salon
              </div>
              </Link>

              <!-- Sessions Management -->
              <Link :href="route('admin.program-sessions.index') + '?event=' + event?.slug"
                class="group p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md hover:border-indigo-300 transition-all">
              <div class="flex items-center justify-between mb-4">
                <SpeakerWaveIcon class="h-8 w-8 text-green-600" />
                <ArrowTopRightOnSquareIcon
                  class="h-5 w-5 text-gray-400 group-hover:text-indigo-600 transition-colors" />
              </div>
              <h4 class="text-lg font-semibold text-gray-900 mb-2">Oturumları Yönet</h4>
              <p class="text-sm text-gray-600 mb-4">Program oturumlarını oluşturun ve düzenleyin</p>
              <div class="text-sm font-medium text-indigo-600">
                {{ event?.total_sessions || 0 }} oturum
              </div>
              </Link>

              <!-- Presentations Management -->
              <Link :href="route('admin.presentations.index') + '?event=' + event?.slug"
                class="group p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md hover:border-indigo-300 transition-all">
              <div class="flex items-center justify-between mb-4">
                <DocumentTextIcon class="h-8 w-8 text-orange-600" />
                <ArrowTopRightOnSquareIcon
                  class="h-5 w-5 text-gray-400 group-hover:text-indigo-600 transition-colors" />
              </div>
              <h4 class="text-lg font-semibold text-gray-900 mb-2">Sunumları Yönet</h4>
              <p class="text-sm text-gray-600 mb-4">Etkinlik sunumlarını organize edin</p>
              <div class="text-sm font-medium text-indigo-600">
                {{ event?.total_presentations || 0 }} sunum
              </div>
              </Link>

              <!-- Participants Management -->
              <Link :href="route('admin.participants.index') + '?event=' + event?.slug"
                class="group p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md hover:border-indigo-300 transition-all">
              <div class="flex items-center justify-between mb-4">
                <UsersIcon class="h-8 w-8 text-indigo-600" />
                <ArrowTopRightOnSquareIcon
                  class="h-5 w-5 text-gray-400 group-hover:text-indigo-600 transition-colors" />
              </div>
              <h4 class="text-lg font-semibold text-gray-900 mb-2">Katılımcıları Yönet</h4>
              <p class="text-sm text-gray-600 mb-4">Konuşmacı ve moderatörleri yönetin</p>
              <div class="text-sm font-medium text-indigo-600">
                {{ event?.total_participants || 0 }} katılımcı
              </div>
              </Link>

              <!-- Timeline Management -->
              <Link :href="route('admin.timeline.show', event?.slug)"
                class="group p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md hover:border-indigo-300 transition-all">
              <div class="flex items-center justify-between mb-4">
                <ClockIcon class="h-8 w-8 text-red-600" />
                <ArrowTopRightOnSquareIcon
                  class="h-5 w-5 text-gray-400 group-hover:text-indigo-600 transition-colors" />
              </div>
              <h4 class="text-lg font-semibold text-gray-900 mb-2">Zaman Çizelgesi</h4>
              <p class="text-sm text-gray-600 mb-4">Program zaman çizelgesini görüntüleyin</p>
              <div class="text-sm font-medium text-indigo-600">
                Görsel timeline
              </div>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Session Detail Modal -->
      <div v-if="selectedTimelineSession"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click="selectedTimelineSession = null">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" @click.stop>
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Oturum Detayları</h3>
              <button @click="selectedTimelineSession = null" class="text-gray-400 hover:text-gray-600">
                <XMarkIcon class="h-6 w-6" />
              </button>
            </div>
          </div>

          <div class="p-6">
            <div class="space-y-4">
              <div>
                <h4 class="text-xl font-semibold text-gray-900">
                  {{ selectedTimelineSession.title }}
                </h4>
                <p v-if="selectedTimelineSession.description" class="text-gray-600 mt-2">
                  {{ selectedTimelineSession.description }}
                </p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="text-sm font-medium text-gray-500">Tarih & Saat:</span>
                  <p class="text-gray-900">
                    {{ formatDate(selectedTimelineSession.venue?.event_day?.date) }}<br>
                    {{ formatTimeRange(selectedTimelineSession) }}
                  </p>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-500">Salon:</span>
                  <p class="text-gray-900">{{ selectedTimelineSession.venue?.display_name }}</p>
                </div>
              </div>

              <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <Link :href="route('admin.program-sessions.show', selectedTimelineSession.id)"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <EyeIcon class="h-4 w-4 mr-2" />
                Detaylı Görünüm
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  ArrowLeftIcon,
  PencilSquareIcon,
  EllipsisVerticalIcon,
  ChevronDownIcon,
  DocumentDuplicateIcon,
  DocumentArrowDownIcon,
  TrashIcon,
  EyeIcon,
  EyeSlashIcon,
  CalendarIcon,
  CalendarDaysIcon,
  MapPinIcon,
  SpeakerWaveIcon,
  ClockIcon,
  BuildingOfficeIcon,
  PencilIcon,
  UsersIcon,
  DocumentTextIcon,
  ArrowTopRightOnSquareIcon,
  XMarkIcon,
  ArrowDownTrayIcon,
  Cog6ToothIcon,
  CursorArrowRaysIcon,
  DocumentIcon,
  TableCellsIcon,
  PhotoIcon,
  ShareIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  event: {
    type: Object,
    default: () => ({})
  },
  statistics: {
    type: Object,
    default: null
  },
  recent_activities: {
    type: Array,
    default: () => []
  },
  timelineData: {
    type: Object,
    default: () => ({
      event: {},
      eventDays: [],
      sessions: [],
      conflicts: []
    })
  }
})

// State
const showActionsMenu = ref(false)
const showTimelineExportMenu = ref(false)
const processing = ref(false)
const actionsMenuRef = ref(null)
const dropdownStyle = ref({})
const timelineExportStyle = ref({})
const activeTab = ref('overview')
const showTimelineExportModal = ref(false)
const selectedTimelineSession = ref(null)
const timelineLoading = ref(false)

// Tab configuration
const tabs = computed(() => [
  {
    id: 'overview',
    name: 'Genel Bakış',
    icon: DocumentTextIcon,
    count: undefined
  },
  {
    id: 'timeline',
    name: 'Program Zaman Çizelgesi',
    icon: ClockIcon,
    count: props.timelineData?.sessions?.length || 0
  },
  {
    id: 'days',
    name: 'Günler',
    icon: CalendarDaysIcon,
    count: props.event?.event_days_count || 0
  },
  {
    id: 'management',
    name: 'Yönetim',
    icon: Cog6ToothIcon,
    count: undefined
  }
])

// Computed properties
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: route('admin.dashboard') },
  { label: 'Etkinlikler', href: route('admin.events.index') },
  { label: props.event?.name || 'Etkinlik', href: null }
])

const event = computed(() => props.event)

const eventSubtitle = computed(() => {
  const org = props.event?.organization?.name || 'Organizasyon'
  const location = props.event?.location ? ` - ${props.event.location}` : ''
  return `${org}${location}`
})

const eventDateRange = computed(() => {
  return formatDateRange(props.event?.start_date, props.event?.end_date)
})

const statusClasses = computed(() => {
  return getStatusClasses(props.event?.status)
})

const statusLabel = computed(() => {
  return getStatusLabel(props.event?.status)
})

// Methods
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateRange = (startDate, endDate) => {
  if (!startDate) return '-'

  const start = new Date(startDate)
  const end = endDate ? new Date(endDate) : start

  const options = { day: 'numeric', month: 'short', year: 'numeric' }

  if (start.toDateString() === end.toDateString()) {
    return start.toLocaleDateString('tr-TR', options)
  }

  return `${start.toLocaleDateString('tr-TR', options)} - ${end.toLocaleDateString('tr-TR', options)}`
}

const formatTimeRange = (session) => {
  if (!session.start_time || !session.end_time) return ''
  const start = session.start_time.substring(0, 5)
  const end = session.end_time.substring(0, 5)
  return `${start} - ${end}`
}

const getStatusClasses = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    upcoming: 'bg-gray-200 text-gray-900',
    ongoing: 'bg-gray-300 text-gray-900',
    completed: 'bg-gray-400 text-white',
    published: 'bg-gray-600 text-white'
  }
  return classes[status] || classes.draft
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Taslak',
    upcoming: 'Yaklaşan',
    ongoing: 'Devam Ediyor',
    completed: 'Tamamlandı',
    published: 'Yayında'
  }
  return labels[status] || status
}

// Timeline methods
const handleTimelineSessionClick = (session) => {
  selectedTimelineSession.value = session
}

const handleTimelineFilterChange = (filters) => {
  console.log('Timeline filters changed:', filters)
}

const handleTimelineExportComplete = (exportInfo) => {
  console.log('Timeline export completed:', exportInfo)
  setTimeout(() => {
    showTimelineExportModal.value = false
  }, 2000)
}

// Export methods
const exportTimeline = (format) => {
  showTimelineExportMenu.value = false

  const exportData = {
    format: format,
    event_slug: event.value.slug
  }

  router.post(route('admin.timeline.export', event.value.slug), exportData, {
    onSuccess: (page) => {
      if (page.props.flash?.success) {
        console.log('Timeline export başlatıldı:', format)
      }
    },
    onError: (errors) => {
      console.error('Timeline export error:', errors)
      alert('Export işlemi sırasında hata oluştu.')
    }
  })
}

const shareTimeline = () => {
  showTimelineExportMenu.value = false

  const timelineUrl = route('admin.timeline.show', event.value.slug)
  const fullUrl = window.location.origin + timelineUrl

  if (navigator.share) {
    navigator.share({
      title: `${event.value.name} - Timeline`,
      text: `${event.value.name} etkinliğinin timeline görünümü`,
      url: fullUrl
    }).catch(console.error)
  } else {
    navigator.clipboard.writeText(fullUrl).then(() => {
      alert('Timeline linki panoya kopyalandı!')
    }).catch(() => {
      prompt('Timeline linki:', fullUrl)
    })
  }
}

// Dropdown positioning
const calculateDropdownPosition = async (ref, type) => {
  if (!ref.value) return
  await nextTick()

  const rect = ref.value.getBoundingClientRect()
  const windowHeight = window.innerHeight

  let top = rect.bottom + 8
  let left = rect.right - 224

  if (top + 300 > windowHeight) {
    top = rect.top - 300
  }

  if (left < 8) {
    left = 8
  }

  const style = {
    top: `${top}px`,
    left: `${left}px`
  }

  if (type === 'actions') {
    dropdownStyle.value = style
  } else if (type === 'timeline') {
    timelineExportStyle.value = style
  }
}

const togglePublishStatus = () => {
  processing.value = true
  setTimeout(() => {
    processing.value = false
  }, 1000)
}

const duplicateEvent = () => {
  showActionsMenu.value = false
  console.log('Duplicate event:', props.event)
}

const exportEvent = () => {
  showActionsMenu.value = false
  console.log('Export event:', props.event)
}

const confirmDelete = () => {
  showActionsMenu.value = false
  console.log('Delete event:', props.event)
}

// Event handlers
const handleClickOutside = (event) => {
  if (showActionsMenu.value && !actionsMenuRef.value?.contains(event.target)) {
    showActionsMenu.value = false
  }
  if (showTimelineExportMenu.value && !event.target.closest('.relative')) {
    showTimelineExportMenu.value = false
  }
}

// Watch dropdown changes
watch(showActionsMenu, (newValue) => {
  if (newValue) {
    calculateDropdownPosition(actionsMenuRef, 'actions')
  }
})

watch(showTimelineExportMenu, (newValue) => {
  if (newValue) {
    const timelineExportRef = { value: event.target }
    calculateDropdownPosition(timelineExportRef, 'timeline')
  }
})

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* Tab styling */
.tab-content {
  min-height: 400px;
}

/* Timeline embedded styling */
.timeline-embedded {
  max-height: 600px;
  overflow-y: auto;
}

/* Card hover effects */
.management-card {
  transition: all 0.2s ease;
}

.management-card:hover {
  transform: translateY(-2px);
}

/* Z-index fixes */
.dropdown-container {
  position: relative;
  z-index: 50;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Tab indicator animation */
.tab-indicator {
  transition: all 0.3s ease;
}

/* Enhanced shadow for dropdown */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}

/* Beta badge styling */
.badge-beta {
  animation: pulse-orange 2s infinite;
}

@keyframes pulse-orange {

  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.4);
  }

  50% {
    box-shadow: 0 0 0 0.5rem rgba(249, 115, 22, 0);
  }
}

/* Responsive improvements */
@media (max-width: 768px) {
  .tab-navigation {
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }

  .tab-navigation::-webkit-scrollbar {
    display: none;
  }
}
</style>