<!--
📁 File Structure:
resources/js/Components/Forms/FormSelect.vue

📁 Usage:
<FormSelect 
  v-model="form.country"
  label="Country"
  placeholder="Select a country"
  :options="countries"
  option-label="name"
  option-value="code"
  searchable
  clearable
  required
  :error-message="form.errors.country"
/>

📁 Dependencies:
- @heroicons/vue/24/outline
- @headlessui/vue (for accessibility)
-->
<template>
  <div class="form-group">
    <!-- Label -->
    <label 
      v-if="label" 
      :for="selectId" 
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Select Container -->
    <div class="relative">
      <!-- Search Input (for searchable select) -->
      <div
        v-if="searchable"
        class="relative"
      >
        <input
          :id="selectId"
          ref="searchInputRef"
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          :disabled="disabled"
          class="form-select-input"
          :class="[
            selectClasses,
            {
              'border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500': hasError,
              'border-green-300 dark:border-green-600 focus:border-green-500 focus:ring-green-500': hasSuccess && !hasError,
              'opacity-50 cursor-not-allowed': disabled
            }
          ]"
          @focus="openDropdown"
          @blur="handleBlur"
          @keydown="handleKeydown"
        />
        
        <!-- Selected Value Display -->
        <div 
          v-if="!isOpen && selectedOption && !searchQuery"
          class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
        >
          <span class="text-gray-900 dark:text-gray-100 truncate">
            {{ getOptionLabel(selectedOption) }}
          </span>
        </div>
      </div>

      <!-- Regular Select (non-searchable) -->
      <button
        v-else
        :id="selectId"
        ref="selectButtonRef"
        type="button"
        :disabled="disabled"
        class="form-select-button"
        :class="[
          selectClasses,
          {
            'border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500': hasError,
            'border-green-300 dark:border-green-600 focus:border-green-500 focus:ring-green-500': hasSuccess && !hasError,
            'opacity-50 cursor-not-allowed': disabled
          }
        ]"
        @click="toggleDropdown"
        @keydown="handleKeydown"
      >
        <span class="flex items-center">
          <!-- Selected Option -->
          <span 
            v-if="selectedOption" 
            class="block truncate text-left"
            :class="selectedOption ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'"
          >
            {{ selectedOption ? getOptionLabel(selectedOption) : placeholder }}
          </span>
          <!-- Placeholder -->
          <span 
            v-else 
            class="block truncate text-left text-gray-500 dark:text-gray-400"
          >
            {{ placeholder }}
          </span>
        </span>
        
        <!-- Dropdown Arrow -->
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
          <ChevronUpDownIcon class="h-5 w-5 text-gray-400" />
        </span>
      </button>

      <!-- Clear Button -->
      <button
        v-if="clearable && selectedOption && !disabled"
        type="button"
        class="absolute inset-y-0 right-8 flex items-center pr-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
        @click="clearSelection"
      >
        <XMarkIcon class="h-4 w-4" />
      </button>

      <!-- Dropdown Menu -->
      <Transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="isOpen"
          class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none"
          :class="dropdownPosition === 'up' ? 'bottom-full mb-1' : 'top-full'"
        >
          <!-- Loading State -->
          <div v-if="loading" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400 text-center">
            <LoadingSpinner class="h-4 w-4 mx-auto" />
            <span class="ml-2">Loading...</span>
          </div>

          <!-- No Options -->
          <div v-else-if="filteredOptions.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400 text-center">
            {{ searchQuery ? 'No results found' : 'No options available' }}
          </div>

          <!-- Options List -->
          <template v-else>
            <div
              v-for="(option, index) in filteredOptions"
              :key="getOptionValue(option)"
              class="select-option"
              :class="{
                'bg-blue-600 text-white': index === highlightedIndex,
                'text-gray-900 dark:text-gray-100': index !== highlightedIndex,
                'bg-blue-50 dark:bg-blue-900': isSelected(option) && index !== highlightedIndex
              }"
              @click="selectOption(option)"
              @mouseenter="highlightedIndex = index"
            >
              <div class="flex items-center px-3 py-2">
                <!-- Option Content -->
                <span class="block truncate font-normal">
                  {{ getOptionLabel(option) }}
                </span>
                
                <!-- Selected Indicator -->
                <span 
                  v-if="isSelected(option)" 
                  class="absolute inset-y-0 right-0 flex items-center pr-4"
                  :class="index === highlightedIndex ? 'text-white' : 'text-blue-600 dark:text-blue-400'"
                >
                  <CheckIcon class="h-5 w-5" />
                </span>
              </div>
            </div>
          </template>
        </div>
      </Transition>
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
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { 
  ChevronUpDownIcon, 
  XMarkIcon, 
  CheckIcon 
} from '@heroicons/vue/24/outline'
import LoadingSpinner from './LoadingSpinner.vue'

// Props
const props = defineProps({
  modelValue: {
    type: [String, Number, Object, Array],
    default: null
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
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select an option'
  },
  searchPlaceholder: {
    type: String,
    default: 'Search options...'
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
  required: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: false
  },
  multiple: {
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
  'change',
  'search',
  'open',
  'close'
])

// Refs
const selectButtonRef = ref(null)
const searchInputRef = ref(null)
const isOpen = ref(false)
const highlightedIndex = ref(-1)
const searchQuery = ref('')
const dropdownPosition = ref('down')

// Computed
const selectId = computed(() => `select-${Math.random().toString(36).substr(2, 9)}`)

const selectedOption = computed(() => {
  if (props.multiple) {
    return props.modelValue || []
  }
  
  if (!props.modelValue) return null
  
  return props.options.find(option => 
    getOptionValue(option) === props.modelValue
  ) || null
})

const hasError = computed(() => Boolean(props.errorMessage))
const hasSuccess = computed(() => Boolean(props.successMessage))

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.options
  }
  
  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option => 
    getOptionLabel(option).toLowerCase().includes(query)
  )
})

const selectClasses = computed(() => {
  const baseClasses = [
    'relative', 'block', 'w-full', 'rounded-md', 'border', 'shadow-sm', 
    'transition-colors', 'duration-200', 'focus:outline-none', 'focus:ring-2', 
    'focus:ring-offset-0', 'bg-white', 'dark:bg-gray-900',
    'text-gray-900', 'dark:text-gray-100'
  ]

  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-3 py-2 text-sm', 
    lg: 'px-4 py-3 text-base'
  }

  return [
    ...baseClasses,
    ...sizeClasses[props.size].split(' '),
    'border-gray-300', 'dark:border-gray-600',
    'focus:border-blue-500', 'focus:ring-blue-500'
  ]
})

// Methods
const getOptionLabel = (option) => {
  if (typeof props.optionLabel === 'function') {
    return props.optionLabel(option)
  }
  return option[props.optionLabel] || option
}

const getOptionValue = (option) => {
  if (typeof props.optionValue === 'function') {
    return props.optionValue(option)
  }
  return option[props.optionValue] || option
}

const isSelected = (option) => {
  if (props.multiple) {
    const values = props.modelValue || []
    return values.includes(getOptionValue(option))
  }
  return getOptionValue(option) === props.modelValue
}

const openDropdown = () => {
  if (props.disabled) return
  
  isOpen.value = true
  highlightedIndex.value = -1
  emit('open')
  
  nextTick(() => {
    calculateDropdownPosition()
    if (props.searchable && searchInputRef.value) {
      searchInputRef.value.focus()
    }
  })
}

const closeDropdown = () => {
  isOpen.value = false
  highlightedIndex.value = -1
  searchQuery.value = ''
  emit('close')
}

const toggleDropdown = () => {
  if (isOpen.value) {
    closeDropdown()
  } else {
    openDropdown()
  }
}

const selectOption = (option) => {
  const value = getOptionValue(option)
  
  if (props.multiple) {
    const currentValues = props.modelValue || []
    const newValues = currentValues.includes(value)
      ? currentValues.filter(v => v !== value)
      : [...currentValues, value]
    
    emit('update:modelValue', newValues)
    emit('change', newValues)
  } else {
    emit('update:modelValue', value)
    emit('change', value)
    closeDropdown()
  }
}

const clearSelection = () => {
  const newValue = props.multiple ? [] : null
  emit('update:modelValue', newValue)
  emit('change', newValue)
}

const handleKeydown = (event) => {
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      if (!isOpen.value) {
        openDropdown()
      } else {
        highlightedIndex.value = Math.min(
          highlightedIndex.value + 1,
          filteredOptions.value.length - 1
        )
      }
      break
      
    case 'ArrowUp':
      event.preventDefault()
      if (isOpen.value) {
        highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
      }
      break
      
    case 'Enter':
      event.preventDefault()
      if (isOpen.value && highlightedIndex.value >= 0) {
        selectOption(filteredOptions.value[highlightedIndex.value])
      } else if (!isOpen.value) {
        openDropdown()
      }
      break
      
    case 'Escape':
      event.preventDefault()
      closeDropdown()
      break
      
    case 'Tab':
      closeDropdown()
      break
  }
}

const handleBlur = (event) => {
  // Delay closing to allow option click
  setTimeout(() => {
    if (!event.relatedTarget?.closest('.select-option')) {
      closeDropdown()
    }
  }, 150)
}

const calculateDropdownPosition = () => {
  if (!selectButtonRef.value && !searchInputRef.value) return
  
  const element = selectButtonRef.value || searchInputRef.value
  const rect = element.getBoundingClientRect()
  const spaceBelow = window.innerHeight - rect.bottom
  const spaceAbove = rect.top
  
  dropdownPosition.value = spaceBelow < 200 && spaceAbove > spaceBelow ? 'up' : 'down'
}

const handleClickOutside = (event) => {
  const element = selectButtonRef.value || searchInputRef.value
  if (element && !element.contains(event.target) && !event.target.closest('.select-option')) {
    closeDropdown()
  }
}

// Watchers
watch(searchQuery, (newQuery) => {
  emit('search', newQuery)
  highlightedIndex.value = -1
})

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('resize', calculateDropdownPosition)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', calculateDropdownPosition)
})

// Expose methods
defineExpose({
  open: openDropdown,
  close: closeDropdown,
  focus: () => {
    const element = selectButtonRef.value || searchInputRef.value
    element?.focus()
  }
})
</script>

<style scoped>
.form-select-input,
.form-select-button {
  cursor: pointer;
}

.form-select-button {
  text-align: left;
  padding-right: 2.5rem;
}

.select-option {
  position: relative;
  cursor: pointer;
  user-select: none;
}

.select-option:hover {
  @apply bg-gray-50 dark:bg-gray-700;
}

/* Custom scrollbar for dropdown */
.overflow-auto {
  scrollbar-width: thin;
  scrollbar-color: rgb(156 163 175) transparent;
}

.overflow-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-auto::-webkit-scrollbar-thumb {
  background-color: rgb(156 163 175);
  border-radius: 3px;
}

.dark .overflow-auto::-webkit-scrollbar-thumb {
  background-color: rgb(75 85 99);
}
</style>!