
<template>
  <div class="form-group">
    <!-- Label (for single checkbox or group) -->
    <label 
      v-if="label && !multiple" 
      :for="checkboxId" 
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Group Label (for multiple checkboxes) -->
    <fieldset v-if="multiple" class="space-y-3">
      <legend 
        v-if="label"
        class="text-sm font-medium text-gray-700 dark:text-gray-300"
        :class="{ 'text-red-600 dark:text-red-400': hasError }"
      >
        {{ label }}
        <span v-if="required" class="text-red-500 ml-1">*</span>
      </legend>

      <!-- Multiple Checkboxes Grid -->
      <div 
        class="checkbox-grid"
        :class="gridClasses"
      >
        <div
          v-for="(option, index) in normalizedOptions"
          :key="`option-${index}`"
          class="checkbox-item"
          :class="{
            'opacity-50': option.disabled,
            'cursor-not-allowed': option.disabled
          }"
        >
          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input
                :id="`${checkboxId}-${index}`"
                v-model="selectedValues"
                :value="option.value"
                :name="name || checkboxId"
                type="checkbox"
                :disabled="disabled || option.disabled"
                :required="required && selectedValues.length === 0"
                class="checkbox-input"
                :class="[
                  checkboxClasses,
                  {
                    'border-red-300 dark:border-red-600': hasError,
                    'border-green-300 dark:border-green-600': hasSuccess && !hasError
                  }
                ]"
                @change="handleMultipleChange"
              />
            </div>
            <div class="ml-3 text-sm">
              <label 
                :for="`${checkboxId}-${index}`" 
                class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
                :class="{ 'cursor-not-allowed': disabled || option.disabled }"
              >
                {{ option.label }}
              </label>
              <p 
                v-if="option.description" 
                class="text-gray-500 dark:text-gray-400 mt-1"
              >
                {{ option.description }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Select All/None for Multiple -->
      <div v-if="showSelectAll && normalizedOptions.length > 2" class="flex space-x-4 text-sm">
        <button
          type="button"
          class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors"
          :disabled="disabled"
          @click="selectAll"
        >
          Select All
        </button>
        <button
          type="button"
          class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 transition-colors"
          :disabled="disabled"
          @click="selectNone"
        >
          Select None
        </button>
      </div>
    </fieldset>

    <!-- Single Checkbox -->
    <div v-else class="checkbox-container">
      <div class="flex items-start">
        <div class="flex items-center h-5">
          <input
            :id="checkboxId"
            ref="checkboxRef"
            v-model="isChecked"
            :name="name || checkboxId"
            type="checkbox"
            :disabled="disabled"
            :required="required"
            :indeterminate.prop="indeterminate"
            class="checkbox-input"
            :class="[
              checkboxClasses,
              {
                'border-red-300 dark:border-red-600': hasError,
                'border-green-300 dark:border-green-600': hasSuccess && !hasError
              }
            ]"
            @change="handleSingleChange"
            @focus="handleFocus"
            @blur="handleBlur"
          />
        </div>
        
        <div class="ml-3 flex-1">
          <label 
            :for="checkboxId" 
            class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
            :class="{ 'cursor-not-allowed': disabled }"
          >
            {{ checkboxLabel }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
          </label>
          
          <p 
            v-if="description" 
            class="text-sm text-gray-500 dark:text-gray-400 mt-1"
          >
            {{ description }}
          </p>
        </div>
      </div>
    </div>

    <!-- Custom Content Slot -->
    <div v-if="$slots.default" class="mt-2">
      <slot />
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

    <!-- Selection Summary (for multiple) -->
    <p v-if="multiple && showSummary && selectedValues.length > 0" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
      {{ selectedValues.length }} of {{ normalizedOptions.length }} selected
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: [Boolean, Array, String, Number],
    default: false
  },
  label: {
    type: String,
    default: ''
  },
  checkboxLabel: {
    type: String,
    default: ''
  },
  description: {
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
  name: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  indeterminate: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  options: {
    type: Array,
    default: () => []
  },
  optionLabel: {
    type: [String, Function],
    default: 'label'
  },
  optionValue: {
    type: [String, Function],
    default: 'value'
  },
  optionDescription: {
    type: [String, Function],
    default: 'description'
  },
  optionDisabled: {
    type: [String, Function],
    default: 'disabled'
  },
  columns: {
    type: Number,
    default: 1,
    validator: (value) => value >= 1 && value <= 4
  },
  showSelectAll: {
    type: Boolean,
    default: true
  },
  showSummary: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'switch', 'button'].includes(value)
  },
  trueValue: {
    type: [Boolean, String, Number],
    default: true
  },
  falseValue: {
    type: [Boolean, String, Number],
    default: false
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'change',
  'focus',
  'blur'
])

// Refs
const checkboxRef = ref(null)

// Computed
const checkboxId = computed(() => `checkbox-${Math.random().toString(36).substr(2, 9)}`)

const hasError = computed(() => Boolean(props.errorMessage))
const hasSuccess = computed(() => Boolean(props.successMessage))

const normalizedOptions = computed(() => {
  if (!props.multiple || !props.options.length) return []
  
  return props.options.map(option => {
    if (typeof option === 'string' || typeof option === 'number') {
      return {
        label: option,
        value: option,
        description: '',
        disabled: false
      }
    }
    
    return {
      label: getOptionProperty(option, props.optionLabel),
      value: getOptionProperty(option, props.optionValue),
      description: getOptionProperty(option, props.optionDescription) || '',
      disabled: getOptionProperty(option, props.optionDisabled) || false
    }
  })
})

const isChecked = computed({
  get() {
    if (props.multiple) return false
    return props.modelValue === props.trueValue
  },
  set(value) {
    const newValue = value ? props.trueValue : props.falseValue
    emit('update:modelValue', newValue)
    emit('change', newValue)
  }
})

const selectedValues = computed({
  get() {
    if (!props.multiple) return []
    return Array.isArray(props.modelValue) ? props.modelValue : []
  },
  set(values) {
    emit('update:modelValue', values)
    emit('change', values)
  }
})

const checkboxClasses = computed(() => {
  const baseClasses = [
    'rounded', 'border', 'transition-colors', 'duration-200',
    'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-0',
    'focus:ring-blue-500', 'focus:ring-opacity-50'
  ]

  const sizeClasses = {
    sm: 'h-3 w-3',
    md: 'h-4 w-4',
    lg: 'h-5 w-5'
  }

  const variantClasses = {
    default: [
      'text-blue-600', 'bg-white', 'dark:bg-gray-900',
      'border-gray-300', 'dark:border-gray-600',
      'checked:bg-blue-600', 'checked:border-blue-600',
      'disabled:opacity-50', 'disabled:cursor-not-allowed'
    ],
    switch: [
      'rounded-full', 'text-blue-600', 'bg-white', 'dark:bg-gray-900',
      'border-gray-300', 'dark:border-gray-600',
      'checked:bg-blue-600', 'checked:border-blue-600'
    ],
    button: [
      'appearance-none', 'rounded-md', 'border-2',
      'text-blue-600', 'bg-white', 'dark:bg-gray-900',
      'border-gray-300', 'dark:border-gray-600',
      'checked:bg-blue-600', 'checked:border-blue-600', 'checked:text-white'
    ]
  }

  return [
    ...baseClasses,
    ...sizeClasses[props.size].split(' '),
    ...variantClasses[props.variant]
  ]
})

const gridClasses = computed(() => {
  const columnClasses = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 sm:grid-cols-2',
    3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4'
  }
  
  return [
    'grid',
    'gap-4',
    columnClasses[props.columns]
  ]
})

// Methods
const getOptionProperty = (option, property) => {
  if (typeof property === 'function') {
    return property(option)
  }
  return option[property]
}

const handleSingleChange = (event) => {
  isChecked.value = event.target.checked
}

const handleMultipleChange = () => {
  // The v-model binding handles the change automatically
  // This is just for additional side effects if needed
  nextTick(() => {
    emit('change', selectedValues.value)
  })
}

const selectAll = () => {
  const enabledOptions = normalizedOptions.value.filter(option => !option.disabled)
  selectedValues.value = enabledOptions.map(option => option.value)
}

const selectNone = () => {
  selectedValues.value = []
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleBlur = (event) => {
  emit('blur', event)
}

const focus = () => {
  checkboxRef.value?.focus()
}

const blur = () => {
  checkboxRef.value?.blur()
}

// Watch for indeterminate state changes
watch(() => props.indeterminate, (newValue) => {
  if (checkboxRef.value) {
    checkboxRef.value.indeterminate = newValue
  }
}, { immediate: true })

// Expose methods
defineExpose({
  focus,
  blur,
  selectAll,
  selectNone,
  checkboxRef
})
</script>

<style scoped>
.checkbox-input {
  /* Custom checkbox styles */
  @apply cursor-pointer;
}

.checkbox-input:disabled {
  @apply cursor-not-allowed;
}

.checkbox-input:indeterminate {
  @apply bg-blue-600 border-blue-600;
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M4 8h8'/%3e%3c/svg%3e");
}

.checkbox-container {
  @apply relative;
}

.checkbox-item {
  @apply transition-opacity duration-150;
}

.checkbox-grid {
  @apply space-y-3;
}

/* Switch variant styles */
.checkbox-input.switch {
  @apply relative appearance-none w-11 h-6 rounded-full;
  background-color: #d1d5db;
  transition: background-color 0.2s;
}

.checkbox-input.switch:checked {
  background-color: #3b82f6;
}

.checkbox-input.switch::before {
  content: '';
  @apply absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow;
  transition: transform 0.2s;
}

.checkbox-input.switch:checked::before {
  transform: translateX(1.25rem);
}

/* Button variant styles */
.checkbox-input.button {
  @apply relative w-full h-10 flex items-center justify-center;
}

.checkbox-input.button + label {
  @apply absolute inset-0 flex items-center justify-center;
}

/* Focus styles */
.checkbox-input:focus {
  @apply ring-2 ring-blue-500 ring-opacity-50;
}

/* Custom checkbox check mark */
.checkbox-input:checked {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='m13.854 3.646-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 9.293l6.646-6.647a.5.5 0 0 1 .708.708z'/%3e%3c/svg%3e");
}

/* Dark mode adjustments */
.dark .checkbox-input {
  @apply bg-gray-800 border-gray-600;
}

.dark .checkbox-input:checked {
  @apply bg-blue-500 border-blue-500;
}

/* Animation for state changes */
.checkbox-input {
  transition: all 0.2s ease-in-out;
}

/* Hover effects */
.checkbox-input:hover:not(:disabled) {
  @apply border-gray-400 dark:border-gray-500;
}

.checkbox-input:checked:hover:not(:disabled) {
  @apply bg-blue-700 border-blue-700;
}

/* Label hover effects */
label:hover .checkbox-input:not(:disabled) {
  @apply border-gray-400 dark:border-gray-500;
}

/* Grid responsive adjustments */
@media (max-width: 640px) {
  .checkbox-grid {
    @apply space-y-2;
  }
  
  .checkbox-item {
    @apply text-sm;
  }
}

/* Selection animation */
.checkbox-item {
  position: relative;
}

.checkbox-item::before {
  content: '';
  position: absolute;
  inset: -4px;
  background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.1), transparent);
  border-radius: 8px;
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.checkbox-item:has(.checkbox-input:checked)::before {
  opacity: 1;
}
</style>