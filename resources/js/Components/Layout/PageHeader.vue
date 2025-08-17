<!-- resources/js/Components/Layout/PageHeader.vue -->
<template>
  <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="py-6">
        <!-- Breadcrumbs -->
        <nav v-if="breadcrumbs.length > 0" class="flex mb-4" aria-label="Breadcrumb">
          <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
              <Link
                :href="route('admin.dashboard')"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white"
              >
                <HomeIcon class="w-4 h-4 mr-2" />
                Dashboard
              </Link>
            </li>
            <li v-for="(crumb, index) in breadcrumbs" :key="index">
              <div class="flex items-center">
                <ChevronRightIcon class="w-6 h-6 text-gray-400" />
                <Link
                  v-if="crumb.href && index < breadcrumbs.length - 1"
                  :href="crumb.href"
                  class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white"
                >
                  {{ crumb.label }}
                </Link>
                <span
                  v-else
                  class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400"
                  :aria-current="index === breadcrumbs.length - 1 ? 'page' : undefined"
                >
                  {{ crumb.label }}
                </span>
              </div>
            </li>
          </ol>
        </nav>

        <!-- Page Title and Actions -->
        <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
          <!-- Left side: Title and Description -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center">
              <!-- Page Icon -->
              <div v-if="icon" class="flex-shrink-0 mr-4">
                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg dark:bg-blue-900">
                  <component :is="icon" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
              </div>

              <!-- Title and Subtitle -->
              <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate">
                  {{ title }}
                </h1>
                <p v-if="subtitle" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                  {{ subtitle }}
                </p>
              </div>

              <!-- Status Badge -->
              <div v-if="status" class="ml-4">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getStatusClasses(status)"
                >
                  {{ getStatusLabel(status) }}
                </span>
              </div>
            </div>

            <!-- Meta Information -->
            <div v-if="meta && meta.length > 0" class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
              <div v-for="(item, index) in meta" :key="index" class="flex items-center">
                <component :is="item.icon" class="w-4 h-4 mr-1" />
                <span>{{ item.label }}: {{ item.value }}</span>
              </div>
            </div>

            <!-- Statistics Pills -->
            <div v-if="statistics && statistics.length > 0" class="mt-4 flex flex-wrap gap-3">
              <div
                v-for="(stat, index) in statistics"
                :key="index"
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
              >
                <component v-if="stat.icon" :is="stat.icon" class="w-3 h-3 mr-1" />
                <span class="font-semibold">{{ stat.value }}</span>
                <span class="ml-1">{{ stat.label }}</span>
              </div>
            </div>
          </div>

          <!-- Right side: Actions -->
          <div class="flex-shrink-0 flex items-center space-x-3">
            <!-- Custom Actions Slot -->
            <slot name="actions" />

            <!-- Default Actions -->
            <div v-if="actions.length > 0" class="flex items-center space-x-3">
              <!-- Secondary Actions (Dropdown) -->
              <div v-if="secondaryActions.length > 0" class="relative">
                <button
                  @click="showSecondaryActions = !showSecondaryActions"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                  <EllipsisHorizontalIcon class="h-4 w-4" />
                  <span class="sr-only">Daha fazla seçenek</span>
                </button>

                <!-- Secondary Actions Dropdown -->
                <div
                  v-if="showSecondaryActions"
                  class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10 dark:bg-gray-800"
                >
                  <div class="py-1">
                    <button
                      v-for="action in secondaryActions"
                      :key="action.key"
                      @click="handleAction(action)"
                      class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                      :class="action.destructive ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300'"
                      :disabled="action.disabled"
                    >
                      <div class="flex items-center">
                        <component v-if="action.icon" :is="action.icon" class="w-4 h-4 mr-2" />
                        {{ action.label }}
                      </div>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Primary Actions -->
              <div class="flex items-center space-x-2">
                <button
                  v-for="action in primaryActions"
                  :key="action.key"
                  @click="handleAction(action)"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                  :class="getActionClasses(action)"
                  :disabled="action.disabled"
                >
                  <component v-if="action.icon" :is="action.icon" class="w-4 h-4 mr-2" />
                  {{ action.label }}
                </button>
              </div>
            </div>

            <!-- Back Button -->
            <button
              v-if="showBackButton"
              @click="goBack"
              class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
              <ArrowLeftIcon class="w-4 h-4 mr-2" />
              Geri
            </button>
          </div>
        </div>

        <!-- Tabs Navigation -->
        <div v-if="tabs && tabs.length > 0" class="mt-6">
          <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
              <button
                v-for="tab in tabs"
                :key="tab.key"
                @click="handleTabChange(tab)"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                :class="tab.active 
                  ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
              >
                <div class="flex items-center">
                  <component v-if="tab.icon" :is="tab.icon" class="w-4 h-4 mr-2" />
                  {{ tab.label }}
                  <span 
                    v-if="tab.count !== undefined" 
                    class="ml-2 py-0.5 px-2 rounded-full text-xs font-medium"
                    :class="tab.active 
                      ? 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-200' 
                      : 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300'"
                  >
                    {{ tab.count }}
                  </span>
                </div>
              </button>
            </nav>
          </div>
        </div>

        <!-- Alert Messages -->
        <div v-if="alerts && alerts.length > 0" class="mt-4 space-y-3">
          <div
            v-for="(alert, index) in alerts"
            :key="index"
            class="rounded-md p-4"
            :class="getAlertClasses(alert.type)"
          >
            <div class="flex">
              <div class="flex-shrink-0">
                <component 
                  :is="getAlertIcon(alert.type)" 
                  class="h-5 w-5"
                  :class="getAlertIconClasses(alert.type)"
                />
              </div>
              <div class="ml-3">
                <h3 v-if="alert.title" class="text-sm font-medium" :class="getAlertTextClasses(alert.type)">
                  {{ alert.title }}
                </h3>
                <div class="mt-2 text-sm" :class="getAlertTextClasses(alert.type)">
                  <p>{{ alert.message }}</p>
                </div>
                <div v-if="alert.actions" class="mt-4">
                  <div class="-mx-2 -my-1.5 flex">
                    <button
                      v-for="action in alert.actions"
                      :key="action.key"
                      @click="handleAlertAction(action, index)"
                      class="px-2 py-1.5 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2"
                      :class="getAlertActionClasses(alert.type, action.type)"
                    >
                      {{ action.label }}
                    </button>
                  </div>
                </div>
              </div>
              <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                  <button
                    @click="dismissAlert(index)"
                    class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    :class="getAlertDismissClasses(alert.type)"
                  >
                    <span class="sr-only">Kapat</span>
                    <XMarkIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
  HomeIcon,
  ChevronRightIcon,
  EllipsisHorizontalIcon,
  ArrowLeftIcon,
  XMarkIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  icon: {
    type: [Object, String],
    default: null
  },
  status: {
    type: String,
    default: null
  },
  meta: {
    type: Array,
    default: () => []
  },
  statistics: {
    type: Array,
    default: () => []
  },
  actions: {
    type: Array,
    default: () => []
  },
  tabs: {
    type: Array,
    default: () => []
  },
  alerts: {
    type: Array,
    default: () => []
  },
  showBackButton: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['action', 'tab-change', 'alert-action', 'alert-dismiss'])

// State
const showSecondaryActions = ref(false)
const localAlerts = ref([...props.alerts])

// Computed
const primaryActions = computed(() => {
  return props.actions.filter(action => action.primary !== false).slice(0, 2)
})

const secondaryActions = computed(() => {
  return props.actions.filter(action => action.primary === false)
})

// Methods
const getStatusClasses = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    published: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
  }
  return classes[status] || classes.inactive
}

const getStatusLabel = (status) => {
  const labels = {
    active: 'Aktif',
    inactive: 'Pasif',
    pending: 'Beklemede',
    published: 'Yayınlandı',
    draft: 'Taslak',
    completed: 'Tamamlandı',
    cancelled: 'İptal Edildi'
  }
  return labels[status] || status
}

const getActionClasses = (action) => {
  if (action.disabled) {
    return 'bg-gray-300 text-gray-500 cursor-not-allowed'
  }

  if (action.variant === 'secondary') {
    return 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600'
  }

  if (action.variant === 'danger') {
    return 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500'
  }

  return 'text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
}

const getAlertClasses = (type) => {
  const classes = {
    info: 'bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
    success: 'bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800',
    warning: 'bg-yellow-50 border border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800',
    error: 'bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800'
  }
  return classes[type] || classes.info
}

const getAlertIcon = (type) => {
  const icons = {
    info: InformationCircleIcon,
    success: CheckCircleIcon,
    warning: ExclamationTriangleIcon,
    error: ExclamationCircleIcon
  }
  return icons[type] || InformationCircleIcon
}

const getAlertIconClasses = (type) => {
  const classes = {
    info: 'text-blue-400 dark:text-blue-300',
    success: 'text-green-400 dark:text-green-300',
    warning: 'text-yellow-400 dark:text-yellow-300',
    error: 'text-red-400 dark:text-red-300'
  }
  return classes[type] || classes.info
}

const getAlertTextClasses = (type) => {
  const classes = {
    info: 'text-blue-800 dark:text-blue-200',
    success: 'text-green-800 dark:text-green-200',
    warning: 'text-yellow-800 dark:text-yellow-200',
    error: 'text-red-800 dark:text-red-200'
  }
  return classes[type] || classes.info
}

const getAlertActionClasses = (alertType, actionType) => {
  const baseClasses = 'mr-2'
  
  if (actionType === 'primary') {
    const classes = {
      info: 'bg-blue-100 text-blue-800 hover:bg-blue-200 focus:ring-blue-600 dark:bg-blue-800 dark:text-blue-100',
      success: 'bg-green-100 text-green-800 hover:bg-green-200 focus:ring-green-600 dark:bg-green-800 dark:text-green-100',
      warning: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 focus:ring-yellow-600 dark:bg-yellow-800 dark:text-yellow-100',
      error: 'bg-red-100 text-red-800 hover:bg-red-200 focus:ring-red-600 dark:bg-red-800 dark:text-red-100'
    }
    return baseClasses + ' ' + (classes[alertType] || classes.info)
  }

  const classes = {
    info: 'text-blue-800 hover:bg-blue-100 focus:ring-blue-600 dark:text-blue-200',
    success: 'text-green-800 hover:bg-green-100 focus:ring-green-600 dark:text-green-200',
    warning: 'text-yellow-800 hover:bg-yellow-100 focus:ring-yellow-600 dark:text-yellow-200',
    error: 'text-red-800 hover:bg-red-100 focus:ring-red-600 dark:text-red-200'
  }
  return baseClasses + ' ' + (classes[alertType] || classes.info)
}

const getAlertDismissClasses = (type) => {
  const classes = {
    info: 'text-blue-400 hover:bg-blue-100 hover:text-blue-500 focus:ring-blue-600 dark:text-blue-300',
    success: 'text-green-400 hover:bg-green-100 hover:text-green-500 focus:ring-green-600 dark:text-green-300',
    warning: 'text-yellow-400 hover:bg-yellow-100 hover:text-yellow-500 focus:ring-yellow-600 dark:text-yellow-300',
    error: 'text-red-400 hover:bg-red-100 hover:text-red-500 focus:ring-red-600 dark:text-red-300'
  }
  return classes[type] || classes.info
}

const handleAction = (action) => {
  showSecondaryActions.value = false
  emit('action', action)
}

const handleTabChange = (tab) => {
  emit('tab-change', tab)
}

const handleAlertAction = (action, alertIndex) => {
  emit('alert-action', { action, alertIndex })
}

const dismissAlert = (index) => {
  localAlerts.value.splice(index, 1)
  emit('alert-dismiss', index)
}

const goBack = () => {
  if (window.history.length > 1) {
    router.visit(window.history.back())
  } else {
    router.visit(route('admin.dashboard'))
  }
}

// Click outside handler for dropdowns
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showSecondaryActions.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* Smooth transitions for dropdowns */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Custom focus styles */
button:focus,
a:focus {
  outline: none;
}

/* Ensure proper spacing for flex items */
.space-x-2 > * + * {
  margin-left: 0.5rem;
}

.space-x-3 > * + * {
  margin-left: 0.75rem;
}

/* Custom tab active state animation */
.border-b-2 {
  transition: border-color 0.2s ease;
}

/* Alert animation */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.alert-enter-active {
  animation: slideDown 0.3s ease;
}

.alert-leave-active {
  transition: all 0.3s ease;
}

.alert-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>