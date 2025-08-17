<!-- resources/js/Components/UI/FlashMessages.vue -->
<template>
  <div
    v-if="hasMessages"
    class="fixed top-4 right-4 z-50 space-y-2"
  >
    <!-- Success Messages -->
    <div
      v-for="message in successMessages"
      :key="message.id"
      class="bg-green-50 border border-green-200 rounded-md p-4 shadow-lg transform transition-all duration-300 ease-in-out dark:bg-green-900/20 dark:border-green-800"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <CheckCircleIcon class="h-5 w-5 text-green-400" aria-hidden="true" />
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium text-green-800 dark:text-green-200">
            {{ message.text }}
          </p>
        </div>
        <div class="ml-auto pl-3">
          <div class="-mx-1.5 -my-1.5">
            <button
              type="button"
              class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40"
              @click="removeMessage(message.id)"
            >
              <span class="sr-only">Kapat</span>
              <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Messages -->
    <div
      v-for="message in errorMessages"
      :key="message.id"
      class="bg-red-50 border border-red-200 rounded-md p-4 shadow-lg transform transition-all duration-300 ease-in-out dark:bg-red-900/20 dark:border-red-800"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <XCircleIcon class="h-5 w-5 text-red-400" aria-hidden="true" />
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium text-red-800 dark:text-red-200">
            {{ message.text }}
          </p>
        </div>
        <div class="ml-auto pl-3">
          <div class="-mx-1.5 -my-1.5">
            <button
              type="button"
              class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
              @click="removeMessage(message.id)"
            >
              <span class="sr-only">Kapat</span>
              <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Warning Messages -->
    <div
      v-for="message in warningMessages"
      :key="message.id"
      class="bg-yellow-50 border border-yellow-200 rounded-md p-4 shadow-lg transform transition-all duration-300 ease-in-out dark:bg-yellow-900/20 dark:border-yellow-800"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400" aria-hidden="true" />
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
            {{ message.text }}
          </p>
        </div>
        <div class="ml-auto pl-3">
          <div class="-mx-1.5 -my-1.5">
            <button
              type="button"
              class="inline-flex rounded-md bg-yellow-50 p-1.5 text-yellow-500 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-600 focus:ring-offset-2 focus:ring-offset-yellow-50 dark:bg-yellow-900/20 dark:text-yellow-400 dark:hover:bg-yellow-900/40"
              @click="removeMessage(message.id)"
            >
              <span class="sr-only">Kapat</span>
              <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Messages -->
    <div
      v-for="message in infoMessages"
      :key="message.id"
      class="bg-blue-50 border border-blue-200 rounded-md p-4 shadow-lg transform transition-all duration-300 ease-in-out dark:bg-blue-900/20 dark:border-blue-800"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <InformationCircleIcon class="h-5 w-5 text-blue-400" aria-hidden="true" />
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
            {{ message.text }}
          </p>
        </div>
        <div class="ml-auto pl-3">
          <div class="-mx-1.5 -my-1.5">
            <button
              type="button"
              class="inline-flex rounded-md bg-blue-50 p-1.5 text-blue-500 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 focus:ring-offset-blue-50 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40"
              @click="removeMessage(message.id)"
            >
              <span class="sr-only">Kapat</span>
              <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import {
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

// State
const messages = ref([])

// Get flash messages from Inertia page props
const page = usePage()

// Computed
const hasMessages = computed(() => messages.value.length > 0)

const successMessages = computed(() => 
  messages.value.filter(m => m.type === 'success')
)

const errorMessages = computed(() => 
  messages.value.filter(m => m.type === 'error')
)

const warningMessages = computed(() => 
  messages.value.filter(m => m.type === 'warning')
)

const infoMessages = computed(() => 
  messages.value.filter(m => m.type === 'info')
)

// Methods
const addMessage = (type, text, duration = 5000) => {
  const id = Date.now() + Math.random()
  const message = { id, type, text }
  
  messages.value.push(message)
  
  // Auto remove after duration
  if (duration > 0) {
    setTimeout(() => {
      removeMessage(id)
    }, duration)
  }
}

const removeMessage = (id) => {
  const index = messages.value.findIndex(m => m.id === id)
  if (index > -1) {
    messages.value.splice(index, 1)
  }
}

const clearAllMessages = () => {
  messages.value = []
}

// Process Laravel flash messages
const processFlashMessages = () => {
  const flash = page.props.flash || {}
  
  if (flash.success) {
    addMessage('success', flash.success)
  }
  
  if (flash.error) {
    addMessage('error', flash.error)
  }
  
  if (flash.warning) {
    addMessage('warning', flash.warning)
  }
  
  if (flash.info) {
    addMessage('info', flash.info)
  }

  // Handle errors object (validation errors)
  if (page.props.errors && Object.keys(page.props.errors).length > 0) {
    const errorMessages = Object.values(page.props.errors)
    errorMessages.forEach(error => {
      if (Array.isArray(error)) {
        error.forEach(err => addMessage('error', err))
      } else {
        addMessage('error', error)
      }
    })
  }
}

// Lifecycle
onMounted(() => {
  processFlashMessages()
})

// Expose methods for global use
defineExpose({
  addMessage,
  removeMessage,
  clearAllMessages
})
</script>

<style scoped>
/* Custom animations */
.flash-enter-active,
.flash-leave-active {
  transition: all 0.3s ease;
}

.flash-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.flash-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

/* Stacking context for multiple messages */
.flash-message {
  margin-bottom: 0.5rem;
}

.flash-message:last-child {
  margin-bottom: 0;
}
</style>