
<template>
  <div class="form-group">
    <!-- Group Label -->
    <fieldset class="space-y-3">
      <legend 
        v-if="label"
        class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
        :class="{ 'text-red-600 dark:text-red-400': hasError }"
      >
        {{ label }}
        <span v-if="required" class="text-red-500 ml-1">*</span>
      </legend>

      <!-- Radio Options -->
      <div 
        class="radio-grid"
        :class="gridClasses"
      >
        <div
          v-for="(option, index) in normalizedOptions"
          :key="`radio-${index}`"
          class="radio-item"
          :class="[
            radioItemClasses,
            {
              'opacity-50 cursor-not-allowed': option.disabled,
              'ring-2 ring-blue-500 ring-opacity-50': selectedValue === option.value && variant === 'card',
              'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700': selectedValue === option.value && variant === 'card',
              'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700': selectedValue !== option.value && variant === 'card'
            }
          ]"
          @click="!option.disabled && selectOption(option.value)"
        >
          <!-- Default/List Variant -->
          <div v-if="variant === 'default'" class="flex items-start">
            <div class="flex items-center h-5">
              <input
                :id="`${radioId}-${index}`"
                v-model="selectedValue"
                :value="option.value"
                :name="name || radioId"
                type="radio"
                :disabled="disabled || option.disabled"
                :required="required"
                class="radio-input"
                :class="[
                  radioClasses,
                  {
                    'border-red-300 dark:border-red-600': hasError,
                    'border-green-300 dark:border-green-600': hasSuccess && !hasError
                  }
                ]"
                @change="handleChange"
                @focus="handleFocus"
                @blur="handleBlur"
              />
            </div>
            <div class="ml-3 flex-1">
              <label 
                :for="`${radioId}-${index}`" 
                class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
                :class="{ 'text-blue-500': selectedValue === option.value }"
              />
            </div>
            
            <p 
              v-if="option.description" 
              class="text-sm text-gray-500 dark:text-gray-400"
            >
              {{ option.description }}
            </p>
            
            <!-- Badge in card -->
            <span 
              v-if="option.badge" 
              class="mt-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium"
              :class="getBadgeClasses(option.badge)"
            >
              {{ option.badge.text || option.badge }}
            </span>
          </div>

          <!-- Button Variant -->
          <div v-else-if="variant === 'button'" class="radio-button-content">
            <input
              :id="`${radioId}-${index}`"
              v-model="selectedValue"
              :value="option.value"
              :name="name || radioId"
              type="radio"
              :disabled="disabled || option.disabled"
              :required="required"
              class="sr-only"
              @change="handleChange"
            />
            <label 
              :for="`${radioId}-${index}`" 
              class="radio-button-label"
              :class="[
                {
                  'bg-blue-600 text-white border-blue-600': selectedValue === option.value,
                  'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700': selectedValue !== option.value,
                  'cursor-not-allowed opacity-50': disabled || option.disabled
                }
              ]"
            >
              <component 
                v-if="option.icon" 
                :is="option.icon" 
                class="h-4 w-4 mr-2"
              />
              {{ option.label }}
              <span 
                v-if="option.badge" 
                class="ml-2 px-1.5 py-0.5 text-xs rounded"
                :class="selectedValue === option.value ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300'"
              >
                {{ option.badge.text || option.badge }}
              </span>
            </label>
          </div>

          <!-- Segmented Variant -->
          <div v-else-if="variant === 'segmented'" class="radio-segmented-content">
            <input
              :id="`${radioId}-${index}`"
              v-model="selectedValue"
              :value="option.value"
              :name="name || radioId"
              type="radio"
              :disabled="disabled || option.disabled"
              :required="required"
              class="sr-only"
              @change="handleChange"
            />
            <label 
              :for="`${radioId}-${index}`" 
              class="radio-segmented-label"
              :class="[
                {
                  'bg-blue-600 text-white': selectedValue === option.value,
                  'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100': selectedValue !== option.value,
                  'cursor-not-allowed opacity-50': disabled || option.disabled
                }
              ]"
            >
              <component 
                v-if="option.icon" 
                :is="option.icon" 
                class="h-4 w-4"
                :class="{ 'mr-2': option.label }"
              />
              <span v-if="option.label">{{ option.label }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Custom Content Slot -->
      <div v-if="$slots.default" class="mt-3">
        <slot />
      </div>
    </fieldset>

    <!-- Help Text -->
    <p v-if="helpText && !hasError" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      {{ helpText }}
    </p>

    <!-- Error Message -->
    <p v-if="hasError" class="mt-2 text-sm text-red-600 dark:text-red-400">
      {{ errorMessage }}
    </p>

    <!-- Success Message -->
    <p v-if="hasSuccess && !hasError && successMessage" class="mt-2 text-sm text-green-600 dark:text-green-400">
      {{ successMessage }}
    </p>

    <!-- Selection Info -->
    <div v-if="showSelectionInfo && selectedValue" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
      Selected: <span class="font-medium">{{ getSelectedOptionLabel() }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: null
  },
  label: {
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
  options: {
    type: Array,
    required: true,
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
  optionIcon: {
    type: [String, Function],
    default: 'icon'
  },
  optionBadge: {
    type: [String, Function],
    default: 'badge'
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
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'card', 'button', 'segmented'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  showSelectionInfo: {
    type: Boolean,
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
const focusedIndex = ref(-1)

// Computed
const radioId = computed(() => `radio-${Math.random().toString(36).substr(2, 9)}`)

const hasError = computed(() => Boolean(props.errorMessage))
const hasSuccess = computed(() => Boolean(props.successMessage))

const normalizedOptions = computed(() => {
  return props.options.map(option => {
    if (typeof option === 'string' || typeof option === 'number') {
      return {
        label: option,
        value: option,
        description: '',
        icon: null,
        badge: null,
        disabled: false
      }
    }
    
    return {
      label: getOptionProperty(option, props.optionLabel),
      value: getOptionProperty(option, props.optionValue),
      description: getOptionProperty(option, props.optionDescription) || '',
      icon: getOptionProperty(option, props.optionIcon) || null,
      badge: getOptionProperty(option, props.optionBadge) || null,
      disabled: getOptionProperty(option, props.optionDisabled) || false
    }
  })
})

const selectedValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
    emit('change', value)
  }
})

const radioClasses = computed(() => {
  const baseClasses = [
    'border', 'transition-colors', 'duration-200',
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
    card: [
      'text-blue-600', 'bg-white', 'dark:bg-gray-900',
      'border-gray-300', 'dark:border-gray-600',
      'checked:bg-blue-600', 'checked:border-blue-600'
    ],
    button: [],
    segmented: []
  }

  return [
    ...baseClasses,
    ...sizeClasses[props.size].split(' '),
    ...variantClasses[props.variant]
  ]
})

const gridClasses = computed(() => {
  if (props.variant === 'segmented') {
    return ['flex', 'rounded-lg', 'border', 'border-gray-300', 'dark:border-gray-600', 'overflow-hidden']
  }

  const columnClasses = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 sm:grid-cols-2',
    3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4'
  }
  
  return [
    'grid',
    'gap-3',
    columnClasses[props.columns]
  ]
})

const radioItemClasses = computed(() => {
  const baseClasses = ['transition-all', 'duration-200']
  
  const variantClasses = {
    default: [],
    card: [
      'p-4', 'rounded-lg', 'border', 'cursor-pointer',
      'hover:shadow-sm', 'hover:border-gray-300', 'dark:hover:border-gray-600'
    ],
    button: [],
    segmented: ['flex-1']
  }
  
  return [
    ...baseClasses,
    ...variantClasses[props.variant]
  ]
})

// Methods
const getOptionProperty = (option, property) => {
  if (typeof property === 'function') {
    return property(option)
  }
  return option[property]
}

const selectOption = (value) => {
  if (props.disabled) return
  selectedValue.value = value
}

const getSelectedOptionLabel = () => {
  const selectedOption = normalizedOptions.value.find(option => option.value === selectedValue.value)
  return selectedOption?.label || ''
}

const getBadgeClasses = (badge) => {
  if (typeof badge === 'object' && badge.variant) {
    const variants = {
      success: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
      warning: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
      error: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
      info: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
      default: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
    }
    return variants[badge.variant] || variants.default
  }
  return 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
}

const handleChange = (event) => {
  selectedValue.value = event.target.value
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleBlur = (event) => {
  emit('blur', event)
}

// Expose methods
defineExpose({
  selectOption,
  getSelectedOptionLabel
})
</script>

<style scoped>
.radio-input {
  @apply cursor-pointer;
}

.radio-input:disabled {
  @apply cursor-not-allowed;
}

.radio-card-content {
  @apply relative;
}

.radio-button-label {
  @apply inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md transition-colors duration-200 cursor-pointer;
}

.radio-button-label:focus-within {
  @apply ring-2 ring-blue-500 ring-opacity-50;
}

.radio-segmented-label {
  @apply flex items-center justify-center px-4 py-2 text-sm font-medium cursor-pointer transition-colors duration-200 flex-1;
}

.radio-segmented-label:first-child {
  @apply border-r border-gray-300 dark:border-gray-600;
}

.radio-segmented-label:focus-within {
  @apply ring-2 ring-blue-500 ring-opacity-50 ring-inset;
}

/* Custom radio button styles */
.radio-input {
  @apply appearance-none rounded-full;
}

.radio-input:checked {
  @apply bg-blue-600 border-blue-600;
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
}

/* Focus styles */
.radio-input:focus {
  @apply ring-2 ring-blue-500 ring-opacity-50;
}

/* Dark mode adjustments */
.dark .radio-input {
  @apply bg-gray-800 border-gray-600;
}

.dark .radio-input:checked {
  @apply bg-blue-500 border-blue-500;
}

/* Hover effects */
.radio-input:hover:not(:disabled) {
  @apply border-gray-400 dark:border-gray-500;
}

.radio-input:checked:hover:not(:disabled) {
  @apply bg-blue-700 border-blue-700;
}

/* Card variant hover effects */
.radio-item.card:hover:not(.opacity-50) {
  @apply shadow-md transform scale-105;
}

/* Button variant spacing */
.radio-grid .radio-item:not(:last-child) {
  @apply mb-2;
}

.radio-grid.grid .radio-item {
  @apply mb-0;
}

/* Segmented variant styles */
.radio-grid.flex .radio-item {
  @apply mb-0;
}

.radio-segmented-label {
  position: relative;
}

.radio-segmented-label::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.radio-input:checked + .radio-segmented-label::before {
  opacity: 1;
}

/* Animation for selection */
.radio-item {
  position: relative;
}

.radio-item::before {
  content: '';
  position: absolute;
  inset: -2px;
  background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.1), transparent);
  border-radius: inherit;
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.radio-item:has(.radio-input:checked)::before {
  opacity: 1;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .radio-button-label {
    @apply px-3 py-2 text-xs;
  }
  
  .radio-segmented-label {
    @apply px-2 py-2 text-xs;
  }
  
  .radio-card-content {
    @apply p-3;
  }
}
</style>