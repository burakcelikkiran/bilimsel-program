<!-- SearchableSelect.vue - Aranabilir dropdown component -->
<template>
    <div class="relative" data-searchable-select>
        <!-- Search Input -->
        <div class="relative">
            <input
                ref="searchInput"
                v-model="displayValue"
                type="text"
                :placeholder="placeholder"
                :class="[
                    'w-full px-4 py-3 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200 cursor-pointer',
                    hasError
                        ? 'border-red-500'
                        : 'border-gray-300 dark:border-gray-600',
                    isOpen ? 'rounded-b-none border-b-0' : '',
                ]"
                @click="handleInputClick"
                @keydown.down.prevent="navigateDown"
                @keydown.up.prevent="navigateUp"
                @keydown.enter.prevent="selectHighlighted"
                @keydown.escape="closeDropdown"
                readonly
            />

            <!-- Dropdown Arrow -->
            <div
                class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer"
                @click="toggleDropdown"
            >
                <ChevronDownIcon
                    :class="[
                        'h-5 w-5 text-gray-400 transition-transform duration-200 hover:text-gray-600',
                        isOpen ? 'transform rotate-180' : '',
                    ]"
                />
            </div>

            <!-- Clear Button -->
            <button
                v-if="selectedOption && !readonly"
                type="button"
                @click="clearSelection"
                class="absolute inset-y-0 right-8 flex items-center pr-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <XMarkIcon class="h-4 w-4" />
            </button>
        </div>

        <!-- Dropdown Options -->
        <div
            v-show="isOpen"
            class="absolute z-50 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 border-t-0 rounded-b-lg shadow-lg max-h-64 overflow-y-auto"
        >
            <!-- Search Input Inside Dropdown -->
            <div class="p-3 border-b border-gray-200 dark:border-gray-600">
                <input
                    ref="searchInputDropdown"
                    v-model="searchQuery"
                    type="text"
                    placeholder="Oturum adı ile ara..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm"
                    @keydown.down.prevent="navigateDown"
                    @keydown.up.prevent="navigateUp"
                    @keydown.enter.prevent="selectHighlighted"
                    @keydown.escape="closeDropdown"
                    @click.stop
                />
            </div>

            <!-- Grouped Options -->
            <div v-if="filteredOptions.length > 0" class="py-1">
                <template v-for="group in filteredOptions" :key="group.id">
                    <!-- Group Header -->
                    <div
                        class="px-4 py-2 bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-600"
                    >
                        {{ group.name }}
                    </div>

                    <!-- Group Items -->
                    <div
                        v-for="(item, itemIndex) in group.sessions"
                        :key="item.id"
                        :ref="
                            (el) => setOptionRef(el, group.id + '_' + itemIndex)
                        "
                        @click="selectOption(item)"
                        @mouseenter="
                            highlightedIndex = getGlobalIndex(
                                group.id,
                                itemIndex
                            )
                        "
                        :class="[
                            'px-4 py-3 cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-b-0',
                            highlightedIndex ===
                            getGlobalIndex(group.id, itemIndex)
                                ? 'bg-gray-100 dark:bg-gray-600'
                                : 'hover:bg-gray-50 dark:hover:bg-gray-600',
                        ]"
                    >
                        <div class="flex flex-col">
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{ item.title }}
                            </span>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                            >
                                {{ item.venue?.display_name }} •
                                {{ formatTime(item.start_time) }} -
                                {{ formatTime(item.end_time) }}
                                <span
                                    v-if="item.session_type_display"
                                    class="ml-2 px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded-full text-xs"
                                >
                                    {{ item.session_type_display }}
                                </span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- No Results -->
            <div
                v-else-if="searchQuery.trim()"
                class="px-4 py-6 text-center text-gray-500 dark:text-gray-400"
            >
                <MagnifyingGlassIcon
                    class="h-8 w-8 mx-auto mb-2 text-gray-300 dark:text-gray-600"
                />
                <p class="text-sm">"{{ searchQuery }}" için sonuç bulunamadı</p>
                <p class="text-xs mt-1">Farklı bir arama terimi deneyin</p>
            </div>

            <!-- No Options Available -->
            <div
                v-else
                class="px-4 py-6 text-center text-gray-500 dark:text-gray-400"
            >
                <MagnifyingGlassIcon
                    class="h-8 w-8 mx-auto mb-2 text-gray-300 dark:text-gray-600"
                />
                <p class="text-sm">Henüz program oturumu bulunmuyor</p>
                <p class="text-xs mt-1">Önce program oturumları oluşturun</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    ref,
    computed,
    watch,
    nextTick,
    onMounted,
    onBeforeUnmount,
} from "vue";
import {
    ChevronDownIcon,
    XMarkIcon,
    MagnifyingGlassIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: null,
    },
    options: {
        type: Array,
        required: true,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: "Seçim yapın...",
    },
    hasError: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

// Reactive state
const searchQuery = ref("");
const isOpen = ref(false);
const highlightedIndex = ref(-1);
const searchInput = ref(null);
const searchInputDropdown = ref(null);
const optionRefs = ref(new Map());

// Set option ref for keyboard navigation
const setOptionRef = (el, key) => {
    if (el) {
        optionRefs.value.set(key, el);
    }
};

// Find selected option
const selectedOption = computed(() => {
    if (!props.modelValue) return null;

    for (const group of props.options) {
        const session = group.sessions?.find((s) => s.id == props.modelValue);
        if (session) return session;
    }
    return null;
});

// Display value for main input
const displayValue = computed({
    get() {
        if (isOpen.value) {
            return searchQuery.value;
        }
        return selectedOption.value
            ? `${selectedOption.value.title} - ${
                  selectedOption.value.venue?.display_name || ""
              }`
            : "";
    },
    set(value) {
        searchQuery.value = value;
    },
});

// Filter options based on search query
const filteredOptions = computed(() => {
    // Her zaman tüm options'ları göster
    if (!searchQuery.value.trim()) {
        return props.options;
    }

    // Arama varsa filtrele
    const query = searchQuery.value.toLowerCase().trim();

    return props.options
        .map((group) => ({
            ...group,
            sessions:
                group.sessions?.filter((session) => {
                    return (
                        session.title?.toLowerCase().includes(query) ||
                        session.venue?.display_name
                            ?.toLowerCase()
                            .includes(query) ||
                        session.session_type_display
                            ?.toLowerCase()
                            .includes(query)
                    );
                }) || [],
        }))
        .filter((group) => group.sessions.length > 0);
});

// Get global index for keyboard navigation
const getGlobalIndex = (groupId, itemIndex) => {
    let globalIndex = 0;

    for (const group of filteredOptions.value) {
        if (group.id === groupId) {
            return globalIndex + itemIndex;
        }
        globalIndex += group.sessions.length;
    }

    return -1;
};

// Get total items count
const totalItems = computed(() => {
    return filteredOptions.value.reduce((total, group) => {
        return total + group.sessions.length;
    }, 0);
});

// Format time helper
const formatTime = (time) => {
    if (!time) return "";

    try {
        // Handle different time formats
        if (time.includes(":")) {
            const parts = time.split(":");
            return `${parts[0]}:${parts[1]}`;
        }
        return time;
    } catch (error) {
        return time;
    }
};

// Open dropdown
const openDropdown = async () => {
    if (props.readonly) return;

    isOpen.value = true;
    highlightedIndex.value = -1;

    // Arama kutusunu temizle ki tüm seçenekler görünsün
    searchQuery.value = "";

    await nextTick();

    // Focus search input in dropdown
    if (searchInputDropdown.value) {
        searchInputDropdown.value.focus();
    }

    // If there's a selected option, highlight it
    if (selectedOption.value) {
        // Find the index of selected option
        let globalIndex = 0;
        for (const group of filteredOptions.value) {
            for (let i = 0; i < group.sessions.length; i++) {
                if (group.sessions[i].id == props.modelValue) {
                    highlightedIndex.value = globalIndex;
                    scrollToHighlighted();
                    return;
                }
                globalIndex++;
            }
        }
    }
};

// Close dropdown
const closeDropdown = () => {
    isOpen.value = false;
    searchQuery.value = "";
    highlightedIndex.value = -1;
};

// Handle input click
const handleInputClick = (event) => {
    event.preventDefault();
    if (!isOpen.value) {
        openDropdown();
    }
};

// Toggle dropdown (for arrow click)
const toggleDropdown = (event) => {
    event.preventDefault();
    event.stopPropagation();
    if (isOpen.value) {
        closeDropdown();
    } else {
        openDropdown();
    }
};

// Navigate down in options
const navigateDown = () => {
    if (highlightedIndex.value < totalItems.value - 1) {
        highlightedIndex.value++;
        scrollToHighlighted();
    }
};

// Navigate up in options
const navigateUp = () => {
    if (highlightedIndex.value > 0) {
        highlightedIndex.value--;
        scrollToHighlighted();
    }
};

// Scroll to highlighted option
const scrollToHighlighted = async () => {
    await nextTick();

    // Find the highlighted option element
    let currentIndex = 0;
    for (const group of filteredOptions.value) {
        for (let i = 0; i < group.sessions.length; i++) {
            if (currentIndex === highlightedIndex.value) {
                const optionKey = group.id + "_" + i;
                const element = optionRefs.value.get(optionKey);
                if (element) {
                    element.scrollIntoView({ block: "nearest" });
                }
                return;
            }
            currentIndex++;
        }
    }
};

// Select highlighted option
const selectHighlighted = () => {
    if (highlightedIndex.value >= 0) {
        // Find the highlighted option
        let currentIndex = 0;
        for (const group of filteredOptions.value) {
            for (const session of group.sessions) {
                if (currentIndex === highlightedIndex.value) {
                    selectOption(session);
                    return;
                }
                currentIndex++;
            }
        }
    }
};

// Select an option
const selectOption = (option) => {
    emit("update:modelValue", option.id);
    emit("change", option);
    closeDropdown();
};

// Clear selection
const clearSelection = () => {
    emit("update:modelValue", null);
    emit("change", null);
    searchQuery.value = "";
};

// Watch for external value changes
watch(
    () => props.modelValue,
    (newValue) => {
        if (!newValue) {
            searchQuery.value = "";
        }
    },
    { immediate: true }
);

// Close dropdown when clicking outside
onMounted(() => {
    const handleClickOutside = (event) => {
        const component = event.target.closest("[data-searchable-select]");
        if (!component && isOpen.value) {
            closeDropdown();
        }
    };

    document.addEventListener("click", handleClickOutside);

    // Cleanup on unmount
    onBeforeUnmount(() => {
        document.removeEventListener("click", handleClickOutside);
    });
});
</script>

<style scoped>
/* Custom scrollbar for dropdown */
.max-h-64::-webkit-scrollbar {
    width: 6px;
}

.max-h-64::-webkit-scrollbar-track {
    @apply bg-gray-100 dark:bg-gray-800;
}

.max-h-64::-webkit-scrollbar-thumb {
    @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

.max-h-64::-webkit-scrollbar-thumb:hover {
    @apply bg-gray-400 dark:bg-gray-500;
}
</style>
