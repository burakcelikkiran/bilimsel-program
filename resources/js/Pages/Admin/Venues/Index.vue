<!-- Admin/Venues/Index.vue - Gray Theme -->
<template>
    <AdminLayout
        page-title="Salonlar"
        page-subtitle="Etkinlik salonlarını yönetin ve düzenleyin"
        :breadcrumbs="breadcrumbs"
    >
        <Head title="Salonlar" />

        <!-- Hero Section with Quick Stats - Gray Theme -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div
                    class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 text-white shadow-lg border border-gray-700"
                >
                    <div class="flex items-center">
                        <div
                            class="p-3 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <BuildingOfficeIcon class="h-8 w-8" />
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-300 text-sm">Toplam Salon</p>
                            <p class="text-2xl font-bold">
                                {{ enhancedStats.total }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl p-6 text-white shadow-lg border border-gray-600"
                >
                    <div class="flex items-center">
                        <div
                            class="p-3 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <UserGroupIcon class="h-8 w-8" />
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-300 text-sm">Toplam Kapasite</p>
                            <p class="text-2xl font-bold">
                                {{ enhancedStats.total_capacity || 0 }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl p-6 text-white shadow-lg border border-gray-500"
                >
                    <div class="flex items-center">
                        <div
                            class="p-3 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <DocumentTextIcon class="h-8 w-8" />
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-300 text-sm">Aktif Oturum</p>
                            <p class="text-2xl font-bold">
                                {{ enhancedStats.active_sessions || 0 }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl p-6 text-white shadow-lg border border-gray-400"
                >
                    <div class="flex items-center">
                        <div
                            class="p-3 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <CalendarIcon class="h-8 w-8" />
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-300 text-sm">Etkinlik Günü</p>
                            <p class="text-2xl font-bold">
                                {{ enhancedStats.event_days || 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Data Table -->
        <div
            class="bg-white dark:bg-gray-900 shadow-xl rounded-2xl overflow-hidden border border-gray-200/50 dark:border-gray-800/50"
        >
            <!-- Enhanced Header -->
            <div
                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 px-8 py-6 border-b border-gray-200 dark:border-gray-800"
            >
                <div
                    class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0"
                >
                    <!-- Left: Title and Description -->
                    <div>
                        <h2
                            class="text-2xl font-bold text-gray-900 dark:text-white"
                        >
                            Salon Yönetimi
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Etkinlik salonlarını düzenleyin ve yönetin
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
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700',
                                ]"
                            >
                                Tümü
                            </button>
                            <button
                                @click="quickFilter('large')"
                                :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                                    currentQuickFilter === 'large'
                                        ? 'bg-gray-700 text-white shadow-lg border border-gray-600'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700',
                                ]"
                            >
                                Büyük (200+)
                            </button>
                            <button
                                @click="quickFilter('has_sessions')"
                                :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                                    currentQuickFilter === 'has_sessions'
                                        ? 'bg-gray-600 text-white shadow-lg border border-gray-500'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700',
                                ]"
                            >
                                Oturumlular
                            </button>
                        </div>

                        <!-- View Toggle -->
                        <div
                            class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg p-1"
                        >
                            <button
                                @click="viewMode = 'list'"
                                :class="[
                                    'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                                    viewMode === 'list'
                                        ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
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
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                                ]"
                            >
                                <Squares2X2Icon class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Create Button -->
                        <Link
                            :href="
                                safeRoute(
                                    'admin.venues.create',
                                    '/admin/venues/create'
                                )
                            "
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 border border-gray-700"
                        >
                            <PlusIcon class="h-5 w-5 mr-2" />
                            Yeni Salon
                        </Link>
                    </div>
                </div>

                <!-- Enhanced Search and Filters -->
                <div
                    class="mt-6 flex flex-col lg:flex-row lg:items-center space-y-4 lg:space-y-0 lg:space-x-4"
                >
                    <!-- Search Bar -->
                    <div class="flex-1 relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                        >
                            <MagnifyingGlassIcon
                                class="h-5 w-5 text-gray-400"
                            />
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Salon adı, görünen ad veya kapasite ile ara..."
                            class="block w-full pl-12 pr-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent shadow-sm"
                            @input="handleSearchDebounced"
                        />
                        <div
                            v-if="searchQuery"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center"
                        >
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

                        <!-- Event Day Filter -->
                        <select
                            v-model="activeFilters.event_day_id"
                            @change="applyFilters"
                            class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
                        >
                            <option value="">Tüm Günler</option>
                            <template
                                v-for="group in groupedEventDays"
                                :key="group.event_id"
                            >
                                <optgroup :label="group.event_name">
                                    <option
                                        v-for="eventDay in group.options"
                                        :key="eventDay.id"
                                        :value="eventDay.id"
                                    >
                                        {{ eventDay.display_name }}
                                    </option>
                                </optgroup>
                            </template>
                        </select>

                        <!-- Capacity Filter -->
                        <select
                            v-model="activeFilters.capacity_range"
                            @change="applyFilters"
                            class="px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm"
                        >
                            <option value="">Tüm Kapasiteler</option>
                            <option value="small">Küçük (1-50)</option>
                            <option value="medium">Orta (51-200)</option>
                            <option value="large">Büyük (200+)</option>
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
                v-else-if="viewMode === 'list' && displayedVenues.length > 0"
                class="overflow-hidden"
            >
                <!-- Bulk Actions -->
                <div
                    v-if="selectedVenues.length > 0"
                    class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-8 py-4"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-gray-800 dark:text-gray-200 font-medium">
                            {{ selectedVenues.length }} salon seçildi
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
                    <table
                        class="w-full divide-y divide-gray-200 dark:divide-gray-800"
                    >
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

                                <!-- Venue Header -->
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200"
                                    @click="handleSort('name')"
                                >
                                    <div class="flex items-center space-x-1">
                                        <span>Salon</span>
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

                                <!-- Event Day Header -->
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48"
                                >
                                    Etkinlik Günü
                                </th>

                                <!-- Capacity Header -->
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200 w-32"
                                    @click="handleSort('capacity')"
                                >
                                    <div class="flex items-center space-x-1">
                                        <span>Kapasite</span>
                                        <div class="flex flex-col">
                                            <ChevronUpIcon
                                                class="h-3 w-3 transition-colors duration-200"
                                                :class="
                                                    sortField === 'capacity' &&
                                                    sortDirection === 'asc'
                                                        ? 'text-gray-600'
                                                        : 'text-gray-300'
                                                "
                                            />
                                            <ChevronDownIcon
                                                class="h-3 w-3 -mt-1 transition-colors duration-200"
                                                :class="
                                                    sortField === 'capacity' &&
                                                    sortDirection === 'desc'
                                                        ? 'text-gray-600'
                                                        : 'text-gray-300'
                                                "
                                            />
                                        </div>
                                    </div>
                                </th>

                                <!-- Sessions Header -->
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32"
                                >
                                    Oturumlar
                                </th>

                                <!-- Sort Order Header -->
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200 w-24"
                                    @click="handleSort('sort_order')"
                                >
                                    <div class="flex items-center space-x-1">
                                        <span>Sıra</span>
                                        <div class="flex flex-col">
                                            <ChevronUpIcon
                                                class="h-3 w-3 transition-colors duration-200"
                                                :class="
                                                    sortField ===
                                                        'sort_order' &&
                                                    sortDirection === 'asc'
                                                        ? 'text-gray-600'
                                                        : 'text-gray-300'
                                                "
                                            />
                                            <ChevronDownIcon
                                                class="h-3 w-3 -mt-1 transition-colors duration-200"
                                                :class="
                                                    sortField ===
                                                        'sort_order' &&
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
                                v-for="venue in displayedVenues"
                                :key="venue.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200"
                            >
                                <!-- Checkbox -->
                                <td class="px-8 py-6 w-12">
                                    <input
                                        type="checkbox"
                                        :value="venue.id"
                                        v-model="selectedVenues"
                                        class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                                    />
                                </td>

                                <!-- Venue Info -->
                                <td class="px-6 py-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div
                                                class="h-12 w-12 rounded-xl flex items-center justify-center shadow-sm border border-gray-200 dark:border-gray-600"
                                                :style="{
                                                    backgroundColor:
                                                        venue.color ||
                                                        '#3B82F6',
                                                }"
                                            >
                                                <BuildingOfficeIcon
                                                    class="w-6 h-6 text-white"
                                                />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <Link
                                                :href="
                                                    safeRoute(
                                                        'admin.venues.show',
                                                        `/admin/venues/${venue.id}`,
                                                        venue.id
                                                    )
                                                "
                                                class="text-lg font-semibold text-gray-900 dark:text-white hover:text-gray-600 dark:hover:text-gray-400 transition-colors duration-200"
                                            >
                                                {{
                                                    venue.display_name ||
                                                    venue.name
                                                }}
                                            </Link>
                                            <p
                                                v-if="
                                                    venue.display_name &&
                                                    venue.display_name !==
                                                        venue.name
                                                "
                                                class="text-gray-600 dark:text-gray-400 mt-1 text-sm"
                                            >
                                                Sistem Adı: {{ venue.name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Event Day -->
                                <td class="px-6 py-6 w-48">
                                    <div class="text-sm">
                                        <div
                                            v-if="venue.event_day"
                                            class="font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ venue.event_day.display_name }}
                                        </div>
                                        <div
                                            v-else
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            Etkinlik günü belirtilmemiş
                                        </div>
                                        <div
                                            v-if="venue.event_day?.event"
                                            class="text-gray-500 dark:text-gray-400 flex items-center mt-1"
                                        >
                                            <CalendarIcon
                                                class="h-4 w-4 mr-1"
                                            />
                                            {{ venue.event_day.event.name }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Capacity -->
                                <td class="px-6 py-6 w-32">
                                    <div class="text-sm">
                                        <div
                                            v-if="venue.capacity"
                                            class="font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ formatCapacity(venue.capacity) }}
                                        </div>
                                        <div
                                            v-else
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            Belirtilmemiş
                                        </div>
                                    </div>
                                </td>

                                <!-- Sessions -->
                                <td class="px-6 py-6 w-32">
                                    <div class="text-sm">
                                        <div
                                            class="font-medium text-gray-900 dark:text-white"
                                        >
                                            {{
                                                venue.program_sessions_count ||
                                                0
                                            }}
                                        </div>
                                        <div
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            oturum
                                        </div>
                                    </div>
                                </td>

                                <!-- Sort Order -->
                                <td class="px-6 py-6 w-24">
                                    <div
                                        class="text-sm font-medium text-gray-900 dark:text-white text-center"
                                    >
                                        {{ venue.sort_order || "-" }}
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
                                                    'admin.venues.show',
                                                    `/admin/venues/${venue.id}`,
                                                    venue.id
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
                                            :href="
                                                safeRoute(
                                                    'admin.venues.edit',
                                                    `/admin/venues/${venue.id}/edit`,
                                                    venue.id
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
                                                    toggleActionsMenu(venue.id)
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
                                                    showActionsMenu === venue.id
                                                "
                                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-50 border border-gray-200 dark:border-gray-700"
                                            >
                                                <div class="py-1">
                                                    <!-- Duplicate -->
                                                    <button
                                                        @click="
                                                            duplicateVenue(
                                                                venue
                                                            )
                                                        "
                                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                    >
                                                        <DocumentDuplicateIcon
                                                            class="h-4 w-4 mr-2"
                                                        />
                                                        Kopyala
                                                    </button>

                                                    <!-- Delete -->
                                                    <button
                                                        v-if="venue.can_delete"
                                                        @click="
                                                            deleteVenue(venue)
                                                        "
                                                        class="flex items-center w-full px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                                    >
                                                        <TrashIcon
                                                            class="h-4 w-4 mr-2"
                                                        />
                                                        Sil
                                                    </button>

                                                    <!-- Sessions Link -->
                                                    <Link
                                                        v-if="
                                                            venue.program_sessions_count >
                                                            0
                                                        "
                                                        :href="
                                                            safeRoute(
                                                                'admin.program-sessions.index',
                                                                `/admin/program-sessions?venue_id=${venue.id}`
                                                            )
                                                        "
                                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                    >
                                                        <DocumentTextIcon
                                                            class="h-4 w-4 mr-2"
                                                        />
                                                        Oturumları Görüntüle
                                                    </Link>
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
                v-else-if="viewMode === 'grid' && displayedVenues.length > 0"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6"
            >
                <div
                    v-for="venue in displayedVenues"
                    :key="venue.id"
                    class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-gray-200 dark:border-gray-700"
                >
                    <!-- Card Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div
                                class="flex-shrink-0 h-12 w-12 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-600"
                                :style="{
                                    backgroundColor: venue.color || '#3B82F6',
                                }"
                            >
                                <BuildingOfficeIcon
                                    class="w-6 h-6 text-white"
                                />
                            </div>
                            <div class="flex-1">
                                <h4
                                    class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1"
                                >
                                    {{ venue.display_name || venue.name }}
                                </h4>
                                <p
                                    v-if="venue.event_day"
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ venue.event_day.display_name }}
                                </p>
                            </div>
                        </div>

                        <span
                            v-if="venue.capacity"
                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                        >
                            {{ formatCapacity(venue.capacity) }}
                        </span>
                    </div>

                    <!-- Card Content -->
                    <div
                        class="space-y-3 text-sm text-gray-600 dark:text-gray-400"
                    >
                        <div
                            v-if="venue.event_day?.event"
                            class="flex items-center"
                        >
                            <CalendarIcon class="h-4 w-4 mr-2" />
                            {{ venue.event_day.event.name }}
                        </div>

                        <div class="flex items-center">
                            <DocumentTextIcon class="h-4 w-4 mr-2" />
                            {{ venue.program_sessions_count || 0 }} oturum
                        </div>

                        <div v-if="venue.sort_order" class="flex items-center">
                            <HashtagIcon class="h-4 w-4 mr-2" />
                            Sıra: {{ venue.sort_order }}
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div
                        class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-600"
                    >
                        <div class="flex items-center space-x-2">
                            <Link
                                :href="
                                    safeRoute(
                                        'admin.venues.show',
                                        `/admin/venues/${venue.id}`,
                                        venue.id
                                    )
                                "
                                class="p-2 text-gray-600 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                title="Görüntüle"
                            >
                                <EyeIcon class="h-4 w-4" />
                            </Link>

                            <Link
                                v-if="venue.can_edit"
                                :href="
                                    safeRoute(
                                        'admin.venues.edit',
                                        `/admin/venues/${venue.id}/edit`,
                                        venue.id
                                    )
                                "
                                class="p-2 text-gray-600 hover:text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                title="Düzenle"
                            >
                                <PencilIcon class="h-4 w-4" />
                            </Link>
                        </div>

                        <span class="text-xs text-gray-400">
                            {{ formatDate(venue.created_at) }}
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
                                : "Henüz salon yok"
                        }}
                    </h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{
                            searchQuery || hasActiveFilters
                                ? "Arama kriterlerinizi değiştirip tekrar deneyin."
                                : "İlk salonu oluşturmak için başlayın."
                        }}
                    </p>
                    <div class="mt-8">
                        <Link
                            v-if="!searchQuery && !hasActiveFilters"
                            :href="
                                safeRoute(
                                    'admin.venues.create',
                                    '/admin/venues/create'
                                )
                            "
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-800 to-gray-900 hover:from-gray-900 hover:to-black text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
                        >
                            <PlusIcon class="h-5 w-5 mr-2" />
                            İlk Salonu Oluşturun
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
                v-if="venues?.last_page > 1"
                class="bg-gray-50 dark:bg-gray-800/50 px-8 py-6 border-t border-gray-200 dark:border-gray-800"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">{{
                                venues.from || 0
                            }}</span>
                            -
                            <span class="font-medium">{{
                                venues.to || 0
                            }}</span>
                            arası, toplam
                            <span class="font-medium">{{
                                venues.total || 0
                            }}</span>
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
                            @click="goToPage(venues.current_page - 1)"
                            :disabled="venues.current_page <= 1"
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
                                    page === venues.current_page
                                        ? 'bg-gray-800 text-white shadow-lg'
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
                            @click="goToPage(venues.current_page + 1)"
                            :disabled="venues.current_page >= venues.last_page"
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

        <!-- Delete Modal -->
        <VenueDeleteModal
            :show="deleteModal.show"
            :venue="deleteModal.venue"
            :sessions="deleteModal.sessions"
            :sessionCount="deleteModal.sessionCount"
            :canCascadeDelete="deleteModal.canCascadeDelete"
            :loading="deleteModal.loading"
            @confirm="confirmDeleteVenue"
            @cancel="closeDeleteModal"
        />
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import VenueDeleteModal from "@/Components/VenueDeleteModal.vue";
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
    UserGroupIcon,
    DocumentTextIcon,
    HashtagIcon,
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
    venues: {
        type: Object,
        default: () => ({ data: [], total: 0 }),
    },
    events: {
        type: Array,
        default: () => [],
    },
    eventDays: {
        type: Array,
        default: () => [],
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
const selectedVenues = ref([]);
const searchQuery = ref(props.filters.search || "");
const currentQuickFilter = ref("all");
const sortField = ref(props.filters.sort || "sort_order");
const sortDirection = ref(props.filters.direction || "asc");
const pageSize = ref(props.filters.per_page || 15);
const viewMode = ref("list");

const activeFilters = ref({
    event_id: props.filters.event_id || "",
    event_day_id: props.filters.event_day_id || "",
    capacity_range: props.filters.capacity_range || "",
});

const confirmDialog = ref({
    show: false,
    title: "",
    message: "",
    type: "warning",
    callback: null,
});

const deleteModal = ref({
    show: false,
    venue: null,
    sessions: [],
    sessionCount: 0,
    canCascadeDelete: false,
    loading: false,
});

// Computed
const breadcrumbs = computed(() => [
    {
        label: "Ana Sayfa",
        href: safeRoute("admin.dashboard", "/admin/dashboard"),
    },
    { label: "Salonlar", href: null },
]);

const displayedVenues = computed(() => props.venues?.data || []);

// Group event days by event name for better UX
const groupedEventDays = computed(() => {
    if (!props.eventDays || props.eventDays.length === 0) return [];

    // Create a map to group by event
    const eventGroups = new Map();

    props.eventDays.forEach((eventDay) => {
        const eventName = eventDay.event?.name || "Etkinlik Belirtilmemiş";
        const eventId = eventDay.event?.id || "unknown";
        const groupKey = `${eventId}-${eventName}`;

        if (!eventGroups.has(groupKey)) {
            eventGroups.set(groupKey, {
                event_name: eventName,
                event_id: eventId,
                options: [],
            });
        }

        eventGroups.get(groupKey).options.push({
            id: eventDay.id,
            display_name: eventDay.display_name,
            date: eventDay.date,
            sort_order: eventDay.sort_order,
        });
    });

    // Convert to array and sort
    const result = Array.from(eventGroups.values()).map((group) => ({
        ...group,
        options: group.options.sort((a, b) => a.sort_order - b.sort_order),
    }));

    // Sort groups by event name
    return result.sort((a, b) => a.event_name.localeCompare(b.event_name));
});

// Calculate stats dynamically if not in stats
const enhancedStats = computed(() => {
    const venues = props.venues?.data || [];

    return {
        total: props.stats?.total || props.venues?.total || 0,
        total_capacity: venues.reduce(
            (sum, venue) => sum + (venue.capacity || 0),
            0
        ),
        active_sessions: venues.reduce(
            (sum, venue) => sum + (venue.program_sessions_count || 0),
            0
        ),
        event_days: new Set(venues.map((v) => v.event_day_id).filter(Boolean))
            .size,
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
        displayedVenues.value.length > 0 &&
        selectedVenues.value.length === displayedVenues.value.length
    );
});

const isIndeterminate = computed(() => {
    return (
        selectedVenues.value.length > 0 &&
        selectedVenues.value.length < displayedVenues.value.length
    );
});

const visiblePages = computed(() => {
    if (!props.venues?.last_page) return [];

    const current = props.venues.current_page;
    const total = props.venues.last_page;
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
const formatCapacity = (capacity) => {
    if (!capacity) return "Yok";
    return `${capacity.toLocaleString()} kişi`;
};

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
        activeFilters.value.capacity_range = "";
    } else if (filter === "large") {
        activeFilters.value.capacity_range = "large";
    } else if (filter === "has_sessions") {
        // This would require backend support for filtering venues with sessions
        activeFilters.value.has_sessions = "1";
    }
    applyFilters();
};

const applyFilters = () => {
    updateUrl({ ...activeFilters.value, page: 1 });
};

const clearFilters = () => {
    activeFilters.value = {
        event_id: "",
        event_day_id: "",
        capacity_range: "",
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
    if (page >= 1 && page <= props.venues.last_page) {
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
        safeRoute("admin.venues.index", "/admin/venues"),
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
        selectedVenues.value = [];
    } else {
        selectedVenues.value = displayedVenues.value.map((venue) => venue.id);
    }
};

const toggleActionsMenu = (venueId) => {
    showActionsMenu.value = showActionsMenu.value === venueId ? null : venueId;
};

// Venue Actions
const duplicateVenue = (venue) => {
    showActionsMenu.value = null;
    router.post(
        safeRoute(
            "admin.venues.duplicate",
            `/admin/venues/${venue.id}/duplicate`,
            venue.id
        ),
        {},
        {
            onError: () => {
                alert("Kopyalama sırasında bir hata oluştu.");
            },
        }
    );
};

const deleteVenue = async (venue) => {
    showActionsMenu.value = null;

    try {
        // Get venue delete preview data
        const response = await fetch(
            safeRoute(
                "admin.venues.delete-preview",
                `/admin/venues/${venue.id}/delete-preview`,
                venue.id
            ),
            {
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            }
        );

        if (!response.ok) {
            throw new Error("Failed to fetch venue data");
        }

        const data = await response.json();

        if (data.success) {
            // Show enhanced delete modal
            deleteModal.value = {
                show: true,
                venue: data.venue,
                sessions: data.sessions,
                sessionCount: data.session_count,
                canCascadeDelete: data.can_cascade_delete,
                loading: false,
            };
        } else {
            alert(
                data.message || "Salon bilgileri yüklenirken bir hata oluştu."
            );
        }
    } catch (error) {
        console.error("Delete preview error:", error);
        alert("Salon bilgileri yüklenirken bir hata oluştu.");
    }
};

// Delete Modal Actions
const confirmDeleteVenue = (options) => {
    deleteModal.value.loading = true;

    const params = {
        cascade_delete: options.cascadeDelete || false,
        confirm_cascade: options.confirmCascade || false,
    };

    router.delete(
        safeRoute(
            "admin.venues.destroy",
            `/admin/venues/${deleteModal.value.venue.id}`,
            deleteModal.value.venue.id
        ),
        {
            data: params,
            onSuccess: () => {
                deleteModal.value.show = false;
                deleteModal.value.loading = false;
            },
            onError: (errors) => {
                deleteModal.value.loading = false;
                const errorMessage =
                    errors?.message ||
                    Object.values(errors)[0] ||
                    "Silme işlemi sırasında bir hata oluştu.";
                alert(errorMessage);
            },
        }
    );
};

const closeDeleteModal = () => {
    deleteModal.value.show = false;
    deleteModal.value.loading = false;
};

// Bulk Actions
const bulkDuplicate = () => {
    router.post(
        safeRoute(
            "admin.venues.bulk-duplicate",
            "/admin/venues/bulk-duplicate"
        ),
        {
            venue_ids: selectedVenues.value,
        },
        {
            onSuccess: () => {
                selectedVenues.value = [];
            },
            onError: () => {
                alert("Kopyalama işlemi sırasında bir hata oluştu.");
            },
        }
    );
};

const bulkDelete = () => {
    confirmDialog.value = {
        show: true,
        title: "Salonları Sil",
        message: `Seçili ${selectedVenues.value.length} salonu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
        type: "danger",
        callback: () => {
            router.delete(
                safeRoute(
                    "admin.venues.bulk-destroy",
                    "/admin/venues/bulk-destroy"
                ),
                {
                    data: { venue_ids: selectedVenues.value },
                    onSuccess: () => {
                        confirmDialog.value.show = false;
                        selectedVenues.value = [];
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
