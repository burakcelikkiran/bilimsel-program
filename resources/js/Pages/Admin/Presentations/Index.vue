<!-- Admin/Presentations/Index.vue - Modern Presentations List View -->
<template>
    <AdminLayout
        page-title="Sunumlar"
        page-subtitle="Etkinlik sunumlarını yönetin ve düzenleyin"
        :breadcrumbs="breadcrumbs"
        :full-width="true"
    >
        <Head title="Sunumlar" />

        <!-- Full Screen Container -->
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950 -m-6 p-6">
            <!-- Hero Section with Quick Stats -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div
                        class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg p-4 text-white shadow border border-slate-700"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-2 bg-white/10 rounded-lg backdrop-blur-sm"
                            >
                                <DocumentTextIcon class="h-5 w-5" />
                            </div>
                            <div class="ml-3">
                                <p class="text-slate-300 text-xs">
                                    Toplam Sunum
                                </p>
                                <p class="text-lg font-bold">
                                    {{ enhancedStats.total }}
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
                                <UserGroupIcon class="h-5 w-5" />
                            </div>
                            <div class="ml-3">
                                <p class="text-slate-300 text-xs">Konuşmacılı</p>
                                <p class="text-lg font-bold">
                                    {{ enhancedStats.with_speakers }}
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
                                <ExclamationTriangleIcon class="h-5 w-5" />
                            </div>
                            <div class="ml-3">
                                <p class="text-slate-300 text-xs">
                                    Konuşmacısız
                                </p>
                                <p class="text-lg font-bold">
                                    {{ enhancedStats.without_speakers }}
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
                                <StarIcon class="h-5 w-5" />
                            </div>
                            <div class="ml-3">
                                <p class="text-slate-300 text-xs">Keynote</p>
                                <p class="text-lg font-bold">
                                    {{ enhancedStats.keynote }}
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
                                Sunum Yönetimi
                            </h2>
                            <p class="text-slate-600 dark:text-slate-400 text-sm">
                                Etkinlik sunumlarını düzenleyin ve yönetin
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
                                    @click="quickFilter('keynote')"
                                    :class="[
                                        'px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200',
                                        currentQuickFilter === 'keynote'
                                            ? 'bg-slate-700 text-white shadow-lg border border-slate-600'
                                            : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Keynote
                                </button>
                                <button
                                    @click="quickFilter('oral')"
                                    :class="[
                                        'px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200',
                                        currentQuickFilter === 'oral'
                                            ? 'bg-slate-600 text-white shadow-lg border border-slate-500'
                                            : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Sözlü
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
                                        'admin.presentations.create',
                                        '/admin/presentations/create'
                                    )
                                "
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white text-sm font-medium rounded-lg shadow hover:shadow-lg transition-all duration-200 border border-slate-700"
                            >
                                <PlusIcon class="h-4 w-4 mr-1.5" />
                                Yeni Sunum
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
                                placeholder="Sunum başlığı, özet veya konuşmacı ile ara..."
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
                                @change="applyFilters"
                                class="px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-slate-500"
                            >
                                <option value="">Tüm Etkinlikler</option>
                                <option
                                    v-for="event in events || []"
                                    :key="event.id"
                                    :value="event.id"
                                >
                                    {{ event.title || event.name }}
                                </option>
                            </select>

                            <!-- Session Filter -->
                            <select
                                v-model="activeFilters.session_id"
                                @change="applyFilters"
                                class="px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-slate-500"
                            >
                                <option value="">Tüm Oturumlar</option>
                                <option
                                    v-for="session in filteredSessions"
                                    :key="session.id"
                                    :value="session.id"
                                >
                                    {{ session.title }}
                                </option>
                            </select>

                            <!-- Presentation Type Filter -->
                            <select
                                v-model="activeFilters.presentation_type"
                                @change="applyFilters"
                                class="px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-slate-500"
                            >
                                <option value="">Tüm Türler</option>
                                <option value="keynote">Keynote</option>
                                <option value="oral">Sözlü Bildiri</option>
                                <option value="poster">Poster</option>
                                <option value="panel">Panel</option>
                                <option value="workshop">Workshop</option>
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
                <div
                    v-if="loading"
                    class="flex items-center justify-center py-16"
                >
                    <div class="flex flex-col items-center space-y-4">
                        <div
                            class="animate-spin rounded-full h-12 w-12 border-b-2 border-slate-600"
                        ></div>
                        <p class="text-slate-600 dark:text-slate-400 font-medium">
                            Yükleniyor...
                        </p>
                    </div>
                </div>

                <!-- List View -->
                <div
                    v-else-if="
                        viewMode === 'list' && displayedPresentations.length > 0
                    "
                    class="overflow-visible"
                >
                    <!-- Bulk Actions -->
                    <div
                        v-if="selectedPresentations.length > 0"
                        class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-4 py-2"
                    >
                        <div class="flex items-center justify-between">
                            <p
                                class="text-slate-800 dark:text-slate-200 font-medium"
                            >
                                {{ selectedPresentations.length }} sunum seçildi
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

                    <!-- Table View -->
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

                                    <!-- Presentation Header -->
                                    <th
                                        class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-all duration-200"
                                        @click="handleSort('title')"
                                    >
                                        <div
                                            class="flex items-center space-x-1"
                                        >
                                            <span>Sunum</span>
                                            <div class="flex flex-col">
                                                <ChevronUpIcon
                                                    class="h-3 w-3 transition-colors duration-200"
                                                    :class="
                                                        sortField === 'title' &&
                                                        sortDirection === 'asc'
                                                            ? 'text-slate-600'
                                                            : 'text-slate-300'
                                                    "
                                                />
                                                <ChevronDownIcon
                                                    class="h-3 w-3 -mt-1 transition-colors duration-200"
                                                    :class="
                                                        sortField === 'title' &&
                                                        sortDirection === 'desc'
                                                            ? 'text-slate-600'
                                                            : 'text-slate-300'
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </th>

                                    <!-- Type Header -->
                                    <th
                                        class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-28"
                                    >
                                        Tür
                                    </th>

                                    <!-- Session Header -->
                                    <th
                                        class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-40"
                                    >
                                        Oturum
                                    </th>

                                    <!-- Speakers Header -->
                                    <th
                                        class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-40"
                                    >
                                        Konuşmacılar
                                    </th>

                                    <!-- Time Header -->
                                    <th
                                        class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-all duration-200 w-28"
                                        @click="handleSort('start_time')"
                                    >
                                        <div
                                            class="flex items-center space-x-1"
                                        >
                                            <span>Zaman</span>
                                            <div class="flex flex-col">
                                                <ChevronUpIcon
                                                    class="h-3 w-3 transition-colors duration-200"
                                                    :class="
                                                        sortField ===
                                                            'start_time' &&
                                                        sortDirection === 'asc'
                                                            ? 'text-slate-600'
                                                            : 'text-slate-300'
                                                    "
                                                />
                                                <ChevronDownIcon
                                                    class="h-3 w-3 -mt-1 transition-colors duration-200"
                                                    :class="
                                                        sortField ===
                                                            'start_time' &&
                                                        sortDirection === 'desc'
                                                            ? 'text-slate-600'
                                                            : 'text-slate-300'
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </th>

                                    <!-- Actions Header -->
                                    <th
                                        class="px-3 py-2 text-center text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32"
                                    >
                                        İşlemler
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="presentation in displayedPresentations"
                                    :key="presentation.id"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all duration-200"
                                >
                                    <!-- Checkbox -->
                                    <td class="px-3 py-2 w-10">
                                        <input
                                            type="checkbox"
                                            :value="presentation.id"
                                            v-model="selectedPresentations"
                                            class="h-3.5 w-3.5 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                        />
                                    </td>

                                    <!-- Presentation Info -->
                                    <td class="px-3 py-2">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-8 w-8"
                                            >
                                                <div
                                                    class="h-8 w-8 rounded-lg bg-gradient-to-br flex items-center justify-center"
                                                    :class="
                                                        getPresentationTypeGradient(
                                                            presentation.presentation_type
                                                        )
                                                    "
                                                >
                                                    <component
                                                        :is="
                                                            getPresentationIcon(
                                                                presentation.presentation_type
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
                                                            'admin.presentations.show',
                                                            `/admin/presentations/${presentation.id}`,
                                                            presentation.id
                                                        )
                                                    "
                                                    class="text-sm font-medium text-slate-900 dark:text-white hover:text-slate-600 dark:hover:text-slate-400 transition-colors duration-200"
                                                >
                                                    {{
                                                        presentation.title ||
                                                        "Başlıksız Sunum"
                                                    }}
                                                </Link>
                                                <p
                                                    v-if="presentation.abstract"
                                                    class="text-slate-500 dark:text-slate-400 text-xs line-clamp-1 max-w-xs"
                                                >
                                                    {{ presentation.abstract }}
                                                </p>
                                                <div
                                                    class="flex items-center mt-0.5 text-xs text-slate-500 dark:text-slate-400 gap-2"
                                                >
                                                    <span class="inline-flex items-center">
                                                        <ClockIcon
                                                            class="h-3 w-3 mr-0.5"
                                                        />
                                                        {{
                                                            formatDuration(
                                                                presentation
                                                            )
                                                        }}
                                                    </span>
                                                    <span
                                                        v-if="
                                                            presentation.language
                                                        "
                                                        class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[10px]"
                                                    >
                                                        {{
                                                            presentation.language?.toUpperCase()
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Type -->
                                    <td class="px-3 py-2 w-28">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="
                                                getPresentationTypeClasses(
                                                    presentation.presentation_type
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
                                                getPresentationTypeLabel(
                                                    presentation.presentation_type
                                                )
                                            }}
                                        </span>
                                    </td>

                                    <!-- Session -->
                                    <td class="px-3 py-2 w-40">
                                        <div class="text-xs">
                                            <div
                                                class="font-medium text-slate-900 dark:text-white line-clamp-1"
                                            >
                                                {{
                                                    presentation.program_session
                                                        ?.title ||
                                                    "Oturum belirtilmemiş"
                                                }}
                                            </div>
                                            <div
                                                class="text-slate-500 dark:text-slate-400 flex items-center mt-0.5"
                                            >
                                                <BuildingOfficeIcon
                                                    class="h-3 w-3 mr-0.5 flex-shrink-0"
                                                />
                                                <span class="truncate">{{
                                                    presentation.program_session
                                                        ?.venue?.display_name ||
                                                    "Salon belirtilmemiş"
                                                }}</span>
                                            </div>
                                            <div
                                                class="text-slate-500 dark:text-slate-400 flex items-center mt-0.5"
                                            >
                                                <CalendarIcon
                                                    class="h-3 w-3 mr-0.5 flex-shrink-0"
                                                />
                                                <span class="truncate">{{
                                                    formatEventInfo(
                                                        presentation
                                                    )
                                                }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Speakers -->
                                    <td class="px-3 py-2 w-40">
                                        <div>
                                            <div
                                                v-if="
                                                    presentation.speakers &&
                                                    presentation.speakers
                                                        .length > 0
                                                "
                                            >
                                                <div
                                                    v-for="speaker in presentation.speakers.slice(
                                                        0,
                                                        2
                                                    )"
                                                    :key="speaker.id"
                                                    class="flex items-center text-xs mb-0.5"
                                                >
                                                    <div
                                                        class="flex-shrink-0 h-5 w-5 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center mr-1"
                                                    >
                                                        <UserIcon
                                                            class="h-2.5 w-2.5 text-slate-600 dark:text-slate-300"
                                                        />
                                                    </div>
                                                    <span
                                                        class="text-slate-900 dark:text-white font-medium truncate"
                                                    >
                                                        {{
                                                            speaker.participant
                                                                ?.first_name
                                                        }}
                                                        {{
                                                            speaker.participant
                                                                ?.last_name
                                                        }}
                                                    </span>
                                                    <span
                                                        v-if="
                                                            speaker.pivot
                                                                ?.speaker_role
                                                        "
                                                        class="ml-1 px-1 py-0.5 rounded text-[10px] flex-shrink-0"
                                                        :class="
                                                            getSpeakerRoleClasses(
                                                                speaker.pivot
                                                                    ?.speaker_role
                                                            )
                                                        "
                                                    >
                                                        {{
                                                            getSpeakerRoleLabel(
                                                                speaker.pivot
                                                                    ?.speaker_role
                                                            )
                                                        }}
                                                    </span>
                                                </div>
                                                <div
                                                    v-if="
                                                        presentation.speakers
                                                            .length > 2
                                                    "
                                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                                >
                                                    +{{
                                                        presentation.speakers
                                                            .length - 2
                                                    }}
                                                    konuşmacı daha
                                                </div>
                                            </div>
                                            <div
                                                v-else
                                                class="text-xs text-slate-500 dark:text-slate-400 flex items-center"
                                            >
                                                <ExclamationTriangleIcon
                                                    class="h-3 w-3 mr-0.5 text-yellow-500"
                                                />
                                                Konuşmacı atanmamış
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Time -->
                                    <td class="px-3 py-2 w-28">
                                        <div class="text-xs">
                                            <div
                                                class="font-medium text-slate-900 dark:text-white"
                                            >
                                                {{
                                                    presentation.formatted_time_range ||
                                                    formatTimeRange(
                                                        presentation
                                                    ) ||
                                                    "Zaman belirtilmemiş"
                                                }}
                                            </div>
                                            <div
                                                class="text-slate-500 dark:text-slate-400"
                                            >
                                                {{
                                                    formatDuration(presentation)
                                                }}
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
                                                        'admin.presentations.show',
                                                        `/admin/presentations/${presentation.id}`,
                                                        presentation.id
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
                                                v-if="presentation.can_edit"
                                                :href="
                                                    safeRoute(
                                                        'admin.presentations.edit',
                                                        `/admin/presentations/${presentation.id}/edit`,
                                                        presentation.id
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
                                                            presentation.id
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
                                                        presentation.id
                                                    "
                                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-[9999] border border-slate-200 dark:border-slate-700"
                                                >
                                                    <div class="py-1">
                                                        <!-- Duplicate -->
                                                        <button
                                                            @click="
                                                                duplicatePresentation(
                                                                    presentation
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
                                                                presentation.can_delete
                                                            "
                                                            @click="
                                                                deletePresentation(
                                                                    presentation
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

                <!-- Grid View -->
                <div
                    v-else-if="
                        viewMode === 'grid' && displayedPresentations.length > 0
                    "
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6"
                >
                    <div
                        v-for="presentation in displayedPresentations"
                        :key="presentation.id"
                        class="bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-slate-200 dark:border-slate-700"
                    >
                        <!-- Card Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <div
                                    class="h-10 w-10 rounded-lg bg-gradient-to-br flex items-center justify-center shadow-md"
                                    :class="
                                        getPresentationTypeGradient(
                                            presentation.presentation_type
                                        )
                                    "
                                >
                                    <component
                                        :is="
                                            getPresentationIcon(
                                                presentation.presentation_type
                                            )
                                        "
                                        class="h-5 w-5 text-white"
                                    />
                                </div>
                                <div class="flex-1">
                                    <h4
                                        class="text-lg font-semibold text-slate-900 dark:text-white line-clamp-2"
                                    >
                                        {{
                                            presentation.title ||
                                            "Başlıksız Sunum"
                                        }}
                                    </h4>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{
                                            presentation.program_session
                                                ?.title ||
                                            "Oturum belirtilmemiş"
                                        }}
                                    </p>
                                </div>
                            </div>

                            <span
                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                                :class="
                                    getPresentationTypeClasses(
                                        presentation.presentation_type
                                    )
                                "
                            >
                                {{
                                    getPresentationTypeLabel(
                                        presentation.presentation_type
                                    )
                                }}
                            </span>
                        </div>

                        <!-- Card Content -->
                        <div
                            class="space-y-3 text-sm text-slate-600 dark:text-slate-400"
                        >
                            <div class="flex items-center">
                                <ClockIcon class="h-4 w-4 mr-2" />
                                {{
                                    presentation.formatted_time_range ||
                                    formatTimeRange(presentation) ||
                                    "Zaman belirtilmemiş"
                                }}
                            </div>

                            <div class="flex items-center">
                                <UserGroupIcon class="h-4 w-4 mr-2" />
                                {{ presentation.speakers_count || 0 }} konuşmacı
                            </div>

                            <div class="flex items-center">
                                <BuildingOfficeIcon class="h-4 w-4 mr-2" />
                                {{
                                    presentation.program_session?.venue
                                        ?.display_name || "Salon belirtilmemiş"
                                }}
                            </div>
                        </div>

                        <!-- Speakers -->
                        <div
                            v-if="
                                presentation.speakers &&
                                presentation.speakers.length > 0
                            "
                            class="mt-4"
                        >
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="speaker in presentation.speakers.slice(
                                        0,
                                        2
                                    )"
                                    :key="speaker.id"
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200"
                                >
                                    {{ speaker.participant?.first_name }}
                                    {{ speaker.participant?.last_name }}
                                </span>
                                <span
                                    v-if="presentation.speakers.length > 2"
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    +{{ presentation.speakers.length - 2 }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div
                            class="flex items-center justify-between mt-6 pt-4 border-t border-slate-200 dark:border-slate-600"
                        >
                            <div class="flex items-center space-x-2">
                                <Link
                                    :href="
                                        safeRoute(
                                            'admin.presentations.show',
                                            `/admin/presentations/${presentation.id}`,
                                            presentation.id
                                        )
                                    "
                                    class="p-2 text-slate-600 hover:text-slate-700 rounded-lg hover:bg-slate-50 transition-colors"
                                    title="Görüntüle"
                                >
                                    <EyeIcon class="h-4 w-4" />
                                </Link>

                                <Link
                                    v-if="presentation.can_edit"
                                    :href="
                                        safeRoute(
                                            'admin.presentations.edit',
                                            `/admin/presentations/${presentation.id}/edit`,
                                            presentation.id
                                        )
                                    "
                                    class="p-2 text-slate-600 hover:text-slate-700 rounded-lg hover:bg-slate-50 transition-colors"
                                    title="Düzenle"
                                >
                                    <PencilIcon class="h-4 w-4" />
                                </Link>
                            </div>

                            <span class="text-xs text-slate-400">
                                {{ formatEventInfo(presentation) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="py-16">
                    <div class="text-center">
                        <div
                            class="mx-auto h-24 w-24 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 rounded-2xl flex items-center justify-center"
                        >
                            <DocumentTextIcon class="h-12 w-12 text-slate-400" />
                        </div>
                        <h3
                            class="mt-6 text-xl font-semibold text-slate-900 dark:text-white"
                        >
                            {{
                                searchQuery || hasActiveFilters
                                    ? "Sonuç bulunamadı"
                                    : "Henüz sunum yok"
                            }}
                        </h3>
                        <p class="mt-2 text-slate-600 dark:text-slate-400">
                            {{
                                searchQuery || hasActiveFilters
                                    ? "Arama kriterlerinizi değiştirip tekrar deneyin."
                                    : "İlk sunumu oluşturmak için başlayın."
                            }}
                        </p>
                        <div class="mt-8">
                            <Link
                                v-if="!searchQuery && !hasActiveFilters"
                                :href="
                                    safeRoute(
                                        'admin.presentations.create',
                                        '/admin/presentations/create'
                                    )
                                "
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white text-sm font-medium rounded-lg shadow hover:shadow-lg transition-all duration-200"
                            >
                                <PlusIcon class="h-4 w-4 mr-1.5" />
                                İlk Sunumu Oluşturun
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

                <!-- Enhanced Pagination -->
                <div
                    v-if="presentations?.last_page > 1"
                    class="bg-slate-50 dark:bg-slate-800/50 px-4 py-3 border-t border-slate-200 dark:border-slate-800"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <p class="text-sm text-slate-700 dark:text-slate-300">
                                <span class="font-medium">{{
                                    presentations.from || 0
                                }}</span>
                                -
                                <span class="font-medium">{{
                                    presentations.to || 0
                                }}</span>
                                arası, toplam
                                <span class="font-medium">{{
                                    presentations.total || 0
                                }}</span>
                                sonuç
                            </p>

                            <select
                                v-model="pageSize"
                                @change="handlePageSizeChange"
                                class="px-3 py-1 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm"
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
                                @click="
                                    goToPage(presentations.current_page - 1)
                                "
                                :disabled="presentations.current_page <= 1"
                                class="px-3 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700"
                            >
                                Önceki
                            </button>

                            <!-- Page Numbers -->
                            <div class="flex items-center space-x-1">
                                <button
                                    v-for="page in visiblePages"
                                    :key="page"
                                    @click="
                                        page !== '...' ? goToPage(page) : null
                                    "
                                    :disabled="page === '...'"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                                        page === presentations.current_page
                                            ? 'bg-slate-800 text-white shadow-lg'
                                            : page === '...'
                                            ? 'text-slate-500 cursor-default'
                                            : 'text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    {{ page }}
                                </button>
                            </div>

                            <!-- Next -->
                            <button
                                @click="
                                    goToPage(presentations.current_page + 1)
                                "
                                :disabled="
                                    presentations.current_page >=
                                    presentations.last_page
                                "
                                class="px-3 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700"
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
        </div>
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
    ClockIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    UserGroupIcon,
    EyeIcon,
    PencilIcon,
    DocumentDuplicateIcon,
    TrashIcon,
    EllipsisVerticalIcon,
    UserIcon,
    ExclamationTriangleIcon,
    StarIcon,
    MicrophoneIcon,
    AcademicCapIcon,
    CogIcon,
    PresentationChartLineIcon,
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
    presentations: {
        type: Object,
        default: () => ({ data: [], total: 0 }),
    },
    events: {
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
const selectedPresentations = ref([]);
const searchQuery = ref(props.filters.search || "");
const currentQuickFilter = ref("all");
const sortField = ref(props.filters.sort || "sort_order");
const sortDirection = ref(props.filters.direction || "asc");
const pageSize = ref(props.filters.per_page || 15);
const viewMode = ref("list");

const activeFilters = ref({
    event_id: props.filters.event_id || "",
    session_id: props.filters.session_id || "",
    speaker_id: props.filters.speaker_id || "",
    presentation_type: props.filters.presentation_type || "",
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
    { label: "Sunumlar", href: null },
]);

const displayedPresentations = computed(() => props.presentations?.data || []);

// Calculate keynote count dynamically if not in stats
const enhancedStats = computed(() => {
    const keynoteCount =
        props.presentations?.data?.filter(
            (p) => p.presentation_type === "keynote"
        ).length || 0;

    return {
        total: props.stats?.total || props.presentations?.total || 0,
        with_speakers: props.stats?.with_speakers || 0,
        without_speakers: props.stats?.without_speakers || 0,
        keynote: keynoteCount,
    };
});

const filteredSessions = computed(() => {
    // This would ideally come from the backend based on selected event
    // For now, return empty array since we don't have sessions data in props
    return [];
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
        displayedPresentations.value.length > 0 &&
        selectedPresentations.value.length ===
            displayedPresentations.value.length
    );
});

const isIndeterminate = computed(() => {
    return (
        selectedPresentations.value.length > 0 &&
        selectedPresentations.value.length < displayedPresentations.value.length
    );
});

const visiblePages = computed(() => {
    if (!props.presentations?.last_page) return [];

    const current = props.presentations.current_page;
    const total = props.presentations.last_page;
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
const getPresentationTypeClasses = (presentationType) => {
    const classes = {
        keynote:
            "bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200",
        oral: "bg-slate-200 text-slate-900 dark:bg-slate-700 dark:text-slate-200",
        poster: "bg-slate-300 text-slate-900 dark:bg-slate-600 dark:text-slate-200",
        panel: "bg-slate-400 text-white dark:bg-slate-500 dark:text-slate-200",
        workshop: "bg-slate-500 text-white dark:bg-slate-400 dark:text-slate-900",
    };
    return (
        classes[presentationType] ||
        "bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300"
    );
};

const getPresentationTypeGradient = (presentationType) => {
    const gradients = {
        keynote: "from-slate-800 to-slate-900",
        oral: "from-slate-700 to-slate-800",
        poster: "from-slate-600 to-slate-700",
        panel: "from-slate-500 to-slate-600",
        workshop: "from-slate-400 to-slate-500",
    };
    return gradients[presentationType] || "from-slate-500 to-slate-600";
};

const getPresentationIcon = (presentationType) => {
    const icons = {
        keynote: StarIcon,
        oral: MicrophoneIcon,
        poster: PresentationChartLineIcon,
        panel: UserGroupIcon,
        workshop: AcademicCapIcon,
    };
    return icons[presentationType] || DocumentTextIcon;
};

const getPresentationTypeLabel = (presentationType) => {
    const labels = {
        keynote: "Keynote",
        oral: "Sözlü",
        poster: "Poster",
        panel: "Panel",
        workshop: "Workshop",
    };
    return labels[presentationType] || presentationType || "Belirtilmemiş";
};

const getSpeakerRoleClasses = (role) => {
    const classes = {
        primary:
            "bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300",
        co_speaker:
            "bg-slate-200 text-slate-900 dark:bg-slate-600 dark:text-slate-300",
        discussant:
            "bg-slate-300 text-slate-900 dark:bg-slate-500 dark:text-slate-300",
    };
    return (
        classes[role] ||
        "bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300"
    );
};

const getSpeakerRoleLabel = (role) => {
    const labels = {
        primary: "Ana",
        co_speaker: "Ko",
        discussant: "Tartışmacı",
    };
    return labels[role] || role || "";
};

const formatEventInfo = (presentation) => {
    const eventName = presentation.program_session?.event?.name || "Etkinlik";
    const dayInfo =
        presentation.program_session?.venue?.event_day?.display_name || "";
    return dayInfo ? `${eventName} - ${dayInfo}` : eventName;
};

const formatDuration = (presentation) => {
    if (presentation.duration_minutes && presentation.duration_minutes > 0) {
        const minutes = presentation.duration_minutes;
        if (minutes < 60) return `${minutes} dk`;
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        return remainingMinutes > 0
            ? `${hours}s ${remainingMinutes}dk`
            : `${hours}s`;
    }

    if (!presentation.start_time || !presentation.end_time)
        return "Süre belirsiz";

    // Zaman formatını normalize et
    const normalizeTime = (timeStr) => {
        if (!timeStr) return null;

        const timeString = timeStr.toString();

        // Carbon timestamp formatında ise
        if (timeString.includes("T")) {
            const date = new Date(timeString);
            return `${date.getHours().toString().padStart(2, "0")}:${date
                .getMinutes()
                .toString()
                .padStart(2, "0")}`;
        }

        // HH:MM:SS formatında ise HH:MM'e kısalt
        if (timeString.includes(":")) {
            return timeString.substring(0, 5);
        }

        return timeString;
    };

    const startTime = normalizeTime(presentation.start_time);
    const endTime = normalizeTime(presentation.end_time);

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

const formatTimeRange = (presentation) => {
    if (!presentation.start_time || !presentation.end_time) return null;

    // Zaman formatını normalize et (Carbon timestamp vs HH:MM)
    const normalizeTime = (timeStr) => {
        if (!timeStr) return null;

        // String değilse string'e çevir
        const timeString = timeStr.toString();

        // Carbon timestamp formatında ise (2025-06-22T09:00:00.000000Z)
        if (timeString.includes("T")) {
            const date = new Date(timeString);
            return `${date.getHours().toString().padStart(2, "0")}:${date
                .getMinutes()
                .toString()
                .padStart(2, "0")}`;
        }

        // HH:MM:SS formatında ise HH:MM'e kısalt
        if (timeString.includes(":")) {
            return timeString.substring(0, 5); // Get HH:MM part
        }

        return timeString;
    };

    const start = normalizeTime(presentation.start_time);
    const end = normalizeTime(presentation.end_time);

    if (!start || !end) return null;

    return `${start} - ${end}`;
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
        activeFilters.value.presentation_type = "";
    } else {
        activeFilters.value.presentation_type = filter;
    }
    applyFilters();
};

const applyFilters = () => {
    updateUrl({ ...activeFilters.value, page: 1 });
};

const clearFilters = () => {
    activeFilters.value = {
        event_id: "",
        session_id: "",
        speaker_id: "",
        presentation_type: "",
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
    if (page >= 1 && page <= props.presentations.last_page) {
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
        safeRoute("admin.presentations.index", "/admin/presentations"),
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
        selectedPresentations.value = [];
    } else {
        selectedPresentations.value = displayedPresentations.value.map(
            (presentation) => presentation.id
        );
    }
};

const toggleActionsMenu = (presentationId) => {
    showActionsMenu.value =
        showActionsMenu.value === presentationId ? null : presentationId;
};

// Presentation Actions
const duplicatePresentation = (presentation) => {
    showActionsMenu.value = null;
    router.post(
        safeRoute(
            "admin.presentations.duplicate",
            `/admin/presentations/${presentation.id}/duplicate`,
            presentation.id
        ),
        {},
        {
            onError: () => {
                alert("Kopyalama sırasında bir hata oluştu.");
            },
        }
    );
};

const deletePresentation = (presentation) => {
    showActionsMenu.value = null;
    confirmDialog.value = {
        show: true,
        title: "Sunumu Sil",
        message: `"${presentation.title}" sunumunu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
        type: "danger",
        callback: () => {
            router.delete(
                safeRoute(
                    "admin.presentations.destroy",
                    `/admin/presentations/${presentation.id}`,
                    presentation.id
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

// Bulk Actions
const bulkDuplicate = () => {
    router.post(
        safeRoute(
            "admin.presentations.bulk-duplicate",
            "/admin/presentations/bulk-duplicate"
        ),
        {
            presentation_ids: selectedPresentations.value,
        },
        {
            onSuccess: () => {
                selectedPresentations.value = [];
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
        title: "Sunumları Sil",
        message: `Seçili ${selectedPresentations.value.length} sunumu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
        type: "danger",
        callback: () => {
            router.delete(
                safeRoute(
                    "admin.presentations.bulk-destroy",
                    "/admin/presentations/bulk-destroy"
                ),
                {
                    data: { presentation_ids: selectedPresentations.value },
                    onSuccess: () => {
                        confirmDialog.value.show = false;
                        selectedPresentations.value = [];
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
    @apply bg-slate-100 dark:bg-slate-800;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    @apply bg-slate-300 dark:bg-slate-600 rounded-full;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    @apply bg-slate-400 dark:bg-slate-500;
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
