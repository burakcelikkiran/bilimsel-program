<!-- Admin/ProgramSessions/Show.vue - Gray Theme Version -->
<template>
    <AdminLayout
        :page-title="safeProgramSession.title"
        :page-subtitle="`${
            safeProgramSession.venue?.display_name || 'Salon'
        } - ${formatEventInfo(safeProgramSession)}`"
        :breadcrumbs="breadcrumbs"
    >
        <Head :title="safeProgramSession.title" />

        <div class="w-full space-y-8">
            <!-- Header Section -->
            <div
                class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
            >
                <div class="relative">
                    <!-- Banner with gray gradient -->
                    <div
                        class="h-48 bg-gradient-to-r from-gray-800 to-gray-900 relative overflow-hidden"
                    >
                        <div class="absolute inset-0 bg-black/20"></div>
                        <div class="absolute inset-0 flex items-end">
                            <div class="p-8 text-white w-full">
                                <div class="flex items-end justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div
                                            class="h-16 w-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center"
                                        >
                                            <SpeakerWaveIcon
                                                class="h-10 w-10"
                                            />
                                        </div>
                                        <div>
                                            <h1 class="text-3xl font-bold mb-1">
                                                {{ safeProgramSession.title }}
                                            </h1>
                                            <p class="text-gray-100 text-lg">
                                                {{
                                                    safeProgramSession.formatted_time_range ||
                                                    "Zaman belirtilmemiş"
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quick Stats -->
                                    <div
                                        class="flex items-center space-x-8 text-white/90"
                                    >
                                        <div class="text-center">
                                            <div class="text-2xl font-bold">
                                                {{
                                                    safeProgramSession
                                                        .presentations
                                                        ?.length || 0
                                                }}
                                            </div>
                                            <div class="text-sm">Sunum</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold">
                                                {{
                                                    safeProgramSession
                                                        .moderators?.length || 0
                                                }}
                                            </div>
                                            <div class="text-sm">Moderatör</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold">
                                                {{
                                                    safeProgramSession.formatted_duration ||
                                                    "0 dk"
                                                }}
                                            </div>
                                            <div class="text-sm">Süre</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Bar -->
                    <div
                        class="px-8 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex flex-wrap items-center gap-6">
                            <!-- Session Type -->
                            <div class="flex items-center space-x-2">
                                <span
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >Tür:</span
                                >
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :class="
                                        getSessionTypeClasses(
                                            safeProgramSession.session_type
                                        )
                                    "
                                >
                                    {{
                                        safeProgramSession.session_type_display ||
                                        safeProgramSession.session_type
                                    }}
                                </span>
                            </div>

                            <!-- Venue Info -->
                            <div class="flex items-center space-x-2">
                                <BuildingOfficeIcon
                                    class="h-5 w-5 text-gray-400"
                                />
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        {{
                                            safeProgramSession.venue
                                                ?.display_name || "Salon Yok"
                                        }}
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Salon
                                    </div>
                                </div>
                            </div>

                            <!-- Event Info -->
                            <div class="flex items-center space-x-2">
                                <CalendarIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        {{
                                            formatEventInfo(safeProgramSession)
                                        }}
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Etkinlik
                                    </div>
                                </div>
                            </div>

                            <!-- Break Status -->
                            <div
                                v-if="safeProgramSession.is_break"
                                class="flex items-center space-x-2"
                            >
                                <PauseIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                    >
                                        Ara Oturumu
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Özel Oturum
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Bar -->
                    <div
                        class="px-8 py-4 flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900"
                    >
                        <div class="flex items-center space-x-3">
                            <!-- Back to List -->
                            <Link
                                :href="route('admin.program-sessions.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
                            >
                                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                                Oturum Listesi
                            </Link>
                        </div>

                        <div class="flex items-center space-x-3">
                            <!-- Presentation Management - yetkilendirme kaldırıldı -->
                            <Link
                                :href="
                                    route('admin.presentations.create', {
                                        program_session_id:
                                            safeProgramSession.id,
                                    })
                                "
                                class="inline-flex items-center px-4 py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-800 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
                            >
                                <DocumentTextIcon class="h-4 w-4 mr-2" />
                                Sunum Ekle
                            </Link>

                            <!-- Edit Button -->
                            <Link
                                :href="
                                    route(
                                        'admin.program-sessions.edit',
                                        safeProgramSession.id
                                    )
                                "
                                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors shadow-sm"
                            >
                                <PencilSquareIcon class="h-4 w-4 mr-2" />
                                Düzenle
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                <!-- Main Content -->
                <div class="xl:col-span-3 space-y-8">
                    <!-- Description -->
                    <div
                        v-if="safeProgramSession.description"
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Açıklama
                        </h3>
                        <div class="prose dark:prose-invert max-w-none">
                            <p
                                class="text-gray-700 dark:text-gray-300 leading-relaxed"
                            >
                                {{ safeProgramSession.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Presentations -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between"
                        >
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-white"
                            >
                                Sunumlar
                            </h3>
                            <div class="flex items-center space-x-3">
                                <!-- Sunum sayısı göstergesi -->
                                <span
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{
                                        safeProgramSession.presentations
                                            ?.length || 0
                                    }}
                                    sunum
                                </span>
                                <!-- Sunum ekle butonu - yetkilendirme kaldırıldı -->
                                <Link
                                    :href="
                                        route('admin.presentations.create', {
                                            program_session_id:
                                                safeProgramSession.id,
                                        })
                                    "
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors shadow-sm"
                                >
                                    <DocumentTextIcon class="h-5 w-5 mr-2" />
                                    Sunum Ekle
                                </Link>
                            </div>
                        </div>

                        <!-- Presentations List -->
                        <div
                            v-if="safeProgramSession.presentations?.length > 0"
                            class="divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <div
                                v-for="presentation in safeProgramSession.presentations"
                                :key="presentation.id"
                                class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-2"
                                        >
                                            {{ presentation.title }}
                                        </h4>

                                        <div
                                            class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400 mb-3"
                                        >
                                            <div class="flex items-center">
                                                <ClockIcon
                                                    class="h-4 w-4 mr-1"
                                                />
                                                {{
                                                    presentation.formatted_time_range ||
                                                    "Zaman belirtilmemiş"
                                                }}
                                            </div>
                                            <div class="flex items-center">
                                                <UsersIcon
                                                    class="h-4 w-4 mr-1"
                                                />
                                                {{
                                                    presentation.speakers
                                                        ?.length || 0
                                                }}
                                                konuşmacı
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                presentation.speakers?.length >
                                                0
                                            "
                                            class="flex flex-wrap gap-2"
                                        >
                                            <span
                                                v-for="speaker in presentation.speakers"
                                                :key="speaker.id"
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200"
                                            >
                                                {{ speaker.full_name }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Presentation Actions -->
                                    <div
                                        class="flex items-center space-x-2 ml-4"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'admin.presentations.show',
                                                    presentation.id
                                                )
                                            "
                                            class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                            title="Sunumu Görüntüle"
                                        >
                                            <EyeIcon class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="
                                                route(
                                                    'admin.presentations.edit',
                                                    presentation.id
                                                )
                                            "
                                            class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                            title="Sunumu Düzenle"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty Presentations State -->
                        <div v-else class="text-center py-16">
                            <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                                <DocumentTextIcon class="h-full w-full" />
                            </div>
                            <h4
                                class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2"
                            >
                                Henüz sunum yok
                            </h4>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">
                                Bu oturum için sunum eklemeye başlayın.
                            </p>
                            <div class="space-y-3">
                                <!-- İlk sunum ekle butonu - yetkilendirme kaldırıldı -->
                                <Link
                                    :href="
                                        route('admin.presentations.create', {
                                            program_session_id:
                                                safeProgramSession.id,
                                        })
                                    "
                                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-base font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors shadow-sm"
                                >
                                    <DocumentTextIcon class="h-5 w-5 mr-2" />
                                    İlk Sunumu Ekle
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Session Details -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Oturum Detayları
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <ClockIcon class="h-5 w-5 text-gray-500" />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Başlangıç</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{
                                        formatTime(
                                            safeProgramSession.start_time
                                        )
                                    }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <ClockIcon class="h-5 w-5 text-gray-500" />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Bitiş</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{
                                        formatTime(safeProgramSession.end_time)
                                    }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <DocumentTextIcon
                                        class="h-5 w-5 text-gray-500"
                                    />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Sunum Sayısı</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{
                                        safeProgramSession.presentations
                                            ?.length || 0
                                    }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <UsersIcon class="h-5 w-5 text-gray-500" />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Moderatör Sayısı</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{
                                        safeProgramSession.moderators?.length ||
                                        0
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Moderators -->
                    <div
                        v-if="safeProgramSession.moderators?.length > 0"
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Moderatörler
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="moderator in safeProgramSession.moderators"
                                :key="moderator.id"
                                class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
                            >
                                <div
                                    class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center"
                                >
                                    <UsersIcon
                                        class="w-4 h-4 text-gray-600 dark:text-gray-400"
                                    />
                                </div>
                                <div class="flex-1">
                                    <div
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ moderator.full_name }}
                                    </div>
                                    <div
                                        v-if="moderator.title"
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ moderator.title }}
                                    </div>
                                    <div
                                        v-if="moderator.affiliation"
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ moderator.affiliation }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div
                        v-if="safeProgramSession.categories?.length > 0"
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Kategoriler
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="category in safeProgramSession.categories"
                                :key="category.id"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :style="{
                                    backgroundColor: category.color + '20',
                                    color: category.color,
                                }"
                            >
                                {{ category.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Sponsor -->
                    <div
                        v-if="safeProgramSession.sponsor"
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Sponsor
                        </h3>
                        <div class="flex items-center space-x-3">
                            <div
                                v-if="safeProgramSession.sponsor.logo_url"
                                class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800"
                            >
                                <img
                                    :src="safeProgramSession.sponsor.logo_url"
                                    :alt="safeProgramSession.sponsor.name"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="flex-1">
                                <div
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    {{ safeProgramSession.sponsor.name }}
                                </div>
                                <div
                                    v-if="safeProgramSession.sponsor.website"
                                    class="text-xs text-gray-600 dark:text-gray-400"
                                >
                                    <a
                                        :href="
                                            safeProgramSession.sponsor.website
                                        "
                                        target="_blank"
                                        class="hover:underline"
                                    >
                                        Website
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
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
    SpeakerWaveIcon,
    CalendarIcon,
    ClockIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    UsersIcon,
    ArrowLeftIcon,
    PencilSquareIcon,
    EllipsisVerticalIcon,
    ChevronDownIcon,
    DocumentDuplicateIcon,
    TrashIcon,
    EyeIcon,
    PencilIcon,
    PauseIcon,
} from "@heroicons/vue/24/outline";

// Props - Backend'den "session" key'i ile geliyor
const props = defineProps({
    session: {
        type: Object,
        required: false,
        default: () => ({
            id: null,
            title: "Program Oturumu",
            description: "",
            start_time: "",
            end_time: "",
            formatted_time_range: "",
            formatted_duration: "",
            session_type: "",
            session_type_display: "",
            moderator_title: "",
            is_break: false,
            venue: null,
            sponsor: null,
            moderators: [],
            categories: [],
            presentations: [],
            can_edit: false,
            can_delete: false,
            can_create_presentations: false,
        }),
    },
});

// State
const showActionsMenu = ref(false);
const actionsMenuRef = ref(null);
const confirmDialog = ref({
    show: false,
    title: "",
    message: "",
    type: "warning",
    callback: null,
});

// Computed
const safeProgramSession = computed(() => ({
    id: props.session?.id || null,
    title: props.session?.title || "Program Oturumu",
    description: props.session?.description || "",
    start_time: props.session?.start_time || "",
    end_time: props.session?.end_time || "",
    formatted_time_range: props.session?.formatted_time_range || "",
    formatted_duration: props.session?.formatted_duration || "",
    session_type: props.session?.session_type || "",
    session_type_display: props.session?.session_type_display || "",
    moderator_title: props.session?.moderator_title || "",
    is_break: props.session?.is_break || false,
    venue: props.session?.venue || null,
    sponsor: props.session?.sponsor || null,
    moderators: props.session?.moderators || [],
    categories: props.session?.categories || [],
    presentations: props.session?.presentations || [],
    can_edit: props.session?.can_edit || false,
    can_delete: props.session?.can_delete || false,
    can_create_presentations: props.session?.can_create_presentations || false,
}));

const breadcrumbs = computed(() => {
    const crumbs = [
        { label: "Ana Sayfa", href: route("admin.dashboard") },
        {
            label: "Program Oturumları",
            href: route("admin.program-sessions.index"),
        },
    ];

    // Event ve EventDay bilgisi varsa ekle
    if (safeProgramSession.value.venue?.event_day?.event) {
        const event = safeProgramSession.value.venue.event_day.event;
        const eventDay = safeProgramSession.value.venue.event_day;

        crumbs.splice(
            1,
            0,
            { label: "Etkinlikler", href: route("admin.events.index") },
            { label: event.name, href: route("admin.events.show", event.slug) },
            {
                label: "Günler",
                href: route("admin.events.days.index", event.slug),
            },
            {
                label: eventDay.display_name,
                href: route("admin.events.days.show", [
                    event.slug,
                    eventDay.id,
                ]),
            }
        );
    }

    crumbs.push({ label: safeProgramSession.value.title, href: null });

    return crumbs;
});

// Helper functions
const formatTime = (timeString) => {
    if (!timeString) return "-";

    try {
        const date = new Date(timeString);
        if (isNaN(date.getTime())) return "-";

        const hours = date.getHours().toString().padStart(2, "0");
        const minutes = date.getMinutes().toString().padStart(2, "0");

        return `${hours}:${minutes}`;
    } catch (error) {
        console.error("Time formatting error:", error);
        return "-";
    }
};

// Methods
const getSessionTypeClasses = (sessionType) => {
    const classes = {
        main: "bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200",
        satellite:
            "bg-gray-200 text-gray-900 dark:bg-gray-800 dark:text-gray-200",
        oral_presentation:
            "bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-gray-200",
        special: "bg-gray-400 text-white dark:bg-gray-600 dark:text-gray-200",
        break: "bg-gray-500 text-white dark:bg-gray-500 dark:text-gray-100",
    };
    return (
        classes[sessionType] ||
        "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
    );
};

const formatEventInfo = (session) => {
    const eventName = session.venue?.event_day?.event?.name || "Etkinlik";
    const dayInfo = session.venue?.event_day?.display_name || "";
    return dayInfo ? `${eventName} - ${dayInfo}` : eventName;
};

const duplicateSession = () => {
    showActionsMenu.value = false;
    if (safeProgramSession.value.id) {
        router.post(
            route(
                "admin.program-sessions.duplicate",
                safeProgramSession.value.id
            ),
            {},
            {
                onSuccess: () => {
                    // Success message will be handled by the backend
                },
                onError: () => {
                    alert("Oturum kopyalama sırasında bir hata oluştu.");
                },
            }
        );
    }
};

const deleteSession = () => {
    showActionsMenu.value = false;
    confirmDialog.value = {
        show: true,
        title: "Oturumu Sil",
        message: `"${safeProgramSession.value.title}" oturumunu silmek istediğinize emin misiniz? Bu işlem geri alınamaz ve oturumdaki tüm sunumlar da silinecektir.`,
        type: "danger",
        callback: () => {
            if (safeProgramSession.value.id) {
                router.delete(
                    route(
                        "admin.program-sessions.destroy",
                        safeProgramSession.value.id
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
            }
        },
    };
};

// Click outside handler for dropdown
const handleClickOutside = (event) => {
    if (actionsMenuRef.value && !actionsMenuRef.value.contains(event.target)) {
        showActionsMenu.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
/* Z-Index ve Dropdown Düzeltmeleri */
.dropdown-container {
    position: relative;
    z-index: 10;
}

.dropdown-menu {
    position: absolute !important;
    z-index: 9999 !important;
    right: 0;
    top: 100%;
    min-width: 14rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Ensure parent containers don't interfere */
.bg-white,
.bg-gray-900 {
    position: relative;
}

/* Prose styles */
.prose p {
    margin-bottom: 1rem;
    line-height: 1.7;
}

/* Smooth transitions */
.transition-colors {
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Focus states for accessibility */
button:focus-visible,
a:focus-visible {
    outline: 2px solid rgb(59 130 246);
    outline-offset: 2px;
}
</style>
