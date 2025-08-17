<template>
  <AdminLayout
    page-title="Bilimsel Etkinlikler"
    page-subtitle="Akademik kongreler, sempozyumlar ve konferansları yönetin"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Bilimsel Etkinlikler" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-blue-950 dark:via-indigo-950 dark:to-purple-950 -mx-8 -my-6 px-8 py-6">
      <div class="max-w-7xl mx-auto">
        <!-- Corporate Hero Section with Enhanced Stats -->
        <div class="mb-10">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Total Events -->
            <div class="group relative overflow-hidden bg-white dark:bg-blue-900 rounded-3xl p-8 shadow-2xl border-2 border-blue-200 dark:border-blue-700 hover:shadow-3xl transition-all duration-300 hover:-translate-y-2">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-3 mb-4">
                    <div class="h-3 w-3 bg-blue-500 rounded-full animate-pulse"></div>
                    <p class="text-base font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wide">Toplam Etkinlik</p>
                  </div>
                  <p class="text-4xl font-bold text-blue-900 dark:text-blue-100 mb-4">{{ stats?.total || events?.meta?.total || 0 }}</p>
                  <div class="flex items-center text-base">
                    <ArrowUpIcon class="h-5 w-5 text-green-500 mr-2" />
                    <span class="text-green-600 dark:text-green-400 font-bold">+12%</span>
                    <span class="text-blue-600 dark:text-blue-300 ml-2">bu ay</span>
                  </div>
                </div>
                <div class="flex-shrink-0">
                  <div class="h-16 w-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-xl">
                    <CalendarIcon class="h-8 w-8 text-white" />
                  </div>
                </div>
              </div>
              <div class="absolute bottom-0 left-0 w-full h-2 bg-gradient-to-r from-blue-400 to-blue-600"></div>
            </div>
            
            <!-- Published Events -->
            <div class="group relative overflow-hidden bg-white dark:bg-green-900 rounded-3xl p-8 shadow-2xl border-2 border-green-200 dark:border-green-700 hover:shadow-3xl transition-all duration-300 hover:-translate-y-2">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-3 mb-4">
                    <div class="h-3 w-3 bg-green-500 rounded-full animate-pulse"></div>
                    <p class="text-base font-bold text-green-800 dark:text-green-200 uppercase tracking-wide">Yayında</p>
                  </div>
                  <p class="text-4xl font-bold text-green-900 dark:text-green-100 mb-4">{{ stats?.published || 0 }}</p>
                  <div class="flex items-center text-base">
                    <EyeIcon class="h-5 w-5 text-green-500 mr-2" />
                    <span class="text-green-600 dark:text-green-400 font-bold">Aktif</span>
                    <span class="text-green-600 dark:text-green-300 ml-2">yayınlar</span>
                  </div>
                </div>
                <div class="flex-shrink-0">
                  <div class="h-16 w-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-xl">
                    <EyeIcon class="h-8 w-8 text-white" />
                  </div>
                </div>
              </div>
              <div class="absolute bottom-0 left-0 w-full h-2 bg-gradient-to-r from-green-400 to-emerald-600"></div>
            </div>
            
            <!-- Upcoming Events -->
            <div class="group relative overflow-hidden bg-white dark:bg-purple-900 rounded-3xl p-8 shadow-2xl border-2 border-purple-200 dark:border-purple-700 hover:shadow-3xl transition-all duration-300 hover:-translate-y-2">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-3 mb-4">
                    <div class="h-3 w-3 bg-purple-500 rounded-full animate-pulse"></div>
                    <p class="text-base font-bold text-purple-800 dark:text-purple-200 uppercase tracking-wide">Yaklaşan</p>
                  </div>
                  <p class="text-4xl font-bold text-purple-900 dark:text-purple-100 mb-4">{{ stats?.upcoming || 0 }}</p>
                  <div class="flex items-center text-base">
                    <ClockIcon class="h-5 w-5 text-purple-500 mr-2" />
                    <span class="text-purple-600 dark:text-purple-400 font-bold">Planlanan</span>
                    <span class="text-purple-600 dark:text-purple-300 ml-2">etkinlik</span>
                  </div>
                </div>
                <div class="flex-shrink-0">
                  <div class="h-16 w-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-xl">
                    <ClockIcon class="h-8 w-8 text-white" />
                  </div>
                </div>
              </div>
              <div class="absolute bottom-0 left-0 w-full h-2 bg-gradient-to-r from-purple-400 to-purple-600"></div>
            </div>
            
            <!-- Ongoing Events -->
            <div class="group relative overflow-hidden bg-white dark:bg-indigo-900 rounded-3xl p-8 shadow-2xl border-2 border-indigo-200 dark:border-indigo-700 hover:shadow-3xl transition-all duration-300 hover:-translate-y-2">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-3 mb-4">
                    <div class="h-3 w-3 bg-indigo-500 rounded-full animate-pulse"></div>
                    <p class="text-base font-bold text-indigo-800 dark:text-indigo-200 uppercase tracking-wide">Devam Eden</p>
                  </div>
                  <p class="text-4xl font-bold text-indigo-900 dark:text-indigo-100 mb-4">{{ stats?.ongoing || 0 }}</p>
                  <div class="flex items-center text-base">
                    <PlayIcon class="h-5 w-5 text-indigo-500 mr-2" />
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">Canlı</span>
                    <span class="text-indigo-600 dark:text-indigo-300 ml-2">oturum</span>
                  </div>
                </div>
                <div class="flex-shrink-0">
                  <div class="h-16 w-16 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-xl">
                    <PlayIcon class="h-8 w-8 text-white" />
                  </div>
                </div>
              </div>
              <div class="absolute bottom-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-400 to-indigo-600"></div>
            </div>
          </div>
        </div>

        <!-- Modern Corporate Data Management Interface -->
        <div class="bg-white dark:bg-blue-900 shadow-2xl rounded-3xl overflow-hidden border-2 border-blue-200 dark:border-blue-700">
          <!-- Enhanced Corporate Header -->
          <div class="bg-gradient-to-r from-blue-100 via-indigo-100 to-purple-100 dark:from-blue-800 dark:via-indigo-800 dark:to-purple-800 px-10 py-10 border-b-2 border-blue-200 dark:border-blue-700">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between space-y-8 xl:space-y-0">
              <!-- Left: Corporate Title and Description -->
              <div class="flex-1">
                <div class="flex items-center space-x-6 mb-6">
                  <div class="h-14 w-14 bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl flex items-center justify-center shadow-xl">
                    <DocumentTextIcon class="h-8 w-8 text-white" />
                  </div>
                  <div>
                    <h2 class="text-3xl font-bold text-blue-900 dark:text-blue-100">
                      Akademik Etkinlik Yönetimi
                    </h2>
                    <p class="text-blue-700 dark:text-blue-200 mt-2 text-lg font-medium">
                      Bilimsel kongreler, sempozyumlar ve konferansları profesyonelce organize edin
                    </p>
                  </div>
                </div>
                
                <!-- Debug Information -->
                <div class="bg-blue-200 dark:bg-blue-800 rounded-2xl p-4 border-2 border-blue-300 dark:border-blue-700">
                  <button 
                    @click="showDebug = !showDebug" 
                    class="text-base text-blue-700 dark:text-blue-200 hover:text-blue-900 dark:hover:text-blue-100 font-bold transition-colors"
                  >
                    {{ showDebug ? '🔍 Sistem Bilgilerini Gizle' : '🔍 Sistem Bilgilerini Göster' }}
                  </button>
                  <div v-if="showDebug" class="mt-4 text-base text-blue-700 dark:text-blue-200 space-y-2">
                    <div>📊 Toplam Kayıt: {{ events?.meta?.total || 0 }}</div>
                    <div>📄 Mevcut Sayfa: {{ events?.meta?.current_page || 1 }}</div>
                    <div>🏢 Organizasyon Sayısı: {{ organizations?.length || 0 }}</div>
                    <div>🔄 Yükleme Durumu: {{ loading ? 'Yükleniyor...' : 'Hazır' }}</div>
                  </div>
                </div>
              </div>

              <!-- Right: Corporate Actions -->
              <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-6 sm:space-y-0 sm:space-x-6">
                <!-- Corporate Quick Filters -->
                <div class="flex flex-wrap items-center gap-4">
                  <button
                    @click="quickFilter('all')"
                    :class="[
                      'px-6 py-3 rounded-2xl text-base font-bold transition-all duration-200 transform hover:scale-105',
                      currentQuickFilter === 'all' 
                        ? 'bg-blue-600 text-white shadow-xl ring-3 ring-blue-600/30' 
                        : 'bg-white dark:bg-blue-800 text-blue-700 dark:text-blue-200 border-2 border-blue-300 dark:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-700 shadow-lg'
                    ]"
                  >
                    Tümü
                  </button>
                  <button
                    @click="quickFilter('published')"
                    :class="[
                      'px-6 py-3 rounded-2xl text-base font-bold transition-all duration-200 transform hover:scale-105',
                      currentQuickFilter === 'published' 
                        ? 'bg-green-600 text-white shadow-xl ring-3 ring-green-600/30' 
                        : 'bg-white dark:bg-blue-800 text-blue-700 dark:text-blue-200 border-2 border-blue-300 dark:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-700 shadow-lg'
                    ]"
                  >
                    Yayında
                  </button>
                  <button
                    @click="quickFilter('upcoming')"
                    :class="[
                      'px-6 py-3 rounded-2xl text-base font-bold transition-all duration-200 transform hover:scale-105',
                      currentQuickFilter === 'upcoming' 
                        ? 'bg-purple-600 text-white shadow-xl ring-3 ring-purple-600/30' 
                        : 'bg-white dark:bg-blue-800 text-blue-700 dark:text-blue-200 border-2 border-blue-300 dark:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-700 shadow-lg'
                    ]"
                  >
                    Yaklaşan
                  </button>
                </div>

                <!-- Create New Corporate Event Button -->
                <Link
                  :href="safeRoute('admin.events.create', '/admin/events/create')"
                  class="group inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105 border-2 border-blue-500"
                >
                  <PlusIcon class="h-6 w-6 mr-3 group-hover:rotate-90 transition-transform duration-200" />
                  Yeni Akademik Etkinlik
                </Link>
              </div>
            </div>

            <!-- Enhanced Corporate Search and Filters -->
            <div class="mt-10 flex flex-col lg:flex-row lg:items-center space-y-6 lg:space-y-0 lg:space-x-8">
              <!-- Corporate Search Bar -->
              <div class="flex-1 relative group">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                  <MagnifyingGlassIcon class="h-6 w-6 text-blue-400 group-focus-within:text-blue-600 transition-colors" />
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Etkinlik adı, açıklama, organizasyon veya konum ile ara..."
                  class="block w-full pl-16 pr-6 py-5 border-3 border-blue-300 dark:border-blue-600 rounded-2xl bg-white dark:bg-blue-800 text-blue-900 dark:text-blue-100 placeholder-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-500/30 focus:border-blue-500 shadow-lg hover:shadow-xl focus:shadow-2xl transition-all duration-300 text-lg font-medium"
                  @input="handleSearchDebounced"
                />
                <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-6 flex items-center">
                  <button
                    @click="clearSearch"
                    class="text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 transition-colors p-2 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-700"
                  >
                    <XMarkIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Advanced Corporate Filters -->
              <div class="flex flex-wrap items-center gap-4">
                <!-- Organization Filter -->
                <select
                  v-model="activeFilters.organization_id"
                  @change="applyFilters"
                  class="px-6 py-4 border-3 border-blue-300 dark:border-blue-600 rounded-2xl bg-white dark:bg-blue-800 text-blue-900 dark:text-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-500/30 shadow-lg hover:shadow-xl transition-all duration-300 font-bold min-w-[250px]"
                >
                  <option value="">🏢 Tüm Organizasyonlar</option>
                  <option 
                    v-for="org in organizations" 
                    :key="org.id" 
                    :value="org.id"
                  >
                    {{ org.name }}
                  </option>
                </select>

                <!-- Corporate Status Filter -->
                <select
                  v-model="activeFilters.status"
                  @change="applyFilters"
                  class="px-6 py-4 border-3 border-blue-300 dark:border-blue-600 rounded-2xl bg-white dark:bg-blue-800 text-blue-900 dark:text-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-500/30 shadow-lg hover:shadow-xl transition-all duration-300 font-bold min-w-[200px]"
                >
                  <option value="">📋 Tüm Durumlar</option>
                  <option value="draft">📝 Taslak</option>
                  <option value="published">📢 Yayında</option>
                  <option value="upcoming">⏰ Yaklaşan</option>
                  <option value="ongoing">▶️ Devam Ediyor</option>
                  <option value="completed">✅ Tamamlandı</option>
                </select>

                <!-- Filter Reset -->
                <button
                  v-if="hasActiveFilters"
                  @click="clearFilters"
                  class="px-6 py-4 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 border-3 border-red-300 dark:border-red-600 rounded-2xl hover:bg-red-50 dark:hover:bg-red-900 transition-all duration-300 shadow-lg hover:shadow-xl"
                  title="Filtreleri Temizle"
                >
                  <XMarkIcon class="h-6 w-6" />
                </button>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center py-24">
            <div class="flex flex-col items-center space-y-6">
              <div class="relative">
                <div class="animate-spin rounded-full h-20 w-20 border-b-4 border-blue-600"></div>
                <div class="absolute inset-0 rounded-full border-4 border-blue-200 dark:border-blue-700"></div>
              </div>
              <p class="text-blue-600 dark:text-blue-400 font-bold text-xl">Akademik veriler yükleniyor...</p>
            </div>
          </div>

          <!-- Corporate Events Table -->
          <div v-else-if="displayedEvents.length > 0" class="overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full divide-y-2 divide-blue-200 dark:divide-blue-800">
                <thead class="bg-blue-50 dark:bg-blue-800/50">
                  <tr>
                    <!-- Corporate Event Header -->
                    <th class="px-10 py-6 text-left text-base font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider">
                      Akademik Etkinlik
                    </th>
                    
                    <!-- Corporate Status Header -->
                    <th class="px-8 py-6 text-left text-base font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider w-40">
                      Durum
                    </th>
                    
                    <!-- Organization Header -->
                    <th class="px-8 py-6 text-left text-base font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider w-60">
                      Düzenleyen Kurum
                    </th>
                    
                    <!-- Corporate Progress Header -->
                    <th class="px-8 py-6 text-left text-base font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider w-48">
                      Program İlerlemesi
                    </th>
                    
                    <!-- Actions Header -->
                    <th class="px-8 py-6 text-center text-base font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider w-80">
                      İşlemler
                    </th>
                  </tr>
                </thead>
                
                <tbody class="bg-white dark:bg-blue-900 divide-y-2 divide-blue-200 dark:divide-blue-800">
                  <tr 
                    v-for="event in displayedEvents" 
                    :key="event.id"
                    class="group hover:bg-blue-50 dark:hover:bg-blue-800/50 transition-all duration-200"
                  >
                    <!-- Corporate Event Info -->
                    <td class="px-10 py-8">
                      <div class="flex items-center space-x-8">
                        <div class="flex-shrink-0 h-24 w-24">
                          <img
                            v-if="event.banner_url"
                            class="h-24 w-24 rounded-3xl object-cover shadow-xl ring-2 ring-blue-200 dark:ring-blue-700"
                            :src="event.banner_url"
                            :alt="event.name"
                          />
                          <div
                            v-else
                            class="h-24 w-24 rounded-3xl bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-700 dark:to-blue-800 flex items-center justify-center shadow-xl ring-2 ring-blue-200 dark:ring-blue-700"
                          >
                            <CalendarIcon class="h-12 w-12 text-blue-600 dark:text-blue-300" />
                          </div>
                        </div>
                        <div class="flex-1 min-w-0">
                          <Link
                            :href="safeRoute('admin.events.show', `/admin/events/${event.slug}`, event.slug)"
                            class="block text-xl font-bold text-blue-900 dark:text-blue-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 mb-3 line-clamp-2"
                          >
                            {{ event.name }}
                          </Link>
                          <p v-if="event.description" class="text-blue-700 dark:text-blue-300 mb-4 line-clamp-2 max-w-lg leading-relaxed">
                            {{ event.description }}
                          </p>
                          <div class="flex items-center space-x-8 text-base">
                            <span class="inline-flex items-center text-blue-600 dark:text-blue-300">
                              <CalendarDaysIcon class="h-5 w-5 mr-2" />
                              {{ formatDateRange(event.start_date, event.end_date) }}
                            </span>
                            <span v-if="event.location" class="inline-flex items-center text-blue-600 dark:text-blue-300">
                              <MapPinIcon class="h-5 w-5 mr-2" />
                              {{ event.location }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Corporate Status -->
                    <td class="px-8 py-8 w-40">
                      <span
                        :class="[
                          'inline-flex items-center px-4 py-3 rounded-2xl text-base font-bold',
                          getStatusClasses(event.status)
                        ]"
                      >
                        <svg class="mr-3 h-3 w-3 fill-current" viewBox="0 0 8 8">
                          <circle cx="4" cy="4" r="3" />
                        </svg>
                        {{ getStatusLabel(event.status) }}
                      </span>
                    </td>

                    <!-- Organization -->
                    <td class="px-8 py-8 w-60">
                      <div class="flex items-center space-x-4">
                        <img
                          v-if="event.organization?.logo_url"
                          class="h-12 w-12 rounded-2xl object-cover"
                          :src="event.organization.logo_url"
                          :alt="event.organization.name"
                        />
                        <BuildingOfficeIcon v-else class="h-12 w-12 text-blue-400" />
                        <div class="flex-1 min-w-0">
                          <span class="text-base font-bold text-blue-900 dark:text-blue-100 block truncate">
                            {{ event.organization?.name || 'Organizasyon Yok' }}
                          </span>
                          <span class="text-sm text-blue-600 dark:text-blue-300">
                            Düzenleyen Kurum
                          </span>
                        </div>
                      </div>
                    </td>

                    <!-- Corporate Progress -->
                    <td class="px-8 py-8 w-48">
                      <div class="space-y-3">
                        <div class="flex items-center justify-between">
                          <span class="text-sm text-blue-600 dark:text-blue-300 font-bold">Günler</span>
                          <span class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ event.event_days_count || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                          <span class="text-sm text-blue-600 dark:text-blue-300 font-bold">Sunumlar</span>
                          <span class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ event.total_presentations || 0 }}</span>
                        </div>
                        <div class="w-full bg-blue-200 dark:bg-blue-700 rounded-full h-3">
                          <div 
                            class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300"
                            :style="{ width: `${Math.min(100, (event.total_presentations || 0) * 10)}%` }"
                          ></div>
                        </div>
                      </div>
                    </td>

                    <!-- Corporate Actions -->
                    <td class="px-8 py-8 w-80">
                      <div class="flex flex-wrap items-center gap-3">
                        <!-- View Button -->
                        <Link
                          :href="safeRoute('admin.events.show', `/admin/events/${event.slug}`, event.slug)"
                          class="inline-flex items-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-2xl shadow-lg transition-all duration-200 hover:shadow-xl transform hover:scale-105"
                          title="Detaylı Görünüm"
                        >
                          <EyeIcon class="h-4 w-4 mr-2" />
                          Görüntüle
                        </Link>

                        <!-- Edit Button -->
                        <Link
                          v-if="event.can_edit"
                          :href="safeRoute('admin.events.edit', `/admin/events/${event.slug}/edit`, event.slug)"
                          class="inline-flex items-center px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-2xl shadow-lg transition-all duration-200 hover:shadow-xl transform hover:scale-105"
                          title="Düzenle"
                        >
                          <PencilIcon class="h-4 w-4 mr-2" />
                          Düzenle
                        </Link>

                        <!-- Timeline Button -->
                        <Link
                          :href="safeRoute('admin.timeline.show', `/admin/timeline/${event.slug}`, event.slug)"
                          class="inline-flex items-center px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-2xl shadow-lg transition-all duration-200 hover:shadow-xl transform hover:scale-105"
                          title="Zaman Çizelgesi"
                        >
                          <ClockIcon class="h-4 w-4 mr-2" />
                          Timeline
                        </Link>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Enhanced Corporate Pagination -->
            <div v-if="events?.meta?.last_page > 1" class="bg-blue-50 dark:bg-blue-800/50 px-10 py-8 border-t-2 border-blue-200 dark:border-blue-800">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-6 sm:space-y-0">
                <div class="flex items-center space-x-8">
                  <p class="text-base text-blue-700 dark:text-blue-300 font-bold">
                    <span class="font-bold">{{ events.meta.from || 0 }}</span>
                    -
                    <span class="font-bold">{{ events.meta.to || 0 }}</span>
                    arası, toplam
                    <span class="font-bold text-blue-900 dark:text-blue-100">{{ events.meta.total || 0 }}</span>
                    akademik etkinlik
                  </p>
                </div>

                <nav class="flex items-center space-x-4">
                  <!-- Previous Page -->
                  <button
                    @click="goToPage(events.meta.current_page - 1)"
                    :disabled="events.meta.current_page <= 1"
                    class="px-6 py-3 text-base font-bold text-blue-600 bg-white dark:bg-blue-800 border-2 border-blue-300 dark:border-blue-700 rounded-2xl hover:bg-blue-50 dark:hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                  >
                    ← Önceki
                  </button>

                  <!-- Page Info -->
                  <div class="px-6 py-3 text-base font-bold text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-800 rounded-2xl">
                    {{ events.meta.current_page }} / {{ events.meta.last_page }}
                  </div>

                  <!-- Next Page -->
                  <button
                    @click="goToPage(events.meta.current_page + 1)"
                    :disabled="events.meta.current_page >= events.meta.last_page"
                    class="px-6 py-3 text-base font-bold text-blue-600 bg-white dark:bg-blue-800 border-2 border-blue-300 dark:border-blue-700 rounded-2xl hover:bg-blue-50 dark:hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                  >
                    Sonraki →
                  </button>
                </nav>
              </div>
            </div>
          </div>

          <!-- Corporate Empty State -->
          <div v-else class="py-24">
            <div class="text-center max-w-2xl mx-auto">
              <div class="mx-auto h-40 w-40 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-800 dark:to-blue-900 rounded-3xl flex items-center justify-center mb-10 shadow-2xl">
                <CalendarIcon class="h-20 w-20 text-blue-400" />
              </div>
              <h3 class="text-3xl font-bold text-blue-900 dark:text-blue-100 mb-6">
                {{ searchQuery || hasActiveFilters ? 'Sonuç bulunamadı' : 'Henüz akademik etkinlik yok' }}
              </h3>
              <p class="text-blue-700 dark:text-blue-300 mb-10 text-xl leading-relaxed">
                {{ searchQuery || hasActiveFilters 
                  ? 'Arama kriterlerinizi değiştirip tekrar deneyin. Farklı filtreler kullanarak daha geniş sonuçlar elde edebilirsiniz.' 
                  : 'İlk bilimsel etkinliğinizi oluşturarak akademik program yönetimine başlayın. Kongreler, sempozyumlar ve konferanslar için ideal bir platform.'
                }}
              </p>
              <div class="space-y-6">
                <Link
                  v-if="!searchQuery && !hasActiveFilters"
                  :href="safeRoute('admin.events.create', '/admin/events/create')"
                  class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-2xl hover:shadow-3xl transition-all duration-200 transform hover:scale-105"
                >
                  <PlusIcon class="h-7 w-7 mr-4" />
                  İlk Akademik Etkinliğinizi Oluşturun
                </Link>
                
                <div v-if="searchQuery || hasActiveFilters" class="flex justify-center space-x-6">
                  <button
                    @click="clearSearch"
                    class="inline-flex items-center px-8 py-4 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 border-2 border-red-300 dark:border-red-700 rounded-2xl hover:bg-red-50 dark:hover:bg-red-900 transition-all duration-200"
                  >
                    <XMarkIcon class="h-6 w-6 mr-3" />
                    Aramayı Temizle
                  </button>
                  <button
                    @click="clearFilters"
                    class="inline-flex items-center px-8 py-4 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 border-2 border-red-300 dark:border-red-700 rounded-2xl hover:bg-red-50 dark:hover:bg-red-900 transition-all duration-200"
                  >
                    <XMarkIcon class="h-6 w-6 mr-3" />
                    Filtreleri Temizle
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  PlusIcon,
  CalendarIcon,
  CalendarDaysIcon,
  EyeIcon,
  PencilIcon,
  BuildingOfficeIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  ClockIcon,
  PlayIcon,
  ArrowUpIcon,
  DocumentTextIcon,
  MapPinIcon
} from '@heroicons/vue/24/outline'

// Debounce function
const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Props with comprehensive defaults
const props = defineProps({
  events: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      meta: {
        current_page: 1,
        from: 0,
        last_page: 1,
        per_page: 15,
        to: 0,
        total: 0
      }
    })
  },
  organizations: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({
      search: '',
      organization_id: '',
      status: '',
      date_from: '',
      date_to: '',
      sort: 'start_date',
      direction: 'desc',
      per_page: 15
    })
  },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      published: 0,
      upcoming: 0,
      ongoing: 0
    })
  }
})

// State
const loading = ref(false)
const showDebug = ref(false)
const searchQuery = ref(props.filters?.search || '')
const currentQuickFilter = ref('all')

const activeFilters = ref({
  organization_id: props.filters?.organization_id || '',
  status: props.filters?.status || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || ''
})

// Helper function to safely create routes
const safeRoute = (routeName, fallback, param = null) => {
  try {
    if (typeof route === 'function') {
      if (param) {
        return route(routeName, param)
      }
      return route(routeName)
    }
  } catch (error) {
    console.warn(`Route ${routeName} not found, using fallback:`, fallback)
  }
  
  if (param && typeof fallback === 'string' && fallback.includes('${')) {
    return fallback.replace('${event.slug}', param)
  }
  return fallback
}

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: safeRoute('admin.dashboard', '/admin/dashboard') },
  { label: 'Bilimsel Etkinlikler', href: null }
])

const displayedEvents = computed(() => {
  if (!props.events?.data || !Array.isArray(props.events.data)) {
    return []
  }
  return props.events.data
})

const hasActiveFilters = computed(() => {
  return Object.values(activeFilters.value).some(value => value !== '' && value !== null) 
    || searchQuery.value !== ''
})

// Methods
const getStatusClasses = (status) => {
  const classes = {
    draft: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200',
    upcoming: 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-200',
    ongoing: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-200',
    completed: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200',
    published: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200'
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

// Search and filter methods
const handleSearchDebounced = debounce(() => {
  handleSearch()
}, 300)

const handleSearch = () => {
  updateUrl({ search: searchQuery.value, page: 1 })
}

const clearSearch = () => {
  searchQuery.value = ''
  handleSearch()
}

const quickFilter = (filter) => {
  currentQuickFilter.value = filter
  if (filter === 'all') {
    activeFilters.value.status = ''
  } else {
    activeFilters.value.status = filter
  }
  applyFilters()
}

const applyFilters = () => {
  updateUrl({ ...activeFilters.value, page: 1 })
}

const clearFilters = () => {
  activeFilters.value = {
    organization_id: '',
    status: '',
    date_from: '',
    date_to: ''
  }
  currentQuickFilter.value = 'all'
  searchQuery.value = ''
  applyFilters()
}

const goToPage = (page) => {
  if (page >= 1 && page <= props.events?.meta?.last_page) {
    updateUrl({ page })
  }
}

const updateUrl = (params) => {
  const currentParams = new URLSearchParams(window.location.search)
  const existingParams = Object.fromEntries(currentParams)
  
  router.get(safeRoute('admin.events.index', '/admin/events'), {
    ...existingParams,
    ...params,
    search: searchQuery.value || undefined
  }, {
    preserveState: true,
    preserveScroll: true,
    onStart: () => loading.value = true,
    onFinish: () => loading.value = false
  })
}

onMounted(() => {
  console.log('🎓 Corporate Event Management System Initialized')
  console.log('📊 System Status:', {
    totalEvents: props.events?.meta?.total || 0,
    organizations: props.organizations?.length || 0,
    currentPage: props.events?.meta?.current_page || 1
  })
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.hover-corporate:hover {
  transform: translateY(-3px);
}

.transition-corporate {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

input:focus,
select:focus {
  transform: translateY(-2px);
}

.shadow-3xl {
  box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}

@keyframes corporatePulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.animate-pulse {
  animation: corporatePulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>