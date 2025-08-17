<!--
📁 File Structure:
resources/js/Components/Forms/FormInput.vue

📁 Usage:
<FormInput 
  v-model="form.name"
  label="Full Name"
  placeholder="Enter your full name"
  required
  :error-message="form.errors.name"
  help-text="This will be displayed publicly"
  clearable
  :left-icon="UserIcon"
  maxlength="100"
  show-counter
/>

📁 Dependencies:
- @heroicons/vue/24/outline
- LoadingSpinner.vue (to be created)
-->
<template>
  <div class="form-group">
    <!-- Label -->
    <label 
      v-if="label" 
      :for="inputId" 
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Input Field -->
    <div class="relative">
      <!-- Left Icon -->
      <div v-if="leftIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <component :is="leftIcon" class="h-5 w-5 text-gray-400" />
      </div>

      <!-- Input Element -->
      <input
        :id="inputId"
        ref="inputRef"
        v-model="inputValue"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :autocomplete="autocomplete"
        :maxlength="maxlength"
        :minlength="minlength"
        :min="min"
        :max="max"
        :step="step"
        :pattern="pattern"
        class="form-input"
        :class="[
          inputClasses,
          {
            'pl-10': leftIcon,
            'pr-10': rightIcon || clearable,
            'border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500': hasError,
            'border-green-300 dark:border-green-600 focus:border-green-500 focus:ring-green-500': hasSuccess && !hasError,
            'opacity-50 cursor-not-allowed': disabled,
            'bg-gray-50 dark:bg-gray-800': readonly
          }
        ]"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        @keydown="handleKeydown"
      />

      <!-- Right Icon or Clear Button -->
      <div v-if="rightIcon || (clearable && inputValue)" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <button
          v-if="clearable && inputValue && !disabled"
          type="button"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          @click="clearInput"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
        <component v-else-if="rightIcon" :is="rightIcon" class="h-5 w-5 text-gray-400" />
      </div>

      <!-- Loading Spinner -->
      <div v-if="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <LoadingSpinner class="h-5 w-5 text-gray-400" />
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

    <!-- Character Counter -->
    <div v-if="maxlength && showCounter" class="mt-1 text-right">
      <span class="text-xs text-gray-500 dark:text-gray-400">
        {{ inputValue?.length || 0 }} / {{ maxlength }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from './LoadingSpinner.vue'

// Props
const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => [
      'text', 'email', 'password', 'tel', 'url', 'search', 
      'number', 'date', 'time', 'datetime-local'
    ].includes(value)
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
  clearable: {
    type: Boolean,
    default: false
  },
  autocomplete: {
    type: String,
    default: 'off'
  },
  maxlength: {
    type: Number,
    default: null
  },
  minlength: {
    type: Number,
    default: null
  },
  min: {
    type: [String, Number],
    default: null
  },
  max: {
    type: [String, Number],
    default: null
  },
  step: {
    type: [String, Number],
    default: null
  },
  pattern: {
    type: String,
    default: null
  },
  showCounter: {
    type: Boolean,
    default: false
  },
  leftIcon: {
    type: [String, Object],
    default: null
  },
  rightIcon: {
    type: [String, Object],
    default: null
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'outlined', 'filled'].includes(value)
  },
  validation: {
    type: Object,
    default: () => ({})
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'input',
  'change',
  'blur',
  'focus',
  'clear',
  'keydown'
])

// Refs
const inputRef = ref(null)
const isFocused = ref(false)

// Computed
const inputId = computed(() => `input-${Math.random().toString(36).substr(2, 9)}`)

const inputValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const hasError = computed(() => Boolean(props.errorMessage))
const hasSuccess = computed(() => Boolean(props.successMessage) || Boolean(props.validation?.valid))

const inputClasses = computed(() => {
  const baseClasses = [
    'block', 'w-full', 'rounded-md', 'border', 'shadow-sm', 'transition-colors', 'duration-200',
    'placeholder-gray-400', 'dark:placeholder-gray-500',
    'bg-white', 'dark:bg-gray-900',
    'text-gray-900', 'dark:text-gray-100',
    'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-0'
  ]

  // Size classes
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-3 py-2 text-sm',
    lg: 'px-4 py-3 text-base'
  }

  // Variant classes
  const variantClasses = {
    default: [
      'border-gray-300', 'dark:border-gray-600',
      'focus:border-blue-500', 'focus:ring-blue-500'
    ],
    outlined: [
      'border-2', 'border-gray-300', 'dark:border-gray-600',
      'focus:border-blue-500', 'focus:ring-blue-500'
    ],
    filled: [
      'border-transparent', 'bg-gray-50', 'dark:bg-gray-800',
      'focus:border-blue-500', 'focus:ring-blue-500', 'focus:bg-white', 'dark:focus:bg-gray-900'
    ]
  }

  return [
    ...baseClasses,
    ...sizeClasses[props.size].split(' '),
    ...variantClasses[props.variant]
  ]
})

// Methods
const handleInput = (event) => {
  const value = event.target.value
  inputValue.value = value
  emit('input', value, event)
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
}

const clearInput = () => {
  inputValue.value = ''
  emit('clear')
  nextTick(() => {
    inputRef.value?.focus()
  })
}

const focus = () => {
  inputRef.value?.focus()
}

const blur = () => {
  inputRef.value?.blur()
}

// Expose methods
defineExpose({
  focus,
  blur,
  inputRef
})

// Watch for validation changes
watch(() => props.validation, (newValidation) => {
  if (newValidation?.errors?.length > 0) {
    // Handle validation errors
    console.log('Validation errors:', newValidation.errors)
  }
}, { deep: true })
</script>

<style scoped>
.form-input {
  /* Custom focus styles for better UX */
  @apply focus:ring-opacity-50;
}

.form-input:focus {
  box-shadow: 
    0 0 0 2px rgb(59 130 246 / 0.1),
    0 0 0 4px rgb(59 130 246 / 0.1);
}

/* Dark mode enhancements */
.dark .form-input:focus {
  box-shadow: 
    0 0 0 2px rgb(96 165 250 / 0.2),
    0 0 0 4px rgb(96 165 250 / 0.1);
}

/* Animation for character counter */
.form-group:hover .text-xs {
  @apply transition-opacity duration-200;
}
</style>