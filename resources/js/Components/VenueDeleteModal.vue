<!-- VenueDeleteModal.vue -->
<template>
    <DialogModal :show="show" @close="$emit('cancel')">
        <template #title>
            <div class="flex items-center">
                <div
                    class="h-3 w-3 rounded-full mr-3"
                    :style="{ backgroundColor: venue?.color || '#6b7280' }"
                ></div>
                Salon Silme Onayı
            </div>
        </template>

        <template #content>
            <div v-if="venue" class="space-y-6">
                <!-- Venue Info -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">
                        {{ venue.display_name || venue.name }}
                    </h4>
                    <div
                        class="text-sm text-gray-600 dark:text-gray-400 space-y-1"
                    >
                        <div v-if="venue.capacity">
                            <span class="font-medium">Kapasite:</span>
                            {{ venue.capacity }} kişi
                        </div>
                    </div>
                </div>

                <!-- Warning for sessions -->
                <div v-if="sessionCount > 0" class="space-y-4">
                    <div
                        class="flex items-start p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
                    >
                        <div class="flex-shrink-0">
                            <ExclamationTriangleIcon
                                class="h-5 w-5 text-gray-600 dark:text-gray-400"
                            />
                        </div>
                        <div class="ml-3">
                            <h3
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Bu salon {{ sessionCount }} adet oturum içeriyor
                            </h3>
                            <p
                                class="mt-2 text-sm text-gray-600 dark:text-gray-400"
                            >
                                Bu salonu silmek için önce bu oturumları da
                                silmeniz gerekir.
                            </p>
                        </div>
                    </div>

                    <!-- Sessions List -->
                    <div class="max-h-48 overflow-y-auto">
                        <div
                            class="text-sm font-medium text-gray-900 dark:text-white mb-2"
                        >
                            İlişkili Oturumlar:
                        </div>
                        <div class="space-y-2">
                            <div
                                v-for="session in sessions"
                                :key="session.id"
                                class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
                            >
                                <div class="flex-1">
                                    <div
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ session.name }}
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                    >
                                        <span v-if="session.event_day?.event">
                                            {{ session.event_day.event.name }}
                                        </span>
                                        <span v-if="session.event_day?.date">
                                            -
                                            {{
                                                formatDate(
                                                    session.event_day.date
                                                )
                                            }}</span
                                        >
                                        <span
                                            v-if="
                                                session.start_time &&
                                                session.end_time
                                            "
                                        >
                                            ({{ session.start_time }} -
                                            {{ session.end_time }})
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cascade Delete Option -->
                    <div v-if="canCascadeDelete" class="space-y-3">
                        <div
                            class="border-t border-gray-200 dark:border-gray-700 pt-4"
                        >
                            <label class="flex items-start space-x-3">
                                <input
                                    type="checkbox"
                                    v-model="cascadeOption"
                                    class="mt-1 h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                                />
                                <div class="text-sm">
                                    <div
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        Oturumları da sil
                                    </div>
                                    <div
                                        class="text-gray-600 dark:text-gray-400"
                                    >
                                        Bu seçeneği işaretlerseniz, salon ile
                                        birlikte {{ sessionCount }} adet oturum
                                        da silinecektir.
                                        <strong
                                            class="text-gray-900 dark:text-white"
                                            >Bu işlem geri alınamaz!</strong
                                        >
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Additional Warning -->
                        <div
                            v-if="cascadeOption"
                            class="p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
                        >
                            <div class="flex items-center">
                                <ExclamationTriangleIcon
                                    class="h-4 w-4 text-gray-600 dark:text-gray-400 mr-2"
                                />
                                <span
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    Dikkat: Bu işlem {{ sessionCount }} adet
                                    oturumu kalıcı olarak silecektir!
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- No Permission Warning -->
                    <div
                        v-else
                        class="p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
                    >
                        <div class="flex items-start">
                            <ExclamationTriangleIcon
                                class="h-5 w-5 text-gray-600 dark:text-gray-400 flex-shrink-0"
                            />
                            <div class="ml-3">
                                <h3
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    Yetkisiz İşlem
                                </h3>
                                <p
                                    class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    Bu oturumları silmek için gerekli yetkiye
                                    sahip değilsiniz. Önce oturumları manuel
                                    olarak silmelisiniz.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No sessions - safe to delete -->
                <div
                    v-else
                    class="p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
                >
                    <div class="flex items-center">
                        <CheckCircleIcon
                            class="h-5 w-5 text-gray-600 dark:text-gray-400 mr-3"
                        />
                        <div>
                            <h3
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Güvenli Silme
                            </h3>
                            <p
                                class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                            >
                                Bu salon herhangi bir oturum içermediği için
                                güvenle silinebilir.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Final Confirmation -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <label class="flex items-center space-x-3">
                        <input
                            type="checkbox"
                            v-model="finalConfirm"
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            <strong
                                >"{{
                                    venue.display_name || venue.name
                                }}"</strong
                            >
                            salonunu
                            <span v-if="sessionCount > 0 && cascadeOption"
                                >ve {{ sessionCount }} adet oturumu</span
                            >
                            silmek istediğimi onaylıyorum.
                        </span>
                    </label>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-8">
                <div
                    class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-600"
                ></div>
                <span class="ml-3 text-gray-600 dark:text-gray-400"
                    >Silme işlemi gerçekleştiriliyor...</span
                >
            </div>
        </template>

        <template #footer>
            <div class="flex items-center justify-end space-x-3">
                <SecondaryButton @click="$emit('cancel')" :disabled="loading">
                    İptal
                </SecondaryButton>

                <DangerButton
                    @click="confirmDelete"
                    :disabled="!canDelete || loading"
                    :class="{
                        'opacity-50 cursor-not-allowed': !canDelete || loading,
                    }"
                >
                    <TrashIcon class="h-4 w-4 mr-2" />
                    {{
                        sessionCount > 0 && cascadeOption
                            ? `Salon ve ${sessionCount} Oturumu Sil`
                            : "Salonu Sil"
                    }}
                </DangerButton>
            </div>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, computed } from "vue";
import DialogModal from "@/Components/DialogModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {
    ExclamationTriangleIcon,
    CheckCircleIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    venue: {
        type: Object,
        default: null,
    },
    sessions: {
        type: Array,
        default: () => [],
    },
    sessionCount: {
        type: Number,
        default: 0,
    },
    canCascadeDelete: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["confirm", "cancel"]);

// Local state
const cascadeOption = ref(false);
const finalConfirm = ref(false);

// Computed
const canDelete = computed(() => {
    if (!finalConfirm.value) return false;

    // If there are sessions but no cascade delete permission
    if (props.sessionCount > 0 && !props.canCascadeDelete) return false;

    // If there are sessions and cascade delete is available, user must check cascade option
    if (
        props.sessionCount > 0 &&
        props.canCascadeDelete &&
        !cascadeOption.value
    )
        return false;

    return true;
});

// Methods
const confirmDelete = () => {
    if (!canDelete.value) return;

    emit("confirm", {
        cascadeDelete: cascadeOption.value && props.sessionCount > 0,
        confirmCascade: cascadeOption.value && props.sessionCount > 0,
    });
};

const formatDate = (date) => {
    if (!date) return "";
    return new Date(date).toLocaleDateString("tr-TR", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

// Reset state when modal closes
const resetState = () => {
    cascadeOption.value = false;
    finalConfirm.value = false;
};

// Watch for show prop changes to reset state
import { watch } from "vue";
watch(
    () => props.show,
    (newValue) => {
        if (!newValue) {
            resetState();
        }
    }
);
</script>
