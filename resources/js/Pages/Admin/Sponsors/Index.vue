<!-- Admin/Sponsors/Index.vue - Gray Theme -->
<template>
  <AdminLayout
    page-title="Sponsorlar"
    page-subtitle="Etkinlik sponsorlarını yönetin ve düzenleyin"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Sponsorlar" />

    <!-- Hero Section with Quick Stats - Gray Theme -->
    <div class="mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 text-white shadow-lg border border-gray-700">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <BuildingOfficeIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Toplam Sponsor</p>
              <p class="text-2xl font-bold">{{ enhancedStats.total }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl p-6 text-white shadow-lg border border-gray-600">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <CheckCircleIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Aktif</p>
              <p class="text-2xl font-bold">{{ enhancedStats.active }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl p-6 text-white shadow-lg border border-gray-500">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <PhotoIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Logolu</p>
              <p class="text-2xl font-bold">{{ enhancedStats.with_logo }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl p-6 text-white shadow-lg border border-gray-400">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <StarIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Ana Sponsor</p>
              <p class="text-2xl font-bold">{{ enhancedStats.main_sponsors }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modern Data Table -->
    <div class="bg-white dark:bg-gray-900 shadow-xl rounded-2xl overflow-hidden border border-gray-200/50 dark:border-gray-800/50">
      <!-- Enhanced Header -->
      <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 px-8 py-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
          <!-- Left: Title and Description -->
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
              Sponsor Yönetimi
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              Etkinlik sponsorlarını düzenleyin ve yönetin
            </p>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center space-x-3">
            <!-- Quick Filters -->
            <div class="flex items-center space-x-2">
              <button
                @click="quickFilter('all')"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  currentQuickFilter === 'all' 
                    ? 'bg-gray-800 text-white shadow-lg border border-gray-700' 
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Tümü
              </button>
              <button
                @click="quickFilter('main')"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  currentQuickFilter === 'main' 
                    ? 'bg-gray-700 text-white shadow-lg border border-gray-600' 
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Ana Sponsor
              </button>
              <button
                @click="quickFilter('active')"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  currentQuickFilter === 'active' 
                    ? 'bg-gray-600 text-white shadow-lg border border-gray-500' 
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Aktif
              </button>
            </div>

            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
              <button
                @click="viewMode = 'list'"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                  viewMode === 'list' 
                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                ]"
              >
                <ListBulletIcon class="h-4 w-4" />
              </button>
              <button
                @click="viewMode = 'grid'"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                  viewMode === 'grid' 
                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                ]"
              >
                <Squares2X2Icon class="h-4 w-4" />
              </button>
            </div>

            <!-- Create Button -->
            <Link
              :href="safeRoute('admin.sponsors.create', '/admin/sponsors/create')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 border border-gray-700"
            >
              <PlusIcon class="h-5 w-5 mr-2" />
              Yeni Sponsor
            </Link>
          </div>
        </div>

        <!-- Enhanced Search and Filters -->
        <div class="mt-6 flex flex-col lg:flex-row lg:items-center space-y-4 lg:space-y-0 lg:space-x-4">
          <!-- Search Bar -->
          <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
            </div>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Sponsor adı, türü veya website ile ara..."
              class="block w-full pl-12 pr-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent shadow-sm"
              @input="handleSearchDebounced"
            />
            <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-4 flex items-center">
              <button
                @click="clearSearch"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              >
                <XMarkIcon class="h-5 w-5" />
              </button>
            </div>
          </div>

          <!-- Advanced Filters -->
          <div class="flex items-center space-x-3">
            <!-- Event Filter -->
            <select
              v-model="activeFilters.event_id"
              @change="applyFilters"
              class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
            >
              <option value="">Tüm Etkinlikler</option>
              <option 
                v-for="event in events || []" 
                :key="event.id" 
                :value="event.id"
              >
                {{ event.name }}
              </option>
            </select>

            <!-- Sponsor Type Filter -->
            <select
              v-model="activeFilters.sponsor_type"
              @change="applyFilters"
              class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
            >
              <option value="">Tüm Türler</option>
              <option value="main">Ana Sponsor</option>
              <option value="gold">Altın Sponsor</option>
              <option value="silver">Gümüş Sponsor</option>
              <option value="bronze">Bronz Sponsor</option>
              <option value="supporter">Destekçi</option>
            </select>

            <!-- Status Filter -->
            <select
              v-model="activeFilters.is_active"
              @change="applyFilters"
              class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
            >
              <option value="">Tüm Durumlar</option>
              <option value="1">Aktif</option>
              <option value="0">Pasif</option>
            </select>

            <!-- Filter Reset -->
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="px-4 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200"
              title="Filtreleri Temizle"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-16">
        <div class="flex flex-col items-center space-y-4">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-600"></div>
          <p class="text-gray-600 dark:text-gray-400 font-medium">Yükleniyor...</p>
        </div>
      </div>

      <!-- List View -->
      <div v-else-if="viewMode === 'list' && displayedSponsors.length > 0" class="overflow-hidden">
        <!-- Bulk Actions -->
        <div 
          v-if="selectedSponsors.length > 0" 
          class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-8 py-4"
        >
          <div class="flex items-center justify-between">
            <p class="text-gray-800 dark:text-gray-200 font-medium">
              {{ selectedSponsors.length }} sponsor seçildi
            </p>
            <div class="flex items-center space-x-3">
              <button
                @click="bulkDuplicate"
                class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition-all duration-200"
              >
                Kopyala
              </button>
              <button
                @click="bulkDelete"
                class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium transition-all duration-200"
              >
                Sil
              </button>
            </div>
          </div>
        </div>

        <!-- Table View -->
        <div class="overflow-visible">
          <table class="w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
              <tr>
                <!-- Select All -->
                <th class="px-8 py-4 text-left w-12">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    :indeterminate="isIndeterminate"
                    @change="toggleSelectAll"
                    class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                  />
                </th>
                
                <!-- Sponsor Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200" @click="handleSort('name')">
                  <div class="flex items-center space-x-1">
                    <span>Sponsor</span>
                    <div class="flex flex-col">
                      <ChevronUpIcon class="h-3 w-3 transition-colors duration-200" :class="sortField === 'name' && sortDirection === 'asc' ? 'text-gray-600' : 'text-gray-300'" />
                      <ChevronDownIcon class="h-3 w-3 -mt-1 transition-colors duration-200" :class="sortField === 'name' && sortDirection === 'desc' ? 'text-gray-600' : 'text-gray-300'" />
                    </div>
                  </div>
                </th>
                
                <!-- Type Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                  Tür
                </th>
                
                <!-- Event Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">
                  Etkinlik
                </th>
                
                <!-- Status Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                  Durum
                </th>
                
                <!-- Sort Order Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200 w-24" @click="handleSort('sort_order')">
                  <div class="flex items-center space-x-1">
                    <span>Sıra</span>
                    <div class="flex flex-col">
                      <ChevronUpIcon class="h-3 w-3 transition-colors duration-200" :class="sortField === 'sort_order' && sortDirection === 'asc' ? 'text-gray-600' : 'text-gray-300'" />
                      <ChevronDownIcon class="h-3 w-3 -mt-1 transition-colors duration-200" :class="sortField === 'sort_order' && sortDirection === 'desc' ? 'text-gray-600' : 'text-gray-300'" />
                    </div>
                  </div>
                </th>
                
                <!-- Actions Header -->
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-60">
                  İşlemler
                </th>
              </tr>
            </thead>
            
           <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
              <tr 
                v-for="sponsor in displayedSponsors" 
                :key="sponsor.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200"
              >
                <!-- Checkbox -->
                <td class="px-8 py-6 w-12">
                  <input
                    type="checkbox"
                    :value="sponsor.id"
                    v-model="selectedSponsors"
                    class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                  />
                </td>

                <!-- Sponsor Info -->
                <td class="px-6 py-6">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                      <img
                        v-if="sponsor.logo_url"
                        :src="sponsor.logo_url"
                        :alt="sponsor.name"
                        class="h-12 w-12 rounded-xl object-cover border border-gray-200 dark:border-gray-600 shadow-sm"
                      />
                      <div
                        v-else
                        class="h-12 w-12 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center shadow-sm"
                      >
                        <BuildingOfficeIcon class="w-6 h-6 text-gray-400" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <Link
                        :href="safeRoute('admin.sponsors.show', `/admin/sponsors/${sponsor.id}`, sponsor.id)"
                        class="text-lg font-semibold text-gray-900 dark:text-white hover:text-gray-600 dark:hover:text-gray-400 transition-colors duration-200"
                      >
                        {{ sponsor.name }}
                      </Link>
                      <p v-if="sponsor.website" class="text-gray-600 dark:text-gray-400 mt-1 text-sm hover:underline">
                        <a :href="sponsor.website" target="_blank">
                          {{ sponsor.website }}
                        </a>
                      </p>
                      <div v-if="sponsor.description" class="text-gray-600 dark:text-gray-400 mt-1 text-sm line-clamp-1 max-w-md">
                        {{ sponsor.description }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Type -->
                <td class="px-6 py-6 w-32">
                  <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                    :class="getSponsorTypeClasses(sponsor.sponsor_type)"
                  >
                    <svg class="mr-2 h-2 w-2 fill-current" viewBox="0 0 8 8">
                      <circle cx="4" cy="4" r="3" />
                    </svg>
                    {{ getSponsorTypeLabel(sponsor.sponsor_type) }}
                  </span>
                </td>

                <!-- Event -->
                <td class="px-6 py-6 w-48">
                  <div class="text-sm">
                    <div v-if="sponsor.event" class="font-medium text-gray-900 dark:text-white">
                      {{ sponsor.event.name }}
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400">
                      Etkinlik belirtilmemiş
                    </div>
                    <div v-if="sponsor.event?.start_date" class="text-gray-500 dark:text-gray-400 flex items-center mt-1">
                      <CalendarIcon class="h-4 w-4 mr-1" />
                      {{ formatDate(sponsor.event.start_date) }}
                    </div>
                  </div>
                </td>

                <!-- Status -->
                <td class="px-6 py-6 w-32">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="sponsor.is_active 
                      ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                      : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                  >
                    <svg
                      :class="[
                        'mr-1.5 h-2 w-2',
                        sponsor.is_active ? 'text-green-400' : 'text-red-400'
                      ]"
                      fill="currentColor"
                      viewBox="0 0 8 8"
                    >
                      <circle cx="4" cy="4" r="3" />
                    </svg>
                    {{ sponsor.is_active ? 'Aktif' : 'Pasif' }}
                  </span>
                </td>

                <!-- Sort Order -->
                <td class="px-6 py-6 w-24">
                  <div class="text-sm font-medium text-gray-900 dark:text-white text-center">
                    {{ sponsor.sort_order || '-' }}
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-6 w-60">
                  <div class="flex flex-wrap items-center gap-2">
                    <!-- View -->
                    <Link
                      :href="safeRoute('admin.sponsors.show', `/admin/sponsors/${sponsor.id}`, sponsor.id)"
                      class="inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                      title="Görüntüle"
                    >
                      <EyeIcon class="h-3 w-3 mr-1" />
                      Görüntüle
                    </Link>

                    <!-- Edit -->
                    <Link
                      :href="safeRoute('admin.sponsors.edit', `/admin/sponsors/${sponsor.id}/edit`, sponsor.id)"
                      class="inline-flex items-center px-2 py-1 bg-gray-500 hover:bg-gray-600 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                      title="Düzenle"
                    >
                      <PencilIcon class="h-3 w-3 mr-1" />
                      Düzenle
                    </Link>

                    <!-- More Actions Dropdown -->
                    <div class="relative">
                      <button
                        @click="toggleActionsMenu(sponsor.id)"
                        class="inline-flex items-center px-2 py-1 bg-gray-800 hover:bg-gray-900 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                        title="Daha Fazla"
                      >
                        <EllipsisVerticalIcon class="h-3 w-3" />
                      </button>

                      <!-- Dropdown Menu -->
                      <div
                        v-if="showActionsMenu === sponsor.id"
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-50 border border-gray-200 dark:border-gray-700"
                      >
                        <div class="py-1">
                          <!-- Toggle Status -->
                          <button
                            @click="toggleStatus(sponsor)"
                            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                          >
                            <CheckCircleIcon v-if="!sponsor.is_active" class="h-4 w-4 mr-2" />
                            <XCircleIcon v-else class="h-4 w-4 mr-2" />
                            {{ sponsor.is_active ? 'Pasifleştir' : 'Aktifleştir' }}
                          </button>

                          <!-- Duplicate -->
                          <button
                            @click="duplicateSponsor(sponsor)"
                            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                          >
                            <DocumentDuplicateIcon class="h-4 w-4 mr-2" />
                            Kopyala
                          </button>

                          <!-- Delete -->
                          <button
                            v-if="sponsor.can_delete"
                            @click="deleteSponsor(sponsor)"
                            class="flex items-center w-full px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                          >
                            <TrashIcon class="h-4 w-4 mr-2" />
                            Sil
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Grid View -->
      <div v-else-if="viewMode === 'grid' && displayedSponsors.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
        <div
          v-for="sponsor in displayedSponsors"
          :key="sponsor.id"
          class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-gray-200 dark:border-gray-700"
        >
          <!-- Card Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 h-12 w-12">
                <img
                  v-if="sponsor.logo_url"
                  :src="sponsor.logo_url"
                  :alt="sponsor.name"
                  class="h-12 w-12 rounded-lg object-cover border border-gray-200 dark:border-gray-600"
                />
                <div
                  v-else
                  class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center"
                >
                  <BuildingOfficeIcon class="w-6 h-6 text-gray-400" />
                </div>
              </div>
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
                  {{ sponsor.name }}
                </h4>
                <p v-if="sponsor.event" class="text-sm text-gray-500 dark:text-gray-400">
                  {{ sponsor.event.name }}
                </p>
              </div>
            </div>
            
            <span
              class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
              :class="getSponsorTypeClasses(sponsor.sponsor_type)"
            >
              {{ getSponsorTypeLabel(sponsor.sponsor_type) }}
            </span>
          </div>

          <!-- Card Content -->
          <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center">
              <CheckCircleIcon class="h-4 w-4 mr-2" />
              {{ sponsor.is_active ? 'Aktif' : 'Pasif' }}
            </div>
            
            <div v-if="sponsor.website" class="flex items-center">
              <LinkIcon class="h-4 w-4 mr-2" />
              <a :href="sponsor.website" target="_blank" class="text-gray-600 hover:underline truncate">
                {{ sponsor.website }}
              </a>
            </div>

            <div v-if="sponsor.sort_order" class="flex items-center">
              <HashtagIcon class="h-4 w-4 mr-2" />
              Sıra: {{ sponsor.sort_order }}
            </div>
          </div>

          <!-- Description -->
          <div v-if="sponsor.description" class="mt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
              {{ sponsor.description }}
            </p>
          </div>

          <!-- Card Actions -->
          <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center space-x-2">
              <Link
                :href="safeRoute('admin.sponsors.show', `/admin/sponsors/${sponsor.id}`, sponsor.id)"
                class="p-2 text-gray-600 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                title="Görüntüle"
              >
                <EyeIcon class="h-4 w-4" />
              </Link>
              
              <Link
                v-if="sponsor.can_edit"
                :href="safeRoute('admin.sponsors.edit', `/admin/sponsors/${sponsor.id}/edit`, sponsor.id)"
                class="p-2 text-gray-600 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                title="Düzenle"
              >
                <PencilIcon class="h-4 w-4" />
              </Link>
            </div>
            
            <span class="text-xs text-gray-400">
              {{ formatDate(sponsor.created_at) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="py-16">
        <div class="text-center">
          <div class="mx-auto h-24 w-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 rounded-2xl flex items-center justify-center">
            <BuildingOfficeIcon class="h-12 w-12 text-gray-400" />
          </div>
          <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
            {{ searchQuery || hasActiveFilters ? 'Sonuç bulunamadı' : 'Henüz sponsor yok' }}
          </h3>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            {{ searchQuery || hasActiveFilters 
              ? 'Arama kriterlerinizi değiştirip tekrar deneyin.' 
              : 'İlk sponsoru oluşturmak için başlayın.'
            }}
          </p>
          <div class="mt-8">
            <Link
              v-if="!searchQuery && !hasActiveFilters"
              :href="safeRoute('admin.sponsors.create', '/admin/sponsors/create')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
            >
              <PlusIcon class="h-5 w-5 mr-2" />
              İlk Sponsoru Oluşturun
            </Link>
            <button
              v-else
              @click="clearAllFilters"
              class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold rounded-xl shadow-sm hover:shadow-md transition-all duration-200"
            >
              <XMarkIcon class="h-5 w-5 mr-2" />
              Filtreleri Temizle
            </button>
          </div>
        </div>
      </div>

      <!-- Enhanced Pagination -->
      <div v-if="sponsors?.last_page > 1" class="bg-gray-50 dark:bg-gray-800/50 px-8 py-6 border-t border-gray-200 dark:border-gray-800">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              <span class="font-medium">{{ sponsors.from || 0 }}</span>
              -
              <span class="font-medium">{{ sponsors.to || 0 }}</span>
              arası, toplam
              <span class="font-medium">{{ sponsors.total || 0 }}</span>
              sonuç
            </p>
            
            <select
              v-model="pageSize"
              @change="handlePageSizeChange"
              class="px-3 py-1 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-sm"
            >
              <option value="15">15</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>

          <nav class="flex items-center space-x-1">
            <!-- Previous -->
            <button
              @click="goToPage(sponsors.current_page - 1)"
              :disabled="sponsors.current_page <= 1"
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700"
            >
              Önceki
            </button>

            <!-- Page Numbers -->
            <div class="flex items-center space-x-1">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="page !== '...' ? goToPage(page) : null"
                :disabled="page === '...'"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                  page === sponsors.current_page
                    ? 'bg-gray-800 text-white shadow-lg'
                    : page === '...'
                    ? 'text-gray-500 cursor-default'
                    : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700'
                ]"
              >
                {{ page }}
              </button>
            </div>

            <!-- Next -->
            <button
              @click="goToPage(sponsors.current_page + 1)"
              :disabled="sponsors.current_page >= sponsors.last_page"
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700"
            >
              Sonraki
            </button>
          </nav>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  ListBulletIcon,
  Squares2X2Icon,
  CalendarIcon,
  BuildingOfficeIcon,
  EyeIcon,
  PencilIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  EllipsisVerticalIcon,
  CheckCircleIcon,
  XCircleIcon,
  PhotoIcon,
  StarIcon,
  LinkIcon,
  HashtagIcon
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

// Props
const props = defineProps({
  sponsors: {
    type: Object,
    default: () => ({ data: [], total: 0 })
  },
  events: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({})
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

// State
const loading = ref(false)
const showActionsMenu = ref(null)
const selectedSponsors = ref([])
const searchQuery = ref(props.filters.search || '')
const currentQuickFilter = ref('all')
const sortField = ref(props.filters.sort || 'sort_order')
const sortDirection = ref(props.filters.direction || 'asc')
const pageSize = ref(props.filters.per_page || 15)
const viewMode = ref('list')

const activeFilters = ref({
  event_id: props.filters.event_id || '',
  sponsor_type: props.filters.sponsor_type || '',
  is_active: props.filters.is_active || ''
})

const confirmDialog = ref({
  show: false,
  title: '',
  message: '',
  type: 'warning',
  callback: null
})

// Computed
const breadcrumbs = computed(() => [
  { label: 'Ana Sayfa', href: safeRoute('admin.dashboard', '/admin/dashboard') },
  { label: 'Sponsorlar', href: null }
])

const displayedSponsors = computed(() => props.sponsors?.data || [])

// Calculate stats dynamically if not in stats
const enhancedStats = computed(() => {
  const mainSponsorsCount = props.sponsors?.data?.filter(s => s.sponsor_type === 'main').length || 0
  
  return {
    total: props.stats?.total || props.sponsors?.total || 0,
    active: props.stats?.active || 0,
    with_logo: props.stats?.with_logo || 0,
    main_sponsors: mainSponsorsCount
  }
})

const hasActiveFilters = computed(() => {
  return Object.values(activeFilters.value).some(value => value !== '' && value !== null) 
    || searchQuery.value !== ''
})

const isAllSelected = computed(() => {
  return displayedSponsors.value.length > 0 && selectedSponsors.value.length === displayedSponsors.value.length
})

const isIndeterminate = computed(() => {
  return selectedSponsors.value.length > 0 && selectedSponsors.value.length < displayedSponsors.value.length
})

const visiblePages = computed(() => {
  if (!props.sponsors?.last_page) return []
  
  const current = props.sponsors.current_page
  const total = props.sponsors.last_page
  const delta = 2
  
  const range = []
  const rangeWithDots = []
  
  for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
    range.push(i)
  }
  
  if (current - delta > 2) {
    rangeWithDots.push(1, '...')
  } else {
    rangeWithDots.push(1)
  }
  
  rangeWithDots.push(...range)
  
  if (current + delta < total - 1) {
    rangeWithDots.push('...', total)
  } else {
    rangeWithDots.push(total)
  }
  
  return rangeWithDots.filter((item, index, array) => array.indexOf(item) === index)
})

// Methods
const getSponsorTypeClasses = (sponsorType) => {
  const classes = {
    main: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    gold: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    silver: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    bronze: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    supporter: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
  }
  return classes[sponsorType] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const getSponsorTypeLabel = (sponsorType) => {
  const labels = {
    main: 'Ana Sponsor',
    gold: 'Altın Sponsor',
    silver: 'Gümüş Sponsor',
    bronze: 'Bronz Sponsor',
    supporter: 'Destekçi'
  }
  return labels[sponsorType] || sponsorType || 'Belirtilmemiş'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Debounced search handler
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
    activeFilters.value.sponsor_type = ''
    activeFilters.value.is_active = ''
  } else if (filter === 'main') {
    activeFilters.value.sponsor_type = 'main'
    activeFilters.value.is_active = ''
  } else if (filter === 'active') {
    activeFilters.value.sponsor_type = ''
    activeFilters.value.is_active = '1'
  }
  applyFilters()
}

const applyFilters = () => {
  updateUrl({ ...activeFilters.value, page: 1 })
}

const clearFilters = () => {
  activeFilters.value = {
    event_id: '',
    sponsor_type: '',
    is_active: ''
  }
  currentQuickFilter.value = 'all'
  applyFilters()
}

const clearAllFilters = () => {
  searchQuery.value = ''
  clearFilters()
}

const handleSort = (column) => {
  if (sortField.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = column
    sortDirection.value = 'asc'
  }
  
  updateUrl({ 
    sort: sortField.value, 
    direction: sortDirection.value 
  })
}

const goToPage = (page) => {
  if (page >= 1 && page <= props.sponsors.last_page) {
    updateUrl({ page })
  }
}

const handlePageSizeChange = () => {
  updateUrl({ per_page: pageSize.value, page: 1 })
}

const updateUrl = (params) => {
  const currentParams = new URLSearchParams(window.location.search)
  const existingParams = Object.fromEntries(currentParams)
  
  router.get(safeRoute('admin.sponsors.index', '/admin/sponsors'), {
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

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedSponsors.value = []
  } else {
    selectedSponsors.value = displayedSponsors.value.map(sponsor => sponsor.id)
  }
}

const toggleActionsMenu = (sponsorId) => {
  showActionsMenu.value = showActionsMenu.value === sponsorId ? null : sponsorId
}

// Sponsor Actions
const duplicateSponsor = (sponsor) => {
  showActionsMenu.value = null
  router.post(safeRoute('admin.sponsors.duplicate', `/admin/sponsors/${sponsor.id}/duplicate`, sponsor.id), {}, {
    onError: () => {
      alert('Kopyalama sırasında bir hata oluştu.')
    }
  })
}

const deleteSponsor = (sponsor) => {
  showActionsMenu.value = null
  confirmDialog.value = {
    show: true,
    title: 'Sponsoru Sil',
    message: `"${sponsor.name}" sponsorunu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
    type: 'danger',
    callback: () => {
      router.delete(safeRoute('admin.sponsors.destroy', `/admin/sponsors/${sponsor.id}`, sponsor.id), {
        onSuccess: () => {
          confirmDialog.value.show = false
        },
        onError: () => {
          alert('Silme işlemi sırasında bir hata oluştu.')
        }
      })
    }
  }
}

const toggleStatus = (sponsor) => {
  showActionsMenu.value = null
  router.patch(safeRoute('admin.sponsors.toggle-status', `/admin/sponsors/${sponsor.id}/toggle-status`, sponsor.id), {}, {
    preserveScroll: true,
    onError: () => {
      alert('Durum değiştirme sırasında bir hata oluştu.')
    }
  })
}

// Bulk Actions
const bulkDuplicate = () => {
  router.post(safeRoute('admin.sponsors.bulk-duplicate', '/admin/sponsors/bulk-duplicate'), { 
    sponsor_ids: selectedSponsors.value 
  }, {
    onSuccess: () => {
      selectedSponsors.value = []
    },
    onError: () => {
      alert('Kopyalama işlemi sırasında bir hata oluştu.')
    }
  })
}

const bulkDelete = () => {
  confirmDialog.value = {
    show: true,
    title: 'Sponsorları Sil',
    message: `Seçili ${selectedSponsors.value.length} sponsoru silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
    type: 'danger',
    callback: () => {
      router.delete(safeRoute('admin.sponsors.bulk-destroy', '/admin/sponsors/bulk-destroy'), {
        data: { sponsor_ids: selectedSponsors.value },
        onSuccess: () => {
          confirmDialog.value.show = false
          selectedSponsors.value = []
        },
        onError: () => {
          alert('Silme işlemi sırasında bir hata oluştu.')
        }
      })
    }
  }
}

// Click outside handler
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showActionsMenu.value = null
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
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

/* Custom scrollbar */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-gray-800;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  @apply bg-gray-400 dark:bg-gray-500;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Focus styles */
input:focus, select:focus, button:focus {
  outline: none;
}

/* Custom checkbox indeterminate state */
input[type="checkbox"]:indeterminate {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M4 8h8v1H4z'/%3e%3c/svg%3e");
}
</style>