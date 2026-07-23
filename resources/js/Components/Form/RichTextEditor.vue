<template>
    <div
        class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 overflow-hidden"
        :class="{ 'ring-2 ring-red-500/30 border-red-400': hasError }"
    >
        <div v-if="editor" class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
            <div class="flex flex-wrap items-center gap-1 px-2 py-2">
                <button
                    v-for="item in primaryToolbar"
                    :key="item.key"
                    type="button"
                    class="toolbar-btn"
                    :class="{ 'toolbar-btn-active': item.isActive?.() }"
                    :title="item.title"
                    :disabled="item.disabled?.()"
                    @click="item.action()"
                >
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="h-4 w-4"
                    />
                    <span v-else class="text-sm font-medium">{{ item.label }}</span>
                </button>

                <span class="mx-1 h-5 w-px bg-slate-300 dark:bg-slate-600" />

                <button
                    v-for="item in headingToolbar"
                    :key="item.key"
                    type="button"
                    class="toolbar-btn min-w-[2rem]"
                    :class="{ 'toolbar-btn-active': item.isActive?.() }"
                    :title="item.title"
                    @click="item.action()"
                >
                    {{ item.label }}
                </button>

                <span class="mx-1 h-5 w-px bg-slate-300 dark:bg-slate-600" />

                <button
                    v-for="item in listToolbar"
                    :key="item.key"
                    type="button"
                    class="toolbar-btn"
                    :class="{ 'toolbar-btn-active': item.isActive?.() }"
                    :title="item.title"
                    @click="item.action()"
                >
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="h-4 w-4"
                    />
                    <span v-else class="text-sm font-medium">{{ item.label }}</span>
                </button>

                <span class="mx-1 h-5 w-px bg-slate-300 dark:bg-slate-600" />

                <button
                    v-for="item in alignToolbar"
                    :key="item.key"
                    type="button"
                    class="toolbar-btn"
                    :class="{ 'toolbar-btn-active': item.isActive?.() }"
                    :title="item.title"
                    @click="item.action()"
                >
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="h-4 w-4"
                    />
                    <span v-else class="text-sm font-medium">{{ item.label }}</span>
                </button>

                <span class="mx-1 h-5 w-px bg-slate-300 dark:bg-slate-600" />

                <button
                    v-for="item in blockToolbar"
                    :key="item.key"
                    type="button"
                    class="toolbar-btn"
                    :class="{ 'toolbar-btn-active': item.isActive?.() }"
                    :title="item.title"
                    @click="item.action()"
                >
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="h-4 w-4"
                    />
                    <span v-else class="text-sm font-medium">{{ item.label }}</span>
                </button>

                <span class="mx-1 h-5 w-px bg-slate-300 dark:bg-slate-600" />

                <button
                    v-for="item in mediaToolbar"
                    :key="item.key"
                    type="button"
                    class="toolbar-btn"
                    :class="{ 'toolbar-btn-active': item.isActive?.() }"
                    :title="item.title"
                    :disabled="item.disabled?.()"
                    @click="item.action()"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                </button>

                <span class="mx-1 h-5 w-px bg-slate-300 dark:bg-slate-600" />

                <button
                    type="button"
                    class="toolbar-btn"
                    title="Tablo ekle"
                    @click="insertTable"
                >
                    <TableCellsIcon class="h-4 w-4" />
                </button>

                <span class="mx-1 h-5 w-px bg-slate-300 dark:bg-slate-600" />

                <button
                    type="button"
                    class="toolbar-btn"
                    title="Biçimlendirmeyi temizle"
                    @click="clearFormatting"
                >
                    <NoSymbolIcon class="h-4 w-4" />
                </button>

                <button
                    v-for="item in historyToolbar"
                    :key="item.key"
                    type="button"
                    class="toolbar-btn"
                    :title="item.title"
                    :disabled="item.disabled?.()"
                    @click="item.action()"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                </button>
            </div>

            <div
                v-if="editor.isActive('table')"
                class="flex flex-wrap items-center gap-1 border-t border-slate-200 dark:border-slate-700 px-2 py-2 bg-slate-100/80 dark:bg-slate-900/50"
            >
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 mr-1">
                    Tablo
                </span>
                <button
                    v-for="item in tableToolbar"
                    :key="item.key"
                    type="button"
                    class="toolbar-btn"
                    :title="item.title"
                    @click="item.action()"
                >
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="h-4 w-4"
                    />
                    <span v-else class="text-xs font-medium">{{ item.label }}</span>
                </button>
            </div>
        </div>

        <EditorContent
            :editor="editor"
            class="rich-text-content min-h-[220px] px-4 py-3 text-sm text-slate-900 dark:text-slate-100"
        />
    </div>

    <p v-if="hasError" class="mt-1 text-xs text-red-600 dark:text-red-400">
        {{ errorMessage }}
    </p>
</template>

<script setup>
import { computed, onBeforeUnmount, watch } from "vue";
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Link from "@tiptap/extension-link";
import Underline from "@tiptap/extension-underline";
import Placeholder from "@tiptap/extension-placeholder";
import TextAlign from "@tiptap/extension-text-align";
import Highlight from "@tiptap/extension-highlight";
import Image from "@tiptap/extension-image";
import { Table, TableRow, TableCell, TableHeader } from "@tiptap/extension-table";
import {
    ArrowUturnLeftIcon,
    ArrowUturnRightIcon,
    Bars3BottomLeftIcon,
    Bars3CenterLeftIcon,
    Bars3Icon,
    ChatBubbleBottomCenterTextIcon,
    CodeBracketIcon,
    LinkIcon,
    LinkSlashIcon,
    ListBulletIcon,
    MinusIcon,
    NoSymbolIcon,
    PhotoIcon,
    TableCellsIcon,
    TrashIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "İçeriği buraya yazın...",
    },
    errorMessage: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const hasError = computed(() => Boolean(props.errorMessage));

const editor = useEditor({
    content: props.modelValue || "",
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3, 4],
            },
        }),
        Underline,
        Highlight.configure({
            multicolor: false,
        }),
        TextAlign.configure({
            types: ["heading", "paragraph"],
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: "text-indigo-600 underline",
            },
        }),
        Image.configure({
            HTMLAttributes: {
                class: "rounded-lg max-w-full h-auto my-4",
            },
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
        Table.configure({
            resizable: true,
        }),
        TableRow,
        TableHeader,
        TableCell,
    ],
    editorProps: {
        attributes: {
            class: "prose prose-sm dark:prose-invert max-w-none focus:outline-none min-h-[180px]",
        },
    },
    onUpdate: ({ editor: currentEditor }) => {
        emit("update:modelValue", currentEditor.getHTML());
    },
});

const run = (callback) => {
    if (!editor.value) {
        return;
    }

    callback(editor.value.chain().focus()).run();
};

const primaryToolbar = computed(() => [
    {
        key: "bold",
        title: "Kalın (Ctrl+B)",
        label: "B",
        isActive: () => editor.value?.isActive("bold"),
        action: () => run((chain) => chain.toggleBold()),
    },
    {
        key: "italic",
        title: "İtalik (Ctrl+I)",
        label: "I",
        isActive: () => editor.value?.isActive("italic"),
        action: () => run((chain) => chain.toggleItalic()),
    },
    {
        key: "underline",
        title: "Altı çizili (Ctrl+U)",
        label: "U",
        isActive: () => editor.value?.isActive("underline"),
        action: () => run((chain) => chain.toggleUnderline()),
    },
    {
        key: "strike",
        title: "Üstü çizili",
        label: "S",
        isActive: () => editor.value?.isActive("strike"),
        action: () => run((chain) => chain.toggleStrike()),
    },
    {
        key: "highlight",
        title: "Vurgula",
        label: "HL",
        isActive: () => editor.value?.isActive("highlight"),
        action: () => run((chain) => chain.toggleHighlight()),
    },
    {
        key: "code",
        title: "Satır içi kod",
        icon: CodeBracketIcon,
        isActive: () => editor.value?.isActive("code"),
        action: () => run((chain) => chain.toggleCode()),
    },
]);

const headingToolbar = computed(() => [
    {
        key: "h1",
        title: "Başlık 1",
        label: "H1",
        isActive: () => editor.value?.isActive("heading", { level: 1 }),
        action: () => run((chain) => chain.toggleHeading({ level: 1 })),
    },
    {
        key: "h2",
        title: "Başlık 2",
        label: "H2",
        isActive: () => editor.value?.isActive("heading", { level: 2 }),
        action: () => run((chain) => chain.toggleHeading({ level: 2 })),
    },
    {
        key: "h3",
        title: "Başlık 3",
        label: "H3",
        isActive: () => editor.value?.isActive("heading", { level: 3 }),
        action: () => run((chain) => chain.toggleHeading({ level: 3 })),
    },
    {
        key: "h4",
        title: "Başlık 4",
        label: "H4",
        isActive: () => editor.value?.isActive("heading", { level: 4 }),
        action: () => run((chain) => chain.toggleHeading({ level: 4 })),
    },
    {
        key: "paragraph",
        title: "Paragraf",
        label: "P",
        isActive: () => editor.value?.isActive("paragraph"),
        action: () => run((chain) => chain.setParagraph()),
    },
]);

const listToolbar = computed(() => [
    {
        key: "bulletList",
        title: "Madde listesi",
        icon: ListBulletIcon,
        isActive: () => editor.value?.isActive("bulletList"),
        action: () => run((chain) => chain.toggleBulletList()),
    },
    {
        key: "orderedList",
        title: "Numaralı liste",
        label: "1.",
        isActive: () => editor.value?.isActive("orderedList"),
        action: () => run((chain) => chain.toggleOrderedList()),
    },
]);

const alignToolbar = computed(() => [
    {
        key: "alignLeft",
        title: "Sola hizala",
        icon: Bars3BottomLeftIcon,
        isActive: () => editor.value?.isActive({ textAlign: "left" }),
        action: () => run((chain) => chain.setTextAlign("left")),
    },
    {
        key: "alignCenter",
        title: "Ortala",
        icon: Bars3CenterLeftIcon,
        isActive: () => editor.value?.isActive({ textAlign: "center" }),
        action: () => run((chain) => chain.setTextAlign("center")),
    },
    {
        key: "alignRight",
        title: "Sağa hizala",
        icon: Bars3Icon,
        isActive: () => editor.value?.isActive({ textAlign: "right" }),
        action: () => run((chain) => chain.setTextAlign("right")),
    },
    {
        key: "alignJustify",
        title: "Yasla",
        label: "≡",
        isActive: () => editor.value?.isActive({ textAlign: "justify" }),
        action: () => run((chain) => chain.setTextAlign("justify")),
    },
]);

const blockToolbar = computed(() => [
    {
        key: "blockquote",
        title: "Alıntı bloğu",
        icon: ChatBubbleBottomCenterTextIcon,
        isActive: () => editor.value?.isActive("blockquote"),
        action: () => run((chain) => chain.toggleBlockquote()),
    },
    {
        key: "codeBlock",
        title: "Kod bloğu",
        icon: CodeBracketIcon,
        isActive: () => editor.value?.isActive("codeBlock"),
        action: () => run((chain) => chain.toggleCodeBlock()),
    },
    {
        key: "horizontalRule",
        title: "Yatay çizgi",
        icon: MinusIcon,
        action: () => run((chain) => chain.setHorizontalRule()),
    },
]);

const mediaToolbar = computed(() => [
    {
        key: "link",
        title: "Link ekle",
        icon: LinkIcon,
        isActive: () => editor.value?.isActive("link"),
        action: setLink,
    },
    {
        key: "unlink",
        title: "Link kaldır",
        icon: LinkSlashIcon,
        disabled: () => !editor.value?.isActive("link"),
        action: () => run((chain) => chain.unsetLink()),
    },
    {
        key: "image",
        title: "Görsel ekle",
        icon: PhotoIcon,
        action: setImage,
    },
]);

const historyToolbar = computed(() => [
    {
        key: "undo",
        title: "Geri al",
        icon: ArrowUturnLeftIcon,
        disabled: () => !editor.value?.can().chain().focus().undo().run(),
        action: () => run((chain) => chain.undo()),
    },
    {
        key: "redo",
        title: "Yinele",
        icon: ArrowUturnRightIcon,
        disabled: () => !editor.value?.can().chain().focus().redo().run(),
        action: () => run((chain) => chain.redo()),
    },
]);

const tableToolbar = computed(() => [
    {
        key: "addColBefore",
        title: "Sola sütun ekle",
        label: "+S←",
        action: () => run((chain) => chain.addColumnBefore()),
    },
    {
        key: "addColAfter",
        title: "Sağa sütun ekle",
        label: "+S→",
        action: () => run((chain) => chain.addColumnAfter()),
    },
    {
        key: "deleteCol",
        title: "Sütun sil",
        label: "−S",
        action: () => run((chain) => chain.deleteColumn()),
    },
    {
        key: "addRowBefore",
        title: "Üste satır ekle",
        label: "+S↑",
        action: () => run((chain) => chain.addRowBefore()),
    },
    {
        key: "addRowAfter",
        title: "Alta satır ekle",
        label: "+S↓",
        action: () => run((chain) => chain.addRowAfter()),
    },
    {
        key: "deleteRow",
        title: "Satır sil",
        label: "−Sat",
        action: () => run((chain) => chain.deleteRow()),
    },
    {
        key: "deleteTable",
        title: "Tabloyu sil",
        icon: TrashIcon,
        action: () => run((chain) => chain.deleteTable()),
    },
]);

watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value) {
            return;
        }

        const currentHtml = editor.value.getHTML();
        const normalizedValue = value || "<p></p>";

        if (currentHtml === normalizedValue) {
            return;
        }

        editor.value.commands.setContent(normalizedValue, { emitUpdate: false });
    }
);

const setLink = () => {
    if (!editor.value) {
        return;
    }

    const previousUrl = editor.value.getAttributes("link").href;
    const url = window.prompt("Link URL'si girin:", previousUrl || "https://");

    if (url === null) {
        return;
    }

    if (url === "") {
        editor.value.chain().focus().extendMarkRange("link").unsetLink().run();
        return;
    }

    editor.value
        .chain()
        .focus()
        .extendMarkRange("link")
        .setLink({ href: url })
        .run();
};

const setImage = () => {
    if (!editor.value) {
        return;
    }

    const url = window.prompt("Görsel URL'si girin:", "https://");

    if (!url) {
        return;
    }

    editor.value.chain().focus().setImage({ src: url }).run();
};

const insertTable = () => {
    editor.value
        ?.chain()
        .focus()
        .insertTable({ rows: 3, cols: 3, withHeaderRow: true })
        .run();
};

const clearFormatting = () => {
    editor.value
        ?.chain()
        .focus()
        .clearNodes()
        .unsetAllMarks()
        .run();
};

onBeforeUnmount(() => {
    editor.value?.destroy();
});
</script>

<style scoped>
.toolbar-btn {
    @apply inline-flex items-center justify-center rounded-md px-2 py-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed;
}

.toolbar-btn-active {
    @apply bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white;
}

:deep(.rich-text-content .ProseMirror) {
    min-height: 180px;
}

:deep(.rich-text-content .ProseMirror p.is-editor-empty:first-child::before) {
    color: rgb(148 163 184);
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}

:deep(.rich-text-content table) {
    border-collapse: collapse;
    margin: 1rem 0;
    width: 100%;
}

:deep(.rich-text-content th),
:deep(.rich-text-content td) {
    border: 1px solid rgb(203 213 225);
    padding: 0.5rem 0.75rem;
}

:deep(.dark .rich-text-content th),
:deep(.dark .rich-text-content td) {
    border-color: rgb(71 85 105);
}

:deep(.rich-text-content mark) {
    background-color: rgb(254 240 138);
    border-radius: 0.125rem;
    padding: 0 0.125rem;
}

:deep(.dark .rich-text-content mark) {
    background-color: rgb(113 63 18);
    color: rgb(254 243 199);
}

:deep(.rich-text-content blockquote) {
    border-left: 4px solid rgb(148 163 184);
    margin: 1rem 0;
    padding-left: 1rem;
    color: rgb(71 85 105);
}

:deep(.dark .rich-text-content blockquote) {
    border-left-color: rgb(100 116 139);
    color: rgb(203 213 225);
}

:deep(.rich-text-content pre) {
    background: rgb(241 245 249);
    border-radius: 0.5rem;
    overflow-x: auto;
    padding: 0.75rem 1rem;
}

:deep(.dark .rich-text-content pre) {
    background: rgb(15 23 42);
}

:deep(.rich-text-content hr) {
    border: none;
    border-top: 1px solid rgb(203 213 225);
    margin: 1.5rem 0;
}

:deep(.dark .rich-text-content hr) {
    border-top-color: rgb(71 85 105);
}
</style>
