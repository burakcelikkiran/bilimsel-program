<!-- resources/js/Components/Export/TimelineExportModal.vue -->
<template>
  <!-- Export Modal -->
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-50" @close="closeModal">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-6xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
              <!-- Modal Header -->
              <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div>
                  <DialogTitle as="h3" class="text-lg font-semibold text-gray-900">
                    Program Zaman Çizelgesi - Dışa Aktarım
                  </DialogTitle>
                  <p class="text-sm text-gray-600 mt-1">
                    {{ event?.name || 'Etkinlik' }} programını farklı formatlarda kaydedin
                  </p>
                </div>
                
                <div class="flex items-center space-x-3">
                  <!-- Quick Export Buttons -->
                  <button
                    @click="quickExport('pdf')"
                    :disabled="exporting"
                    class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                  >
                    <DocumentArrowDownIcon class="h-4 w-4 mr-1" />
                    PDF
                  </button>
                  
                  <button
                    @click="quickExport('image')"
                    :disabled="exporting"
                    class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                  >
                    <PhotoIcon class="h-4 w-4 mr-1" />
                    PNG
                  </button>
                  
                  <button
                    @click="closeModal"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                  >
                    <XMarkIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <!-- Modal Content -->
              <div class="p-6 max-h-[70vh] overflow-y-auto">
                <!-- Export Status -->
                <div v-if="exporting" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                  <div class="flex items-center">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-3"></div>
                    <div>
                      <p class="text-sm font-medium text-blue-900">{{ exportStatus }}</p>
                      <p class="text-xs text-blue-700 mt-1">Lütfen bekleyin, işlem tamamlanıyor...</p>
                    </div>
                  </div>
                  
                  <!-- Progress Bar -->
                  <div class="mt-3 w-full bg-blue-200 rounded-full h-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: exportProgress + '%' }"
                    ></div>
                  </div>
                </div>

                <!-- Export Success -->
                <div v-if="exportSuccess" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                  <div class="flex items-center">
                    <CheckCircleIcon class="h-5 w-5 text-green-600 mr-3" />
                    <div>
                      <p class="text-sm font-medium text-green-900">Dışa aktarım başarılı!</p>
                      <p class="text-xs text-green-700 mt-1">
                        {{ lastExportInfo?.format?.toUpperCase() }} dosyası başarıyla oluşturuldu ve indirildi.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Export Error -->
                <div v-if="exportError" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                  <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-5 w-5 text-red-600 mr-3" />
                    <div>
                      <p class="text-sm font-medium text-red-900">Dışa aktarım hatası!</p>
                      <p class="text-xs text-red-700 mt-1">{{ exportError }}</p>
                    </div>
                  </div>
                </div>

                <!-- Timeline Export Component -->
                <TimelineExport
                  :event="event"
                  :event-days="eventDays"
                  :sessions="sessions"
                  :conflicts="conflicts"
                  @export-complete="handleExportComplete"
                  @export-error="handleExportError"
                />
              </div>

              <!-- Modal Footer -->
              <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                  <span class="font-medium">{{ sessions.length }}</span> oturum •
                  <span class="font-medium">{{ eventDays.length }}</span> gün •
                  <span class="font-medium">{{ uniqueVenues }}</span> salon
                </div>
                
                <div class="flex space-x-3">
                  <button
                    @click="previewMode = !previewMode"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors"
                  >
                    <EyeIcon class="h-4 w-4 mr-2" />
                    {{ previewMode ? 'Düzenleme Moduna Dön' : 'Önizleme' }}
                  </button>
                  
                  <button
                    @click="closeModal"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors"
                  >
                    Kapat
                  </button>
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
import { ref, computed, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import TimelineExport from './TimelineExport.vue'
import {
  XMarkIcon,
  DocumentArrowDownIcon,
  PhotoIcon,
  EyeIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  event: {
    type: Object,
    required: true
  },
  eventDays: {
    type: Array,
    default: () => []
  },
  sessions: {
    type: Array,
    default: () => []
  },
  conflicts: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['close', 'export-complete'])

// State
const exporting = ref(false)
const exportProgress = ref(0)
const exportStatus = ref('')
const exportSuccess = ref(false)
const exportError = ref(null)
const lastExportInfo = ref(null)
const previewMode = ref(false)

// Computed
const uniqueVenues = computed(() => {
  const venues = new Set()
  props.sessions.forEach(session => {
    if (session.venue?.id) {
      venues.add(session.venue.id)
    }
  })
  return venues.size
})

// Methods
const closeModal = () => {
  // Reset states
  exporting.value = false
  exportProgress.value = 0
  exportStatus.value = ''
  exportSuccess.value = false
  exportError.value = null
  previewMode.value = false
  
  emit('close')
}

const quickExport = async (format) => {
  if (exporting.value) return
  
  exporting.value = true
  exportProgress.value = 0
  exportSuccess.value = false
  exportError.value = null
  
  // Simulate progress for better UX
  const progressInterval = setInterval(() => {
    if (exportProgress.value < 90) {
      exportProgress.value += Math.random() * 10
    }
  }, 200)
  
  try {
    switch (format) {
      case 'pdf':
        exportStatus.value = 'PDF oluşturuluyor...'
        break
      case 'image':
        exportStatus.value = 'Görsel oluşturuluyor...'
        break
    }
    
    // Trigger export through TimelineExport component
    // This is a simplified approach - in real implementation,
    // you would emit an event to the child component
    await simulateExport(format)
    
    clearInterval(progressInterval)
    exportProgress.value = 100
    exportSuccess.value = true
    lastExportInfo.value = { format }
    
    emit('export-complete', { format })
    
  } catch (error) {
    clearInterval(progressInterval)
    exportError.value = error.message || 'Dışa aktarım sırasında bir hata oluştu'
  } finally {
    exporting.value = false
    
    // Auto-hide success message after 3 seconds
    if (exportSuccess.value) {
      setTimeout(() => {
        exportSuccess.value = false
      }, 3000)
    }
  }
}

const simulateExport = (format) => {
  return new Promise((resolve, reject) => {
    // Simulate export process
    setTimeout(() => {
      if (Math.random() > 0.1) { // 90% success rate
        resolve()
      } else {
        reject(new Error('Simüle edilmiş export hatası'))
      }
    }, 2000)
  })
}

const handleExportComplete = (exportInfo) => {
  exportSuccess.value = true
  lastExportInfo.value = exportInfo
  emit('export-complete', exportInfo)
  
  // Auto-hide after 3 seconds
  setTimeout(() => {
    exportSuccess.value = false
  }, 3000)
}

const handleExportError = (error) => {
  exportError.value = error.message || 'Dışa aktarım sırasında bir hata oluştu'
  exporting.value = false
}

// Watch for modal close
watch(() => props.show, (newValue) => {
  if (!newValue) {
    // Reset states when modal closes
    setTimeout(() => {
      exportSuccess.value = false
      exportError.value = null
      previewMode.value = false
    }, 300)
  }
})
</script>

<style scoped>
/* Additional modal-specific styles */
.modal-content {
  max-height: calc(100vh - 8rem);
}

/* Scrollbar styling for modal content */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>