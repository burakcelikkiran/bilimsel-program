<!-- resources/js/Components/UI/Modal.vue -->
<template>
  <TransitionRoot :show="show" as="template">
    <Dialog as="div" class="relative z-50" @close="closeModal">
      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <!-- Modal Container -->
      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              :class="[
                'relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all dark:bg-gray-800',
                maxWidthClass,
                'sm:my-8 sm:w-full sm:p-6'
              ]"
            >
              <!-- Header -->
              <div v-if="title || $slots.header" class="flex items-center justify-between mb-4">
                <div>
                  <DialogTitle
                    v-if="title"
                    as="h3"
                    class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100"
                  >
                    {{ title }}
                  </DialogTitle>
                  <slot name="header" />
                </div>
                
                <!-- Close Button -->
                <button
                  v-if="closable"
                  type="button"
                  class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-500 dark:hover:text-gray-400"
                  @click="closeModal"
                >
                  <span class="sr-only">Kapat</span>
                  <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                </button>
              </div>

              <!-- Subtitle -->
              <div v-if="subtitle" class="mb-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ subtitle }}
                </p>
              </div>

              <!-- Content -->
              <div class="mt-2">
                <slot />
              </div>

              <!-- Footer -->
              <div v-if="$slots.footer" class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <slot name="footer" />
              </div>

              <!-- Default Actions -->
              <div
                v-else-if="showDefaultActions"
                class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0"
              >
                <!-- Cancel Button -->
                <button
                  v-if="showCancelButton"
                  type="button"
                  class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:w-auto dark:bg-gray-700 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-600"
                  @click="cancel"
                >
                  {{ cancelText }}
                </button>

                <!-- Confirm Button -->
                <button
                  v-if="showConfirmButton"
                  type="button"
                  :class="[
                    'inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:w-auto',
                    confirmButtonClasses
                  ]"
                  :disabled="confirmLoading"
                  @click="confirm"
                >
                  <svg
                    v-if="confirmLoading"
                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    ></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  {{ confirmText }}
                </button>
              </div>

              <!-- Loading Overlay -->
              <div
                v-if="loading"
                class="absolute inset-0 bg-white bg-opacity-50 flex items-center justify-center dark:bg-gray-800 dark:bg-opacity-50"
              >
                <div class="flex items-center space-x-2">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ loadingText }}</span>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: null
  },
  subtitle: {
    type: String,
    default: null
  },
  maxWidth: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'].includes(value)
  },
  closable: {
    type: Boolean,
    default: true
  },
  closeOnEscape: {
    type: Boolean,
    default: true
  },
  closeOnBackdropClick: {
    type: Boolean,
    default: true
  },
  showDefaultActions: {
    type: Boolean,
    default: false
  },
  showCancelButton: {
    type: Boolean,
    default: true
  },
  showConfirmButton: {
    type: Boolean,
    default: true
  },
  cancelText: {
    type: String,
    default: 'İptal'
  },
  confirmText: {
    type: String,
    default: 'Onayla'
  },
  confirmVariant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'danger'].includes(value)
  },
  confirmLoading: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'Yükleniyor...'
  }
})

const emit = defineEmits(['close', 'confirm', 'cancel'])

// Computed
const maxWidthClass = computed(() => {
  const sizes = {
    xs: 'sm:max-w-xs',
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
    '4xl': 'sm:max-w-4xl',
    '5xl': 'sm:max-w-5xl',
    '6xl': 'sm:max-w-6xl',
    '7xl': 'sm:max-w-7xl'
  }
  return sizes[props.maxWidth]
})

const confirmButtonClasses = computed(() => {
  const variants = {
    primary: 'bg-blue-600 hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600',
    secondary: 'bg-gray-600 hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600',
    success: 'bg-green-600 hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600',
    warning: 'bg-yellow-600 hover:bg-yellow-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-600',
    danger: 'bg-red-600 hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600'
  }
  return variants[props.confirmVariant]
})

// Methods
const closeModal = () => {
  if (props.closeOnBackdropClick) {
    emit('close')
  }
}

const confirm = () => {
  emit('confirm')
}

const cancel = () => {
  emit('cancel')
  emit('close')
}
</script>

<style scoped>
/* Custom scrollbar for modal content */
.modal-content::-webkit-scrollbar {
  width: 6px;
}

.modal-content::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.modal-content::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Dark mode scrollbar */
.dark .modal-content::-webkit-scrollbar-track {
  background: #374151;
}

.dark .modal-content::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark .modal-content::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>