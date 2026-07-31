<!-- Admin/ProgramSessions/Index.vue - Gray Theme Version -->
<template>
    <AdminLayout
        page-title="Program Oturumları"
        page-subtitle="Etkinlik program oturumlarını yönetin ve düzenleyin"
        :breadcrumbs="breadcrumbs"
    >
        <Head title="Program Oturumları" />

        <!-- Hero Section with Quick Stats - Gray Theme -->
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div
                    class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-4 text-white shadow border border-slate-700"
                >
                    <div class="flex items-center">
                        <div
                            class="p-2 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <SpeakerWaveIcon class="h-5 w-5" />
                        </div>
                        <div class="ml-3">
                            <p class="text-slate-300 text-xs">Toplam Oturum</p>
                            <p class="text-lg font-bold">
                                {{ stats.total || sessions?.total || 0 }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-lg p-4 text-white shadow border border-slate-600"
                >
                    <div class="flex items-center">
                        <div
                            class="p-2 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <DocumentTextIcon class="h-5 w-5" />
                        </div>
                        <div class="ml-3">
                            <p class="text-slate-300 text-xs">
                                Sunumlu Oturumlar
                            </p>
                            <p class="text-lg font-bold">
                                {{ stats.with_presentations || 0 }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-slate-600 to-slate-700 rounded-lg p-4 text-white shadow border border-slate-500"
                >
                    <div class="flex items-center">
                        <div
                            class="p-2 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <UsersIcon class="h-5 w-5" />
                        </div>
                        <div class="ml-3">
                            <p class="text-slate-300 text-xs">Moderatörlü</p>
                            <p class="text-lg font-bold">
                                {{ stats.with_moderators || 0 }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-slate-500 to-slate-600 rounded-lg p-4 text-white shadow border border-slate-400"
                >
                    <div class="flex items-center">
                        <div
                            class="p-2 bg-white/10 rounded-lg backdrop-blur-sm"
                        >
                            <ClockIcon class="h-5 w-5" />
                        </div>
                        <div class="ml-3">
                            <p class="text-slate-300 text-xs">
                                Bugünkü Oturumlar
                            </p>
                            <p class="text-lg font-bold">
                                {{ stats.today || 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Data Table -->
        <div
            class="bg-white dark:bg-slate-800 shadow-lg rounded-lg overflow-visible border border-slate-200/50 dark:border-slate-800/50"
        >
            <!-- Enhanced Header -->
            <div
                class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 px-4 py-4 border-b border-slate-200 dark:border-slate-800"
            >
                <div
                    class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0"
                >
                    <!-- Left: Title and Description -->
                    <div>
                        <h2
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Oturum Yönetimi
                        </h2>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">
                            Program oturumlarını düzenleyin ve yönetin
                        </p>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center space-x-2">
                        <!-- Quick Filters -->
                        <div class="flex items-center space-x-2">
                            <button
                                @click="quickFilter('all')"
                                :class="[
                                    'px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200',
                                    currentQuickFilter === 'all'
                                        ? 'bg-slate-800 text-white shadow-lg border border-slate-700'
                                        : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                ]"
                            >
                                Tümü
                            </button>
                            <button
                                @click="quickFilter('main')"
                                :class="[
                                    'px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200',
                                    currentQuickFilter === 'main'
                                        ? 'bg-slate-700 text-white shadow-lg border border-slate-600'
                                        : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                ]"
                            >
                                Ana Oturum
                            </button>
                            <button
                                @click="quickFilter('oral_presentation')"
                                :class="[
                                    'px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200',
                                    currentQuickFilter === 'oral_presentation'
                                        ? 'bg-slate-600 text-white shadow-lg border border-slate-500'
                                        : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                ]"
                            >
                                Sözlü Bildiri
                            </button>
                        </div>

                        <!-- View Toggle -->
                        <div
                            class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-md p-0.5"
                        >
                            <button
                                @click="viewMode = 'list'"
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded-sm transition-colors',
                                    viewMode === 'list'
                                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
                                ]"
                            >
                                <ListBulletIcon class="h-3 w-3" />
                            </button>
                            <button
                                @click="viewMode = 'grid'"
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded-sm transition-colors',
                                    viewMode === 'grid'
                                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
                                ]"
                            >
                                <Squares2X2Icon class="h-3 w-3" />
                            </button>
                        </div>

                        <!-- Create Button -->
                        <Link
                            :href="
                                safeRoute(
                                    'admin.program-sessions.create',
                                    '/admin/program-sessions/create',
                                )
                            "
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white text-sm font-medium rounded-lg shadow hover:shadow-lg transition-all duration-200 border border-slate-700"
                        >
                            <PlusIcon class="h-4 w-4 mr-1.5" />
                            Yeni Oturum
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
                                class="h-5 w-5 text-slate-400"
                            />
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Oturum başlığı, açıklama veya moderatör ile ara..."
                            class="block w-full pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent"
                            @input="handleSearchDebounced"
                        />
                        <div
                            v-if="searchQuery"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <button
                                @click="clearSearch"
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                            >
                                <XMarkIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="flex items-center space-x-2">
                        <!-- Event Filter -->
                        <select
                            v-model="activeFilters.event_id"
                            @change="handleEventFilterChange"
                            class="px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-slate-500"
                        >
                            <option value="">Tüm Etkinlikler</option>
                            <option
                                v-for="event in filterOptions?.events || []"
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
                            class="px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-slate-500"
                        >
                            <option value="">Tüm Günler</option>
                            <option
                                v-for="eventDay in filterOptions?.event_days || []"
                                :key="eventDay.id"
                                :value="eventDay.id"
                            >
                                {{ eventDay.name }}
                            </option>
                        </select>

                        <!-- Session Type Filter -->
                        <select
                            v-model="activeFilters.session_type"
                            @change="applyFilters"
                            class="px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 text-xs"
                        >
                            <option value="">Tüm Türler</option>
                            <option value="main">Ana Oturum</option>
                            <option value="satellite">Uydu Sempozyumu</option>
                            <option value="oral_presentation">
                                Sözlü Bildiri
                            </option>
                            <option value="special">Özel Oturum</option>
                            <option value="break">Ara</option>
                        </select>

                        <!-- Filter Reset -->
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="px-2 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 border border-slate-300 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200"
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
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-slate-600"
                    ></div>
                    <p class="text-slate-600 dark:text-slate-400 font-medium">
                        Yükleniyor...
                    </p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="displayedSessions.length === 0" class="py-16">
                <div class="text-center">
                    <div
                        class="mx-auto h-24 w-24 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 rounded-2xl flex items-center justify-center"
                    >
                        <SpeakerWaveIcon class="h-12 w-12 text-slate-400" />
                    </div>
                    <h3
                        class="mt-6 text-xl font-semibold text-slate-900 dark:text-white"
                    >
                        {{
                            searchQuery || hasActiveFilters
                                ? "Sonuç bulunamadı"
                                : "Henüz oturum yok"
                        }}
                    </h3>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">
                        {{
                            searchQuery || hasActiveFilters
                                ? "Arama kriterlerinizi değiştirip tekrar deneyin."
                                : "İlk program oturumunu oluşturmak için başlayın."
                        }}
                    </p>
                    <div class="mt-8">
                        <Link
                            v-if="!searchQuery && !hasActiveFilters"
                            :href="
                                safeRoute(
                                    'admin.program-sessions.create',
                                    '/admin/program-sessions/create',
                                )
                            "
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white text-sm font-medium rounded-lg shadow hover:shadow-lg transition-all duration-200"
                        >
                            <PlusIcon class="h-4 w-4 mr-1.5" />
                            İlk Oturumu Oluşturun
                        </Link>
                        <button
                            v-else
                            @click="clearAllFilters"
                            class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200"
                        >
                            <XMarkIcon class="h-5 w-5 mr-2" />
                            Filtreleri Temizle
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sessions Content -->
            <div v-else>
                <!-- Bulk Actions -->
                <div
                    v-if="selectedSessions.length > 0"
                    class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-4 py-2"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-slate-800 dark:text-slate-200 font-medium"
                        >
                            {{ selectedSessions.length }} oturum seçildi
                        </p>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="bulkDuplicate"
                                class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-sm font-medium transition-all duration-200"
                            >
                                Kopyala
                            </button>
                            <button
                                @click="bulkDelete"
                                class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-medium transition-all duration-200"
                            >
                                Sil
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sessions Table -->
                <div class="overflow-visible">
                    <table
                        class="w-full divide-y divide-slate-200 dark:divide-slate-800"
                    >
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <!-- Select All -->
                                <th class="px-3 py-2 text-left w-10">
                                    <input
                                        type="checkbox"
                                        :checked="isAllSelected"
                                        :indeterminate="isIndeterminate"
                                        @change="toggleSelectAll"
                                        class="h-3.5 w-3.5 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                    />
                                </th>

                                <!-- Session Header -->
                                <th
                                    class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                >
                                    <span>Oturum</span>
                                </th>

                                <!-- Type Header -->
                                <th
                                    class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-64"
                                >
                                    Tür
                                </th>

                                <!-- Time Header -->
                                <th
                                    class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-64"
                                >
                                    Zaman
                                </th>

                                <!-- Stats Header -->
                                <th
                                    class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-64"
                                >
                                    İstatistikler
                                </th>

                                <!-- Actions Header -->
                                <th
                                    class="px-3 py-2 text-center text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-"
                                >
                                    İşlemler
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-800"
                        >
                            <tr
                                v-for="session in displayedSessions"
                                :key="session.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all duration-200"
                            >
                                <!-- Checkbox -->
                                <td class="px-3 py-2 w-10">
                                    <input
                                        type="checkbox"
                                        :value="session.id"
                                        v-model="selectedSessions"
                                        class="h-3.5 w-3.5 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                    />
                                </td>

                                <!-- Session Info -->
                                <td class="px-3 py-2">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-gradient-to-br flex items-center justify-center"
                                                :class="
                                                    getSessionTypeGradient(
                                                        session.session_type,
                                                    )
                                                "
                                            >
                                                <component
                                                    :is="
                                                        getSessionIcon(
                                                            session.session_type,
                                                        )
                                                    "
                                                    class="h-4 w-4 text-white"
                                                />
                                            </div>
                                        </div>
                                        <div class="ml-2.5 min-w-0">
                                            <Link
                                                :href="
                                                    safeRoute(
                                                        'admin.program-sessions.show',
                                                        `/admin/program-sessions/${session.id}`,
                                                        session.id,
                                                    )
                                                "
                                                class="text-sm font-medium text-slate-900 dark:text-white hover:text-slate-600 dark:hover:text-slate-400 transition-colors duration-200"
                                            >
                                                {{
                                                    session.title ||
                                                    "Başlıksız Oturum"
                                                }}
                                            </Link>
                                            <p
                                                v-if="session.description"
                                                class="text-slate-500 dark:text-slate-400 text-xs line-clamp-1 max-w-xs"
                                            >
                                                {{ session.description }}
                                            </p>
                                            <div
                                                class="flex items-center mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                <BuildingOfficeIcon
                                                    class="h-3 w-3 mr-0.5 flex-shrink-0"
                                                />
                                                <span class="truncate">{{
                                                    session.venue
                                                        ?.display_name ||
                                                    session.venue?.name ||
                                                    "Salon belirtilmemiş"
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Type -->
                                <td class="px-3 py-2 w-28">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="
                                            getSessionTypeClasses(
                                                session.session_type,
                                            )
                                        "
                                    >
                                        <svg
                                            class="mr-2 h-2 w-2 fill-current"
                                            viewBox="0 0 8 8"
                                        >
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        {{
                                            session.session_type_display ||
                                            session.session_type
                                        }}
                                    </span>
                                </td>

                                <!-- Time -->
                                <td class="px-3 py-2 w-32">
                                    <div class="text-xs">
                                        <div
                                            class="font-medium text-slate-900 dark:text-white"
                                        >
                                            {{
                                                session.formatted_time_range ||
                                                "Zaman belirtilmemiş"
                                            }}
                                        </div>
                                        <div
                                            class="text-slate-500 dark:text-slate-400 flex items-center mt-0.5"
                                        >
                                            <ClockIcon class="h-3 w-3 mr-0.5" />
                                            {{ formatDuration(session) }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Stats -->
                                <td class="px-3 py-2 w-28">
                                    <div class="text-xs space-y-0.5">
                                        <div
                                            class="flex items-center text-slate-900 dark:text-white"
                                        >
                                            <DocumentTextIcon
                                                class="h-3 w-3 mr-0.5 text-slate-400"
                                            />
                                            <span class="font-medium">{{
                                                session.presentations_count || 0
                                            }}</span>
                                            <span
                                                class="text-slate-500 dark:text-slate-400 ml-0.5"
                                                >sunum</span
                                            >
                                        </div>
                                        <div
                                            class="flex items-center text-slate-900 dark:text-white"
                                        >
                                            <UsersIcon
                                                class="h-3 w-3 mr-0.5 text-slate-400"
                                            />
                                            <span class="font-medium">{{
                                                session.moderators_count || 0
                                            }}</span>
                                            <span
                                                class="text-slate-500 dark:text-slate-400 ml-0.5"
                                                >moderatör</span
                                            >
                                        </div>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-3 py-2 w-32">
                                    <div
                                        class="flex flex-wrap items-center gap-1"
                                    >
                                        <!-- View -->
                                        <div class="relative">
                                            <Link
                                                :href="
                                                    safeRoute(
                                                        'admin.program-sessions.show',
                                                        `/admin/program-sessions/${session.id}`,
                                                        session.id,
                                                    )
                                                "
                                                class="inline-flex items-center p-1 bg-slate-600 hover:bg-slate-700 text-white rounded shadow-sm transition-all duration-200"
                                                title="Görüntüle"
                                            >
                                                <EyeIcon class="h-3.5 w-3.5" />
                                            </Link>
                                        </div>

                                        <!-- Edit -->
                                        <div class="relative">
                                            <Link
                                                v-if="session.can_edit"
                                                :href="
                                                    safeRoute(
                                                        'admin.program-sessions.edit',
                                                        `/admin/program-sessions/${session.id}/edit`,
                                                        session.id,
                                                    )
                                                "
                                                class="inline-flex items-center p-1 bg-slate-500 hover:bg-slate-600 text-white rounded shadow-sm transition-all duration-200"
                                                title="Düzenle"
                                            >
                                                <PencilIcon
                                                    class="h-3.5 w-3.5"
                                                />
                                            </Link>
                                        </div>

                                        <!-- More Actions Dropdown -->
                                        <div class="relative">
                                            <button
                                                @click="
                                                    toggleActionsMenu(
                                                        session.id,
                                                    )
                                                "
                                                class="inline-flex items-center p-1 bg-slate-500 hover:bg-slate-600 text-white rounded shadow-sm transition-all duration-200"
                                                title="Daha Fazla"
                                            >
                                                <EllipsisVerticalIcon
                                                    class="h-3.5 w-3.5"
                                                />
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div
                                                v-if="
                                                    showActionsMenu ===
                                                    session.id
                                                "
                                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-[9999] border border-slate-200 dark:border-slate-700"
                                            >
                                                <div class="py-1">
                                                    <!-- Duplicate -->
                                                    <button
                                                        @click="
                                                            duplicateSession(
                                                                session,
                                                            )
                                                        "
                                                        class="flex items-center w-full px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700"
                                                    >
                                                        <DocumentDuplicateIcon
                                                            class="h-4 w-4 mr-2"
                                                        />
                                                        Kopyala
                                                    </button>

                                                    <!-- Delete -->
                                                    <button
                                                        v-if="
                                                            session.can_delete
                                                        "
                                                        @click="
                                                            deleteSession(
                                                                session,
                                                            )
                                                        "
                                                        class="flex items-center w-full px-3 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700"
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
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
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
    ClockIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    UsersIcon,
    EyeIcon,
    PencilIcon,
    DocumentDuplicateIcon,
    TrashIcon,
    SpeakerWaveIcon,
    PauseIcon,
    EllipsisVerticalIcon,
    MicrophoneIcon,
    AcademicCapIcon,
    CogIcon,
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
    sessions: {
        type: Object,
        default: () => ({ data: [], total: 0 }),
    },
    filter_options: {
        type: Object,
        default: () => ({}),
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
const selectedSessions = ref([]);

const normalizeFilterValue = (value) => {
    if (value === null || value === undefined) {
        return "";
    }

    return String(value);
};

const resolveQuickFilterFromSessionType = (sessionType) => {
    if (sessionType === "main" || sessionType === "oral_presentation") {
        return sessionType;
    }

    return "all";
};

const searchQuery = ref(props.filters.search || "");
const currentQuickFilter = ref(
    resolveQuickFilterFromSessionType(props.filters.session_type),
);
const sortField = ref(props.filters.sort || "start_time");
const sortDirection = ref(props.filters.direction || "asc");
const pageSize = ref(props.filters.per_page || 15);
const viewMode = ref("list");

const activeFilters = ref({
    event_id: normalizeFilterValue(props.filters.event_id),
    event_day_id: normalizeFilterValue(props.filters.event_day_id),
    session_type: props.filters.session_type || "",
    category_id: normalizeFilterValue(props.filters.category_id),
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

const syncFiltersFromProps = () => {
    searchQuery.value = props.filters.search || "";
    sortField.value = props.filters.sort || "start_time";
    sortDirection.value = props.filters.direction || "asc";
    activeFilters.value = {
        event_id: normalizeFilterValue(props.filters.event_id),
        event_day_id: normalizeFilterValue(props.filters.event_day_id),
        session_type: props.filters.session_type || "",
        category_id: normalizeFilterValue(props.filters.category_id),
        date_from: props.filters.date_from || "",
        date_to: props.filters.date_to || "",
    };
    currentQuickFilter.value = resolveQuickFilterFromSessionType(
        props.filters.session_type,
    );
};

watch(() => props.filters, syncFiltersFromProps, { deep: true });

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
    { label: "Program Oturumları", href: null },
]);

const displayedSessions = computed(() => props.sessions?.data || []);

const filterOptions = computed(() => props.filter_options || {});

const isAllSelected = computed(() => {
    return (
        displayedSessions.value.length > 0 &&
        selectedSessions.value.length === displayedSessions.value.length
    );
});

const isIndeterminate = computed(() => {
    return (
        selectedSessions.value.length > 0 &&
        selectedSessions.value.length < displayedSessions.value.length
    );
});

// Methods
const getSessionTypeClasses = (sessionType) => {
    const classes = {
        main: "bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-200",
        satellite:
            "bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-slate-200",
        oral_presentation:
            "bg-slate-300 text-slate-900 dark:bg-slate-700 dark:text-slate-200",
        special:
            "bg-slate-400 text-white dark:bg-slate-600 dark:text-slate-200",
        break: "bg-slate-500 text-white dark:bg-slate-500 dark:text-slate-100",
    };
    return (
        classes[sessionType] ||
        "bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300"
    );
};

const getSessionTypeGradient = (sessionType) => {
    const gradients = {
        main: "from-slate-500 to-slate-600",
        satellite: "from-slate-600 to-slate-700",
        oral_presentation: "from-slate-700 to-slate-800",
        special: "from-slate-800 to-slate-900",
        break: "from-slate-400 to-slate-500",
    };
    return gradients[sessionType] || "from-slate-500 to-slate-600";
};

const getSessionIcon = (sessionType) => {
    const icons = {
        main: MicrophoneIcon,
        satellite: AcademicCapIcon,
        oral_presentation: DocumentTextIcon,
        special: CogIcon,
        break: PauseIcon,
    };
    return icons[sessionType] || SpeakerWaveIcon;
};

const formatDuration = (session) => {
    // Backend'den formatted_duration geliyorsa onu kullan
    if (session.formatted_duration) {
        return session.formatted_duration;
    }

    // Eğer duration_in_minutes var ise onu kullan
    if (session.duration_in_minutes && session.duration_in_minutes > 0) {
        const minutes = session.duration_in_minutes;
        if (minutes < 60) return `${minutes} dk`;
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        return remainingMinutes > 0
            ? `${hours}s ${remainingMinutes}dk`
            : `${hours}s`;
    }

    // Manuel hesaplama için zaman formatını kontrol et
    if (!session.start_time || !session.end_time) return "Süre belirsiz";

    // Zaman formatını normalize et (Carbon timestamp vs HH:MM)
    const normalizeTime = (timeStr) => {
        if (!timeStr) return null;

        // Carbon timestamp formatında ise (2025-06-22T09:00:00.000000Z)
        if (timeStr.includes("T")) {
            const date = new Date(timeStr);
            return `${date.getHours().toString().padStart(2, "0")}:${date
                .getMinutes()
                .toString()
                .padStart(2, "0")}`;
        }

        // HH:MM formatında ise olduğu gibi döndür
        if (timeStr.match(/^\d{1,2}:\d{2}$/)) {
            return timeStr;
        }

        return timeStr;
    };

    const startTime = normalizeTime(session.start_time);
    const endTime = normalizeTime(session.end_time);

    if (!startTime || !endTime) return "Süre belirsiz";

    const start = new Date(`2000-01-01 ${startTime}`);
    const end = new Date(`2000-01-01 ${endTime}`);

    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
        return "Süre belirsiz";
    }

    const diff = (end - start) / (1000 * 60); // minutes

    if (diff <= 0) return "Süre belirsiz";
    if (diff < 60) return `${Math.round(diff)} dk`;

    const hours = Math.floor(diff / 60);
    const minutes = Math.round(diff % 60);
    return minutes > 0 ? `${hours}s ${minutes}dk` : `${hours}s`;
};

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedSessions.value = [];
    } else {
        selectedSessions.value = displayedSessions.value.map(
            (session) => session.id,
        );
    }
};

const toggleActionsMenu = (sessionId) => {
    showActionsMenu.value =
        showActionsMenu.value === sessionId ? null : sessionId;
};

// Session Actions
const duplicateSession = (session) => {
    showActionsMenu.value = null;
    router.post(
        safeRoute(
            "admin.program-sessions.duplicate",
            `/admin/program-sessions/${session.id}/duplicate`,
            session.id,
        ),
        {},
        {
            onError: () => {
                alert("Kopyalama sırasında bir hata oluştu.");
            },
        },
    );
};

const deleteSession = (session) => {
    showActionsMenu.value = null;
    confirmDialog.value = {
        show: true,
        title: "Oturumu Sil",
        message: `"${session.title}" oturumunu silmek istediğinize emin misiniz? Bu işlem geri alınamaz; oturumdaki tüm sunumlar silinecek ve kişi ilişkilendirmeleri kaldırılacaktır.`,
        type: "danger",
        callback: () => {
            router.delete(
                safeRoute(
                    "admin.program-sessions.destroy",
                    `/admin/program-sessions/${session.id}`,
                    session.id,
                ),
                {
                    onSuccess: () => {
                        confirmDialog.value.show = false;
                    },
                    onError: () => {
                        alert("Silme işlemi sırasında bir hata oluştu.");
                    },
                },
            );
        },
    };
};

const hasActiveFilters = computed(() => {
    return Object.values(activeFilters.value).some(
        (value) => value !== "" && value !== null,
    );
});

// Bulk Actions
const bulkDuplicate = () => {
    router.post(
        safeRoute(
            "admin.program-sessions.bulk-duplicate",
            "/admin/program-sessions/bulk-duplicate",
        ),
        {
            session_ids: selectedSessions.value,
        },
        {
            onSuccess: () => {
                selectedSessions.value = [];
            },
            onError: () => {
                alert("Kopyalama işlemi sırasında bir hata oluştu.");
            },
        },
    );
};

const bulkDelete = () => {
    confirmDialog.value = {
        show: true,
        title: "Oturumları Sil",
        message: `Seçili ${selectedSessions.value.length} oturumu silmek istediğinize emin misiniz? Bu işlem geri alınamaz; oturumlardaki tüm sunumlar silinecek ve kişi ilişkilendirmeleri kaldırılacaktır.`,
        type: "danger",
        callback: () => {
            router.delete(
                safeRoute(
                    "admin.program-sessions.bulk-destroy",
                    "/admin/program-sessions/bulk-destroy",
                ),
                {
                    data: { session_ids: selectedSessions.value },
                    onSuccess: () => {
                        confirmDialog.value.show = false;
                        selectedSessions.value = [];
                    },
                    onError: () => {
                        alert("Silme işlemi sırasında bir hata oluştu.");
                    },
                },
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

// Debounced search handler
const handleSearchDebounced = debounce(() => {
    handleSearch();
}, 300);

const handleSearch = () => {
    updateUrl({ page: 1 });
};

const clearSearch = () => {
    searchQuery.value = "";
    handleSearch();
};

const quickFilter = (filter) => {
    currentQuickFilter.value = filter;
    if (filter === "all") {
        activeFilters.value.session_type = "";
    } else {
        activeFilters.value.session_type = filter;
    }
    applyFilters();
};

const handleEventFilterChange = () => {
    activeFilters.value.event_day_id = "";
    applyFilters();
};

const applyFilters = () => {
    updateUrl({ page: 1 });
};

const clearFilters = () => {
    activeFilters.value = {
        event_id: "",
        event_day_id: "",
        session_type: "",
        category_id: "",
        date_from: "",
        date_to: "",
    };
    currentQuickFilter.value = "all";
    applyFilters();
};

const clearAllFilters = () => {
    searchQuery.value = "";
    clearFilters();
};

const buildQueryParams = (overrides = {}) => {
    const params = {
        search: searchQuery.value || undefined,
        event_id: activeFilters.value.event_id || undefined,
        event_day_id: activeFilters.value.event_day_id || undefined,
        session_type: activeFilters.value.session_type || undefined,
        category_id: activeFilters.value.category_id || undefined,
        sort: sortField.value || undefined,
        direction: sortDirection.value || undefined,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(params).filter(
            ([, value]) =>
                value !== "" && value !== null && value !== undefined,
        ),
    );
};

const updateUrl = (overrides = {}) => {
    router.get(
        safeRoute("admin.program-sessions.index", "/admin/program-sessions"),
        buildQueryParams(overrides),
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => (loading.value = true),
            onFinish: () => (loading.value = false),
        },
    );
};

// Lifecycle
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
