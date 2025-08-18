<!-- resources/js/Pages/Admin/Presentations/Edit.vue -->
<template>
    <AdminLayout
        page-title="Sunumu Düzenle"
        :page-subtitle="`'${presentation.title}' sunumunu düzenle`"
        :breadcrumbs="breadcrumbs"
        :full-width="true"
    >
        <Head :title="`${presentation.title} - Düzenle`" />

        <!-- Full Screen Container -->
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 -m-6 p-6">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6">
                <div class="px-8 py-6">
                    <div
                        class="flex flex-col lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="mb-4 lg:mb-0">
                            <h1
                                class="text-3xl font-bold text-gray-900 dark:text-white"
                            >
                                Sunumu Düzenle
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">
                                "{{ presentation.title }}" sunumunu düzenleyin
                                ve değişikliklerinizi kaydedin
                            </p>
                        </div>

                        <!-- Header Actions -->
                        <div class="flex items-center space-x-3">
                            <Link
                                :href="
                                    route(
                                        'admin.presentations.show',
                                        presentation.id
                                    )
                                "
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors"
                            >
                                <EyeIcon class="h-4 w-4 mr-2" />
                                Görüntüle
                            </Link>

                            <Link
                                :href="route('admin.presentations.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors"
                            >
                                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                                Geri Dön
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                <!-- Left Column - Form (3/4 width on xl screens) -->
                <div class="xl:col-span-3">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information Card -->
                        <div
                            class="bg-white dark:bg-gray-800 shadow-sm rounded-lg"
                        >
                            <div
                                class="px-8 py-6 border-b border-gray-200 dark:border-gray-700"
                            >
                                <h2
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    Temel Bilgiler
                                </h2>
                                <p
                                    class="text-gray-600 dark:text-gray-400 mt-1"
                                >
                                    Sunumun başlık, özet ve temel özelliklerini
                                    düzenleyin
                                </p>
                            </div>

                            <div class="p-8 space-y-6">
                                <!-- Program Session Selection -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Program Oturumu
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.program_session_id"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.program_session_id,
                                        }"
                                    >
                                        <option value="">Oturum Seçin</option>
                                        <optgroup
                                            v-for="event in groupedSessions"
                                            :key="event.id"
                                            :label="event.name"
                                        >
                                            <option
                                                v-for="session in event.sessions"
                                                :key="session.id"
                                                :value="session.id"
                                            >
                                                {{ session.title }} -
                                                {{
                                                    session.venue?.display_name
                                                }}
                                                ({{
                                                    formatTime(
                                                        session.start_time
                                                    )
                                                }})
                                            </option>
                                        </optgroup>
                                    </select>
                                    <p
                                        v-if="form.errors.program_session_id"
                                        class="text-red-500 text-sm mt-1"
                                    >
                                        {{ form.errors.program_session_id }}
                                    </p>
                                </div>

                                <!-- Title -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Sunum Başlığı
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 text-lg"
                                        :class="{
                                            'border-red-500': form.errors.title,
                                        }"
                                        placeholder="Sunum başlığını girin"
                                        maxlength="500"
                                    />
                                    <p
                                        v-if="form.errors.title"
                                        class="text-red-500 text-sm mt-1"
                                    >
                                        {{ form.errors.title }}
                                    </p>
                                </div>

                                <!-- Abstract -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Özet
                                    </label>
                                    <textarea
                                        v-model="form.abstract"
                                        rows="8"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.abstract,
                                        }"
                                        placeholder="Sunum özetini girin"
                                        maxlength="5000"
                                    ></textarea>
                                    <div
                                        class="flex justify-between items-center mt-2"
                                    >
                                        <p
                                            v-if="form.errors.abstract"
                                            class="text-red-500 text-sm"
                                        >
                                            {{ form.errors.abstract }}
                                        </p>
                                        <p class="text-gray-500 text-sm">
                                            {{
                                                form.abstract?.length || 0
                                            }}/5000 karakter
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Card -->
                        <div
                            class="bg-white dark:bg-gray-800 shadow-sm rounded-lg"
                        >
                            <div
                                class="px-8 py-6 border-b border-gray-200 dark:border-gray-700"
                            >
                                <h2
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    Detaylar
                                </h2>
                                <p
                                    class="text-gray-600 dark:text-gray-400 mt-1"
                                >
                                    Sunum türü, dil ve süre bilgilerini
                                    belirleyin
                                </p>
                            </div>

                            <div class="p-8">
                                <div
                                    class="grid grid-cols-1 md:grid-cols-3 gap-6"
                                >
                                    <!-- Presentation Type -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                        >
                                            Sunum Türü
                                        </label>
                                        <select
                                            v-model="form.presentation_type"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                                        >
                                            <option value="">Tür Seçin</option>
                                            <option value="keynote">
                                                Keynote
                                            </option>
                                            <option value="oral">
                                                Sözlü Bildiri
                                            </option>
                                            <option value="poster">
                                                Poster
                                            </option>
                                            <option value="panel">Panel</option>
                                            <option value="workshop">
                                                Workshop
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Duration -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                        >
                                            Süre (Dakika)
                                        </label>
                                        <input
                                            v-model.number="
                                                form.duration_minutes
                                            "
                                            type="number"
                                            min="1"
                                            max="480"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                                            placeholder="60"
                                        />
                                    </div>

                                    <!-- Language -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                        >
                                            Dil
                                        </label>
                                        <select
                                            v-model="form.language"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                                        >
                                            <option value="">Dil Seçin</option>
                                            <option value="tr">Türkçe</option>
                                            <option value="en">
                                                İngilizce
                                            </option>
                                            <option value="de">Almanca</option>
                                            <option value="fr">
                                                Fransızca
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Speakers Card -->
                        <div
                            class="bg-white dark:bg-gray-800 shadow-sm rounded-lg"
                        >
                            <div
                                class="px-8 py-6 border-b border-gray-200 dark:border-gray-700"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2
                                            class="text-xl font-semibold text-gray-900 dark:text-white"
                                        >
                                            Konuşmacılar
                                        </h2>
                                        <p
                                            class="text-gray-600 dark:text-gray-400 mt-1"
                                        >
                                            Sunumda yer alacak konuşmacıları
                                            ekleyin ve rollerini belirleyin
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="addSpeaker"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors"
                                    >
                                        <PlusIcon class="h-4 w-4 mr-2" />
                                        Konuşmacı Ekle
                                    </button>
                                </div>
                            </div>

                            <div class="p-8">
                                <!-- Speaker List -->
                                <div
                                    v-if="form.speakers.length === 0"
                                    class="text-center py-12 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg"
                                >
                                    <UserGroupIcon
                                        class="h-16 w-16 text-gray-400 mx-auto mb-4"
                                    />
                                    <h3
                                        class="text-lg font-medium text-gray-900 dark:text-white mb-2"
                                    >
                                        Henüz konuşmacı eklenmedi
                                    </h3>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Sunuma konuşmacı eklemek için yukarıdaki
                                        butonu kullanın
                                    </p>
                                </div>

                                <div v-else class="space-y-4">
                                    <div
                                        v-for="(
                                            speaker, index
                                        ) in form.speakers"
                                        :key="index"
                                        class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div
                                            class="grid grid-cols-1 lg:grid-cols-4 gap-4"
                                        >
                                            <!-- Participant Selection -->
                                            <div class="lg:col-span-2">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                                >
                                                    Katılımcı
                                                </label>
                                                <select
                                                    v-model="
                                                        speaker.participant_id
                                                    "
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                                                >
                                                    <option value="">
                                                        Katılımcı Seçin
                                                    </option>
                                                    <option
                                                        v-for="participant in participants"
                                                        :key="participant.id"
                                                        :value="participant.id"
                                                    >
                                                        {{
                                                            participant.first_name
                                                        }}
                                                        {{
                                                            participant.last_name
                                                        }}
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Role Selection -->
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                                >
                                                    Rol
                                                </label>
                                                <select
                                                    v-model="speaker.role"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                                                >
                                                    <option value="primary">
                                                        Ana Konuşmacı
                                                    </option>
                                                    <option value="secondary">
                                                        İkincil Konuşmacı
                                                    </option>
                                                    <option value="moderator">
                                                        Moderatör
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Actions -->
                                            <div class="flex items-end">
                                                <button
                                                    type="button"
                                                    @click="
                                                        removeSpeaker(index)
                                                    "
                                                    class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors inline-flex items-center justify-center"
                                                >
                                                    <TrashIcon
                                                        class="h-4 w-4 mr-2"
                                                    />
                                                    Kaldır
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Card -->
                        <div
                            class="bg-white dark:bg-gray-800 shadow-sm rounded-lg"
                        >
                            <div
                                class="px-8 py-6 border-b border-gray-200 dark:border-gray-700"
                            >
                                <h2
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    Notlar
                                </h2>
                                <p
                                    class="text-gray-600 dark:text-gray-400 mt-1"
                                >
                                    İç notlar ve ek bilgiler ekleyin (opsiyonel)
                                </p>
                            </div>

                            <div class="p-8">
                                <textarea
                                    v-model="form.notes"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500"
                                    placeholder="İç notlar (opsiyonel)"
                                    maxlength="1000"
                                ></textarea>
                                <p class="text-gray-500 text-sm mt-2">
                                    {{ form.notes?.length || 0 }}/1000 karakter
                                </p>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Right Column - Sidebar (1/4 width on xl screens) -->
                <div class="xl:col-span-1 space-y-6">
                    <!-- Action Buttons -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            İşlemler
                        </h3>

                        <div class="space-y-3">
                            <button
                                @click="submit"
                                :disabled="form.processing"
                                class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-600 hover:bg-gray-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition-colors"
                            >
                                <span
                                    v-if="form.processing"
                                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
                                ></span>
                                <CheckIcon v-else class="h-4 w-4 mr-2" />
                                {{
                                    form.processing
                                        ? "Güncelleniyor..."
                                        : "Değişiklikleri Kaydet"
                                }}
                            </button>

                            <button
                                type="button"
                                @click="duplicatePresentation"
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors"
                            >
                                <DocumentDuplicateIcon class="h-4 w-4 mr-2" />
                                Kopyala
                            </button>

                            <button
                                type="button"
                                @click="deletePresentation"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors"
                            >
                                <TrashIcon class="h-4 w-4 mr-2" />
                                Sil
                            </button>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Hızlı Bilgiler
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400"
                                    >ID</span
                                >
                                <span
                                    class="text-gray-900 dark:text-white font-medium"
                                    >#{{ presentation.id }}</span
                                >
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400"
                                    >Konuşmacı</span
                                >
                                <span
                                    class="text-gray-900 dark:text-white font-medium"
                                    >{{ form.speakers.length }}</span
                                >
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400"
                                    >Durum</span
                                >
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
                                >
                                    Aktif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Gezinti
                        </h3>

                        <div class="space-y-2">
                            <Link
                                :href="
                                    route(
                                        'admin.presentations.show',
                                        presentation.id
                                    )
                                "
                                class="block px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                            >
                                <EyeIcon class="h-4 w-4 inline mr-2" />
                                Sunumu Görüntüle
                            </Link>

                            <Link
                                :href="route('admin.presentations.index')"
                                class="block px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                            >
                                <ArrowLeftIcon class="h-4 w-4 inline mr-2" />
                                Sunum Listesi
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4"
            >
                <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                >
                    Sunumu Sil
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Bu sunumu silmek istediğinize emin misiniz? Bu işlem geri
                    alınamaz.
                </p>
                <div class="flex items-center justify-end space-x-3">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        İptal
                    </button>
                    <button
                        @click="confirmDelete"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg"
                    >
                        Sil
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    PlusIcon,
    TrashIcon,
    UserGroupIcon,
    DocumentDuplicateIcon,
    EyeIcon,
    ArrowLeftIcon,
    CheckIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    presentation: {
        type: Object,
        required: true,
    },
    programSessions: {
        type: Array,
        default: () => [],
    },
    participants: {
        type: Array,
        default: () => [],
    },
});

// State
const showDeleteModal = ref(false);

// Form - Initialize with existing data
const form = useForm({
    program_session_id: props.presentation.program_session_id,
    title: props.presentation.title,
    abstract: props.presentation.abstract || "",
    duration_minutes: props.presentation.duration_minutes,
    presentation_type: props.presentation.presentation_type || "",
    language: props.presentation.language || "",
    notes: props.presentation.notes || "",
    sort_order: props.presentation.sort_order || 0,
    speakers: props.presentation.speakers || [],
});

// Computed
const breadcrumbs = computed(() => [
    { label: "Ana Sayfa", href: route("admin.dashboard") },
    { label: "Sunumlar", href: route("admin.presentations.index") },
    {
        label: props.presentation.title,
        href: route("admin.presentations.show", props.presentation.id),
    },
    { label: "Düzenle", href: null },
]);

const groupedSessions = computed(() => {
    const grouped = {};

    props.programSessions.forEach((session) => {
        const event = session.venue?.eventDay?.event;
        if (!event) return;

        if (!grouped[event.id]) {
            grouped[event.id] = {
                id: event.id,
                name: event.name,
                sessions: [],
            };
        }

        grouped[event.id].sessions.push(session);
    });

    return Object.values(grouped);
});

// Methods
const formatTime = (timeString) => {
    if (!timeString) return "";
    try {
        return timeString.substring(0, 5); // HH:MM format
    } catch (error) {
        return timeString;
    }
};

const addSpeaker = () => {
    form.speakers.push({
        participant_id: "",
        role: "primary",
        sort_order: form.speakers.length,
    });
};

const removeSpeaker = (index) => {
    form.speakers.splice(index, 1);
    // Update sort orders
    form.speakers.forEach((speaker, idx) => {
        speaker.sort_order = idx;
    });
};

const submit = () => {
    form.put(route("admin.presentations.update", props.presentation.id), {
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (errors) => {
            console.error("Form errors:", errors);
        },
    });
};

const duplicatePresentation = () => {
    router.post(
        route("admin.presentations.duplicate", props.presentation.id),
        {},
        {
            onSuccess: () => {
                // Redirect handled by controller
            },
        }
    );
};

const deletePresentation = () => {
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    router.delete(route("admin.presentations.destroy", props.presentation.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            // Redirect handled by controller
        },
    });
};
</script>

<style scoped>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    @apply bg-gray-100 dark:bg-gray-800;
}

::-webkit-scrollbar-thumb {
    @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
    @apply bg-gray-400 dark:bg-gray-500;
}
</style>
