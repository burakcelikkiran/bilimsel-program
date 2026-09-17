<script setup>
import { computed, ref, watch } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import Modal from '@/Components/UI/Modal.vue'
import {
  ArrowUpTrayIcon,
  DocumentTextIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  eventSlug: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close'])

const step = ref('upload')
const jsonText = ref('')
const selectedFile = ref(null)
const selectedFileName = ref('')
const clientError = ref('')
const serverErrors = ref({})
const processing = ref(false)
const preview = ref(null)
const result = ref(null)
const fileInput = ref(null)

const title = computed(() => {
  if (step.value === 'preview') {
    return 'İçe aktarmayı onayla'
  }

  if (step.value === 'result') {
    return 'İçe aktarma tamamlandı'
  }

  return 'Program JSON içe aktar'
})

const subtitle = computed(() => {
  if (step.value === 'upload') {
    return 'Dışa aktarılan program JSON dosyasını yükleyin veya içeriği yapıştırın.'
  }

  if (step.value === 'preview') {
    return 'Mevcut program silinip bu içerikle değiştirilecek.'
  }

  return 'Program verisi etkinliğe yazıldı.'
})

watch(() => props.show, (isOpen) => {
  if (isOpen) {
    resetState()
  }
})

const resetState = () => {
  step.value = 'upload'
  jsonText.value = ''
  selectedFile.value = null
  selectedFileName.value = ''
  clientError.value = ''
  serverErrors.value = {}
  processing.value = false
  preview.value = null
  result.value = null

  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const close = () => {
  const shouldReload = step.value === 'result'

  emit('close')

  if (shouldReload) {
    router.reload()
  }
}

const onFileChange = (event) => {
  const file = event.target.files?.[0] ?? null
  selectedFile.value = file
  selectedFileName.value = file?.name ?? ''
  clientError.value = ''
  serverErrors.value = {}
}

const clearFile = () => {
  selectedFile.value = null
  selectedFileName.value = ''

  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const buildFormData = () => {
  const formData = new FormData()

  if (selectedFile.value) {
    formData.append('file', selectedFile.value)
  } else {
    formData.append('json', jsonText.value)
  }

  return formData
}

const csrfHeaders = () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

  return csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}
}

const extractErrorMessage = (error) => {
  const errors = error.response?.data?.errors

  if (errors && typeof errors === 'object') {
    serverErrors.value = errors
    const firstKey = Object.keys(errors)[0]

    if (firstKey && Array.isArray(errors[firstKey]) && errors[firstKey][0]) {
      return errors[firstKey][0]
    }
  }

  return error.response?.data?.message || 'İçe aktarma sırasında bir hata oluştu.'
}

const validateClientPayload = () => {
  clientError.value = ''
  serverErrors.value = {}

  if (selectedFile.value) {
    return true
  }

  const trimmed = jsonText.value.trim()

  if (trimmed === '') {
    clientError.value = 'JSON dosyası seçin veya JSON metni yapıştırın.'

    return false
  }

  try {
    JSON.parse(trimmed)
  } catch {
    clientError.value = 'Yapıştırılan metin geçerli bir JSON değil.'

    return false
  }

  return true
}

const requestPreview = async () => {
  if (! validateClientPayload()) {
    return
  }

  processing.value = true

  try {
    const { data } = await axios.post(
      route('admin.events.import-program.preview', props.eventSlug),
      buildFormData(),
      { headers: csrfHeaders() },
    )

    preview.value = data
    step.value = 'preview'
  } catch (error) {
    clientError.value = extractErrorMessage(error)
  } finally {
    processing.value = false
  }
}

const confirmImport = async () => {
  processing.value = true
  clientError.value = ''

  try {
    const { data } = await axios.post(
      route('admin.events.import-program', props.eventSlug),
      buildFormData(),
      { headers: csrfHeaders() },
    )

    result.value = data
    step.value = 'result'
  } catch (error) {
    clientError.value = extractErrorMessage(error)
  } finally {
    processing.value = false
  }
}

const backToUpload = () => {
  step.value = 'upload'
  preview.value = null
  clientError.value = ''
}

const metricItems = (payload) => ([
  { label: 'Gün', value: payload?.days ?? 0 },
  { label: 'Salon', value: payload?.venues ?? 0 },
  { label: 'Oturum', value: payload?.sessions ?? 0 },
  { label: 'Sunum', value: payload?.presentations ?? 0 },
  { label: 'Katılımcı', value: payload?.participants ?? 0 },
])
</script>

<template>
  <Modal
    :show="show"
    :title="title"
    :subtitle="subtitle"
    max-width="2xl"
    :closable="!processing"
    :close-on-backdrop-click="!processing"
    @close="close"
  >
    <div class="space-y-5">
      <div v-if="step === 'upload'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">JSON dosyası</label>
          <input
            ref="fileInput"
            type="file"
            accept=".json,application/json,text/plain"
            class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/40 dark:file:text-indigo-200"
            @change="onFileChange"
          >
          <p v-if="selectedFileName" class="mt-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <DocumentTextIcon class="h-4 w-4" />
            <span>{{ selectedFileName }}</span>
            <button type="button" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400" @click="clearFile">
              Kaldır
            </button>
          </p>
        </div>

        <div class="relative">
          <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-slate-200 dark:border-slate-700" />
          </div>
          <div class="relative flex justify-center">
            <span class="bg-white dark:bg-slate-800 px-2 text-xs uppercase tracking-wide text-slate-400">veya</span>
          </div>
        </div>

        <div>
          <label for="program-json-paste" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            JSON yapıştır
          </label>
          <textarea
            id="program-json-paste"
            v-model="jsonText"
            rows="8"
            :disabled="Boolean(selectedFile)"
            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-slate-100 font-mono px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-60"
            placeholder='[{"Date":"16.04.2026","IsoDate":"2026-04-16","Venues":[]}]'
          />
        </div>
      </div>

      <div v-else-if="step === 'preview'" class="space-y-4">
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/40">
          <div class="flex gap-3">
            <ExclamationTriangleIcon class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
            <p class="text-sm text-amber-800 dark:text-amber-200">
              Mevcut program (gün, salon, oturum, sunum) silinecek. Katılımcılar organizasyonda kalır.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
          <div
            v-for="item in metricItems(preview)"
            :key="item.label"
            class="rounded-lg bg-slate-50 dark:bg-slate-900/60 px-3 py-3 text-center"
          >
            <div class="text-xl font-semibold text-slate-900 dark:text-white">{{ item.value }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">{{ item.label }}</div>
          </div>
        </div>

        <div v-if="preview?.warnings?.length" class="rounded-lg border border-slate-200 dark:border-slate-700 p-3">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Uyarılar</p>
          <ul class="space-y-1 text-sm text-slate-600 dark:text-slate-400">
            <li v-for="(warning, index) in preview.warnings" :key="index">{{ warning }}</li>
          </ul>
        </div>
      </div>

      <div v-else class="space-y-4">
        <p class="text-sm text-slate-600 dark:text-slate-300">
          {{ result?.message || 'Program başarıyla içe aktarıldı.' }}
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
          <div
            v-for="item in metricItems(result)"
            :key="item.label"
            class="rounded-lg bg-slate-50 dark:bg-slate-900/60 px-3 py-3 text-center"
          >
            <div class="text-xl font-semibold text-slate-900 dark:text-white">{{ item.value }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">{{ item.label }}</div>
          </div>
        </div>

        <div v-if="result?.warnings?.length" class="rounded-lg border border-slate-200 dark:border-slate-700 p-3">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Uyarılar</p>
          <ul class="space-y-1 text-sm text-slate-600 dark:text-slate-400">
            <li v-for="(warning, index) in result.warnings" :key="index">{{ warning }}</li>
          </ul>
        </div>
      </div>

      <p v-if="clientError" class="text-sm text-red-600 dark:text-red-400">{{ clientError }}</p>
    </div>

    <template #footer>
      <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
        <button
          v-if="step !== 'result'"
          type="button"
          class="inline-flex justify-center rounded-lg border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700"
          :disabled="processing"
          @click="step === 'preview' ? backToUpload() : close()"
        >
          {{ step === 'preview' ? 'Geri' : 'İptal' }}
        </button>

        <button
          v-if="step === 'upload'"
          type="button"
          class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
          :disabled="processing"
          @click="requestPreview"
        >
          <ArrowUpTrayIcon class="h-4 w-4 mr-2" />
          {{ processing ? 'Analiz ediliyor...' : 'Önizle' }}
        </button>

        <button
          v-else-if="step === 'preview'"
          type="button"
          class="inline-flex justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
          :disabled="processing"
          @click="confirmImport"
        >
          {{ processing ? 'İçe aktarılıyor...' : 'Sil ve içe aktar' }}
        </button>

        <button
          v-else
          type="button"
          class="inline-flex justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
          @click="close"
        >
          Kapat
        </button>
      </div>
    </template>
  </Modal>
</template>
