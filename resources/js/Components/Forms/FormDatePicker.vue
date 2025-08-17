<!--
📁 File Structure:
resources/js/Components/Forms/FormDatePicker.vue

📁 Usage:
<FormDatePicker 
  v-model="form.start_date"
  label="Event Start Date"
  placeholder="Select start date"
  :min-date="today"
  :max-date="maxEventDate"
  :disabled-dates="weekends"
  required
  show-time
  format="DD/MM/YYYY HH:mm"
  :error-message="form.errors.start_date"
/>

📁 Dependencies:
- @heroicons/vue/24/outline
- date-fns or dayjs library for date manipulation
-->
<template>
  <div class="form-group">
    <!-- Label -->
    <label 
      v-if="label" 
      :for="pickerId" 
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Date Input Container -->
    <div class="relative">
      <!-- Input Field -->
      <input
        :id="pickerId"
        ref="inputRef"
        v-model="displayValue"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly || !allowManualInput"
        :required="required"
        class="form-date-input"
        :class="[
          inputClasses,
          {
            'border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500': hasError,
            'border-green-300 dark:border-green-600 focus:border-green-500 focus:ring-green-500': hasSuccess && !hasError,
            'opacity-50 cursor-not-allowed': disabled,
            'cursor-pointer': !allowManualInput
          }
        ]"
        @click="openCalendar"
        @focus="handleFocus"
        @blur="handleBlur"
        @input="handleManualInput"
        @keydown="handleKeydown"
      />

      <!-- Calendar Icon -->
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <button
          type="button"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          :disabled="disabled"
          @click="openCalendar"
        >
          <CalendarDaysIcon class="h-5 w-5" />
        </button>
      </div>

      <!-- Clear Button -->
      <button
        v-if="clearable && selectedDate && !disabled"
        type="button"
        class="absolute inset-y-0 right-8 flex items-center pr-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
        @click="clearDate"
      >
        <XMarkIcon class="h-4 w-4" />
      </button>

      <!-- Calendar Dropdown -->
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
          class="absolute z-50 mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 p-4"
          :class="dropdownPosition === 'up' ? 'bottom-full mb-1' : 'top-full'"
        >
          <!-- Calendar Header -->
          <div class="flex items-center justify-between mb-4">
            <button
              type="button"
              class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              @click="previousMonth"
            >
              <ChevronLeftIcon class="h-5 w-5 text-gray-600 dark:text-gray-400" />
            </button>
            
            <div class="flex items-center space-x-2">
              <!-- Month Selector -->
              <select
                v-model="currentMonth"
                class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-transparent border-0 focus:ring-0"
                @change="updateCalendar"
              >
                <option v-for="(month, index) in monthNames" :key="index" :value="index">
                  {{ month }}
                </option>
              </select>
              
              <!-- Year Selector -->
              <select
                v-model="currentYear"
                class="text-sm font-medium text-gray-900 dark:text-gray-100 bg-transparent border-0 focus:ring-0"
                @change="updateCalendar"
              >
                <option v-for="year in availableYears" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>
            
            <button
              type="button"
              class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              @click="nextMonth"
            >
              <ChevronRightIcon class="h-5 w-5 text-gray-600 dark:text-gray-400" />
            </button>
          </div>

          <!-- Calendar Grid -->
          <div class="grid grid-cols-7 gap-1 mb-2">
            <!-- Day Headers -->
            <div
              v-for="day in dayNames"
              :key="day"
              class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-2"
            >
              {{ day }}
            </div>
            
            <!-- Calendar Days -->
            <button
              v-for="date in calendarDays"
              :key="date.key"
              type="button"
              class="calendar-day"
              :class="[
                {
                  'text-gray-400 dark:text-gray-600': !date.inCurrentMonth,
                  'bg-blue-600 text-white': date.isSelected,
                  'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400': date.isToday && !date.isSelected,
                  'hover:bg-gray-100 dark:hover:bg-gray-700': !date.isDisabled && !date.isSelected,
                  'opacity-50 cursor-not-allowed': date.isDisabled,
                  'ring-2 ring-blue-500 ring-opacity-50': date.isSelected
                }
              ]"
              :disabled="date.isDisabled"
              @click="selectDate(date)"
            >
              {{ date.day }}
            </button>
          </div>

          <!-- Time Picker (if enabled) -->
          <div v-if="showTime" class="border-t border-gray-200 dark:border-gray-600 pt-4 mt-4">
            <div class="flex items-center justify-center space-x-2">
              <!-- Hours -->
              <select
                v-model="selectedHour"
                class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
              >
                <option v-for="hour in hours" :key="hour" :value="hour">
                  {{ String(hour).padStart(2, '0') }}
                </option>
              </select>
              
              <span class="text-gray-500 dark:text-gray-400">:</span>
              
              <!-- Minutes -->
              <select
                v-model="selectedMinute"
                class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
              >
                <option v-for="minute in minutes" :key="minute" :value="minute">
                  {{ String(minute).padStart(2, '0') }}
                </option>
              </select>

              <!-- AM/PM (12-hour format) -->
              <select
                v-if="!use24Hour"
                v-model="selectedPeriod"
                class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
              >
                <option value="AM">AM</option>
                <option value="PM">PM</option>
              </select>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="flex justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
            <button
              type="button"
              class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
              @click="selectToday"
            >
              Today
            </button>
            
            <div class="space-x-2">
              <button
                type="button"
                class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                @click="closeCalendar"
              >
                Cancel
              </button>
              <button
                type="button"
                class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                @click="confirmSelection"
              >
                OK
              </button>
            </div>
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
  CalendarDaysIcon,
  XMarkIcon,
  ChevronLeftIcon,
  ChevronRightIcon
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
    default: 'Select date'
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
  showTime: {
    type: Boolean,
    default: false
  },
  use24Hour: {
    type: Boolean,
    default: true
  },
  allowManualInput: {
    type: Boolean,
    default: false
  },
  format: {
    type: String,
    default: 'YYYY-MM-DD'
  },
  minDate: {
    type: [String, Date],
    default: null
  },
  maxDate: {
    type: [String, Date],
    default: null
  },
  disabledDates: {
    type: Array,
    default: () => []
  },
  disabledWeekdays: {
    type: Array,
    default: () => []
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

// Calendar state
const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())
const selectedHour = ref(0)
const selectedMinute = ref(0)
const selectedPeriod = ref('AM')

// Computed
const pickerId = computed(() => `datepicker-${Math.random().toString(36).substr(2, 9)}`)

const selectedDate = computed(() => {
  if (!props.modelValue) return null
  return props.modelValue instanceof Date ? props.modelValue : new Date(props.modelValue)
})

const displayValue = computed({
  get() {
    if (!selectedDate.value) return ''
    return formatDate(selectedDate.value)
  },
  set(value) {
    if (!value) {
      emit('update:modelValue', null)
      return
    }
    
    const parsed = parseDate(value)
    if (parsed) {
      emit('update:modelValue', parsed)
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

const monthNames = computed(() => [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
])

const dayNames = computed(() => ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'])

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let year = currentYear - 50; year <= currentYear + 10; year++) {
    years.push(year)
  }
  return years
})

const hours = computed(() => {
  if (props.use24Hour) {
    return Array.from({ length: 24 }, (_, i) => i)
  }
  return Array.from({ length: 12 }, (_, i) => i + 1)
})

const minutes = computed(() => {
  return Array.from({ length: 60 }, (_, i) => i)
})

const calendarDays = computed(() => {
  const days = []
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())

  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)
    
    const dayInfo = {
      key: `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`,
      date: date,
      day: date.getDate(),
      inCurrentMonth: date.getMonth() === currentMonth.value,
      isToday: isToday(date),
      isSelected: selectedDate.value && isSameDay(date, selectedDate.value),
      isDisabled: isDateDisabled(date)
    }
    
    days.push(dayInfo)
  }

  return days
})

// Methods
const formatDate = (date) => {
  if (!date) return ''
  
  // Simple format implementation - you might want to use a library like date-fns
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  
  if (props.showTime) {
    return `${day}/${month}/${year} ${hours}:${minutes}`
  }
  
  return `${day}/${month}/${year}`
}

const parseDate = (dateString) => {
  if (!dateString) return null
  
  // Simple parse implementation
  const parts = dateString.split(/[\s\/\-\.]/)
  if (parts.length >= 3) {
    const day = parseInt(parts[0])
    const month = parseInt(parts[1]) - 1
    const year = parseInt(parts[2])
    
    if (!isNaN(day) && !isNaN(month) && !isNaN(year)) {
      const date = new Date(year, month, day)
      
      if (props.showTime && parts.length >= 4) {
        const timeParts = parts[3].split(':')
        if (timeParts.length >= 2) {
          date.setHours(parseInt(timeParts[0]) || 0)
          date.setMinutes(parseInt(timeParts[1]) || 0)
        }
      }
      
      return date
    }
  }
  
  return null
}

const isToday = (date) => {
  const today = new Date()
  return isSameDay(date, today)
}

const isSameDay = (date1, date2) => {
  return date1.getFullYear() === date2.getFullYear() &&
         date1.getMonth() === date2.getMonth() &&
         date1.getDate() === date2.getDate()
}

const isDateDisabled = (date) => {
  // Check min/max dates
  if (props.minDate && date < new Date(props.minDate)) return true
  if (props.maxDate && date > new Date(props.maxDate)) return true
  
  // Check disabled weekdays
  if (props.disabledWeekdays.includes(date.getDay())) return true
  
  // Check disabled specific dates
  return props.disabledDates.some(disabledDate => 
    isSameDay(date, new Date(disabledDate))
  )
}

const openCalendar = () => {
  if (props.disabled) return
  
  isOpen.value = true
  emit('open')
  
  // Set calendar to selected date or current date
  if (selectedDate.value) {
    currentMonth.value = selectedDate.value.getMonth()
    currentYear.value = selectedDate.value.getFullYear()
    
    if (props.showTime) {
      selectedHour.value = props.use24Hour 
        ? selectedDate.value.getHours()
        : selectedDate.value.getHours() % 12 || 12
      selectedMinute.value = selectedDate.value.getMinutes()
      selectedPeriod.value = selectedDate.value.getHours() >= 12 ? 'PM' : 'AM'
    }
  }
  
  nextTick(() => calculateDropdownPosition())
}

const closeCalendar = () => {
  isOpen.value = false
  emit('close')
}

const selectDate = (dayInfo) => {
  if (dayInfo.isDisabled) return
  
  const newDate = new Date(dayInfo.date)
  
  if (props.showTime) {
    let hour = selectedHour.value
    if (!props.use24Hour) {
      if (selectedPeriod.value === 'PM' && hour !== 12) hour += 12
      if (selectedPeriod.value === 'AM' && hour === 12) hour = 0
    }
    
    newDate.setHours(hour, selectedMinute.value, 0, 0)
  }
  
  emit('update:modelValue', newDate)
  emit('change', newDate)
  
  if (!props.showTime) {
    closeCalendar()
  }
}

const selectToday = () => {
  const today = new Date()
  
  if (props.showTime) {
    today.setHours(selectedHour.value, selectedMinute.value, 0, 0)
  }
  
  emit('update:modelValue', today)
  emit('change', today)
  
  if (!props.showTime) {
    closeCalendar()
  }
}

const confirmSelection = () => {
  closeCalendar()
}

const clearDate = () => {
  emit('update:modelValue', null)
  emit('change', null)
}

const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

const updateCalendar = () => {
  // Force reactivity update
  nextTick()
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
    openCalendar()
  } else if (event.key === 'Escape') {
    closeCalendar()
  }
}

const calculateDropdownPosition = () => {
  if (!inputRef.value) return
  
  const rect = inputRef.value.getBoundingClientRect()
  const spaceBelow = window.innerHeight - rect.bottom
  const spaceAbove = rect.top
  
  dropdownPosition.value = spaceBelow < 400 && spaceAbove > spaceBelow ? 'up' : 'down'
}

const handleClickOutside = (event) => {
  if (inputRef.value && !inputRef.value.contains(event.target) && !event.target.closest('.calendar-day')) {
    closeCalendar()
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
  open: openCalendar,
  close: closeCalendar,
  focus: () => inputRef.value?.focus()
})
</script>

<style scoped>
.form-date-input {
  cursor: pointer;
}

.calendar-day {
  @apply w-8 h-8 text-sm rounded-md flex items-center justify-center transition-colors duration-150;
}

.calendar-day:hover:not(:disabled) {
  @apply bg-gray-100 dark:bg-gray-700;
}

.calendar-day:focus {
  @apply outline-none ring-2 ring-blue-500 ring-opacity-50;
}

/* Calendar animation */
.calendar-day {
  position: relative;
}

.calendar-day::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  transition: all 0.15s ease;
}

.calendar-day:hover::before {
  background-color: rgb(59 130 246 / 0.1);
}
</style>