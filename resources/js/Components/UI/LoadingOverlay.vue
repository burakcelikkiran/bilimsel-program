<!-- resources/js/Components/UI/LoadingOverlay.vue -->
<template>
  <div
    v-if="show"
    class="loading-overlay"
    :class="overlayClasses"
    @click.self="handleBackdropClick"
  >
    <!-- Loading Content -->
    <div class="loading-content" :class="contentClasses">
      <!-- Custom Loading Slot -->
      <slot name="loading" :loading="loading">
        <!-- Default Loading Content -->
        <div class="flex flex-col items-center justify-center space-y-4">
          <!-- Spinner -->
          <div class="relative">
            <div 
              class="animate-spin rounded-full border-solid" 
              :class="spinnerClasses"
            ></div>
            
            <!-- Optional Icon in Center -->
            <div 
              v-if="icon" 
              class="absolute inset-0 flex items-center justify-center"
            >
              <component :is="icon" class="h-6 w-6 text-gray-400" />
            </div>
          </div>

          <!-- Loading Text -->
          <div v-if="message" class="text-center">
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              {{ message }}
            </p>
            <p v-if="description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ description }}
            </p>
          </div>

          <!-- Progress Bar -->
          <div v-if="showProgress && progress !== null" class="w-full max-w-xs">
            <div class="bg-gray-200 rounded-full h-2 dark:bg-gray-700">
              <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                :style="{ width: `${Math.min(Math.max(progress, 0), 100)}%` }"
              ></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">
              {{ Math.round(progress) }}%
            </p>
          </div>

          <!-- Cancel Button -->
          <button
            v-if="cancellable"
            type="button"
            class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
            @click="handleCancel"
          >
            {{ cancelText }}
          </button>
        </div>
      </slot>
    </div>

    <!-- Background Blur Effect -->
    <div
      v-if="blur"
      class="absolute inset-0 backdrop-blur-sm pointer-events-none"
    ></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  message: {
    type: String,
    default: 'Yükleniyor...'
  },
  description: {
    type: String,
    default: null
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  variant: {
    type: String,
    default: 'light',
    validator: (value) => ['light', 'dark', 'transparent'].includes(value)
  },
  position: {
    type: String,
    default: 'fixed',
    validator: (value) => ['fixed', 'absolute'].includes(value)
  },
  zIndex: {
    type: [String, Number],
    default: 50
  },
  blur: {
    type: Boolean,
    default: true
  },
  dismissible: {
    type: Boolean,
    default: false
  },
  cancellable: {
    type: Boolean,
    default: false
  },
  cancelText: {
    type: String,
    default: 'İptal'
  },
  showProgress: {
    type: Boolean,
    default: false
  },
  progress: {
    type: Number,
    default: null
  },
  icon: {
    type: [String, Object],
    default: null
  },
  loading: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits([
  'cancel',
  'backdrop-click',
  'mounted',
  'unmounted'
])

const overlayClasses = computed(() => {
  const classes = [
    'loading-overlay',
    'flex',
    'items-center',
    'justify-center',
    'inset-0'
  ]

  // Position
  classes.push(props.position)

  // Z-index
  classes.push(`z-${props.zIndex}`)

  // Background variant
  const variants = {
    light: 'bg-white bg-opacity-80 dark:bg-gray-900 dark:bg-opacity-80',
    dark: 'bg-gray-900 bg-opacity-80',
    transparent: 'bg-transparent'
  }
  classes.push(variants[props.variant])

  return classes.join(' ')
})

const contentClasses = computed(() => {
  const classes = ['relative', 'z-10']

  // Size classes
  const sizes = {
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
    xl: 'p-10'
  }
  classes.push(sizes[props.size])

  return classes.join(' ')
})

const spinnerClasses = computed(() => {
  const sizes = {
    sm: 'h-8 w-8 border-2',
    md: 'h-12 w-12 border-3',
    lg: 'h-16 w-16 border-4',
    xl: 'h-20 w-20 border-4'
  }

  const variants = {
    light: 'border-blue-600 border-t-transparent',
    dark: 'border-white border-t-transparent',
    transparent: 'border-blue-600 border-t-transparent'
  }

  return `${sizes[props.size]} ${variants[props.variant]}`
})

const handleBackdropClick = () => {
  if (props.dismissible) {
    emit('backdrop-click')
  }
}

const handleCancel = () => {
  emit('cancel')
}

// Prevent body scroll when overlay is active
onMounted(() => {
  if (props.show) {
    document.body.style.overflow = 'hidden'
    emit('mounted')
  }
})

onUnmounted(() => {
  document.body.style.overflow = ''
  emit('unmounted')
})

// Watch for show prop changes to control body scroll
import { watch } from 'vue'
watch(() => props.show, (newValue) => {
  if (newValue) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})
</script>

<style scoped>
.loading-overlay {
  transition: opacity 0.3s ease-in-out;
}

.loading-content {
  animation: fadeInScale 0.3s ease-out;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>