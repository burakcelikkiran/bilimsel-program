<!-- Admin/Participants/Index.vue - Modern Participants List View with Gray Theme -->
<template>
  <AdminLayout
    page-title="Katılımcılar"
    page-subtitle="Etkinlik katılımcılarını yönetin ve düzenleyin"
    :breadcrumbs="breadcrumbs"
  >
    <Head title="Katılımcılar" />

    <!-- Hero Section with Quick Stats - Gray Theme -->
    <div class="mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 text-white shadow-lg border border-gray-700">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <UsersIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Toplam Katılımcı</p>
              <p class="text-2xl font-bold">{{ stats.total || participants?.total || 0 }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl p-6 text-white shadow-lg border border-gray-600">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <MicrophoneIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Konuşmacılar</p>
              <p class="text-2xl font-bold">{{ stats.speakers || 0 }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl p-6 text-white shadow-lg border border-gray-500">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <UserGroupIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Moderatörler</p>
              <p class="text-2xl font-bold">{{ stats.moderators || 0 }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl p-6 text-white shadow-lg border border-gray-400">
          <div class="flex items-center">
            <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
              <BuildingOfficeIcon class="h-8 w-8" />
            </div>
            <div class="ml-4">
              <p class="text-gray-300 text-sm">Farklı Kurum</p>
              <p class="text-2xl font-bold">{{ stats.organizations || 0 }}</p>
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
              Katılımcı Yönetimi
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              Etkinlik katılımcılarını düzenleyin ve yönetin
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
                @click="quickFilter('speakers')"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  currentQuickFilter === 'speakers' 
                    ? 'bg-gray-700 text-white shadow-lg border border-gray-600' 
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Konuşmacılar
              </button>
              <button
                @click="quickFilter('moderators')"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  currentQuickFilter === 'moderators' 
                    ? 'bg-gray-600 text-white shadow-lg border border-gray-500' 
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Moderatörler
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
              :href="safeRoute('admin.participants.create', '/admin/participants/create')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 border border-gray-700"
            >
              <PlusIcon class="h-5 w-5 mr-2" />
              Yeni Katılımcı
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
              placeholder="Ad, soyad, e-posta veya kurum ile ara..."
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
            <!-- Organization Filter -->
            <select
              v-model="activeFilters.organization_id"
              @change="applyFilters"
              class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
            >
              <option value="">Tüm Organizasyonlar</option>
              <option 
                v-for="organization in filterOptions?.organizations || []" 
                :key="organization.id" 
                :value="organization.id"
              >
                {{ organization.name }}
              </option>
            </select>

            <!-- Role Filter -->
            <select
              v-model="activeFilters.type"
              @change="applyFilters"
              class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
            >
              <option value="">Tüm Roller</option>
              <option value="speakers">Konuşmacılar</option>
              <option value="moderators">Moderatörler</option>
              <option value="both">Her İkisi de</option>
            </select>

            <!-- Affiliation Filter -->
            <select
              v-model="activeFilters.affiliation"
              @change="applyFilters"
              class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
            >
              <option value="">Tüm Kurumlar</option>
              <option 
                v-for="affiliation in filterOptions?.affiliations || []" 
                :key="affiliation" 
                :value="affiliation"
              >
                {{ affiliation }}
              </option>
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
      <div v-else-if="viewMode === 'list' && displayedParticipants.length > 0" class="overflow-hidden">
        <!-- Bulk Actions -->
        <div 
          v-if="selectedParticipants.length > 0" 
          class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-8 py-4"
        >
          <div class="flex items-center justify-between">
            <p class="text-gray-800 dark:text-gray-200 font-medium">
              {{ selectedParticipants.length }} katılımcı seçildi
            </p>
            <div class="flex items-center space-x-3">
              <button
                @click="bulkExport"
                class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition-all duration-200"
              >
                Dışa Aktar
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
                
                <!-- Participant Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200" @click="handleSort('first_name')">
                  <div class="flex items-center space-x-1">
                    <span>Katılımcı</span>
                    <div class="flex flex-col">
                      <ChevronUpIcon class="h-3 w-3 transition-colors duration-200" :class="sortField === 'first_name' && sortDirection === 'asc' ? 'text-gray-600' : 'text-gray-300'" />
                      <ChevronDownIcon class="h-3 w-3 -mt-1 transition-colors duration-200" :class="sortField === 'first_name' && sortDirection === 'desc' ? 'text-gray-600' : 'text-gray-300'" />
                    </div>
                  </div>
                </th>
                
                <!-- Contact Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">
                  İletişim
                </th>
                
                <!-- Organization & Affiliation Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">
                  Organizasyon / Kurum
                </th>
                
                <!-- Roles Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                  Roller
                </th>
                
                <!-- Stats Header -->
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                  İstatistikler
                </th>
                
                <!-- Actions Header -->
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-60">
                  İşlemler
                </th>
              </tr>
            </thead>
            
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
              <tr 
                v-for="participant in displayedParticipants" 
                :key="participant.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200"
              >
                <!-- Checkbox -->
                <td class="px-8 py-6 w-12">
                  <input
                    type="checkbox"
                    :value="participant.id"
                    v-model="selectedParticipants"
                    class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                  />
                </td>

                <!-- Participant Info -->
                <td class="px-6 py-6">
                  <div class="flex items-start">
                    <div class="flex-shrink-0 h-12 w-12">
                      <div v-if="participant.photo_url" class="h-12 w-12 rounded-xl overflow-hidden shadow-md">
                        <img :src="participant.photo_url" :alt="participant.full_name" class="h-full w-full object-cover" />
                      </div>
                      <div v-else class="h-12 w-12 rounded-xl bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center shadow-md">
                        <span class="text-white font-semibold text-lg">{{ getInitials(participant.full_name) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <Link
                        :href="safeRoute('admin.participants.show', `/admin/participants/${participant.id}`, participant.id)"
                        class="text-lg font-semibold text-gray-900 dark:text-white hover:text-gray-600 dark:hover:text-gray-400 transition-colors duration-200"
                      >
                        {{ participant.full_name || 'İsimsiz Katılımcı' }}
                      </Link>
                      <p v-if="participant.title" class="text-gray-600 dark:text-gray-400 mt-1">
                        {{ participant.title }}
                      </p>
                      <div v-if="participant.bio" class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2 max-w-md">
                        {{ participant.bio }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Contact -->
                <td class="px-6 py-6 w-48">
                  <div class="text-sm space-y-1">
                    <div v-if="participant.email" class="flex items-center text-gray-900 dark:text-white">
                      <EnvelopeIcon class="h-4 w-4 mr-2 text-gray-400" />
                      <a :href="`mailto:${participant.email}`" class="hover:text-gray-600 dark:hover:text-gray-400 transition-colors">
                        {{ participant.email }}
                      </a>
                    </div>
                    <div v-if="participant.phone" class="flex items-center text-gray-900 dark:text-white">
                      <PhoneIcon class="h-4 w-4 mr-2 text-gray-400" />
                      <a :href="`tel:${participant.phone}`" class="hover:text-gray-600 dark:hover:text-gray-400 transition-colors">
                        {{ participant.phone }}
                      </a>
                    </div>
                  </div>
                </td>

                <!-- Organization & Affiliation -->
                <td class="px-6 py-6 w-48">
                  <div class="text-sm">
                    <div class="font-medium text-gray-900 dark:text-white">
                      {{ participant.organization?.name || 'Organizasyon yok' }}
                    </div>
                    <div v-if="participant.affiliation" class="text-gray-500 dark:text-gray-400 flex items-center mt-1">
                      <BuildingOfficeIcon class="h-4 w-4 mr-1" />
                      {{ participant.affiliation }}
                    </div>
                  </div>
                </td>

                <!-- Roles -->
                <td class="px-6 py-6 w-32">
                  <div class="flex flex-col space-y-1">
                    <span
                      v-if="participant.is_speaker"
                      class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                    >
                      <MicrophoneIcon class="w-3 h-3 mr-1" />
                      Konuşmacı
                    </span>
                    <span
                      v-if="participant.is_moderator"
                      class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-300 text-gray-900 dark:bg-gray-600 dark:text-gray-200"
                    >
                      <UserGroupIcon class="w-3 h-3 mr-1" />
                      Moderatör
                    </span>
                    <span
                      v-if="!participant.is_speaker && !participant.is_moderator"
                      class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                    >
                      <UserIcon class="w-3 h-3 mr-1" />
                      Katılımcı
                    </span>
                  </div>
                </td>

                <!-- Stats -->
                <td class="px-6 py-6 w-32">
                  <div class="text-sm space-y-1">
                    <div class="flex items-center text-gray-900 dark:text-white">
                      <DocumentTextIcon class="h-4 w-4 mr-1" />
                      <span class="font-semibold">{{ participant.presentations_count || 0 }}</span>
                      <span class="text-gray-500 dark:text-gray-400 ml-1">sunum</span>
                    </div>
                    <div class="flex items-center text-gray-900 dark:text-white">
                      <UserGroupIcon class="h-4 w-4 mr-1" />
                      <span class="font-semibold">{{ participant.moderated_sessions_count || 0 }}</span>
                      <span class="text-gray-500 dark:text-gray-400 ml-1">oturum</span>
                    </div>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-6 w-60">
                  <div class="flex flex-wrap items-center gap-2">
                    <!-- View -->
                    <Link
                      :href="safeRoute('admin.participants.show', `/admin/participants/${participant.id}`, participant.id)"
                      class="inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                      title="Görüntüle"
                    >
                      <EyeIcon class="h-3 w-3 mr-1" />
                      Görüntüle
                    </Link>

                    <!-- Edit -->
                    <Link
                      v-if="participant.can_edit"
                      :href="safeRoute('admin.participants.edit', `/admin/participants/${participant.id}/edit`, participant.id)"
                      class="inline-flex items-center px-2 py-1 bg-gray-500 hover:bg-gray-600 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                      title="Düzenle"
                    >
                      <PencilIcon class="h-3 w-3 mr-1" />
                      Düzenle
                    </Link>

                    <!-- More Actions Dropdown -->
                    <div class="relative">
                      <button
                        @click="toggleActionsMenu(participant.id)"
                        class="inline-flex items-center px-2 py-1 bg-gray-800 hover:bg-gray-900 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                        title="Daha Fazla"
                      >
                        <EllipsisVerticalIcon class="h-3 w-3" />
                      </button>

                      <!-- Dropdown Menu -->
                      <div
                        v-if="showActionsMenu === participant.id"
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-50 border border-gray-200 dark:border-gray-700"
                      >
                        <div class="py-1">
                          <!-- Duplicate -->
                          <button
                            @click="duplicateParticipant(participant)"
                            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                          >
                            <DocumentDuplicateIcon class="h-4 w-4 mr-2" />
                            Kopyala
                          </button>

                          <!-- Delete -->
                          <button
                            v-if="participant.can_delete"
                            @click="deleteParticipant(participant)"
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
      <div v-else-if="viewMode === 'grid' && displayedParticipants.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
        <div
          v-for="participant in displayedParticipants"
          :key="participant.id"
          class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-gray-200 dark:border-gray-700"
        >
          <!-- Card Header -->
          <div class="flex items-center space-x-3 mb-4">
            <div class="flex-shrink-0">
              <div v-if="participant.photo_url" class="h-12 w-12 rounded-lg overflow-hidden shadow-md">
                <img :src="participant.photo_url" :alt="participant.full_name" class="h-full w-full object-cover" />
              </div>
              <div v-else class="h-12 w-12 rounded-lg bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center shadow-md">
                <span class="text-white font-semibold text-lg">{{ getInitials(participant.full_name) }}</span>
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                {{ participant.full_name || 'İsimsiz Katılımcı' }}
              </h4>
              <p v-if="participant.title" class="text-sm text-gray-500 dark:text-gray-400 truncate">
                {{ participant.title }}
              </p>
            </div>
          </div>

          <!-- Roles -->
          <div class="flex flex-wrap gap-1 mb-3">
            <span
              v-if="participant.is_speaker"
              class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
            >
              <MicrophoneIcon class="w-3 h-3 mr-1" />
              Konuşmacı
            </span>
            <span
              v-if="participant.is_moderator"
              class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-300 text-gray-900 dark:bg-gray-600 dark:text-gray-200"
            >
              <UserGroupIcon class="w-3 h-3 mr-1" />
              Moderatör
            </span>
          </div>

          <!-- Card Content -->
          <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400 mb-4">
            <div v-if="participant.email" class="flex items-center">
              <EnvelopeIcon class="h-4 w-4 mr-2" />
              <span class="truncate">{{ participant.email }}</span>
            </div>
            
            <div v-if="participant.affiliation" class="flex items-center">
              <BuildingOfficeIcon class="h-4 w-4 mr-2" />
              <span class="truncate">{{ participant.affiliation }}</span>
            </div>

            <div class="flex items-center justify-between">
              <span class="flex items-center">
                <DocumentTextIcon class="h-4 w-4 mr-1" />
                {{ participant.presentations_count || 0 }} sunum
              </span>
              <span class="flex items-center">
                <UserGroupIcon class="h-4 w-4 mr-1" />
                {{ participant.moderated_sessions_count || 0 }} oturum
              </span>
            </div>
          </div>

          <!-- Card Actions -->
          <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center space-x-2">
              <Link
                :href="safeRoute('admin.participants.show', `/admin/participants/${participant.id}`, participant.id)"
                class="p-2 text-gray-600 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                title="Görüntüle"
              >
                <EyeIcon class="h-4 w-4" />
              </Link>
              
              <Link
                v-if="participant.can_edit"
                :href="safeRoute('admin.participants.edit', `/admin/participants/${participant.id}/edit`, participant.id)"
                class="p-2 text-gray-600 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                title="Düzenle"
              >
                <PencilIcon class="h-4 w-4" />
              </Link>
            </div>
            
            <span class="text-xs text-gray-400">
              {{ participant.organization?.name || 'Org. yok' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="py-16">
        <div class="text-center">
          <div class="mx-auto h-24 w-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 rounded-2xl flex items-center justify-center">
            <UsersIcon class="h-12 w-12 text-gray-400" />
          </div>
          <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
            {{ searchQuery || hasActiveFilters ? 'Sonuç bulunamadı' : 'Henüz katılımcı yok' }}
          </h3>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            {{ searchQuery || hasActiveFilters 
              ? 'Arama kriterlerinizi değiştirip tekrar deneyin.' 
              : 'İlk katılımcıyı oluşturmak için başlayın.'
            }}
          </p>
          <div class="mt-8">
            <Link
              v-if="!searchQuery && !hasActiveFilters"
              :href="safeRoute('admin.participants.create', '/admin/participants/create')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
            >
              <PlusIcon class="h-5 w-5 mr-2" />
              İlk Katılımcıyı Oluşturun
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
      <div v-if="participants?.last_page > 1" class="bg-gray-50 dark:bg-gray-800/50 px-8 py-6 border-t border-gray-200 dark:border-gray-800">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              <span class="font-medium">{{ participants.from || 0 }}</span>
              -
              <span class="font-medium">{{ participants.to || 0 }}</span>
              arası, toplam
              <span class="font-medium">{{ participants.total || 0 }}</span>
              sonuç
            </p>
            
            <select
              v-model="pageSize"
              @change="handlePageSizeChange"
              class="px-3 py-1 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-sm"
            >
              <option value="20">20</option>
              <option value="50">50</option>
              <option value="100">100</option>
              <option value="200">200</option>
            </select>
          </div>

          <nav class="flex items-center space-x-1">
            <!-- Previous -->
            <button
              @click="goToPage(participants.current_page - 1)"
              :disabled="participants.current_page <= 1"
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
                  page === participants.current_page
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
              @click="goToPage(participants.current_page + 1)"
              :disabled="participants.current_page >= participants.last_page"
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
  UsersIcon,
  UserIcon,
  UserGroupIcon,
  MicrophoneIcon,
  BuildingOfficeIcon,
  DocumentTextIcon,
  EnvelopeIcon,
  PhoneIcon,
  EyeIcon,
  PencilIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  EllipsisVerticalIcon
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
  participants: {
    type: Object,
    default: () => ({ data: [], total: 0 })
  },
  filter_options: {
    type: Object,
    default: () => ({})
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
const selectedParticipants = ref([])
const searchQuery = ref(props.filters.search || '')
const currentQuickFilter = ref('all')
const sortField = ref(props.filters.sort || 'first_name')
const sortDirection = ref(props.filters.direction || 'asc')
const pageSize = ref(props.filters.per_page || 20)
const viewMode = ref('list')

const activeFilters = ref({
  organization_id: props.filters.organization_id || '',
  type: props.filters.type || '',
  affiliation: props.filters.affiliation || ''
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
  { label: 'Katılımcılar', href: null }
])

const displayedParticipants = computed(() => props.participants?.data || [])

const filterOptions = computed(() => props.filter_options || {})

const hasActiveFilters = computed(() => {
  return Object.values(activeFilters.value).some(value => value !== '' && value !== null) 
    || searchQuery.value !== ''
})

const isAllSelected = computed(() => {
  return displayedParticipants.value.length > 0 && selectedParticipants.value.length === displayedParticipants.value.length
})

const isIndeterminate = computed(() => {
  return selectedParticipants.value.length > 0 && selectedParticipants.value.length < displayedParticipants.value.length
})

const visiblePages = computed(() => {
  if (!props.participants?.last_page) return []
  
  const current = props.participants.current_page
  const total = props.participants.last_page
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
const getInitials = (name) => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
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
    activeFilters.value.type = ''
  } else {
    activeFilters.value.type = filter
  }
  applyFilters()
}

const applyFilters = () => {
  updateUrl({ ...activeFilters.value, page: 1 })
}

const clearFilters = () => {
  activeFilters.value = {
    organization_id: '',
    type: '',
    affiliation: ''
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
  if (page >= 1 && page <= props.participants.last_page) {
    updateUrl({ page })
  }
}

const handlePageSizeChange = () => {
  updateUrl({ per_page: pageSize.value, page: 1 })
}

const updateUrl = (params) => {
  const currentParams = new URLSearchParams(window.location.search)
  const existingParams = Object.fromEntries(currentParams)
  
  router.get(safeRoute('admin.participants.index', '/admin/participants'), {
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
    selectedParticipants.value = []
  } else {
    selectedParticipants.value = displayedParticipants.value.map(participant => participant.id)
  }
}

const toggleActionsMenu = (participantId) => {
  showActionsMenu.value = showActionsMenu.value === participantId ? null : participantId
}

// Participant Actions
const duplicateParticipant = (participant) => {
  showActionsMenu.value = null
  router.post(safeRoute('admin.participants.duplicate', `/admin/participants/${participant.id}/duplicate`, participant.id), {}, {
    onError: () => {
      alert('Kopyalama sırasında bir hata oluştu.')
    }
  })
}

const deleteParticipant = (participant) => {
  showActionsMenu.value = null
  confirmDialog.value = {
    show: true,
    title: 'Katılımcıyı Sil',
    message: `"${participant.full_name}" katılımcısını silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
    type: 'danger',
    callback: () => {
      router.delete(safeRoute('admin.participants.destroy', `/admin/participants/${participant.id}`, participant.id), {
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

// Bulk Actions
const bulkExport = () => {
  router.post(safeRoute('admin.participants.bulk-export', '/admin/participants/bulk-export'), { 
    participant_ids: selectedParticipants.value 
  }, {
    onSuccess: () => {
      selectedParticipants.value = []
    },
    onError: () => {
      alert('Dışa aktarma işlemi sırasında bir hata oluştu.')
    }
  })
}

const bulkDelete = () => {
  confirmDialog.value = {
    show: true,
    title: 'Katılımcıları Sil',
    message: `Seçili ${selectedParticipants.value.length} katılımcıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
    type: 'danger',
    callback: () => {
      router.delete(safeRoute('admin.participants.bulk-destroy', '/admin/participants/bulk-destroy'), {
        data: { participant_ids: selectedParticipants.value },
        onSuccess: () => {
          confirmDialog.value.show = false
          selectedParticipants.value = []
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