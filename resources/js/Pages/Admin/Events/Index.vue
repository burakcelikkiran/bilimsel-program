<template>
    <AdminLayout
        page-title="Bilimsel Etkinlikler"
        page-subtitle="Akademik kongreler, sempozyumlar ve konferansları yönetin"
        :breadcrumbs="breadcrumbs"
    >
        <Head title="Bilimsel Etkinlikler" />

        <div class="-mx-8 -my-6 px-8 py-6">
            <div class="max-w-7xl mx-auto">
                <!-- Hero Section with Quick Stats -->
                <div class="mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Total Events -->
                        <div
                            class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 text-white shadow-lg border border-gray-700"
                        >
                            <div class="flex items-center">
                                <div
                                    class="p-3 bg-white/10 rounded-lg backdrop-blur-sm"
                                >
                                    <CalendarIcon class="h-8 w-8" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-300 text-sm">
                                        Toplam Etkinlik
                                    </p>
                                    <p class="text-2xl font-bold">
                                        {{
                                            stats?.total ||
                                            events?.meta?.total ||
                                            0
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Published Events -->
                        <div
                            class="bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl p-6 text-white shadow-lg border border-gray-600"
                        >
                            <div class="flex items-center">
                                <div
                                    class="p-3 bg-white/10 rounded-lg backdrop-blur-sm"
                                >
                                    <EyeIcon class="h-8 w-8" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-300 text-sm">Yayında</p>
                                    <p class="text-2xl font-bold">
                                        {{ stats?.published || 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Events -->
                        <div
                            class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl p-6 text-white shadow-lg border border-gray-500"
                        >
                            <div class="flex items-center">
                                <div
                                    class="p-3 bg-white/10 rounded-lg backdrop-blur-sm"
                                >
                                    <ClockIcon class="h-8 w-8" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-300 text-sm">
                                        Yaklaşan
                                    </p>
                                    <p class="text-2xl font-bold">
                                        {{ stats?.upcoming || 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Ongoing Events -->
                        <div
                            class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl p-6 text-white shadow-lg border border-gray-400"
                        >
                            <div class="flex items-center">
                                <div
                                    class="p-3 bg-white/10 rounded-lg backdrop-blur-sm"
                                >
                                    <PlayIcon class="h-8 w-8" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-300 text-sm">
                                        Devam Eden
                                    </p>
                                    <p class="text-2xl font-bold">
                                        {{ stats?.ongoing || 0 }}
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
                                    Etkinlik Yönetimi
                                </h2>
                                <p
                                    class="text-gray-600 dark:text-gray-400 mt-1"
                                >
                                    Bilimsel etkinlikleri düzenleyin ve yönetin
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
                                        @click="quickFilter('published')"
                                        :class="[
                                            'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                                            currentQuickFilter === 'published'
                                                ? 'bg-gray-700 text-white shadow-lg border border-gray-600'
                                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        ]"
                                    >
                                        Yayında
                                    </button>
                                    <button
                                        @click="quickFilter('upcoming')"
                                        :class="[
                                            'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                                            currentQuickFilter === 'upcoming'
                                                ? 'bg-gray-600 text-white shadow-lg border border-gray-500'
                                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        ]"
                                    >
                                        Yaklaşan
                                    </button>
                                </div>

                                <!-- Create Button -->
                                <Link
                                    :href="
                                        safeRoute(
                                            'admin.events.create',
                                            '/admin/events/create'
                                        )
                                    "
                                    class="inline-flex items-center px-6 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-all duration-200 border border-gray-700"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Yeni Etkinlik
                                </Link>
                            </div>
                        </div>

                        <!-- Search and Filters -->
                        <div
                            class="mt-6 flex flex-col lg:flex-row lg:items-center space-y-4 lg:space-y-0 lg:space-x-4"
                        >
                            <!-- Search Bar -->
                            <div class="flex-1 relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                >
                                    <MagnifyingGlassIcon
                                        class="h-5 w-5 text-gray-400"
                                    />
                                </div>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Etkinlik adı, açıklama veya organizasyon ara..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm"
                                    @input="handleSearchDebounced"
                                />
                                <div
                                    v-if="searchQuery"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <button
                                        @click="clearSearch"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                    >
                                        <XMarkIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Filters -->
                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Organization Filter -->
                                <select
                                    v-model="activeFilters.organization_id"
                                    @change="applyFilters"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm min-w-[200px]"
                                >
                                    <option value="">
                                        Tüm Organizasyonlar
                                    </option>
                                    <option
                                        v-for="org in organizations"
                                        :key="org.id"
                                        :value="org.id"
                                    >
                                        {{ org.name }}
                                    </option>
                                </select>

                                <!-- Status Filter -->
                                <select
                                    v-model="activeFilters.status"
                                    @change="applyFilters"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm min-w-[150px]"
                                >
                                    <option value="">Tüm Durumlar</option>
                                    <option value="draft">Taslak</option>
                                    <option value="published">Yayında</option>
                                    <option value="upcoming">Yaklaşan</option>
                                    <option value="ongoing">
                                        Devam Ediyor
                                    </option>
                                    <option value="completed">
                                        Tamamlandı
                                    </option>
                                </select>

                                <!-- Filter Reset -->
                                <button
                                    v-if="hasActiveFilters"
                                    @click="clearFilters"
                                    class="px-3 py-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 border border-red-300 dark:border-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900"
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
                        class="flex items-center justify-center py-12"
                    >
                        <div class="flex items-center space-x-3">
                            <div
                                class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-600"
                            ></div>
                            <p class="text-gray-600 dark:text-gray-400">
                                Yükleniyor...
                            </p>
                        </div>
                    </div>

                    <!-- Events Table -->
                    <div
                        v-else-if="displayedEvents.length > 0"
                        class="overflow-hidden"
                    >
                        <div class="overflow-x-auto">
                            <table
                                class="w-full divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <!-- Event Header -->
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                        >
                                            Etkinlik
                                        </th>

                                        <!-- Status Header -->
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32"
                                        >
                                            Durum
                                        </th>

                                        <!-- Organization Header -->
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48"
                                        >
                                            Organizasyon
                                        </th>

                                        <!-- Progress Header -->
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36"
                                        >
                                            İlerleme
                                        </th>

                                        <!-- Actions Header -->
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48"
                                        >
                                            İşlemler
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700"
                                >
                                    <tr
                                        v-for="event in displayedEvents"
                                        :key="event.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                    >
                                        <!-- Event Info -->
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 h-12 w-12"
                                                >
                                                    <img
                                                        v-if="event.banner_url"
                                                        class="h-12 w-12 rounded-lg object-cover"
                                                        :src="event.banner_url"
                                                        :alt="event.name"
                                                    />
                                                    <div
                                                        v-else
                                                        class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center"
                                                    >
                                                        <CalendarIcon
                                                            class="h-6 w-6 text-gray-400"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <Link
                                                        :href="
                                                            safeRoute(
                                                                'admin.events.show',
                                                                `/admin/events/${event.slug}`,
                                                                event.slug
                                                            )
                                                        "
                                                        class="text-sm font-medium text-gray-900 dark:text-white hover:text-gray-600 dark:hover:text-gray-300"
                                                    >
                                                        {{ event.name }}
                                                    </Link>
                                                    <div
                                                        class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400 space-x-3"
                                                    >
                                                        <span
                                                            class="inline-flex items-center"
                                                        >
                                                            <CalendarDaysIcon
                                                                class="h-3 w-3 mr-1"
                                                            />
                                                            {{
                                                                formatDateRange(
                                                                    event.start_date,
                                                                    event.end_date
                                                                )
                                                            }}
                                                        </span>
                                                        <span
                                                            v-if="
                                                                event.location
                                                            "
                                                            class="inline-flex items-center"
                                                        >
                                                            <MapPinIcon
                                                                class="h-3 w-3 mr-1"
                                                            />
                                                            {{ event.location }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-6 py-4 w-32">
                                            <span
                                                :class="[
                                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                    getStatusClasses(
                                                        event.status
                                                    ),
                                                ]"
                                            >
                                                {{
                                                    getStatusLabel(event.status)
                                                }}
                                            </span>
                                        </td>

                                        <!-- Organization -->
                                        <td class="px-6 py-4 w-48">
                                            <div class="flex items-center">
                                                <img
                                                    v-if="
                                                        event.organization
                                                            ?.logo_url
                                                    "
                                                    class="h-8 w-8 rounded-lg object-cover"
                                                    :src="
                                                        event.organization
                                                            .logo_url
                                                    "
                                                    :alt="
                                                        event.organization.name
                                                    "
                                                />
                                                <BuildingOfficeIcon
                                                    v-else
                                                    class="h-8 w-8 text-gray-400"
                                                />
                                                <div class="ml-3">
                                                    <span
                                                        class="text-sm font-medium text-gray-900 dark:text-white block truncate"
                                                    >
                                                        {{
                                                            event.organization
                                                                ?.name ||
                                                            "Organizasyon Yok"
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Progress -->
                                        <td class="px-6 py-4 w-36">
                                            <div
                                                class="text-sm text-gray-900 dark:text-white"
                                            >
                                                <div>
                                                    {{
                                                        event.event_days_count ||
                                                        0
                                                    }}
                                                    gün
                                                </div>
                                                <div
                                                    class="text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        event.total_presentations ||
                                                        0
                                                    }}
                                                    sunum
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 w-48">
                                            <div
                                                class="flex items-center space-x-2"
                                            >
                                                <!-- View Button -->
                                                <Link
                                                    :href="
                                                        safeRoute(
                                                            'admin.events.show',
                                                            `/admin/events/${event.slug}`,
                                                            event.slug
                                                        )
                                                    "
                                                    class="inline-flex items-center px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-md"
                                                >
                                                    <EyeIcon
                                                        class="h-3 w-3 mr-1"
                                                    />
                                                    Görüntüle
                                                </Link>

                                                <!-- Edit Button -->
                                                <Link
                                                    v-if="event.can_edit"
                                                    :href="
                                                        safeRoute(
                                                            'admin.events.edit',
                                                            `/admin/events/${event.slug}/edit`,
                                                            event.slug
                                                        )
                                                    "
                                                    class="inline-flex items-center px-3 py-1.5 bg-gray-700 hover:bg-gray-800 text-white text-xs font-medium rounded-md"
                                                >
                                                    <PencilIcon
                                                        class="h-3 w-3 mr-1"
                                                    />
                                                    Düzenle
                                                </Link>

                                                <!-- Timeline Button -->
                                                <Link
                                                    :href="
                                                        safeRoute(
                                                            'admin.timeline.show',
                                                            `/admin/timeline/${event.slug}`,
                                                            event.slug
                                                        )
                                                    "
                                                    class="inline-flex items-center px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white text-xs font-medium rounded-md"
                                                >
                                                    <ClockIcon
                                                        class="h-3 w-3 mr-1"
                                                    />
                                                    Timeline
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="events?.meta?.last_page > 1"
                            class="bg-gray-50 dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-sm text-gray-700 dark:text-gray-300"
                                    >
                                        <span class="font-medium">{{
                                            events.meta.from || 0
                                        }}</span>
                                        -
                                        <span class="font-medium">{{
                                            events.meta.to || 0
                                        }}</span>
                                        arası, toplam
                                        <span class="font-medium">{{
                                            events.meta.total || 0
                                        }}</span>
                                        etkinlik
                                    </p>
                                </div>

                                <nav class="flex items-center space-x-2">
                                    <!-- Previous Page -->
                                    <button
                                        @click="
                                            goToPage(
                                                events.meta.current_page - 1
                                            )
                                        "
                                        :disabled="
                                            events.meta.current_page <= 1
                                        "
                                        class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Önceki
                                    </button>

                                    <!-- Page Info -->
                                    <div
                                        class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-md"
                                    >
                                        {{ events.meta.current_page }} /
                                        {{ events.meta.last_page }}
                                    </div>

                                    <!-- Next Page -->
                                    <button
                                        @click="
                                            goToPage(
                                                events.meta.current_page + 1
                                            )
                                        "
                                        :disabled="
                                            events.meta.current_page >=
                                            events.meta.last_page
                                        "
                                        class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Sonraki
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-12">
                        <div class="text-center">
                            <div
                                class="mx-auto h-24 w-24 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mb-6"
                            >
                                <CalendarIcon class="h-12 w-12 text-gray-400" />
                            </div>
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-white mb-2"
                            >
                                {{
                                    searchQuery || hasActiveFilters
                                        ? "Sonuç bulunamadı"
                                        : "Henüz etkinlik yok"
                                }}
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">
                                {{
                                    searchQuery || hasActiveFilters
                                        ? "Arama kriterlerinizi değiştirip tekrar deneyin."
                                        : "İlk etkinliğinizi oluşturarak başlayın."
                                }}
                            </p>
                            <div class="space-y-3">
                                <Link
                                    v-if="!searchQuery && !hasActiveFilters"
                                    :href="
                                        safeRoute(
                                            'admin.events.create',
                                            '/admin/events/create'
                                        )
                                    "
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg"
                                >
                                    <PlusIcon class="h-4 w-4 mr-2" />
                                    Yeni Etkinlik Oluştur
                                </Link>

                                <div
                                    v-if="searchQuery || hasActiveFilters"
                                    class="flex justify-center space-x-3"
                                >
                                    <button
                                        @click="clearSearch"
                                        class="inline-flex items-center px-3 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <XMarkIcon class="h-4 w-4 mr-2" />
                                        Aramayı Temizle
                                    </button>
                                    <button
                                        @click="clearFilters"
                                        class="inline-flex items-center px-3 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <XMarkIcon class="h-4 w-4 mr-2" />
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
import { ref, computed, onMounted } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
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
    MapPinIcon,
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
                total: 0,
            },
        }),
    },
    organizations: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: "",
            organization_id: "",
            status: "",
            date_from: "",
            date_to: "",
            sort: "start_date",
            direction: "desc",
            per_page: 15,
        }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            published: 0,
            upcoming: 0,
            ongoing: 0,
        }),
    },
});

// State
const loading = ref(false);
const searchQuery = ref(props.filters?.search || "");
const currentQuickFilter = ref("all");

const activeFilters = ref({
    organization_id: props.filters?.organization_id || "",
    status: props.filters?.status || "",
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
});

// Helper function to safely create routes
const safeRoute = (routeName, fallback, param = null) => {
    try {
        if (typeof route === "function") {
            if (param) {
                return route(routeName, param);
            }
            return route(routeName);
        }
    } catch (error) {
        console.warn(`Route ${routeName} not found, using fallback:`, fallback);
    }

    if (param && typeof fallback === "string" && fallback.includes("${")) {
        return fallback.replace("${event.slug}", param);
    }
    return fallback;
};

// Computed
const breadcrumbs = computed(() => [
    {
        label: "Ana Sayfa",
        href: safeRoute("admin.dashboard", "/admin/dashboard"),
    },
    { label: "Bilimsel Etkinlikler", href: null },
]);

const displayedEvents = computed(() => {
    if (!props.events?.data || !Array.isArray(props.events.data)) {
        return [];
    }
    return props.events.data;
});

const hasActiveFilters = computed(() => {
    return (
        Object.values(activeFilters.value).some(
            (value) => value !== "" && value !== null
        ) || searchQuery.value !== ""
    );
});

// Methods
const getStatusClasses = (status) => {
    const classes = {
        draft: "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
        upcoming:
            "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300",
        ongoing:
            "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300",
        completed:
            "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
        published:
            "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300",
    };
    return classes[status] || classes.draft;
};

const getStatusLabel = (status) => {
    const labels = {
        draft: "Taslak",
        upcoming: "Yaklaşan",
        ongoing: "Devam Ediyor",
        completed: "Tamamlandı",
        published: "Yayında",
    };
    return labels[status] || status;
};

const formatDateRange = (startDate, endDate) => {
    if (!startDate) return "-";

    const start = new Date(startDate);
    const end = endDate ? new Date(endDate) : start;

    const options = { day: "numeric", month: "short", year: "numeric" };

    if (start.toDateString() === end.toDateString()) {
        return start.toLocaleDateString("tr-TR", options);
    }

    return `${start.toLocaleDateString(
        "tr-TR",
        options
    )} - ${end.toLocaleDateString("tr-TR", options)}`;
};

// Search and filter methods
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
    } else {
        activeFilters.value.status = filter;
    }
    applyFilters();
};

const applyFilters = () => {
    updateUrl({ ...activeFilters.value, page: 1 });
};

const clearFilters = () => {
    activeFilters.value = {
        organization_id: "",
        status: "",
        date_from: "",
        date_to: "",
    };
    currentQuickFilter.value = "all";
    searchQuery.value = "";
    applyFilters();
};

const goToPage = (page) => {
    if (page >= 1 && page <= props.events?.meta?.last_page) {
        updateUrl({ page });
    }
};

const updateUrl = (params) => {
    const currentParams = new URLSearchParams(window.location.search);
    const existingParams = Object.fromEntries(currentParams);

    router.get(
        safeRoute("admin.events.index", "/admin/events"),
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

onMounted(() => {
    // Events page initialized
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
