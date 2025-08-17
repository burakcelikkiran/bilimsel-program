<!--
📁 File Structure:
resources/js/Components/Forms/FormTextarea.vue

📁 Usage:
<FormTextarea 
  v-model="form.description"
  label="Event Description"
  placeholder="Describe your event in detail..."
  :rows="4"
  :maxlength="1000"
  show-counter
  resizable
  required
  :error-message="form.errors.description"
  help-text="This will be visible to attendees"
/>

📁 Dependencies:
- @heroicons/vue/24/outline
-->
<template>
  <div class="form-group">
    <!-- Label -->
    <label 
      v-if="label" 
      :for="textareaId" 
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Textarea Container -->
    <div class="relative">
      <!-- Textarea Element -->
      <textarea
        :id="textareaId"
        ref="textareaRef"
        v-model="textareaValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :rows="currentRows"
        :maxlength="maxlength"
        :minlength="minlength"
        class="form-textarea"
        :class="[
          textareaClasses,
          {
            'border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500': hasError,
            'border-green-300 dark:border-green-600 focus:border-green-500 focus:ring-green-500': hasSuccess && !hasError,
            'opacity-50 cursor-not-allowed': disabled,
            'bg-gray-50 dark:bg-gray-800': readonly,
            'resize-none': !resizable,
            'resize-y': resizable === 'vertical',
            'resize-x': resizable === 'horizontal',
            'resize': resizable === true
          }
        ]"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        @keydown="handleKeydown"
        @paste="handlePaste"
      />

      <!-- Auto-resize overlay (hidden) -->
      <div
        v-if="autoResize"
        ref="shadowTextarea"
        class="absolute top-0 left-0 w-full pointer-events-none opacity-0 whitespace-pre-wrap break-words"
        :class="textareaClasses"
        :style="{ 
          height: 'auto',
          minHeight: `${minRows * 1.5}rem`,
          maxHeight: maxRows ? `${maxRows * 1.5}rem` : 'none'
        }"
      >
        {{ textareaValue || placeholder }}&nbsp;
      </div>

      <!-- Loading Spinner -->
      <div v-if="loading" class="absolute top-2 right-2">
        <LoadingSpinner class="h-5 w-5 text-gray-400" />
      </div>

      <!-- Toolbar -->
      <div 
        v-if="showToolbar"
        class="absolute bottom-2 right-2 flex space-x-1 bg-white dark:bg-gray-800 rounded-md shadow-sm border border-gray-200 dark:border-gray-600 p-1"
      >
        <!-- Format Buttons -->
        <button
          v-if="allowFormatting"
          type="button"
          class="toolbar-button"
          title="Bold"
          @click="insertFormat('**', '**')"
        >
          <BoldIcon class="h-4 w-4" />
        </button>
        
        <button
          v-if="allowFormatting"
          type="button"
          class="toolbar-button"
          title="Italic"
          @click="insertFormat('*', '*')"
        >
          <ItalicIcon class="h-4 w-4" />
        </button>

        <!-- Word Count Toggle -->
        <button
          v-if="showCounter"
          type="button"
          class="toolbar-button"
          :class="{ 'text-blue-600 dark:text-blue-400': showWordCount }"
          title="Toggle word count"
          @click="showWordCount = !showWordCount"
        >
          <HashtagIcon class="h-4 w-4" />
        </button>

        <!-- Expand/Collapse -->
        <button
          v-if="allowExpand"
          type="button"
          class="toolbar-button"
          :title="isExpanded ? 'Collapse' : 'Expand'"
          @click="toggleExpand"
        >
          <component :is="isExpanded ? ChevronUpIcon : ChevronDownIcon" class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Help Text -->
    <p v-if="helpText && !hasError" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ helpText }}
    </p>

    <!-- Error Message -->
    <p v-if="hasError" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ errorMessage }}
    </p>

    <!-- Success Message -->
    <p v-if="hasSuccess && !hasError && successMessage" class="mt-1 text-sm text-green-600 dark:text-green-400">
      {{ successMessage }}
    </p>

    <!-- Counter -->
    <div v-if="showCounter" class="mt-1 flex justify-between text-xs text-gray-500 dark:text-gray-400">
      <div v-if="showWordCount" class="flex space-x-4">
        <span>Words: {{ wordCount }}</span>
        <span>Characters: {{ characterCount }}</span>
      </div>
      <div v-else></div>
      
      <div v-if="maxlength" class="text-right">
        <span :class="{ 'text-red-600 dark:text-red-400': isOverLimit }">
          {{ characterCount }} / {{ maxlength }}
        </span>
      </div>
    </div>

    <!-- Character/Word Statistics -->
    <div 
      v-if="showStats && (showWordCount || isFocused)"
      class="mt-2 text-xs text-gray-500 dark:text-gray-400 space-y-1"
    >
      <div class="flex justify-between">
        <span>Paragraphs: {{ paragraphCount }}</span>
        <span>Lines: {{ lineCount }}</span>
      </div>
      <div class="flex justify-between">
        <span>Reading time: {{ readingTime }}</span>
        <span>Characters (no spaces): {{ characterCountNoSpaces }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { 
  HashtagIcon,
  ChevronUpIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'

// Mock icons for formatting (you can replace with actual icons)
const BoldIcon = { template: '<strong>B</strong>' }
const ItalicIcon = { template: '<em>I</em>' }

// Props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  errorMessage: {
    type: String,
    default: ''
  },
  successMessage: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  rows: {
    type: Number,
    default: 3
  },
  minRows: {
    type: Number,
    default: 2
  },
  maxRows: {
    type: Number,
    default: null
  },
  maxlength: {
    type: Number,
    default: null
  },
  minlength: {
    type: Number,
    default: null
  },
  autoResize: {
    type: Boolean,
    default: false
  },
  resizable: {
    type: [Boolean, String],
    default: true,
    validator: (value) => [true, false, 'vertical', 'horizontal'].includes(value)
  },
  showCounter: {
    type: Boolean,
    default: false
  },
  showStats: {
    type: Boolean,
    default: false
  },
  showToolbar: {
    type: Boolean,
    default: false
  },
  allowFormatting: {
    type: Boolean,
    default: false
  },
  allowExpand: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'input',
  'change',
  'blur',
  'focus',
  'paste',
  'keydown'
])

// Refs
const textareaRef = ref(null)
const shadowTextarea = ref(null)
const isFocused = ref(false)
const showWordCount = ref(false)
const isExpanded = ref(false)

// Computed
const textareaId = computed(() => `textarea-${Math.random().toString(36).substr(2, 9)}`)

const textareaValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const hasError = computed(() => Boolean(props.errorMessage))
const hasSuccess = computed(() => Boolean(props.successMessage))

const characterCount = computed(() => textareaValue.value?.length || 0)
const characterCountNoSpaces = computed(() => textareaValue.value?.replace(/\s/g, '').length || 0)
const wordCount = computed(() => {
  if (!textareaValue.value) return 0
  return textareaValue.value.trim().split(/\s+/).filter(word => word.length > 0).length
})
const paragraphCount = computed(() => {
  if (!textareaValue.value) return 0
  return textareaValue.value.split(/\n\s*\n/).filter(p => p.trim().length > 0).length
})
const lineCount = computed(() => {
  if (!textareaValue.value) return 0
  return textareaValue.value.split('\n').length
})
const readingTime = computed(() => {
  const wordsPerMinute = 200
  const minutes = Math.ceil(wordCount.value / wordsPerMinute)
  return minutes === 1 ? '1 min' : `${minutes} mins`
})

const isOverLimit = computed(() => {
  return props.maxlength && characterCount.value > props.maxlength
})

const currentRows = computed(() => {
  if (isExpanded.value) {
    return Math.max(props.rows * 2, 8)
  }
  if (props.autoResize) {
    const lines = textareaValue.value ? textareaValue.value.split('\n').length : 1
    const calculatedRows = Math.max(lines, props.minRows)
    return props.maxRows ? Math.min(calculatedRows, props.maxRows) : calculatedRows
  }
  return props.rows
})

const textareaClasses = computed(() => {
  const baseClasses = [
    'block', 'w-full', 'rounded-md', 'border', 'shadow-sm', 'transition-colors', 'duration-200',
    'placeholder-gray-400', 'dark:placeholder-gray-500',
    'bg-white', 'dark:bg-gray-900',
    'text-gray-900', 'dark:text-gray-100',
    'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-0',
    'focus:border-blue-500', 'focus:ring-blue-500'
  ]

  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-3 py-2 text-sm',
    lg: 'px-4 py-3 text-base'
  }

  return [
    ...baseClasses,
    ...sizeClasses[props.size].split(' '),
    'border-gray-300', 'dark:border-gray-600'
  ]
})

// Methods
const handleInput = (event) => {
  const value = event.target.value
  textareaValue.value = value
  emit('input', value, event)
  
  if (props.autoResize) {
    nextTick(() => autoResizeTextarea())
  }
}

const handleBlur = (event) => {
  isFocused.value = false
  emit('blur', event.target.value, event)
}

const handleFocus = (event) => {
  isFocused.value = true
  emit('focus', event.target.value, event)
}

const handleKeydown = (event) => {
  emit('keydown', event)
  
  // Handle Tab key for indentation
  if (event.key === 'Tab' && props.allowFormatting) {
    event.preventDefault()
    insertText('  ') // Insert 2 spaces
  }
}

const handlePaste = (event) => {
  emit('paste', event)
}

const autoResizeTextarea = () => {
  if (!props.autoResize || !textareaRef.value) return
  
  const textarea = textareaRef.value
  textarea.style.height = 'auto'
  
  const scrollHeight = textarea.scrollHeight
  const minHeight = props.minRows * 24 // Approximate line height
  const maxHeight = props.maxRows ? props.maxRows * 24 : Infinity
  
  const newHeight = Math.min(Math.max(scrollHeight, minHeight), maxHeight)
  textarea.style.height = `${newHeight}px`
}

const insertText = (text) => {
  if (!textareaRef.value) return
  
  const textarea = textareaRef.value
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  
  const newValue = textareaValue.value.substring(0, start) + text + textareaValue.value.substring(end)
  textareaValue.value = newValue
  
  nextTick(() => {
    textarea.setSelectionRange(start + text.length, start + text.length)
    textarea.focus()
  })
}

const insertFormat = (startTag, endTag) => {
  if (!textareaRef.value) return
  
  const textarea = textareaRef.value
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const selectedText = textareaValue.value.substring(start, end)
  
  const formattedText = startTag + selectedText + endTag
  const newValue = textareaValue.value.substring(0, start) + formattedText + textareaValue.value.substring(end)
  
  textareaValue.value = newValue
  
  nextTick(() => {
    const newStart = start + startTag.length
    const newEnd = newStart + selectedText.length
    textarea.setSelectionRange(newStart, newEnd)
    textarea.focus()
  })
}

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value
}

const focus = () => {
  textareaRef.value?.focus()
}

const blur = () => {
  textareaRef.value?.blur()
}

// Watchers
watch(() => props.modelValue, () => {
  if (props.autoResize) {
    nextTick(() => autoResizeTextarea())
  }
}, { immediate: true })

// Lifecycle
onMounted(() => {
  if (props.autoResize) {
    autoResizeTextarea()
  }
})

// Expose methods
defineExpose({
  focus,
  blur,
  insertText,
  insertFormat,
  textareaRef
})
</script>

<style scoped>
.form-textarea {
  font-family: inherit;
  line-height: 1.5;
}

.toolbar-button {
  @apply p-1 rounded text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150;
}

.toolbar-button:focus {
  @apply outline-none ring-2 ring-blue-500 ring-opacity-50;
}

/* Custom scrollbar for textarea */
.form-textarea {
  scrollbar-width: thin;
  scrollbar-color: rgb(156 163 175) transparent;
}

.form-textarea::-webkit-scrollbar {
  width: 8px;
}

.form-textarea::-webkit-scrollbar-track {
  background: transparent;
}

.form-textarea::-webkit-scrollbar-thumb {
  background-color: rgb(156 163 175);
  border-radius: 4px;
}

.dark .form-textarea::-webkit-scrollbar-thumb {
  background-color: rgb(75 85 99);
}

/* Auto-resize animation */
.form-textarea {
  transition: height 0.1s ease-out;
}
</style>