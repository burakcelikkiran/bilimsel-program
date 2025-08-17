<!-- resources/js/Components/Export/TimelineExport.vue -->
<template>
  <div class="timeline-export-container">
    <!-- Export Controls -->
    <div v-if="!printMode" class="export-controls bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Dışa Aktarım Seçenekleri</h3>
          <p class="text-sm text-gray-600 mt-1">Timeline'ı farklı formatlarda kaydedin</p>
        </div>
        
        <div class="flex items-center space-x-3">
          <!-- Export Format Selection -->
          <select
            v-model="exportFormat"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="pdf">PDF</option>
            <option value="image">PNG Görsel</option>
            <option value="print">Yazdır</option>
          </select>
          
          <!-- Export Settings -->
          <button
            @click="showSettings = !showSettings"
            class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <CogIcon class="h-5 w-5" />
          </button>
          
          <!-- Export Button -->
          <button
            @click="handleExport"
            :disabled="exporting"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <template v-if="exporting">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              {{ exportFormat === 'pdf' ? 'PDF oluşturuluyor...' : 'İşleniyor...' }}
            </template>
            <template v-else>
              <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
              {{ exportFormat === 'pdf' ? 'PDF İndir' : exportFormat === 'image' ? 'Görsel İndir' : 'Yazdır' }}
            </template>
          </button>
        </div>
      </div>
      
      <!-- Export Settings Panel -->
      <div v-if="showSettings" class="mt-4 pt-4 border-t border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sayfa Boyutu</label>
            <select v-model="settings.pageSize" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
              <option value="a4">A4</option>
              <option value="a3">A3</option>
              <option value="letter">Letter</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Yönlendirme</label>
            <select v-model="settings.orientation" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
              <option value="landscape">Yatay</option>
              <option value="portrait">Dikey</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kalite</label>
            <select v-model="settings.quality" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
              <option value="high">Yüksek</option>
              <option value="medium">Orta</option>
              <option value="low">Düşük</option>
            </select>
          </div>
        </div>
        
        <div class="mt-4 flex items-center space-x-4">
          <label class="flex items-center">
            <input
              v-model="settings.includeStats"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
            <span class="ml-2 text-sm text-gray-700">İstatistikleri dahil et</span>
          </label>
          
          <label class="flex items-center">
            <input
              v-model="settings.includeLegend"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
            <span class="ml-2 text-sm text-gray-700">Açıklamaları dahil et</span>
          </label>
          
          <label class="flex items-center">
            <input
              v-model="settings.colorPrint"
              type="checkbox"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
            <span class="ml-2 text-sm text-gray-700">Renkli çıktı</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Timeline Print Layout -->
    <div
      ref="printContent"
      class="timeline-print-layout"
      :class="{
        'print-mode': printMode,
        'orientation-landscape': settings.orientation === 'landscape',
        'orientation-portrait': settings.orientation === 'portrait'
      }"
    >
      <!-- Page Header -->
      <div class="print-header">
        <div class="header-content">
          <div class="event-info">
            <h1 class="event-title">{{ event?.name || 'Etkinlik Programı' }}</h1>
            <h2 class="event-subtitle">Program Zaman Çizelgesi</h2>
            <div class="event-details">
              <span v-if="event?.location" class="detail-item">
                <MapPinIcon class="h-4 w-4" />
                {{ event.location }}
              </span>
              <span v-if="eventDateRange" class="detail-item">
                <CalendarIcon class="h-4 w-4" />
                {{ eventDateRange }}
              </span>
              <span class="detail-item">
                <BuildingOfficeIcon class="h-4 w-4" />
                {{ event?.organization?.name || 'Organizasyon' }}
              </span>
            </div>
          </div>
          
          <div class="export-info">
            <div class="export-date">{{ formatExportDate(new Date()) }}</div>
            <div class="page-info">Sayfa <span class="page-number"></span></div>
          </div>
        </div>
      </div>

      <!-- Quick Stats (if enabled) -->
      <div v-if="settings.includeStats" class="print-stats">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-value">{{ stats.totalDays }}</div>
            <div class="stat-label">Gün</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">{{ stats.totalSessions }}</div>
            <div class="stat-label">Oturum</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">{{ stats.totalVenues }}</div>
            <div class="stat-label">Salon</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">{{ stats.conflicts }}</div>
            <div class="stat-label">Çakışma</div>
          </div>
        </div>
      </div>

      <!-- Timeline Content -->
      <div class="timeline-content">
        <div v-for="(dayData, date) in groupedSessions" :key="date" class="day-section">
          <!-- Day Header -->
          <div class="day-header">
            <div class="day-info">
              <h3 class="day-title">{{ dayData.display_name }}</h3>
              <div class="day-date">{{ formatPrintDate(date) }}</div>
            </div>
            <div class="day-stats">
              {{ Object.keys(dayData.venues).length }} salon • 
              {{ getTotalSessionsForDay(dayData) }} oturum
            </div>
          </div>

          <!-- Venues for this day -->
          <div class="venues-container">
            <div
              v-for="(venueData, venueId) in dayData.venues"
              :key="venueId"
              class="venue-section"
            >
              <!-- Venue Header -->
              <div class="venue-header">
                <div class="venue-info">
                  <div
                    class="venue-color"
                    :style="{ backgroundColor: venueData.color || '#6B7280' }"
                  ></div>
                  <span class="venue-name">{{ venueData.display_name }}</span>
                </div>
                <div class="venue-stats">
                  {{ venueData.sessions.length }} oturum
                </div>
              </div>

              <!-- Sessions List -->
              <div class="sessions-list">
                <div
                  v-for="session in venueData.sessions"
                  :key="session.id"
                  class="session-item"
                  :class="getSessionClasses(session)"
                >
                  <div class="session-time">
                    <div class="time-range">{{ formatTimeRange(session) }}</div>
                    <div v-if="session.duration_in_minutes" class="duration">
                      {{ session.duration_in_minutes }}dk
                    </div>
                  </div>
                  
                  <div class="session-content">
                    <div class="session-title">
                      {{ session.title }}
                      <div class="session-badges">
                        <span v-if="session.is_break" class="badge badge-break">ARA</span>
                        <span v-if="session.is_sponsored" class="badge badge-sponsored">SPONSORLU</span>
                        <span v-if="hasTimeConflict(session)" class="badge badge-conflict">ÇAKIŞMA</span>
                      </div>
                    </div>
                    
                    <div v-if="session.description" class="session-description">
                      {{ truncateText(session.description, 120) }}
                    </div>
                    
                    <div class="session-meta">
                      <span v-if="session.moderators_count > 0" class="meta-item">
                        <UsersIcon class="h-3 w-3" />
                        {{ session.moderators_count }} moderatör
                      </span>
                      <span v-if="session.presentations_count > 0" class="meta-item">
                        <DocumentTextIcon class="h-3 w-3" />
                        {{ session.presentations_count }} sunum
                      </span>
                      <span v-if="session.session_type" class="meta-item">
                        {{ session.session_type_display }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Legend (if enabled) -->
      <div v-if="settings.includeLegend" class="print-legend">
        <h4 class="legend-title">Açıklamalar</h4>
        <div class="legend-items">
          <div class="legend-item">
            <div class="legend-color normal"></div>
            <span>Normal Oturum</span>
          </div>
          <div class="legend-item">
            <div class="legend-color sponsored"></div>
            <span>Sponsorlu Oturum</span>
          </div>
          <div class="legend-item">
            <div class="legend-color break"></div>
            <span>Ara / Mola</span>
          </div>
          <div class="legend-item">
            <div class="legend-color conflict"></div>
            <span>Zaman Çakışması</span>
          </div>
        </div>
      </div>

      <!-- Page Footer -->
      <div class="print-footer">
        <div class="footer-content">
          <div class="footer-left">
            {{ event?.name || 'Etkinlik' }} - Program Zaman Çizelgesi
          </div>
          <div class="footer-center">
            {{ event?.organization?.name || 'Organizasyon' }}
          </div>
          <div class="footer-right">
            Sayfa <span class="page-number"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import {
  ArrowDownTrayIcon,
  CogIcon,
  CalendarIcon,
  MapPinIcon,
  BuildingOfficeIcon,
  UsersIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
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
  },
  printMode: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['export-complete', 'export-error'])

// State
const printContent = ref(null)
const exporting = ref(false)
const showSettings = ref(false)
const exportFormat = ref('pdf')

const settings = ref({
  pageSize: 'a4',
  orientation: 'landscape',
  quality: 'high',
  includeStats: true,
  includeLegend: true,
  colorPrint: true
})

// Computed
const eventDateRange = computed(() => {
  if (!props.event?.start_date || !props.event?.end_date) return ''
  
  const start = new Date(props.event.start_date).toLocaleDateString('tr-TR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
  
  const end = new Date(props.event.end_date).toLocaleDateString('tr-TR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
  
  return start === end ? start : `${start} - ${end}`
})

const groupedSessions = computed(() => {
  const grouped = {}
  
  props.sessions.forEach(session => {
    if (!session.venue?.event_day) return
    
    const date = session.venue.event_day.date
    const venueId = session.venue.id
    
    if (!grouped[date]) {
      grouped[date] = {
        display_name: session.venue.event_day.display_name,
        venues: {}
      }
    }
    
    if (!grouped[date].venues[venueId]) {
      grouped[date].venues[venueId] = {
        display_name: session.venue.display_name,
        color: session.venue.color,
        sessions: []
      }
    }
    
    grouped[date].venues[venueId].sessions.push(session)
  })
  
  // Sort sessions by start time
  Object.values(grouped).forEach(dayData => {
    Object.values(dayData.venues).forEach(venueData => {
      venueData.sessions.sort((a, b) => {
        const timeA = a.start_time || '00:00'
        const timeB = b.start_time || '00:00'
        return timeA.localeCompare(timeB)
      })
    })
  })
  
  return grouped
})

const stats = computed(() => {
  const uniqueVenues = new Set()
  props.sessions.forEach(session => {
    if (session.venue?.id) {
      uniqueVenues.add(session.venue.id)
    }
  })

  return {
    totalDays: props.eventDays.length,
    totalSessions: props.sessions.length,
    totalVenues: uniqueVenues.size,
    conflicts: props.conflicts.length
  }
})

// Methods
const handleExport = async () => {
  if (exporting.value) return
  
  exporting.value = true
  
  try {
    switch (exportFormat.value) {
      case 'pdf':
        await exportToPDF()
        break
      case 'image':
        await exportToImage()
        break
      case 'print':
        await printTimeline()
        break
    }
    
    emit('export-complete', {
      format: exportFormat.value,
      settings: settings.value
    })
  } catch (error) {
    console.error('Export error:', error)
    emit('export-error', error)
  } finally {
    exporting.value = false
  }
}

const exportToPDF = async () => {
  // Dynamic import to keep bundle size small
  const html2pdf = await import('html2pdf.js')
  
  const element = printContent.value
  const opt = {
    margin: 0.5,
    filename: `${props.event?.name || 'timeline'}-program.pdf`,
    image: { type: 'jpeg', quality: settings.value.quality === 'high' ? 0.98 : 0.85 },
    html2canvas: { 
      scale: settings.value.quality === 'high' ? 2 : 1,
      useCORS: true,
      letterRendering: true
    },
    jsPDF: {
      unit: 'in',
      format: settings.value.pageSize,
      orientation: settings.value.orientation
    }
  }
  
  await html2pdf.default().set(opt).from(element).save()
}

const exportToImage = async () => {
  const html2canvas = await import('html2canvas')
  
  const canvas = await html2canvas.default(printContent.value, {
    scale: 2,
    useCORS: true,
    backgroundColor: '#ffffff'
  })
  
  const link = document.createElement('a')
  link.download = `${props.event?.name || 'timeline'}-program.png`
  link.href = canvas.toDataURL()
  link.click()
}

const printTimeline = async () => {
  const printWindow = window.open('', '_blank')
  const content = printContent.value.outerHTML
  
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>${props.event?.name || 'Timeline'} - Program</title>
        <style>
          ${getPrintStyles()}
        </style>
      </head>
      <body class="print-mode">
        ${content}
      </body>
    </html>
  `)
  
  printWindow.document.close()
  printWindow.focus()
  
  // Wait for content to load then print
  setTimeout(() => {
    printWindow.print()
    printWindow.close()
  }, 500)
}

const getPrintStyles = () => {
  return `
    /* Print-specific styles */
    body { font-family: 'Segoe UI', system-ui, sans-serif; font-size: 10px; line-height: 1.4; margin: 0; padding: 0; }
    .print-header { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333; }
    .event-title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
    .event-subtitle { font-size: 12px; color: #666; margin-bottom: 10px; }
    .day-section { page-break-inside: avoid; margin-bottom: 25px; }
    .venue-section { margin-bottom: 15px; }
    .session-item { margin-bottom: 8px; padding: 6px; border: 1px solid #e5e7eb; border-radius: 4px; }
    .session-title { font-weight: bold; margin-bottom: 2px; }
    .badge { display: inline-block; padding: 1px 4px; border-radius: 2px; font-size: 7px; margin-left: 4px; }
    .badge-break { background: #dcfce7; color: #166534; }
    .badge-sponsored { background: #ede9fe; color: #7c3aed; }
    .badge-conflict { background: #fee2e2; color: #dc2626; }
    /* Color print styles */
    ${settings.value.colorPrint ? `
      .venue-color { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 6px; }
      .legend-color.normal { background: #3b82f6; }
      .legend-color.sponsored { background: #8b5cf6; }
      .legend-color.break { background: #10b981; }
      .legend-color.conflict { background: #ef4444; }
    ` : `
      .venue-color { display: none; }
      .badge { background: #f3f4f6 !important; color: #374151 !important; border: 1px solid #d1d5db; }
    `}
  `
}

const formatTimeRange = (session) => {
  if (!session.start_time || !session.end_time) return 'Zaman belirsiz'
  const start = session.start_time.substring(0, 5)
  const end = session.end_time.substring(0, 5)
  return `${start} - ${end}`
}

const formatPrintDate = (dateString) => {
  try {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  } catch {
    return dateString
  }
}

const formatExportDate = (date) => {
  return date.toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTotalSessionsForDay = (dayData) => {
  return Object.values(dayData.venues).reduce((total, venue) => {
    return total + venue.sessions.length
  }, 0)
}

const getSessionClasses = (session) => {
  const classes = ['session-item']
  
  if (session.is_break) {
    classes.push('session-break')
  } else if (session.is_sponsored) {
    classes.push('session-sponsored')
  } else {
    classes.push('session-normal')
  }
  
  if (hasTimeConflict(session)) {
    classes.push('session-conflict')
  }
  
  return classes
}

const hasTimeConflict = (session) => {
  return props.conflicts.some(conflict => 
    conflict.session1.id === session.id || conflict.session2.id === session.id
  )
}

const truncateText = (text, length) => {
  if (!text || text.length <= length) return text
  return text.substring(0, length) + '...'
}
</script>

<style scoped>
/* Timeline Export Styles */
.timeline-export-container {
  @apply w-full;
}

.export-controls {
  @apply no-print;
}

.timeline-print-layout {
  @apply bg-white text-gray-900;
  font-family: 'Segoe UI', system-ui, sans-serif;
  font-size: 10px;
  line-height: 1.4;
}

.print-header {
  @apply mb-6 pb-4 border-b-2 border-gray-800;
}

.header-content {
  @apply flex justify-between items-start;
}

.event-title {
  @apply text-xl font-bold text-gray-900 mb-1;
}

.event-subtitle {
  @apply text-sm text-gray-600 mb-3;
}

.event-details {
  @apply flex flex-wrap gap-4 text-xs text-gray-600;
}

.detail-item {
  @apply flex items-center gap-1;
}

.export-info {
  @apply text-right text-xs text-gray-500;
}

.print-stats {
  @apply mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200;
}

.stats-grid {
  @apply grid grid-cols-4 gap-4;
}

.stat-item {
  @apply text-center;
}

.stat-value {
  @apply text-lg font-bold text-gray-900;
}

.stat-label {
  @apply text-xs text-gray-600;
}

.day-section {
  @apply mb-8;
  page-break-inside: avoid;
}

.day-header {
  @apply flex justify-between items-center mb-4 pb-2 border-b border-gray-300;
}

.day-title {
  @apply text-base font-bold text-gray-900;
}

.day-date {
  @apply text-xs text-gray-600;
}

.day-stats {
  @apply text-xs text-gray-500;
}

.venues-container {
  @apply space-y-4;
}

.venue-section {
  @apply mb-4;
  page-break-inside: avoid;
}

.venue-header {
  @apply flex justify-between items-center mb-2 p-2 bg-gray-100 rounded;
}

.venue-info {
  @apply flex items-center;
}

.venue-color {
  @apply w-3 h-3 rounded-full mr-2 border border-gray-300;
}

.venue-name {
  @apply text-sm font-semibold text-gray-900;
}

.venue-stats {
  @apply text-xs text-gray-600;
}

.sessions-list {
  @apply space-y-2;
}

.session-item {
  @apply flex gap-3 p-3 border border-gray-200 rounded;
  page-break-inside: avoid;
}

.session-time {
  @apply flex-shrink-0 w-20 text-center;
}

.time-range {
  @apply text-xs font-bold text-gray-700;
}

.duration {
  @apply text-xs text-gray-500 mt-1;
}

.session-content {
  @apply flex-1;
}

.session-title {
  @apply text-sm font-semibold text-gray-900 mb-1 flex items-center justify-between;
}

.session-badges {
  @apply flex gap-1;
}

.badge {
  @apply inline-block px-1.5 py-0.5 text-xs font-medium rounded;
}

.badge-break {
  @apply bg-green-100 text-green-800;
}

.badge-sponsored {
  @apply bg-purple-100 text-purple-800;
}

.badge-conflict {
  @apply bg-red-100 text-red-800;
}

.session-description {
  @apply text-xs text-gray-600 mb-2;
}

.session-meta {
  @apply flex gap-3 text-xs text-gray-500;
}

.meta-item {
  @apply flex items-center gap-1;
}

.print-legend {
  @apply mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200;
  page-break-inside: avoid;
}

.legend-title {
  @apply text-sm font-semibold text-gray-900 mb-3;
}

.legend-items {
  @apply grid grid-cols-4 gap-3;
}

.legend-item {
  @apply flex items-center gap-2 text-xs text-gray-700;
}

.legend-color {
  @apply w-3 h-3 rounded;
}

.legend-color.normal {
  @apply bg-blue-500;
}

.legend-color.sponsored {
  @apply bg-purple-500;
}

.legend-color.break {
  @apply bg-green-500;
}

.legend-color.conflict {
  @apply bg-red-500;
}

.print-footer {
  @apply mt-6 pt-4 border-t border-gray-300;
}

.footer-content {
  @apply flex justify-between text-xs text-gray-600;
}

/* Print-specific styles */
@media print {
  .no-print {
    display: none !important;
  }
  
  .timeline-print-layout {
    font-size: 10px !important;
  }
  
  .day-section {
    page-break-inside: avoid;
  }
  
  .venue-section {
    page-break-inside: avoid;
  }
  
  .session-item {
    page-break-inside: avoid;
  }
  
  .print-legend {
    page-break-inside: avoid;
  }
}

/* Orientation styles */
.orientation-landscape {
  max-width: 11in;
}

.orientation-portrait {
  max-width: 8.5in;
}

.print-mode {
  background: white !important;
  color: black !important;
}
</style>