<!-- Admin/Activities/Index.vue -->
<template>
    <AdminLayout
        page-title="Aktiviteler"
        page-subtitle="Sistemdeki tüm aktiviteleri görüntüleyin"
        :breadcrumbs="breadcrumbs"
    >
        <Head title="Aktiviteler" />

        <!-- Header Section with Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                            Aktivite Geçmişi
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Sistemdeki tüm aktiviteleri filtreleyin ve görüntüleyin
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            Toplam: {{ activities.total }} aktivite
                        </span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-6 bg-slate-50 dark:bg-slate-700/50">
                <form @submit.prevent="applyFilters" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Arama
                            </label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Açıklama veya kullanıcı adı..."
                                class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Activity Type -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Aktivite Türü
                            </label>
                            <select
                                v-model="filters.type"
                                class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent"
                            >
                                <option value="">Tüm Türler</option>
                                <option
                                    v-for="(label, type) in filterOptions.types"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- User -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Kullanıcı
                            </label>
                            <select
                                v-model="filters.user_id"
                                class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent"
                            >
                                <option value="">Tüm Kullanıcılar</option>
                                <option
                                    v-for="user in filterOptions.users"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Organization -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Organizasyon
                            </label>
                            <select
                                v-model="filters.organization_id"
                                class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent"
                            >
                                <option value="">Tüm Organizasyonlar</option>
                                <option
                                    v-for="org in filterOptions.organizations"
                                    :key="org.id"
                                    :value="org.id"
                                >
                                    {{ org.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Başlangıç Tarihi
                            </label>
                            <input
                                v-model="filters.date_from"
                                type="date"
                                class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Bitiş Tarihi
                            </label>
                            <input
                                v-model="filters.date_to"
                                type="date"
                                class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-end space-x-2">
                            <button
                                type="submit"
                                class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors"
                            >
                                Filtrele
                            </button>
                            <button
                                type="button"
                                @click="clearFilters"
                                class="px-4 py-2 border border-slate-300 text-slate-700 dark:text-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors"
                            >
                                Temizle
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activities List -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div v-if="activities.data.length === 0" class="text-center py-12">
                <ClockIcon class="mx-auto h-16 w-16 text-slate-300 dark:text-slate-600 mb-4" />
                <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">
                    Aktivite bulunamadı
                </h3>
                <p class="text-slate-500 dark:text-slate-400">
                    Belirttiğiniz kriterlere uygun aktivite bulunmamaktadır.
                </p>
            </div>

            <div v-else class="divide-y divide-slate-200 dark:divide-slate-700">
                <div
                    v-for="activity in activities.data"
                    :key="activity.id"
                    class="p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                >
                    <div class="flex items-start space-x-4">
                        <!-- Activity Icon -->
                        <div class="flex-shrink-0">
                            <div
                                class="w-10 h-10 rounded-xl flex items-center justify-center"
                                :class="getActivityClasses(activity.type)"
                            >
                                <component :is="getActivityIcon(activity.type)" class="w-5 h-5" />
                            </div>
                        </div>

                        <!-- Activity Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">
                                        {{ activity.description }}
                                    </p>
                                    <div class="mt-1 flex items-center space-x-4 text-xs text-slate-500 dark:text-slate-400">
                                        <span class="flex items-center">
                                            <UserIcon class="w-3 h-3 mr-1" />
                                            {{ activity.user.name }}
                                        </span>
                                        <span v-if="activity.organization" class="flex items-center">
                                            <BuildingOfficeIcon class="w-3 h-3 mr-1" />
                                            {{ activity.organization.name }}
                                        </span>
                                        <span class="flex items-center">
                                            <ClockIcon class="w-3 h-3 mr-1" />
                                            {{ formatTime(activity.performed_at) }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                                            :class="getTypeClasses(activity.type)"
                                        >
                                            {{ activity.type_label }}
                                        </span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ activity.subject.name }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-2 ml-4">
                                    <Link
                                        v-if="activity.link"
                                        :href="activity.link"
                                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                        title="İlgili kaydı görüntüle"
                                    >
                                        <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="activities.total > activities.per_page" class="p-6 border-t border-slate-200 dark:border-slate-700">
                <Pagination :links="activities.links" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import {
    ClockIcon,
    UserIcon,
    BuildingOfficeIcon,
    ArrowTopRightOnSquareIcon,
    CalendarIcon,
    DocumentTextIcon,
    UsersIcon,
    MapPinIcon,
    TagIcon,
    CheckCircleIcon,
    InformationCircleIcon,
    TrashIcon,
    PencilIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
    activities: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    filterOptions: {
        type: Object,
        required: true
    }
})

// Local state
const filters = ref({
    search: props.filters.search || '',
    type: props.filters.type || '',
    user_id: props.filters.user_id || '',
    organization_id: props.filters.organization_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || ''
})

// Computed
const breadcrumbs = computed(() => [
    { label: 'Dashboard', href: route('admin.dashboard') },
    { label: 'Aktiviteler', href: null }
])

// Methods
const applyFilters = () => {
    router.get(route('admin.activities.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    filters.value = {
        search: '',
        type: '',
        user_id: '',
        organization_id: '',
        date_from: '',
        date_to: ''
    }
    applyFilters()
}

const getActivityIcon = (type) => {
    const icons = {
        event_created: CalendarIcon,
        event_updated: CalendarIcon,
        event_deleted: TrashIcon,
        event_published: CheckCircleIcon,
        event_unpublished: InformationCircleIcon,
        session_created: DocumentTextIcon,
        session_updated: PencilIcon,
        session_deleted: TrashIcon,
        presentation_created: DocumentTextIcon,
        presentation_updated: PencilIcon,
        presentation_deleted: TrashIcon,
        participant_created: UsersIcon,
        participant_updated: PencilIcon,
        participant_deleted: TrashIcon,
        venue_created: MapPinIcon,
        venue_updated: PencilIcon,
        venue_deleted: TrashIcon,
        sponsor_created: TagIcon,
        sponsor_updated: PencilIcon,
        sponsor_deleted: TrashIcon,
        organization_created: BuildingOfficeIcon,
        organization_updated: PencilIcon,
        organization_deleted: TrashIcon
    }
    return icons[type] || InformationCircleIcon
}

const getActivityClasses = (type) => {
    if (type.includes('created')) {
        return 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400'
    } else if (type.includes('updated')) {
        return 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
    } else if (type.includes('deleted')) {
        return 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400'
    } else if (type.includes('published')) {
        return 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400'
    } else {
        return 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400'
    }
}

const getTypeClasses = (type) => {
    if (type.includes('created')) {
        return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'
    } else if (type.includes('updated')) {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300'
    } else if (type.includes('deleted')) {
        return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'
    } else if (type.includes('published')) {
        return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'
    } else {
        return 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300'
    }
}

const formatTime = (date) => {
    return new Date(date).toLocaleString('tr-TR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>