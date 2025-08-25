<template>
    <AdminLayout
        :page-title="`${event.name} - Timeline Düzenleyici`"
        page-subtitle="Etkinlik programını sürükle-bırak ile düzenleyin"
        :breadcrumbs="breadcrumbs"
    >
        <Head :title="`${event.name} - Timeline Düzenleyici`" />

        <!-- Header Section -->
        <div class="mb-6">
            <div
                class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
            >
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <!-- Title & Mode Info -->
                        <div class="flex items-center space-x-4">
                            <div
                                class="h-12 w-12 bg-orange-600 rounded-xl flex items-center justify-center"
                            >
                                <CursorArrowRaysIcon
                                    class="h-8 w-8 text-white"
                                />
                            </div>
                            <div>
                                <h1
                                    class="text-2xl font-bold text-gray-900 dark:text-white"
                                >
                                    {{ event.name }}
                                </h1>
                                <p
                                    class="text-sm text-orange-600 dark:text-orange-400"
                                >
                                    <ExclamationTriangleIcon
                                        class="h-4 w-4 inline mr-1"
                                    />
                                    Düzenleme Modu - Oturumları sürükleyerek
                                    yeniden düzenleyebilirsiniz
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-3">
                            <!-- View Only Mode -->
                            <Link
                                :href="route('admin.timeline.show', event.slug)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                <EyeIcon class="h-4 w-4 mr-2" />
                                Sadece Görüntüle
                            </Link>

                            <!-- Save All Changes -->
                            <button
                                @click="saveAllChanges"
                                :disabled="!hasUnsavedChanges || saving"
                                :class="[
                                    hasUnsavedChanges && !saving
                                        ? 'bg-green-600 hover:bg-green-700 text-white'
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed dark:bg-gray-600 dark:text-gray-400',
                                ]"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                            >
                                <CheckIcon
                                    v-if="!saving"
                                    class="h-4 w-4 mr-2"
                                />
                                <div
                                    v-else
                                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
                                ></div>
                                {{
                                    saving
                                        ? "Kaydediliyor..."
                                        : "Tüm Değişiklikleri Kaydet"
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Change Summary -->
                <div
                    v-if="hasUnsavedChanges"
                    class="px-6 py-4 bg-orange-50 dark:bg-orange-900/20"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <ClockIcon class="h-5 w-5 text-orange-600" />
                            <span
                                class="text-sm font-medium text-orange-800 dark:text-orange-200"
                            >
                                {{ pendingChanges.length }} kaydedilmemiş
                                değişiklik
                            </span>
                        </div>
                        <button
                            @click="discardChanges"
                            class="text-sm text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 transition-colors"
                        >
                            Değişiklikleri İptal Et
                        </button>
                    </div>

                    <!-- Changes List -->
                    <div class="mt-3 space-y-1">
                        <div
                            v-for="change in pendingChanges.slice(0, 3)"
                            :key="change.timestamp"
                            class="text-xs text-orange-700 dark:text-orange-300"
                        >
                            • {{ formatChangeDescription(change) }}
                        </div>
                        <div
                            v-if="pendingChanges.length > 3"
                            class="text-xs text-orange-600 dark:text-orange-400"
                        >
                            ... ve {{ pendingChanges.length - 3 }} değişiklik
                            daha
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Editor -->
        <div class="space-y-8">
            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div
                    class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"
                ></div>
                <span class="ml-3 text-gray-500 dark:text-gray-400"
                    >Timeline yükleniyor...</span
                >
            </div>

            <!-- Draggable Timeline Container -->
            <div v-else>
                <div v-if="timelineData.length === 0" class="text-center py-12">
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8"
                    >
                        <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
                            <CalendarDaysIcon class="h-full w-full" />
                        </div>
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2"
                        >
                            Program bulunamadı
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Bu etkinlik için henüz program oluşturulmamış.
                        </p>
                        <Link
                            :href="route('admin.events.show', event.id)"
                            class="mt-4 inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700"
                        >
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Etkinliği Düzenle
                        </Link>
                    </div>
                </div>

                <DraggableTimelineContainer
                    v-else
                    :timeline-data="timelineData"
                    :event="event"
                    :stats="editableData?.stats || {}"
                    @session-click="handleSessionClick"
                    @presentation-click="handlePresentationClick"
                    @timeline-updated="handleTimelineUpdate"
                    @changes-pending="handleChangesPending"
                    ref="timelineContainer"
                />
            </div>
        </div>

        <!-- Conflicts and Warnings -->
        <div v-if="conflicts.length > 0" class="mt-8">
            <div
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6"
            >
                <div class="flex items-start space-x-3">
                    <ExclamationTriangleIcon
                        class="h-6 w-6 text-red-500 mt-0.5 flex-shrink-0"
                    />
                    <div class="flex-1">
                        <h3
                            class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2"
                        >
                            Tespit Edilen Çakışmalar
                        </h3>
                        <div class="space-y-2">
                            <div
                                v-for="conflict in conflicts"
                                :key="conflict.id"
                                class="bg-white dark:bg-red-900/30 rounded-lg p-3"
                            >
                                <div
                                    class="text-sm font-medium text-red-800 dark:text-red-200"
                                >
                                    {{ conflict.venue_name }} - Zaman Çakışması
                                </div>
                                <div
                                    class="text-sm text-red-600 dark:text-red-300 mt-1"
                                >
                                    "{{ conflict.session1_title }}" ile "{{
                                        conflict.session2_title
                                    }}" arasında
                                    {{ conflict.overlap_minutes }} dakika
                                    çakışma
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-8">
            <div
                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6"
            >
                <div class="flex items-start space-x-3">
                    <InformationCircleIcon
                        class="h-6 w-6 text-blue-500 mt-0.5 flex-shrink-0"
                    />
                    <div>
                        <h3
                            class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2"
                        >
                            Sürükle-Bırak Kullanım Rehberi
                        </h3>
                        <div
                            class="text-sm text-blue-700 dark:text-blue-300 space-y-1"
                        >
                            <div>
                                • Oturumları sürükleyerek farklı salonlara veya
                                zamanlara taşıyabilirsiniz
                            </div>
                            <div>• Yeşil alan: Geçerli bırakma alanı</div>
                            <div>
                                • Kırmızı alan: Çakışma nedeniyle geçersiz alan
                            </div>
                            <div>• Mavi alan: Oturumun mevcut konumu</div>
                            <div>
                                • Değişiklikler otomatik olarak kaydedilmez,
                                "Kaydet" butonuna basmanız gerekir
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DraggableTimelineContainer from "@/Components/Timeline/DraggableTimelineContainer.vue";
import {
    CursorArrowRaysIcon,
    ExclamationTriangleIcon,
    EyeIcon,
    CheckIcon,
    ClockIcon,
    CalendarDaysIcon,
    PlusIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    timelineData: {
        type: Array,
        required: true,
    },
    editableData: {
        type: Object,
        default: () => ({}),
    },
    availableVenues: {
        type: Array,
        default: () => [],
    },
    availableCategories: {
        type: Array,
        default: () => [],
    },
});

// Reactive data
const loading = ref(false);
const saving = ref(false);
const pendingChanges = ref([]);
const conflicts = ref(props.editableData?.conflicts || []);
const timelineContainer = ref(null);

// Computed
const breadcrumbs = computed(() => [
    { label: "Ana Sayfa", href: route("admin.dashboard") },
    { label: "Etkinlikler", href: route("admin.events.index") },
    {
        label: props.event.name,
        href: route("admin.events.show", props.event.slug),
    },
    { label: "Timeline", href: route("admin.timeline.show", props.event.slug) },
    { label: "Düzenle", href: null },
]);

const hasUnsavedChanges = computed(() => pendingChanges.value.length > 0);

// Methods
const handleSessionClick = (session) => {
    router.visit(route("admin.program-sessions.show", session.id));
};

const handlePresentationClick = (presentation) => {
    router.visit(route("admin.presentations.show", presentation.id));
};

const handleTimelineUpdate = (updatedData) => {
    // Timeline component'ten gelen güncellenmiş veri
    Object.assign(props.timelineData, updatedData.timeline);
    if (updatedData.conflicts) {
        conflicts.value = updatedData.conflicts;
    }
};

const handleChangesPending = (changes) => {
    pendingChanges.value = changes;
};

const saveAllChanges = async () => {
    if (!hasUnsavedChanges.value || saving.value) return;

    saving.value = true;

    try {
        // Get CSRF token safely
        let csrfToken = "";
        const csrfMetaTag = document.querySelector('meta[name="csrf-token"]');
        if (csrfMetaTag) {
            csrfToken = csrfMetaTag.content;
        } else {
            // Try to get from Inertia page props
            const page = usePage();
            csrfToken = page.props.csrf_token || "";
        }

        if (!csrfToken) {
            throw new Error("CSRF token bulunamadı");
        }

        // Transform pending changes to backend format
        const transformedChanges = [];
        const processedSessions = new Set(); // Track processed sessions to avoid duplicates
        
        console.log("Pending changes before transform:", pendingChanges.value);
        
        // Process each pending change
        pendingChanges.value.forEach(change => {
            if (change.type === "session_reordered" && change.session_order) {
                // For each session in the reordered list, create a change entry
                change.session_order.forEach((sessionId, index) => {
                    // Create unique key for this session in this venue
                    const sessionKey = `${sessionId}-${change.venue_id}`;
                    
                    if (!processedSessions.has(sessionKey)) {
                        transformedChanges.push({
                            sessionId: sessionId,
                            toVenueId: change.venue_id,
                            toDayId: change.day_id,
                            newSortOrder: index + 1,
                        });
                        processedSessions.add(sessionKey);
                    }
                });
            } else if (change.type === "session_moved") {
                // Handle session moved between venues
                const sessionKey = `${change.session_id}-${change.to_venue_id}`;
                
                if (!processedSessions.has(sessionKey)) {
                    transformedChanges.push({
                        sessionId: change.session_id,
                        toVenueId: change.to_venue_id,
                        toDayId: change.to_day_id,
                        newSortOrder: change.new_sort_order || 1,
                        newStartTime: change.new_start_time,
                        newEndTime: change.new_end_time,
                    });
                    processedSessions.add(sessionKey);
                }
            }
        });

        console.log("Transformed changes to send:", transformedChanges);

        if (transformedChanges.length === 0) {
            throw new Error("Kaydedilecek değişiklik bulunamadı");
        }

        const response = await fetch(
            route("admin.timeline.update-order", props.event.slug),
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json",
                },
                body: JSON.stringify({
                    changes: transformedChanges,
                    timeline_data: props.timelineData,
                }),
            }
        );

        console.log("Response status:", response.status);
        console.log("Response ok:", response.ok);
        
        let result;
        try {
            result = await response.json();
        } catch (e) {
            console.error("Failed to parse JSON response:", e);
            const text = await response.text();
            console.error("Response text:", text);
            throw new Error("Backend yanıtı JSON formatında değil");
        }
        
        console.log("Backend response:", result);

        if (result.success) {
            pendingChanges.value = [];
            conflicts.value = result.data?.conflicts || [];

            // Show success message
            if (window.toast) {
                window.toast.success("Tüm değişiklikler başarıyla kaydedildi!");
            }

            // Use updated data from response instead of reloading
            if (result.data?.timelineData) {
                // Update props with fresh data from server
                Object.assign(props.timelineData, result.data.timelineData);
                
                // Clear timeline container's local state
                if (timelineContainer.value) {
                    timelineContainer.value.clearPendingChanges();
                }
                
                // Force component update with cache busting
                setTimeout(() => {
                    router.reload({ 
                        only: ["timelineData", "editableData"],
                        preserveState: false,
                        replace: true
                    });
                }, 100);
            } else {
                // Fallback to full reload if no timeline data in response
                setTimeout(() => {
                    router.reload({ only: ["timelineData", "editableData"] });
                }, 1000);
            }
        } else {
            console.error("Backend error:", result);
            throw new Error(result.message || "Kaydetme sırasında hata oluştu");
        }
    } catch (error) {
        console.error("Save error:", error);
        if (window.toast) {
            window.toast.error("Değişiklikler kaydedilemedi: " + error.message);
        }
    } finally {
        saving.value = false;
    }
};

const discardChanges = () => {
    if (
        confirm("Tüm kaydedilmemiş değişiklikler iptal edilecek. Emin misiniz?")
    ) {
        // Clear pending changes first
        pendingChanges.value = [];
        
        // Force reload to get original data from server
        router.reload({
            onSuccess: () => {
                // Clear any local state after reload
                if (timelineContainer.value) {
                    timelineContainer.value.clearPendingChanges();
                }
            }
        });
    }
};

const formatChangeDescription = (change) => {
    switch (change.type) {
        case "session_moved":
            return `Oturum taşındı: ${change.session_title || "Oturum"}`;
        case "session_reordered":
            return `Oturum sırası değiştirildi`;
        default:
            return "Bilinmeyen değişiklik";
    }
};

const beforeUnloadHandler = (event) => {
    if (hasUnsavedChanges.value) {
        event.preventDefault();
        event.returnValue =
            "Kaydedilmemiş değişiklikler var. Sayfadan çıkmak istediğinizden emin misiniz?";
        return event.returnValue;
    }
};

// Lifecycle
onMounted(() => {
    window.addEventListener("beforeunload", beforeUnloadHandler);
});

onBeforeUnmount(() => {
    window.removeEventListener("beforeunload", beforeUnloadHandler);
});
</script>

<style scoped>
/* Timeline editor specific styles */
.timeline-editor {
    position: relative;
}

/* Loading overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
}

.dark .loading-overlay {
    background: rgba(17, 24, 39, 0.8);
}

/* Change summary animations */
.change-summary {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Conflict alerts */
.conflict-alert {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .timeline-editor .flex {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
