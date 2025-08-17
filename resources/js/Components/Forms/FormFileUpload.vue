<!--
📁 File Structure:
resources/js/Components/Forms/FormFileUpload.vue

📁 Usage:
<FormFileUpload 
  v-model="form.documents"
  label="Event Documents"
  :accept="['pdf', 'doc', 'docx', 'jpg', 'png']"
  :max-size="10"
  :multiple="true"
  :max-files="5"
  show-preview
  drag-drop
  required
  :error-message="form.errors.documents"
  help-text="Upload event related documents (max 10MB each)"
/>

📁 Dependencies:
- @heroicons/vue/24/outline
- @heroicons/vue/24/solid
-->
<template>
  <div class="form-group">
    <!-- Label -->
    <label 
      v-if="label" 
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Upload Area -->
    <div
      class="upload-container"
      :class="[
        uploadContainerClasses,
        {
          'border-red-300 dark:border-red-600': hasError,
          'border-green-300 dark:border-green-600': hasSuccess && !hasError,
          'border-blue-400 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/20': isDragOver,
          'opacity-50 cursor-not-allowed': disabled
        }
      ]"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop"
      @click="triggerFileInput"
    >
      <!-- Hidden File Input -->
      <input
        ref="fileInputRef"
        type="file"
        class="hidden"
        :accept="acceptString"
        :multiple="multiple"
        :disabled="disabled"
        @change="handleFileSelect"
      />

      <!-- Drop Zone Content -->
      <div class="text-center py-8">
        <!-- Upload Icon -->
        <div class="mx-auto mb-4">
          <component 
            :is="isDragOver ? CloudArrowUpIcon : CloudArrowUpIcon"
            class="h-12 w-12 text-gray-400 mx-auto"
            :class="{ 'text-blue-500 animate-bounce': isDragOver }"
          />
        </div>

        <!-- Upload Text -->
        <div class="space-y-2">
          <p class="text-lg font-medium text-gray-700 dark:text-gray-300">
            {{ isDragOver ? 'Drop files here' : (uploadText || defaultUploadText) }}
          </p>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ dragDrop ? 'Drag and drop files here, or click to browse' : 'Click to browse files' }}
          </p>
          
          <!-- File Type Info -->
          <p v-if="acceptedTypes.length" class="text-xs text-gray-400 dark:text-gray-500">
            Accepted: {{ acceptedTypes.join(', ').toUpperCase() }}
            <span v-if="maxSize"> • Max size: {{ maxSize }}MB</span>
            <span v-if="multiple && maxFiles"> • Max files: {{ maxFiles }}</span>
          </p>
        </div>

        <!-- Browse Button -->
        <button
          v-if="!isDragOver"
          type="button"
          class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          :disabled="disabled"
        >
          <FolderOpenIcon class="h-4 w-4 mr-2" />
          Browse Files
        </button>
      </div>
    </div>

    <!-- File List -->
    <div v-if="fileList.length > 0" class="mt-4 space-y-3">
      <div class="flex items-center justify-between">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
          Selected Files ({{ fileList.length }})
        </h4>
        <button
          v-if="multiple && fileList.length > 1"
          type="button"
          class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
          @click="clearAllFiles"
        >
          Clear All
        </button>
      </div>

      <!-- Files -->
      <div class="space-y-2 max-h-64 overflow-y-auto">
        <div
          v-for="(file, index) in fileList"
          :key="file.id"
          class="file-item"
          :class="{
            'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20': file.error,
            'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20': file.uploaded,
            'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800': !file.error && !file.uploaded
          }"
        >
          <div class="flex items-center space-x-3">
            <!-- File Icon/Preview -->
            <div class="flex-shrink-0">
              <!-- Image Preview -->
              <div
                v-if="file.preview && showPreview"
                class="w-10 h-10 rounded overflow-hidden bg-gray-100 dark:bg-gray-700"
              >
                <img
                  :src="file.preview"
                  :alt="file.name"
                  class="w-full h-full object-cover"
                />
              </div>
              <!-- File Icon -->
              <div
                v-else
                class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center"
              >
                <component
                  :is="getFileIcon(file.type)"
                  class="h-5 w-5 text-gray-500 dark:text-gray-400"
                />
              </div>
            </div>

            <!-- File Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                  {{ file.name }}
                </p>
                <button
                  type="button"
                  class="ml-2 flex-shrink-0 text-gray-400 hover:text-red-500 dark:hover:text-red-400"
                  @click="removeFile(index)"
                >
                  <XMarkIcon class="h-4 w-4" />
                </button>
              </div>
              
              <div class="flex items-center space-x-2 mt-1">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatFileSize(file.size) }}
                </p>
                
                <!-- Upload Status -->
                <div v-if="file.uploading" class="flex items-center space-x-1">
                  <LoadingSpinner class="h-3 w-3" />
                  <span class="text-xs text-blue-600 dark:text-blue-400">
                    {{ file.progress }}%
                  </span>
                </div>
                <div v-else-if="file.uploaded" class="flex items-center space-x-1">
                  <CheckCircleIcon class="h-3 w-3 text-green-500" />
                  <span class="text-xs text-green-600 dark:text-green-400">
                    Uploaded
                  </span>
                </div>
                <div v-else-if="file.error" class="flex items-center space-x-1">
                  <XCircleIcon class="h-3 w-3 text-red-500" />
                  <span class="text-xs text-red-600 dark:text-red-400">
                    {{ file.errorMessage }}
                  </span>
                </div>
              </div>
              
              <!-- Progress Bar -->
              <div
                v-if="file.uploading && file.progress > 0"
                class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1"
              >
                <div
                  class="bg-blue-600 h-1 rounded-full transition-all duration-300"
                  :style="{ width: `${file.progress}%` }"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Actions -->
    <div v-if="fileList.length > 0 && !autoUpload" class="mt-4 flex justify-between">
      <div class="text-sm text-gray-500 dark:text-gray-400">
        {{ fileList.filter(f => !f.error).length }} of {{ fileList.length }} files ready
      </div>
      <div class="space-x-2">
        <button
          type="button"
          class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
          @click="clearAllFiles"
        >
          Clear
        </button>
        <button
          type="button"
          class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
          :disabled="!hasValidFiles"
          @click="uploadFiles"
        >
          Upload Files
        </button>
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
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { 
  CloudArrowUpIcon,
  FolderOpenIcon,
  XMarkIcon,
  CheckCircleIcon,
  XCircleIcon,
  DocumentIcon,
  PhotoIcon,
  FilmIcon,
  MusicalNoteIcon,
  ArchiveBoxIcon
} from '@heroicons/vue/24/outline'
import LoadingSpinner from './LoadingSpinner.vue'

// Props
const props = defineProps({
  modelValue: {
    type: [Array, File, String],
    default: () => []
  },
  label: {
    type: String,
    default: ''
  },
  uploadText: {
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
  required: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  accept: {
    type: Array,
    default: () => []
  },
  maxSize: {
    type: Number,
    default: 10 // MB
  },
  maxFiles: {
    type: Number,
    default: null
  },
  dragDrop: {
    type: Boolean,
    default: true
  },
  showPreview: {
    type: Boolean,
    default: true
  },
  autoUpload: {
    type: Boolean,
    default: false
  },
  uploadUrl: {
    type: String,
    default: ''
  },
  uploadHeaders: {
    type: Object,
    default: () => ({})
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'change',
  'upload',
  'upload-progress',
  'upload-success',
  'upload-error',
  'file-added',
  'file-removed'
])

// Refs
const fileInputRef = ref(null)
const isDragOver = ref(false)
const fileList = ref([])
const nextFileId = ref(1)

// Computed
const hasError = computed(() => Boolean(props.errorMessage))
const hasSuccess = computed(() => Boolean(props.successMessage))

const acceptedTypes = computed(() => {
  if (props.accept.length === 0) return []
  return props.accept.map(type => type.startsWith('.') ? type.slice(1) : type)
})

const acceptString = computed(() => {
  if (props.accept.length === 0) return ''
  return props.accept.map(type => {
    if (type.startsWith('.')) return type
    return `.${type}`
  }).join(',')
})

const defaultUploadText = computed(() => {
  return props.multiple ? 'Upload files' : 'Upload file'
})

const uploadContainerClasses = computed(() => [
  'relative',
  'border-2',
  'border-dashed',
  'border-gray-300',
  'dark:border-gray-600',
  'rounded-lg',
  'cursor-pointer',
  'transition-colors',
  'duration-200',
  'hover:border-gray-400',
  'dark:hover:border-gray-500',
  'focus-within:border-blue-500',
  'focus-within:ring-2',
  'focus-within:ring-blue-500',
  'focus-within:ring-opacity-50'
])

const hasValidFiles = computed(() => {
  return fileList.value.some(file => !file.error && !file.uploaded)
})

// Methods
const triggerFileInput = () => {
  if (props.disabled) return
  fileInputRef.value?.click()
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files || [])
  processFiles(files)
  
  // Clear input for re-selection
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

const handleDragOver = (event) => {
  if (props.disabled || !props.dragDrop) return
  
  event.preventDefault()
  isDragOver.value = true
}

const handleDragLeave = (event) => {
  if (props.disabled || !props.dragDrop) return
  
  event.preventDefault()
  // Only set to false if leaving the container completely
  if (!event.currentTarget.contains(event.relatedTarget)) {
    isDragOver.value = false
  }
}

const handleDrop = (event) => {
  if (props.disabled || !props.dragDrop) return
  
  event.preventDefault()
  isDragOver.value = false
  
  const files = Array.from(event.dataTransfer?.files || [])
  processFiles(files)
}

const processFiles = (files) => {
  if (!files.length) return
  
  // Check max files limit
  if (props.maxFiles && (fileList.value.length + files.length) > props.maxFiles) {
    const remaining = props.maxFiles - fileList.value.length
    files = files.slice(0, remaining)
    
    if (remaining <= 0) {
      alert(`Maximum ${props.maxFiles} files allowed`)
      return
    }
  }
  
  // Clear existing files if not multiple
  if (!props.multiple) {
    fileList.value = []
  }
  
  files.forEach(file => {
    const fileData = {
      id: nextFileId.value++,
      file: file,
      name: file.name,
      size: file.size,
      type: file.type,
      preview: null,
      uploading: false,
      uploaded: false,
      progress: 0,
      error: false,
      errorMessage: ''
    }
    
    // Validate file
    const validation = validateFile(file)
    if (!validation.valid) {
      fileData.error = true
      fileData.errorMessage = validation.message
    }
    
    // Generate preview for images
    if (file.type.startsWith('image/') && props.showPreview) {
      generatePreview(file, fileData)
    }
    
    fileList.value.push(fileData)
    emit('file-added', fileData)
  })
  
  updateModelValue()
  
  // Auto upload if enabled
  if (props.autoUpload && hasValidFiles.value) {
    nextTick(() => uploadFiles())
  }
}

const validateFile = (file) => {
  // Check file type
  if (props.accept.length > 0) {
    const fileExt = file.name.split('.').pop()?.toLowerCase()
    const isValidType = props.accept.some(type => {
      const acceptType = type.startsWith('.') ? type.slice(1) : type
      return acceptType.toLowerCase() === fileExt || 
             file.type.includes(acceptType.toLowerCase())
    })
    
    if (!isValidType) {
      return {
        valid: false,
        message: `Invalid file type. Accepted: ${acceptedTypes.value.join(', ')}`
      }
    }
  }
  
  // Check file size
  if (props.maxSize && (file.size / 1024 / 1024) > props.maxSize) {
    return {
      valid: false,
      message: `File too large. Max size: ${props.maxSize}MB`
    }
  }
  
  return { valid: true }
}

const generatePreview = (file, fileData) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    fileData.preview = e.target?.result
  }
  reader.readAsDataURL(file)
}

const removeFile = (index) => {
  const removedFile = fileList.value.splice(index, 1)[0]
  emit('file-removed', removedFile)
  updateModelValue()
}

const clearAllFiles = () => {
  fileList.value = []
  updateModelValue()
}

const uploadFiles = async () => {
  const filesToUpload = fileList.value.filter(f => !f.error && !f.uploaded && !f.uploading)
  
  for (const fileData of filesToUpload) {
    await uploadSingleFile(fileData)
  }
}

const uploadSingleFile = async (fileData) => {
  if (!props.uploadUrl) {
    emit('upload', fileData)
    return
  }
  
  fileData.uploading = true
  fileData.progress = 0
  
  try {
    const formData = new FormData()
    formData.append('file', fileData.file)
    
    const xhr = new XMLHttpRequest()
    
    // Progress tracking
    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        fileData.progress = Math.round((e.loaded / e.total) * 100)
        emit('upload-progress', fileData, e)
      }
    })
    
    // Success
    xhr.addEventListener('load', () => {
      if (xhr.status >= 200 && xhr.status < 300) {
        fileData.uploading = false
        fileData.uploaded = true
        fileData.progress = 100
        emit('upload-success', fileData, xhr.response)
      } else {
        throw new Error(`Upload failed: ${xhr.status}`)
      }
    })
    
    // Error
    xhr.addEventListener('error', () => {
      throw new Error('Upload failed')
    })
    
    // Start upload
    xhr.open('POST', props.uploadUrl)
    
    // Add headers
    Object.entries(props.uploadHeaders).forEach(([key, value]) => {
      xhr.setRequestHeader(key, value)
    })
    
    xhr.send(formData)
    
  } catch (error) {
    fileData.uploading = false
    fileData.error = true
    fileData.errorMessage = error.message || 'Upload failed'
    emit('upload-error', fileData, error)
  }
}

const updateModelValue = () => {
  const files = fileList.value.map(f => f.file)
  
  if (props.multiple) {
    emit('update:modelValue', files)
  } else {
    emit('update:modelValue', files[0] || null)
  }
  
  emit('change', files)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getFileIcon = (fileType) => {
  if (fileType.startsWith('image/')) return PhotoIcon
  if (fileType.startsWith('video/')) return FilmIcon
  if (fileType.startsWith('audio/')) return MusicalNoteIcon
  if (fileType.includes('zip') || fileType.includes('rar')) return ArchiveBoxIcon
  return DocumentIcon
}

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  if (!newValue) {
    fileList.value = []
  }
}, { immediate: true })

// Expose methods
defineExpose({
  triggerFileInput,
  uploadFiles,
  clearAllFiles,
  fileList: fileList
})
</script>

<style scoped>
.file-item {
  @apply p-3 rounded-lg border transition-colors duration-150;
}

.upload-container {
  background-image: 
    radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.05) 0%, transparent 50%),
    radial-gradient(circle at 75% 75%, rgba(168, 85, 247, 0.05) 0%, transparent 50%);
}

.upload-container:hover {
  background-image: 
    radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
    radial-gradient(circle at 75% 75%, rgba(168, 85, 247, 0.08) 0%, transparent 50%);
}

/* Custom scrollbar for file list */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgb(156 163 175) transparent;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgb(156 163 175);
  border-radius: 3px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgb(75 85 99);
}

/* Drag animation */
@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0,0,0);
  }
  40%, 43% {
    transform: translate3d(0, -8px, 0);
  }
  70% {
    transform: translate3d(0, -4px, 0);
  }
  90% {
    transform: translate3d(0, -2px, 0);
  }
}

.animate-bounce {
  animation: bounce 1s infinite;
}
</style>