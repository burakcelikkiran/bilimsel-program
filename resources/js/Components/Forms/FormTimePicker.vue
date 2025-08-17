<!--
📁 File Structure:
resources/js/Components/Forms/FormTimePicker.vue

📁 Usage:
<FormTimePicker 
  v-model="form.start_time"
  label="Session Start Time"
  placeholder="Select start time"
  :use24-hour="false"
  :minute-step="15"
  required
  :error-message="form.errors.start_time"
  help-text="Event start time (local timezone)"
/>

📁 Dependencies:
- @heroicons/vue/24/outline
-->
<template>
  <div class="form-group">
    <!-- Label -->
    <label 
      v-if="label" 
      :for="timepickerId" 
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Time Input Container -->
    <div class="relative">
      <!-- Input Field -->
      <input
        :id="timepickerId"
        ref="inputRef"
        v-model="displayValue"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly || !allowManualInput"
        :required="required"
        class="form-time-input"
        :class="[
          inputClasses,
          {
            'border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500': hasError,
            'border-green-300 dark:border-green-600 focus:border-green-500 focus:ring-green-500': hasSuccess && !hasError,
            'opacity-50 cursor-not-allowed': disabled,
            'cursor-pointer': !allowManualInput
          }
        ]"
        @click="openTimePicker"
        @focus="handleFocus"
        @blur="handleBlur"
        @input="handleManualInput"
        @keydown="handleKeydown"
      />

      <!-- Clock Icon -->
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <button
          type="button"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          :disabled="disabled"
          @click="openTimePicker"
        >
          <ClockIcon class="h-5 w-5" />
        </button>
      </div>

      <!-- Clear Button -->
      <button
        v-if="clearable && selectedTime && !disabled"
        type="button"
        class="absolute inset-y-0 right-8 flex items-center pr-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
        @click="clearTime"
      >
        <XMarkIcon class="h-4 w-4" />
      </button>

      <!-- Time Picker Dropdown -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="isOpen"
          class="absolute z-50 mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 p-4 min-w-[280px]"
          :class="dropdownPosition === 'up' ? 'bottom-full mb-1' : 'top-full'"
        >
          <!-- Time Display -->
          <div class="text-center mb-4">
            <div class="text-2xl font-mono font-bold text-gray-900 dark:text-gray-100">
              {{ formatTimeDisplay(tempHour, tempMinute, tempSecond, tempPeriod) }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ use24Hour ? '24-hour format' : '12-hour format' }}
            </div>
          </div>

          <!-- Time Controls -->
          <div class="grid grid-cols-3 gap-4 mb-4" :class="{ 'grid-cols-4': !use24Hour }">
            <!-- Hours -->
            <div class="text-center">
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                Hours
              </label>
              <div class="flex flex-col space-y-1">
                <button
                  type="button"
                  class="time-control-btn"
                  @click="adjustHour(1)"
                >
                  <ChevronUpIcon class="h-4 w-4" />
                </button>
                <input
                  v-model.number="tempHour"
                  type="number"
                  class="time-input"
                  :min="use24Hour ? 0 : 1"
                  :max="use24Hour ? 23 : 12"
                  @input="validateHour"
                  @blur="formatHour"
                />
                <button
                  type="button"
                  class="time-control-btn"
                  @click="adjustHour(-1)"
                >
                  <ChevronDownIcon class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- Minutes -->
            <div class="text-center">
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                Minutes
              </label>
              <div class="flex flex-col space-y-1">
                <button
                  type="button"
                  class="time-control-btn"
                  @click="adjustMinute(minuteStep)"
                >
                  <ChevronUpIcon class="h-4 w-4" />
                </button>
                <input
                  v-model.number="tempMinute"
                  type="number"
                  class="time-input"
                  min="0"
                  max="59"
                  @input="validateMinute"
                  @blur="formatMinute"
                />
                <button
                  type="button"
                  class="time-control-btn"
                  @click="adjustMinute(-minuteStep)"
                >
                  <ChevronDownIcon class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- Seconds (if enabled) -->
            <div v-if="showSeconds" class="text-center">
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                Seconds
              </label>
              <div class="flex flex-col space-y-1">
                <button
                  type="button"
                  class="time-control-btn"
                  @click="adjustSecond(secondStep)"
                >
                  <ChevronUpIcon class="h-4 w-4" />
                </button>
                <input
                  v-model.number="tempSecond"
                  type="number"
                  class="time-input"
                  min="0"
                  max="59"
                  @input="validateSecond"
                  @blur="formatSecond"
                />
                <button
                  type="button"
                  class="time-control-btn"
                  @click="adjustSecond(-secondStep)"
                >
                  <ChevronDownIcon class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- AM/PM (12-hour format) -->
            <div v-if="!use24Hour" class="text-center">
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                Period
              </label>
              <div class="flex flex-col space-y-1">
                <button
                  type="button"
                  class="time-control-btn"
                  @click="togglePeriod"
                >
                  <ChevronUpIcon class="h-4 w-4" />
                </button>
                <div class="time-input flex items-center justify-center font-medium">
                  {{ tempPeriod }}
                </div>
                <button
                  type="button"
                  class="time-control-btn"
                  @click="togglePeriod"
                >
                  <ChevronDownIcon class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Quick Time Presets -->
          <div v-if="showPresets" class="mb-4">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
              Quick Select
            </label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="preset in timePresets"
                :key="preset.label"
                type="button"
                class="preset-btn"
                :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': isCurrentPreset(preset) }"
                @click="selectPreset(preset)"
              >
                {{ preset.label }}
              </button>
            </div>
          </div>

          <!-- Now Button -->
          <div class="flex justify-center mb-4">
            <button
              type="button"
              class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors"
              @click="selectNow"
            >
              Now
            </button>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-600">
            <button
              type="button"
              class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
              @click="closeTimePicker"
            >
              Cancel
            </button>
            <button
              type="button"
              class="px-4 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
              @click="confirmSelection"
            >
              OK
            </button>
          </div>
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
  ClockIcon,
  XMarkIcon,
  ChevronUpIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  modelValue: {
    type: [String, Date],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select time'
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
  clearable: {
    type: Boolean,
    default: true
  },
  use24Hour: {
    type: Boolean,
    default: true
  },
  showSeconds: {
    type: Boolean,
    default: false
  },
  allowManualInput: {
    type: Boolean,
    default: false
  },
  minuteStep: {
    type: Number,
    default: 1,
    validator: (value) => [1, 5, 10, 15, 30].includes(value)
  },
  secondStep: {
    type: Number,
    default: 1,
    validator: (value) => [1, 5, 10, 15, 30].includes(value)
  },
  showPresets: {
    type: Boolean,
    default: true
  },
  format: {
    type: String,
    default: 'HH:mm'
  },
  minTime: {
    type: String,
    default: null
  },
  maxTime: {
    type: String,
    default: null
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
  'focus',
  'blur',
  'open',
  'close'
])

// Refs
const inputRef = ref(null)
const isOpen = ref(false)
const dropdownPosition = ref('down')

// Time state
const tempHour = ref(0)
const tempMinute = ref(0)
const tempSecond = ref(0)
const tempPeriod = ref('AM')

// Computed
const timepickerId = computed(() => `timepicker-${Math.random().toString(36).substr(2, 9)}`)

const selectedTime = computed(() => {
  if (!props.modelValue) return null
  
  if (props.modelValue instanceof Date) {
    return props.modelValue
  }
  
  // Parse time string
  const timeMatch = props.modelValue.match(/(\d{1,2}):(\d{2})(?::(\d{2}))?(?:\s*(AM|PM))?/i)
  if (timeMatch) {
    const date = new Date()
    let hour = parseInt(timeMatch[1])
    const minute = parseInt(timeMatch[2])
    const second = parseInt(timeMatch[3]) || 0
    const period = timeMatch[4]?.toUpperCase()
    
    if (period) {
      if (period === 'PM' && hour !== 12) hour += 12
      if (period === 'AM' && hour === 12) hour = 0
    }
    
    date.setHours(hour, minute, second, 0)
    return date
  }
  
  return null
})

const displayValue = computed({
  get() {
    if (!selectedTime.value) return ''
    return formatTime(selectedTime.value)
  },
  set(value) {
    if (!value) {
      emit('update:modelValue', null)
      return
    }
    
    const parsed = parseTime(value)
    if (parsed) {
      emit('update:modelValue', formatTimeString(parsed))
    }
  }
})

const hasError = computed(() => Boolean(props.errorMessage))
const hasSuccess = computed(() => Boolean(props.successMessage))

const inputClasses = computed(() => {
  const baseClasses = [
    'block', 'w-full', 'rounded-md', 'border', 'shadow-sm', 'transition-colors', 'duration-200',
    'placeholder-gray-400', 'dark:placeholder-gray-500',
    'bg-white', 'dark:bg-gray-900',
    'text-gray-900', 'dark:text-gray-100',
    'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-0',
    'focus:border-blue-500', 'focus:ring-blue-500',
    'pr-16' // Space for icons
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

const timePresets = computed(() => {
  const presets = [
    { label: '09:00', hour: 9, minute: 0 },
    { label: '12:00', hour: 12, minute: 0 },
    { label: '18:00', hour: 18, minute: 0 }
  ]
  
  if (!props.use24Hour) {
    return [
      { label: '9:00 AM', hour: 9, minute: 0 },
      { label: '12:00 PM', hour: 12, minute: 0 },
      { label: '6:00 PM', hour: 18, minute: 0 }
    ]
  }
  
  return presets
})

// Methods
const formatTime = (date) => {
  if (!date) return ''
  
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()
  
  if (props.use24Hour) {
    const timeStr = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`
    return props.showSeconds ? `${timeStr}:${String(second).padStart(2, '0')}` : timeStr
  } else {
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour
    const period = hour >= 12 ? 'PM' : 'AM'
    const timeStr = `${displayHour}:${String(minute).padStart(2, '0')}`
    return props.showSeconds ? `${timeStr}:${String(second).padStart(2, '0')} ${period}` : `${timeStr} ${period}`
  }
}

const formatTimeString = (date) => {
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()
  
  return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}${props.showSeconds ? ':' + String(second).padStart(2, '0') : ''}`
}

const formatTimeDisplay = (hour, minute, second, period) => {
  if (props.use24Hour) {
    const timeStr = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`
    return props.showSeconds ? `${timeStr}:${String(second).padStart(2, '0')}` : timeStr
  } else {
    const timeStr = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`
    return props.showSeconds ? `${timeStr}:${String(second).padStart(2, '0')} ${period}` : `${timeStr} ${period}`
  }
}

const parseTime = (timeString) => {
  const timeMatch = timeString.match(/(\d{1,2}):(\d{2})(?::(\d{2}))?(?:\s*(AM|PM))?/i)
  if (!timeMatch) return null
  
  const date = new Date()
  let hour = parseInt(timeMatch[1])
  const minute = parseInt(timeMatch[2])
  const second = parseInt(timeMatch[3]) || 0
  const period = timeMatch[4]?.toUpperCase()
  
  if (period) {
    if (period === 'PM' && hour !== 12) hour += 12
    if (period === 'AM' && hour === 12) hour = 0
  }
  
  date.setHours(hour, minute, second, 0)
  return date
}

const openTimePicker = () => {
  if (props.disabled) return
  
  isOpen.value = true
  emit('open')
  
  // Initialize temp values
  if (selectedTime.value) {
    const time = selectedTime.value
    tempHour.value = props.use24Hour ? time.getHours() : (time.getHours() % 12 || 12)
    tempMinute.value = time.getMinutes()
    tempSecond.value = time.getSeconds()
    tempPeriod.value = time.getHours() >= 12 ? 'PM' : 'AM'
  } else {
    const now = new Date()
    tempHour.value = props.use24Hour ? now.getHours() : (now.getHours() % 12 || 12)
    tempMinute.value = Math.floor(now.getMinutes() / props.minuteStep) * props.minuteStep
    tempSecond.value = props.showSeconds ? Math.floor(now.getSeconds() / props.secondStep) * props.secondStep : 0
    tempPeriod.value = now.getHours() >= 12 ? 'PM' : 'AM'
  }
  
  nextTick(() => calculateDropdownPosition())
}

const closeTimePicker = () => {
  isOpen.value = false
  emit('close')
}

const confirmSelection = () => {
  const date = new Date()
  let hour = tempHour.value
  
  if (!props.use24Hour) {
    if (tempPeriod.value === 'PM' && hour !== 12) hour += 12
    if (tempPeriod.value === 'AM' && hour === 12) hour = 0
  }
  
  date.setHours(hour, tempMinute.value, tempSecond.value, 0)
  
  const timeString = formatTimeString(date)
  emit('update:modelValue', timeString)
  emit('change', timeString)
  
  closeTimePicker()
}

const clearTime = () => {
  emit('update:modelValue', null)
  emit('change', null)
}

const selectNow = () => {
  const now = new Date()
  tempHour.value = props.use24Hour ? now.getHours() : (now.getHours() % 12 || 12)
  tempMinute.value = now.getMinutes()
  tempSecond.value = now.getSeconds()
  tempPeriod.value = now.getHours() >= 12 ? 'PM' : 'AM'
}

const selectPreset = (preset) => {
  tempHour.value = props.use24Hour ? preset.hour : (preset.hour % 12 || 12)
  tempMinute.value = preset.minute
  tempSecond.value = 0
  tempPeriod.value = preset.hour >= 12 ? 'PM' : 'AM'
}

const isCurrentPreset = (preset) => {
  const presetHour = props.use24Hour ? preset.hour : (preset.hour % 12 || 12)
  return tempHour.value === presetHour && tempMinute.value === preset.minute
}

const adjustHour = (delta) => {
  if (props.use24Hour) {
    tempHour.value = Math.max(0, Math.min(23, tempHour.value + delta))
  } else {
    tempHour.value = Math.max(1, Math.min(12, tempHour.value + delta))
  }
}

const adjustMinute = (delta) => {
  tempMinute.value = Math.max(0, Math.min(59, tempMinute.value + delta))
}

const adjustSecond = (delta) => {
  tempSecond.value = Math.max(0, Math.min(59, tempSecond.value + delta))
}

const togglePeriod = () => {
  tempPeriod.value = tempPeriod.value === 'AM' ? 'PM' : 'AM'
}

const validateHour = () => {
  if (props.use24Hour) {
    tempHour.value = Math.max(0, Math.min(23, tempHour.value || 0))
  } else {
    tempHour.value = Math.max(1, Math.min(12, tempHour.value || 1))
  }
}

const validateMinute = () => {
  tempMinute.value = Math.max(0, Math.min(59, tempMinute.value || 0))
}

const validateSecond = () => {
  tempSecond.value = Math.max(0, Math.min(59, tempSecond.value || 0))
}

const formatHour = () => {
  // Ensure hour is properly formatted when user finishes editing
  validateHour()
}

const formatMinute = () => {
  validateMinute()
}

const formatSecond = () => {
  validateSecond()
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleBlur = (event) => {
  emit('blur', event)
}

const handleManualInput = (event) => {
  if (props.allowManualInput) {
    displayValue.value = event.target.value
  }
}

const handleKeydown = (event) => {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    openTimePicker()
  } else if (event.key === 'Escape') {
    closeTimePicker()
  }
}

const calculateDropdownPosition = () => {
  if (!inputRef.value) return
  
  const rect = inputRef.value.getBoundingClientRect()
  const spaceBelow = window.innerHeight - rect.bottom
  const spaceAbove = rect.top
  
  dropdownPosition.value = spaceBelow < 350 && spaceAbove > spaceBelow ? 'up' : 'down'
}

const handleClickOutside = (event) => {
  if (inputRef.value && !inputRef.value.contains(event.target) && !event.target.closest('.time-control-btn')) {
    closeTimePicker()
  }
}

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
  open: openTimePicker,
  close: closeTimePicker,
  focus: () => inputRef.value?.focus()
})
</script>

<style scoped>
.form-time-input {
  cursor: pointer;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.time-control-btn {
  @apply w-8 h-6 flex items-center justify-center rounded text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150;
}

.time-control-btn:focus {
  @apply outline-none ring-2 ring-blue-500 ring-opacity-50;
}

.time-input {
  @apply w-12 h-8 text-center text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.preset-btn {
  @apply px-3 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 text-gray-700 dark:text-gray-300;
}

.preset-btn:focus {
  @apply outline-none ring-2 ring-blue-500 ring-opacity-50;
}

/* Remove number input spinners */
.time-input::-webkit-outer-spin-button,
.time-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.time-input[type=number] {
  -moz-appearance: textfield;
}

/* Time display animation */
.text-2xl {
  transition: all 0.2s ease;
}
</style>