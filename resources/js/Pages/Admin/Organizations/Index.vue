<!-- Admin/Organizations/Index.vue - Gray Theme -->
<template>
    <AdminLayout
        page-title="Organizasyonlar"
        page-subtitle="Sistem organizasyonlarını yönetin ve düzenleyin"
        :breadcrumbs="breadcrumbs"
    >
        <Head title="Organizasyonlar" />

        <!-- Hero Section with Quick Stats - Gray Theme -->
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div
                    class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg p-4 text-white shadow border border-gray-700"
                >
                    <div class="flex items-center">
                        <div
                            class="p-2 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <BuildingOfficeIcon class="h-5 w-5" />
                        </div>
                        <div class="ml-3">
                            <p class="text-gray-300 text-xs">
                                Toplam Organizasyon
                            </p>
                            <p class="text-lg font-bold">
                                {{ enhancedStats.total }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-gray-700 to-gray-800 rounded-lg p-4 text-white shadow border border-gray-600"
                >
                    <div class="flex items-center">
                        <div
                            class="p-2 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <CheckCircleIcon class="h-5 w-5" />
                        </div>
                        <div class="ml-3">
                            <p class="text-gray-300 text-xs">Aktif</p>
                            <p class="text-lg font-bold">
                                {{ enhancedStats.active }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-lg p-4 text-white shadow border border-gray-500"
                >
                    <div class="flex items-center">
                        <div
                            class="p-2 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <CalendarIcon class="h-5 w-5" />
                        </div>
                        <div class="ml-3">
                            <p class="text-gray-300 text-xs">Etkinlikli</p>
                            <p class="text-lg font-bold">
                                {{ enhancedStats.withEvents }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg p-4 text-white shadow border border-gray-400"
                >
                    <div class="flex items-center">
                        <div
                            class="p-2 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <UserGroupIcon class="h-5 w-5" />
                        </div>
                        <div class="ml-3">
                            <p class="text-gray-300 text-xs">
                                Toplam Katılımcı
                            </p>
                            <p class="text-lg font-bold">
                                {{ enhancedStats.totalParticipants }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Data Table -->
        <div
            class="bg-white dark:bg-gray-900 shadow-lg rounded-lg overflow-hidden border border-gray-200/50 dark:border-gray-800/50"
        >
            <!-- Enhanced Header -->
            <div
                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 px-4 py-4 border-b border-gray-200 dark:border-gray-800"
            >
                <div
                    class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0"
                >
                    <!-- Left: Title and Description -->
                    <div>
                        <h2
                            class="text-lg font-bold text-gray-900 dark:text-white"
                        >
                            Organizasyon Yönetimi
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            Sistem organizasyonlarını düzenleyin ve yönetin
                        </p>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center space-x-2">
                        <!-- Quick Filters -->
                        <div class="flex items-center space-x-1">
                            <button
                                @click="quickFilter('all')"
                                :class="[
                                    'px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200',
                                    currentQuickFilter === 'all'
                                        ? 'bg-gray-800 text-white shadow border border-gray-700'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700',
                                ]"
                            >
                                Tümü
                            </button>
                            <button
                                @click="quickFilter('active')"
                                :class="[
                                    'px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200',
                                    currentQuickFilter === 'active'
                                        ? 'bg-gray-700 text-white shadow border border-gray-600'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700',
                                ]"
                            >
                                Aktif
                            </button>
                            <button
                                @click="quickFilter('with_events')"
                                :class="[
                                    'px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200',
                                    currentQuickFilter === 'with_events'
                                        ? 'bg-gray-600 text-white shadow border border-gray-500'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700',
                                ]"
                            >
                                Etkinlikli
                            </button>
                        </div>

                        <!-- View Toggle -->
                        <div
                            class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-md p-0.5"
                        >
                            <button
                                @click="viewMode = 'list'"
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded-sm transition-colors',
                                    viewMode === 'list'
                                        ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                                ]"
                            >
                                <ListBulletIcon class="h-3 w-3" />
                            </button>
                            <button
                                @click="viewMode = 'grid'"
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded-sm transition-colors',
                                    viewMode === 'grid'
                                        ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                                ]"
                            >
                                <Squares2X2Icon class="h-3 w-3" />
                            </button>
                        </div>

                        <!-- Create Button -->
                        <Link
                            :href="
                                safeRoute(
                                    'admin.organizations.create',
                                    '/admin/organizations/create'
                                )
                            "
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white text-sm font-medium rounded-lg shadow hover:shadow-lg transition-all duration-200 border border-gray-700"
                        >
                            <PlusIcon class="h-4 w-4 mr-1.5" />
                            Yeni Organizasyon
                        </Link>
                    </div>
                </div>

                <!-- Enhanced Search and Filters -->
                <div
                    class="mt-4 flex flex-col lg:flex-row lg:items-center space-y-3 lg:space-y-0 lg:space-x-3"
                >
                    <!-- Search Bar -->
                    <div class="flex-1 relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                        >
                            <MagnifyingGlassIcon
                                class="h-4 w-4 text-gray-400"
                            />
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Organizasyon adı, e-posta veya açıklama ile ara..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                            @input="handleSearchDebounced"
                        />
                        <div
                            v-if="searchQuery"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <button
                                @click="clearSearch"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                            >
                                <XMarkIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="flex items-center space-x-2">
                        <!-- Status Filter -->
                        <select
                            v-model="activeFilters.status"
                            @change="applyFilters"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            <option value="">Tüm Durumlar</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
                        </select>

                        <!-- Sort Filter -->
                        <select
                            v-model="activeFilters.sort"
                            @change="applyFilters"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            <option value="name">Ad (A-Z)</option>
                            <option value="created_at">Oluşturma Tarihi</option>
                            <option value="events_count">
                                Etkinlik Sayısı
                            </option>
                            <option value="participants_count">
                                Katılımcı Sayısı
                            </option>
                        </select>

                        <!-- Filter Reset -->
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="px-2 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200"
                            title="Filtreleri Temizle"
                        >
                            <XMarkIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-16">
                <div class="flex flex-col items-center space-y-4">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-600"
                    ></div>
                    <p class="text-gray-600 dark:text-gray-400 font-medium">
                        Yükleniyor...
                    </p>
                </div>
            </div>

            <!-- List View -->
            <div
                v-else-if="
                    viewMode === 'list' && displayedOrganizations.length > 0
                "
                class="overflow-hidden"
            >
                <!-- Bulk Actions -->
                <div
                    v-if="selectedOrganizations.length > 0"
                    class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-8 py-4"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-gray-800 dark:text-gray-200 font-medium">
                            {{ selectedOrganizations.length }} organizasyon
                            seçildi
                        </p>
                        <div class="flex items-center space-x-3">
                            <button
                                @click="bulkToggleStatus"
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition-all duration-200"
                            >
                                Durum Değiştir
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
                    <table
                        class="w-full divide-y divide-gray-200 dark:divide-gray-800"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <!-- Select All -->
                                <th class="px-4 py-3 text-left w-10">
                                    <input
                                        type="checkbox"
                                        :checked="isAllSelected"
                                        :indeterminate="isIndeterminate"
                                        @change="toggleSelectAll"
                                        class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                                    />
                                </th>

                                <!-- Organization Header -->
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200"
                                    @click="handleSort('name')"
                                >
                                    <div class="flex items-center space-x-1">
                                        <span>Organizasyon</span>
                                        <div class="flex flex-col">
                                            <ChevronUpIcon
                                                class="h-3 w-3 transition-colors duration-200"
                                                :class="
                                                    sortField === 'name' &&
                                                    sortDirection === 'asc'
                                                        ? 'text-gray-600'
                                                        : 'text-gray-300'
                                                "
                                            />
                                            <ChevronDownIcon
                                                class="h-3 w-3 -mt-1 transition-colors duration-200"
                                                :class="
                                                    sortField === 'name' &&
                                                    sortDirection === 'desc'
                                                        ? 'text-gray-600'
                                                        : 'text-gray-300'
                                                "
                                            />
                                        </div>
                                    </div>
                                </th>

                                <!-- Contact Header -->
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48"
                                >
                                    İletişim
                                </th>

                                <!-- Status Header -->
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24"
                                >
                                    Durum
                                </th>

                                <!-- Events Header -->
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200 w-32"
                                    @click="handleSort('events_count')"
                                >
                                    <div class="flex items-center space-x-1">
                                        <span>Etkinlikler</span>
                                        <div class="flex flex-col">
                                            <ChevronUpIcon
                                                class="h-3 w-3 transition-colors duration-200"
                                                :class="
                                                    sortField ===
                                                        'events_count' &&
                                                    sortDirection === 'asc'
                                                        ? 'text-gray-600'
                                                        : 'text-gray-300'
                                                "
                                            />
                                            <ChevronDownIcon
                                                class="h-3 w-3 -mt-1 transition-colors duration-200"
                                                :class="
                                                    sortField ===
                                                        'events_count' &&
                                                    sortDirection === 'desc'
                                                        ? 'text-gray-600'
                                                        : 'text-gray-300'
                                                "
                                            />
                                        </div>
                                    </div>
                                </th>

                                <!-- Participants Header -->
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200 w-32"
                                    @click="handleSort('participants_count')"
                                >
                                    <div class="flex items-center space-x-1">
                                        <span>Katılımcılar</span>
                                        <div class="flex flex-col">
                                            <ChevronUpIcon
                                                class="h-3 w-3 transition-colors duration-200"
                                                :class="
                                                    sortField ===
                                                        'participants_count' &&
                                                    sortDirection === 'asc'
                                                        ? 'text-gray-600'
                                                        : 'text-gray-300'
                                                "
                                            />
                                            <ChevronDownIcon
                                                class="h-3 w-3 -mt-1 transition-colors duration-200"
                                                :class="
                                                    sortField ===
                                                        'participants_count' &&
                                                    sortDirection === 'desc'
                                                        ? 'text-gray-600'
                                                        : 'text-gray-300'
                                                "
                                            />
                                        </div>
                                    </div>
                                </th>

                                <!-- Actions Header -->
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-60"
                                >
                                    İşlemler
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800"
                        >
                            <tr
                                v-for="organization in displayedOrganizations"
                                :key="organization.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200"
                            >
                                <!-- Checkbox -->
                                <td class="px-4 py-4 w-10">
                                    <input
                                        type="checkbox"
                                        :value="organization.id"
                                        v-model="selectedOrganizations"
                                        class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                                    />
                                </td>

                                <!-- Organization Info -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <img
                                                v-if="organization.logo_url"
                                                :src="organization.logo_url"
                                                :alt="organization.name"
                                                class="h-8 w-8 rounded-lg object-cover border border-gray-200 dark:border-gray-600"
                                            />
                                            <div
                                                v-else
                                                class="h-8 w-8 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center"
                                            >
                                                <BuildingOfficeIcon
                                                    class="w-4 h-4 text-gray-400"
                                                />
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <Link
                                                :href="
                                                    safeRoute(
                                                        'admin.organizations.show',
                                                        `/admin/organizations/${organization.id}`,
                                                        organization.id
                                                    )
                                                "
                                                class="text-sm font-semibold text-gray-900 dark:text-white hover:text-gray-600 dark:hover:text-gray-400 transition-colors duration-200"
                                            >
                                                {{ organization.name }}
                                            </Link>
                                            <p
                                                v-if="organization.description"
                                                class="text-gray-600 dark:text-gray-400 text-xs line-clamp-1 max-w-md"
                                            >
                                                {{ organization.description }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact -->
                                <td class="px-4 py-4 w-48">
                                    <div class="text-xs">
                                        <div
                                            v-if="organization.contact_email"
                                            class="flex items-center mb-1"
                                        >
                                            <EnvelopeIcon
                                                class="h-3 w-3 mr-1.5 text-gray-400"
                                            />
                                            <a
                                                :href="`mailto:${organization.contact_email}`"
                                                class="text-gray-600 dark:text-gray-400 hover:underline"
                                            >
                                                {{ organization.contact_email }}
                                            </a>
                                        </div>
                                        <div
                                            v-if="organization.contact_phone"
                                            class="flex items-center"
                                        >
                                            <PhoneIcon
                                                class="h-4 w-4 mr-2 text-gray-400"
                                            />
                                            <span
                                                class="text-gray-900 dark:text-white"
                                                >{{
                                                    organization.contact_phone
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-6 w-32">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="
                                            organization.is_active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                        "
                                    >
                                        <svg
                                            :class="[
                                                'mr-1.5 h-2 w-2',
                                                organization.is_active
                                                    ? 'text-green-400'
                                                    : 'text-red-400',
                                            ]"
                                            fill="currentColor"
                                            viewBox="0 0 8 8"
                                        >
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        {{
                                            organization.is_active
                                                ? "Aktif"
                                                : "Pasif"
                                        }}
                                    </span>
                                </td>

                                <!-- Events -->
                                <td class="px-6 py-6 w-32">
                                    <div class="text-center">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ organization.events_count || 0 }}
                                        </div>
                                        <div
                                            v-if="organization.active_events"
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                organization.active_events
                                            }}
                                            aktif
                                        </div>
                                    </div>
                                </td>

                                <!-- Participants -->
                                <td class="px-6 py-6 w-32">
                                    <div class="text-center">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{
                                                organization.participants_count ||
                                                0
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            katılımcı
                                        </div>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-6 w-60">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <!-- View -->
                                        <Link
                                            :href="
                                                safeRoute(
                                                    'admin.organizations.show',
                                                    `/admin/organizations/${organization.id}`,
                                                    organization.id
                                                )
                                            "
                                            class="inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                                            title="Görüntüle"
                                        >
                                            <EyeIcon class="h-3 w-3 mr-1" />
                                            Görüntüle
                                        </Link>

                                        <!-- Edit -->
                                        <Link
                                            v-if="organization.can_edit"
                                            :href="
                                                safeRoute(
                                                    'admin.organizations.edit',
                                                    `/admin/organizations/${organization.id}/edit`,
                                                    organization.id
                                                )
                                            "
                                            class="inline-flex items-center px-2 py-1 bg-gray-500 hover:bg-gray-600 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                                            title="Düzenle"
                                        >
                                            <PencilIcon class="h-3 w-3 mr-1" />
                                            Düzenle
                                        </Link>

                                        <!-- More Actions Dropdown -->
                                        <div class="relative">
                                            <button
                                                @click="
                                                    toggleActionsMenu(
                                                        organization.id
                                                    )
                                                "
                                                class="inline-flex items-center px-2 py-1 bg-gray-800 hover:bg-gray-900 text-white text-xs font-medium rounded shadow-sm transition-all duration-200"
                                                title="Daha Fazla"
                                            >
                                                <EllipsisVerticalIcon
                                                    class="h-3 w-3"
                                                />
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div
                                                v-if="
                                                    showActionsMenu ===
                                                    organization.id
                                                "
                                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-50 border border-gray-200 dark:border-gray-700"
                                            >
                                                <div class="py-1">
                                                    <!-- Toggle Status -->
                                                    <button
                                                        @click="
                                                            toggleStatus(
                                                                organization
                                                            )
                                                        "
                                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                    >
                                                        <CheckCircleIcon
                                                            v-if="
                                                                !organization.is_active
                                                            "
                                                            class="h-4 w-4 mr-2"
                                                        />
                                                        <XCircleIcon
                                                            v-else
                                                            class="h-4 w-4 mr-2"
                                                        />
                                                        {{
                                                            organization.is_active
                                                                ? "Pasifleştir"
                                                                : "Aktifleştir"
                                                        }}
                                                    </button>

                                                    <!-- Delete -->
                                                    <button
                                                        v-if="
                                                            organization.can_delete
                                                        "
                                                        @click="
                                                            deleteOrganization(
                                                                organization
                                                            )
                                                        "
                                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                                                    >
                                                        <TrashIcon
                                                            class="h-4 w-4 mr-2"
                                                        />
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
            <div
                v-else-if="
                    viewMode === 'grid' && displayedOrganizations.length > 0
                "
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6"
            >
                <div
                    v-for="organization in displayedOrganizations"
                    :key="organization.id"
                    class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-gray-200 dark:border-gray-700"
                >
                    <!-- Card Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 h-12 w-12">
                                <img
                                    v-if="organization.logo_url"
                                    :src="organization.logo_url"
                                    :alt="organization.name"
                                    class="h-12 w-12 rounded-lg object-cover border border-gray-200 dark:border-gray-600"
                                />
                                <div
                                    v-else
                                    class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center"
                                >
                                    <BuildingOfficeIcon
                                        class="w-6 h-6 text-gray-400"
                                    />
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4
                                    class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1"
                                >
                                    {{ organization.name }}
                                </h4>
                                <p
                                    v-if="organization.contact_email"
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ organization.contact_email }}
                                </p>
                            </div>
                        </div>

                        <span
                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                            :class="
                                organization.is_active
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                            "
                        >
                            {{ organization.is_active ? "Aktif" : "Pasif" }}
                        </span>
                    </div>

                    <!-- Card Content -->
                    <div
                        class="space-y-3 text-sm text-gray-600 dark:text-gray-400"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <CalendarIcon class="h-4 w-4 mr-2" />
                                <span>Etkinlikler</span>
                            </div>
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{{ organization.events_count || 0 }}</span
                            >
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <UserGroupIcon class="h-4 w-4 mr-2" />
                                <span>Katılımcılar</span>
                            </div>
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{{
                                    organization.participants_count || 0
                                }}</span
                            >
                        </div>

                        <div
                            v-if="organization.contact_phone"
                            class="flex items-center"
                        >
                            <PhoneIcon class="h-4 w-4 mr-2" />
                            <span
                                class="text-gray-600 dark:text-gray-400 truncate"
                            >
                                {{ organization.contact_phone }}
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="organization.description" class="mt-4">
                        <p
                            class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2"
                        >
                            {{ organization.description }}
                        </p>
                    </div>

                    <!-- Card Actions -->
                    <div
                        class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-600"
                    >
                        <div class="flex items-center space-x-2">
                            <Link
                                :href="
                                    safeRoute(
                                        'admin.organizations.show',
                                        `/admin/organizations/${organization.id}`,
                                        organization.id
                                    )
                                "
                                class="p-2 text-gray-600 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                title="Görüntüle"
                            >
                                <EyeIcon class="h-4 w-4" />
                            </Link>

                            <Link
                                v-if="organization.can_edit"
                                :href="
                                    safeRoute(
                                        'admin.organizations.edit',
                                        `/admin/organizations/${organization.id}/edit`,
                                        organization.id
                                    )
                                "
                                class="p-2 text-gray-600 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                title="Düzenle"
                            >
                                <PencilIcon class="h-4 w-4" />
                            </Link>
                        </div>

                        <span class="text-xs text-gray-400">
                            {{ formatDate(organization.created_at) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16">
                <div class="text-center">
                    <div
                        class="mx-auto h-24 w-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 rounded-2xl flex items-center justify-center"
                    >
                        <BuildingOfficeIcon class="h-12 w-12 text-gray-400" />
                    </div>
                    <h3
                        class="mt-6 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{
                            searchQuery || hasActiveFilters
                                ? "Sonuç bulunamadı"
                                : "Henüz organizasyon yok"
                        }}
                    </h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{
                            searchQuery || hasActiveFilters
                                ? "Arama kriterlerinizi değiştirip tekrar deneyin."
                                : "İlk organizasyonu oluşturmak için başlayın."
                        }}
                    </p>
                    <div class="mt-8">
                        <Link
                            v-if="!searchQuery && !hasActiveFilters"
                            :href="
                                safeRoute(
                                    'admin.organizations.create',
                                    '/admin/organizations/create'
                                )
                            "
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                        >
                            <PlusIcon class="h-5 w-5 mr-2" />
                            İlk Organizasyonu Oluşturun
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
            <div
                v-if="organizations?.last_page > 1"
                class="bg-gray-50 dark:bg-gray-800/50 px-4 py-4 border-t border-gray-200 dark:border-gray-800"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <p class="text-xs text-gray-700 dark:text-gray-300">
                            <span class="font-medium">{{
                                organizations.from || 0
                            }}</span>
                            -
                            <span class="font-medium">{{
                                organizations.to || 0
                            }}</span>
                            arası, toplam
                            <span class="font-medium">{{
                                organizations.total || 0
                            }}</span>
                            sonuç
                        </p>

                        <select
                            v-model="pageSize"
                            @change="handlePageSizeChange"
                            class="px-2 py-1 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-xs"
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
                            @click="goToPage(organizations.current_page - 1)"
                            :disabled="organizations.current_page <= 1"
                            class="px-2 py-1.5 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700"
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
                                    'px-2 py-1.5 text-xs font-medium rounded-md transition-all duration-200',
                                    page === organizations.current_page
                                        ? 'bg-gray-800 text-white shadow'
                                        : page === '...'
                                        ? 'text-gray-500 cursor-default'
                                        : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700',
                                ]"
                            >
                                {{ page }}
                            </button>
                        </div>

                        <!-- Next -->
                        <button
                            @click="goToPage(organizations.current_page + 1)"
                            :disabled="
                                organizations.current_page >=
                                organizations.last_page
                            "
                            class="px-2 py-1.5 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700"
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
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
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
    TrashIcon,
    EllipsisVerticalIcon,
    CheckCircleIcon,
    XCircleIcon,
    UserGroupIcon,
    EnvelopeIcon,
    PhoneIcon,
} from "@heroicons/vue/24/outline";

// Debounce function
const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Props
const props = defineProps({
    organizations: {
        type: Object,
        default: () => ({ data: [], total: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

// Helper function to safely create routes
const safeRoute = (routeName, fallback, param = null) => {
    try {
        if (param) {
            return route(routeName, param);
        }
        return route(routeName);
    } catch (error) {
        return fallback;
    }
};

// State
const loading = ref(false);
const showActionsMenu = ref(null);
const selectedOrganizations = ref([]);
const searchQuery = ref(props.filters.search || "");
const currentQuickFilter = ref("all");
const sortField = ref(props.filters.sort || "name");
const sortDirection = ref(props.filters.direction || "asc");
const pageSize = ref(props.filters.per_page || 15);
const viewMode = ref("list");

const activeFilters = ref({
    status: props.filters.status || "",
    sort: props.filters.sort || "name",
});

const confirmDialog = ref({
    show: false,
    title: "",
    message: "",
    type: "warning",
    callback: null,
});

// Computed
const breadcrumbs = computed(() => [
    {
        label: "Ana Sayfa",
        href: safeRoute("admin.dashboard", "/admin/dashboard"),
    },
    { label: "Organizasyonlar", href: null },
]);

const displayedOrganizations = computed(() => props.organizations?.data || []);

// Calculate stats dynamically
const enhancedStats = computed(() => {
    const data = displayedOrganizations.value;
    const totalParticipants = data.reduce(
        (sum, org) => sum + (org.participants_count || 0),
        0
    );

    return {
        total:
            props.stats?.total ||
            props.organizations?.total ||
            data.length ||
            0,
        active:
            props.stats?.active ||
            data.filter((org) => org.is_active).length ||
            0,
        withEvents:
            props.stats?.withEvents ||
            data.filter((org) => (org.events_count || 0) > 0).length ||
            0,
        totalParticipants: totalParticipants,
    };
});

const hasActiveFilters = computed(() => {
    return (
        Object.values(activeFilters.value).some(
            (value) => value !== "" && value !== null
        ) || searchQuery.value !== ""
    );
});

const isAllSelected = computed(() => {
    return (
        displayedOrganizations.value.length > 0 &&
        selectedOrganizations.value.length ===
            displayedOrganizations.value.length
    );
});

const isIndeterminate = computed(() => {
    return (
        selectedOrganizations.value.length > 0 &&
        selectedOrganizations.value.length < displayedOrganizations.value.length
    );
});

const visiblePages = computed(() => {
    if (!props.organizations?.last_page) return [];

    const current = props.organizations.current_page;
    const total = props.organizations.last_page;
    const delta = 2;

    const range = [];
    const rangeWithDots = [];

    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(total - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, "...");
    } else {
        rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (current + delta < total - 1) {
        rangeWithDots.push("...", total);
    } else {
        rangeWithDots.push(total);
    }

    return rangeWithDots.filter(
        (item, index, array) => array.indexOf(item) === index
    );
});

// Methods
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("tr-TR", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

// Debounced search handler
const handleSearchDebounced = debounce(() => {
    handleSearch();
}, 300);

const handleSearch = () => {
    updateUrl({ search: searchQuery.value, page: 1 });
};

const clearSearch = () => {
    searchQuery.value = "";
    handleSearch();
};

const quickFilter = (filter) => {
    currentQuickFilter.value = filter;
    if (filter === "all") {
        activeFilters.value.status = "";
    } else if (filter === "active") {
        activeFilters.value.status = "active";
    } else if (filter === "with_events") {
        // This would need backend support for filtering organizations with events
        activeFilters.value.status = "";
    }
    applyFilters();
};

const applyFilters = () => {
    updateUrl({ ...activeFilters.value, page: 1 });
};

const clearFilters = () => {
    activeFilters.value = {
        status: "",
        sort: "name",
    };
    currentQuickFilter.value = "all";
    applyFilters();
};

const clearAllFilters = () => {
    searchQuery.value = "";
    clearFilters();
};

const handleSort = (column) => {
    if (sortField.value === column) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = column;
        sortDirection.value = "asc";
    }

    updateUrl({
        sort: sortField.value,
        direction: sortDirection.value,
    });
};

const goToPage = (page) => {
    if (page >= 1 && page <= props.organizations.last_page) {
        updateUrl({ page });
    }
};

const handlePageSizeChange = () => {
    updateUrl({ per_page: pageSize.value, page: 1 });
};

const updateUrl = (params) => {
    const currentParams = new URLSearchParams(window.location.search);
    const existingParams = Object.fromEntries(currentParams);

    router.get(
        safeRoute("admin.organizations.index", "/admin/organizations"),
        {
            ...existingParams,
            ...params,
            search: searchQuery.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => (loading.value = true),
            onFinish: () => (loading.value = false),
        }
    );
};

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedOrganizations.value = [];
    } else {
        selectedOrganizations.value = displayedOrganizations.value.map(
            (organization) => organization.id
        );
    }
};

const toggleActionsMenu = (organizationId) => {
    showActionsMenu.value =
        showActionsMenu.value === organizationId ? null : organizationId;
};

// Organization Actions
const deleteOrganization = (organization) => {
    showActionsMenu.value = null;
    confirmDialog.value = {
        show: true,
        title: "Organizasyonu Sil",
        message: `"${organization.name}" organizasyonunu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
        type: "danger",
        callback: () => {
            router.delete(
                safeRoute(
                    "admin.organizations.destroy",
                    `/admin/organizations/${organization.id}`,
                    organization.id
                ),
                {
                    onSuccess: () => {
                        confirmDialog.value.show = false;
                    },
                    onError: () => {
                        alert("Silme işlemi sırasında bir hata oluştu.");
                    },
                }
            );
        },
    };
};

const toggleStatus = (organization) => {
    showActionsMenu.value = null;
    router.patch(
        safeRoute(
            "admin.organizations.toggle-status",
            `/admin/organizations/${organization.id}/toggle-status`,
            organization.id
        ),
        {},
        {
            preserveScroll: true,
            onError: () => {
                alert("Durum değiştirme sırasında bir hata oluştu.");
            },
        }
    );
};

// Bulk Actions
const bulkToggleStatus = () => {
    router.patch(
        safeRoute(
            "admin.organizations.bulk-toggle-status",
            "/admin/organizations/bulk-toggle-status"
        ),
        {
            organization_ids: selectedOrganizations.value,
        },
        {
            onSuccess: () => {
                selectedOrganizations.value = [];
            },
            onError: () => {
                alert("Durum değiştirme işlemi sırasında bir hata oluştu.");
            },
        }
    );
};

const bulkDelete = () => {
    confirmDialog.value = {
        show: true,
        title: "Organizasyonları Sil",
        message: `Seçili ${selectedOrganizations.value.length} organizasyonu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
        type: "danger",
        callback: () => {
            router.delete(
                safeRoute(
                    "admin.organizations.bulk-destroy",
                    "/admin/organizations/bulk-destroy"
                ),
                {
                    data: { organization_ids: selectedOrganizations.value },
                    onSuccess: () => {
                        confirmDialog.value.show = false;
                        selectedOrganizations.value = [];
                    },
                    onError: () => {
                        alert("Silme işlemi sırasında bir hata oluştu.");
                    },
                }
            );
        },
    };
};

// Click outside handler
const handleClickOutside = (event) => {
    if (!event.target.closest(".relative")) {
        showActionsMenu.value = null;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
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
input:focus,
select:focus,
button:focus {
    outline: none;
}

/* Custom checkbox indeterminate state */
input[type="checkbox"]:indeterminate {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M4 8h8v1H4z'/%3e%3c/svg%3e");
}
</style>
