<template>
    <div class="space-y-6 pt-6 border-t border-slate-200 dark:border-slate-700">
        <div class="flex items-center space-x-4 mb-2">
            <div
                class="h-8 w-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center"
            >
                <NewspaperIcon
                    class="h-4 w-4 text-slate-600 dark:text-slate-300"
                />
            </div>
            <div>
                <h3
                    class="text-lg font-bold text-slate-900 dark:text-slate-100"
                >
                    Kongre İçerikleri
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    Genel bilgiler, davet, kurullar, kayıt ve iletişim
                    sayfalarını yönetin
                </p>
            </div>
        </div>

        <div
            class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden"
        >
            <nav
                class="flex flex-wrap gap-1 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 p-2"
                aria-label="Kongre içerik sekmeleri"
            >
                <button
                    v-for="section in pageSections"
                    :key="section.key"
                    type="button"
                    class="px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors"
                    :class="
                        activeSection === section.key
                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm ring-1 ring-slate-200 dark:ring-slate-700'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/70 dark:hover:bg-slate-900/50'
                    "
                    @click="activeSection = section.key"
                >
                    {{ section.label }}
                    <span
                        v-if="hasContent(section.key)"
                        class="ml-1.5 inline-block h-1.5 w-1.5 rounded-full bg-green-500"
                    />
                </button>
            </nav>

            <div class="p-4 sm:p-6">
                <div
                    v-for="section in pageSections"
                    :key="`editor-${section.key}`"
                    v-show="activeSection === section.key"
                    class="space-y-3"
                >
                    <div class="flex items-center justify-between gap-4">
                        <label
                            class="block text-sm font-bold text-slate-900 dark:text-slate-100"
                        >
                            {{ section.label }}
                        </label>
                    </div>

                    <RichTextEditor
                        v-model="pages[section.key]"
                        :placeholder="`${section.label} içeriğini girin...`"
                        :error-message="errors[`pages.${section.key}`]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { NewspaperIcon } from "@heroicons/vue/24/outline";
import RichTextEditor from "@/Components/Form/RichTextEditor.vue";

const props = defineProps({
    pageSections: {
        type: Array,
        required: true,
    },
    pages: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const activeSection = ref(props.pageSections[0]?.key ?? "general_info");

const hasContent = (key) => {
    const content = props.pages[key] ?? "";
    const stripped = content.replace(/<[^>]*>/g, "").trim();

    return stripped.length > 0;
};
</script>
