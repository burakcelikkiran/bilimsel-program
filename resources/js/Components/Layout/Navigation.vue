<!-- resources/js/Components/Layout/Navigation.vue -->
<template>
  <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Left side -->
        <div class="flex items-center">
          <!-- Mobile menu button -->
          <button
            @click="$emit('toggle-sidebar')"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 lg:hidden dark:hover:bg-gray-700"
          >
            <span class="sr-only">Menüyü aç</span>
            <Bars3Icon v-if="!sidebarOpen" class="block h-6 w-6" />
            <XMarkIcon v-else class="block h-6 w-6" />
          </button>

          <!-- Breadcrumb for mobile -->
          <div class="ml-4 lg:hidden">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ currentPageTitle }}
            </h1>
          </div>

          <!-- Global Search -->
          <div class="hidden lg:block lg:ml-6" ref="searchRef">
            <div class="relative max-w-lg">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Genel arama... (Ctrl+K)"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                @keydown.enter="performSearch"
                @focus="showSearchDropdown = true"
              />

              <!-- Search Dropdown -->
              <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div
                  v-if="showSearchDropdown && (searchResults.length > 0 || searchQuery.length > 0)"
                  class="absolute z-50 mt-1 w-full bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800"
                  @click.stop
                >
                  <div class="py-1 max-h-64 overflow-y-auto">
                    <!-- Quick Actions -->
                    <div v-if="searchQuery.length === 0" class="px-4 py-2">
                      <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                        Hızlı İşlemler
                      </p>
                      <div class="mt-2 space-y-1">
                        <a
                          v-for="action in quickActions"
                          :key="action.name"
                          :href="action.href"
                          class="flex items-center px-2 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                          @click="closeSearchDropdown"
                        >
                          <component :is="action.icon" class="mr-3 h-4 w-4 text-gray-400" />
                          {{ action.name }}
                        </a>
                      </div>
                    </div>

                    <!-- Search Results -->
                    <div v-else-if="searchResults.length > 0">
                      <div
                        v-for="(group, type) in groupedSearchResults"
                        :key="type"
                        class="px-4 py-2"
                      >
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                          {{ getTypeLabel(type) }}
                        </p>
                        <div class="mt-2 space-y-1">
                          <a
                            v-for="result in group"
                            :key="result.id"
                            :href="result.url"
                            class="flex items-center px-2 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                            @click="closeSearchDropdown"
                          >
                            <component :is="result.icon" class="mr-3 h-4 w-4 text-gray-400" />
                            <div class="flex-1">
                              <p class="font-medium">{{ result.title }}</p>
                              <p v-if="result.subtitle" class="text-xs text-gray-500 dark:text-gray-400">
                                {{ result.subtitle }}
                              </p>
                            </div>
                          </a>
                        </div>
                      </div>
                    </div>

                    <!-- No Results -->
                    <div v-else class="px-4 py-6 text-center">
                      <p class="text-sm text-gray-500 dark:text-gray-400">
                        "{{ searchQuery }}" için sonuç bulunamadı
                      </p>
                    </div>
                  </div>
                </div>
              </transition>
            </div>
          </div>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
          <!-- Quick Actions -->
          <div class="hidden lg:flex lg:items-center lg:space-x-2">
            <Link
              :href="route('admin.events.create')"
              class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <PlusIcon class="h-4 w-4 mr-1" />
              Yeni Etkinlik
            </Link>
          </div>

          <!-- Theme Toggle -->
          <button
            @click="$emit('toggle-theme')"
            class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-150"
            title="Tema Değiştir"
          >
            <SunIcon v-if="isDark" class="h-5 w-5" />
            <MoonIcon v-else class="h-5 w-5" />
          </button>

          <!-- Notifications -->
          <div class="relative" ref="notificationsRef">
            <button
              @click="toggleNotifications"
              class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md relative transition-colors duration-150"
              :class="{ 'bg-gray-100 dark:bg-gray-700': showNotifications }"
            >
              <span class="sr-only">Bildirimler</span>
              <BellIcon class="h-6 w-6" />
              <span
                v-if="unreadNotificationsCount > 0"
                class="absolute -top-1 -right-1 block h-5 w-5 rounded-full bg-red-500 text-xs font-bold text-white leading-5 text-center animate-pulse"
              >
                {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
              </span>
            </button>

            <!-- Notifications Dropdown -->
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="showNotifications"
                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50 dark:bg-gray-800 dark:ring-gray-700"
                @click.stop
              >
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                  <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bildirimler</h3>
                    <button
                      v-if="unreadNotificationsCount > 0"
                      @click="markAllAsRead"
                      class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 transition-colors duration-150"
                    >
                      Tümünü okundu işaretle
                    </button>
                  </div>
                </div>

                <div class="max-h-64 overflow-y-auto">
                  <div v-if="notifications.length === 0" class="p-4 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Bildirim bulunmuyor</p>
                  </div>

                  <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                      v-for="notification in notifications"
                      :key="notification.id"
                      class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-150"
                      :class="!notification.read_at ? 'bg-blue-50 dark:bg-blue-900/20' : ''"
                      @click="handleNotificationClick(notification)"
                    >
                      <div class="flex items-start">
                        <div class="flex-shrink-0">
                          <component
                            :is="getNotificationIcon(notification.type)"
                            class="h-6 w-6"
                            :class="getNotificationIconColor(notification.type)"
                          />
                        </div>
                        <div class="ml-3 flex-1">
                          <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ notification.data.title }}
                          </p>
                          <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ notification.data.message }}
                          </p>
                          <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ formatRelativeTime(notification.created_at) }}
                          </p>
                        </div>
                        <div v-if="!notification.read_at" class="flex-shrink-0">
                          <div class="h-2 w-2 bg-blue-600 rounded-full"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                  <Link
                    :href="route('admin.notifications.index')"
                    class="block w-full text-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 transition-colors duration-150"
                    @click="closeNotifications"
                  >
                    Tüm bildirimleri gör
                  </Link>
                </div>
              </div>
            </transition>
          </div>

          <!-- User Menu -->
          <div class="relative" ref="userMenuRef">
            <button
              @click="toggleUserMenu"
              class="flex items-center space-x-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-1 transition-all duration-150"
              :class="{ 'ring-2 ring-blue-500 ring-offset-2': showUserMenu }"
            >
              <span class="sr-only">Kullanıcı menüsü</span>
              <img
                class="h-8 w-8 rounded-full object-cover border-2 border-transparent transition-all duration-150"
                :class="{ 'border-blue-500': showUserMenu }"
                :src="currentUser?.profile_photo_url || '/images/default-avatar.png'"
                :alt="currentUser?.name"
              />
              <!-- Dropdown arrow -->
              <svg 
                class="w-4 h-4 text-gray-500 transition-transform duration-200"
                :class="{ 'rotate-180': showUserMenu }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- User Dropdown -->
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="showUserMenu"
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50 dark:bg-gray-800 dark:ring-gray-700"
                @click.stop
              >
                <!-- User Info Header -->
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                  <div class="flex items-center space-x-3">
                    <img
                      class="h-10 w-10 rounded-full object-cover"
                      :src="currentUser?.profile_photo_url || '/images/default-avatar.png'"
                      :alt="currentUser?.name"
                    />
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ currentUser?.name }}
                      </p>
                      <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                        {{ currentUser?.email }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Menu Items -->
                <div class="py-1">
                  <Link
                    v-for="item in userMenuItems"
                    :key="item.name"
                    :href="item.href"
                    :method="item.method || 'get'"
                    :as="item.as || 'a'"
                    @click="closeUserMenu"
                    class="flex items-center px-4 py-2 text-sm transition-colors duration-150"
                    :class="[
                      item.destructive 
                        ? 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20' 
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                    ]"
                  >
                    <component 
                      :is="item.icon" 
                      class="mr-3 h-4 w-4 flex-shrink-0" 
                      :class="item.destructive ? 'text-red-500' : 'text-gray-400'"
                    />
                    <span>{{ item.name }}</span>
                  </Link>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import {
  Bars3Icon,
  XMarkIcon,
  MagnifyingGlassIcon,
  BellIcon,
  SunIcon,
  MoonIcon,
  PlusIcon,
  UserCircleIcon,
  CogIcon,
  ArrowRightOnRectangleIcon,
  CalendarIcon,
  UsersIcon,
  BuildingOfficeIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: false
  },
  currentUser: {
    type: Object,
    required: true
  },
  notifications: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['toggle-sidebar', 'toggle-theme', 'mark-notification-read'])

// Page data
const page = usePage()
const isDark = computed(() => page.props.theme === 'dark')

// Template refs
const searchRef = ref(null)
const notificationsRef = ref(null)
const userMenuRef = ref(null)

// State
const searchQuery = ref('')
const searchResults = ref([])
const showSearchDropdown = ref(false)
const showNotifications = ref(false)
const showUserMenu = ref(false)

// Computed
const currentPageTitle = computed(() => {
  return page.props.title || 'Dashboard'
})

const unreadNotificationsCount = computed(() => {
  return props.notifications.filter(n => !n.read_at).length
})

const groupedSearchResults = computed(() => {
  return searchResults.value.reduce((groups, result) => {
    const type = result.type
    if (!groups[type]) {
      groups[type] = []
    }
    groups[type].push(result)
    return groups
  }, {})
})

// Quick actions for empty search
const quickActions = [
  {
    name: 'Yeni Etkinlik Oluştur',
    href: route('admin.events.create'),
    icon: CalendarIcon
  },
  {
    name: 'Katılımcı Ekle',
    href: route('admin.participants.create'),
    icon: UsersIcon
  },
  {
    name: 'Organizasyon Ayarları',
    href: route('admin.organizations.index'),
    icon: BuildingOfficeIcon
  }
]

// User menu items
const userMenuItems = [
  {
    name: 'Profil Ayarları',
    href: route('profile.show'),
    icon: UserCircleIcon
  },
  {
    name: 'Sistem Ayarları',
    href: route('admin.settings.index'),
    icon: CogIcon
  },
  {
    name: 'Çıkış Yap',
    href: route('logout'),
    method: 'post',
    as: 'button',
    icon: ArrowRightOnRectangleIcon,
    destructive: true
  }
]

// Methods - Dropdown Controls
const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
  // Close other dropdowns
  if (showUserMenu.value) {
    showSearchDropdown.value = false
    showNotifications.value = false
  }
}

const closeUserMenu = () => {
  showUserMenu.value = false
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  // Close other dropdowns
  if (showNotifications.value) {
    showSearchDropdown.value = false
    showUserMenu.value = false
  }
}

const closeNotifications = () => {
  showNotifications.value = false
}

const closeSearchDropdown = () => {
  showSearchDropdown.value = false
}

// Search functionality
const performSearch = debounce(async () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }

  try {
    // Note: Replace with your actual search API endpoint
    const response = await fetch('/api/search?' + new URLSearchParams({
      q: searchQuery.value
    }))
    
    if (response.ok) {
      const data = await response.json()
      searchResults.value = data.results || []
    }
  } catch (error) {
    console.error('Search error:', error)
    searchResults.value = []
  }
}, 300)

// Utility functions
const getTypeLabel = (type) => {
  const labels = {
    events: 'Etkinlikler',
    participants: 'Katılımcılar',
    sessions: 'Oturumlar',
    presentations: 'Sunumlar',
    venues: 'Salonlar',
    sponsors: 'Sponsorlar'
  }
  return labels[type] || type
}

const getNotificationIcon = (type) => {
  const icons = {
    info: InformationCircleIcon,
    success: CheckCircleIcon,
    warning: ExclamationTriangleIcon,
    error: ExclamationTriangleIcon
  }
  return icons[type] || InformationCircleIcon
}

const getNotificationIconColor = (type) => {
  const colors = {
    info: 'text-blue-500',
    success: 'text-green-500',
    warning: 'text-yellow-500',
    error: 'text-red-500'
  }
  return colors[type] || 'text-blue-500'
}

const formatRelativeTime = (date) => {
  const now = new Date()
  const past = new Date(date)
  const diffInSeconds = Math.floor((now - past) / 1000)

  if (diffInSeconds < 60) return 'Az önce'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} dakika önce`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} saat önce`
  return `${Math.floor(diffInSeconds / 86400)} gün önce`
}

// Event handlers
const handleNotificationClick = (notification) => {
  if (!notification.read_at) {
    emit('mark-notification-read', notification.id)
  }
  
  if (notification.data.url) {
    window.location.href = notification.data.url
  }
  
  closeNotifications()
}

const markAllAsRead = () => {
  props.notifications
    .filter(n => !n.read_at)
    .forEach(n => emit('mark-notification-read', n.id))
}

// Improved click outside handler
const handleClickOutside = (event) => {
  // Check search dropdown
  if (searchRef.value && !searchRef.value.contains(event.target)) {
    showSearchDropdown.value = false
  }
  
  // Check notifications dropdown
  if (notificationsRef.value && !notificationsRef.value.contains(event.target)) {
    showNotifications.value = false
  }
  
  // Check user menu dropdown
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    showUserMenu.value = false
  }
}

// Keyboard shortcuts
const handleKeydown = (event) => {
  // Ctrl+K or Cmd+K for search focus
  if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
    event.preventDefault()
    const searchInput = document.querySelector('input[type="text"]')
    if (searchInput) {
      searchInput.focus()
      showSearchDropdown.value = true
    }
  }
  
  // Escape to close all dropdowns
  if (event.key === 'Escape') {
    showSearchDropdown.value = false
    showNotifications.value = false
    showUserMenu.value = false
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside, true)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside, true)
  document.removeEventListener('keydown', handleKeydown)
})

// Watch search query
watch(searchQuery, (newValue) => {
  if (newValue.length >= 2) {
    performSearch()
  } else {
    searchResults.value = []
  }
})

// Watch search dropdown visibility
watch(showSearchDropdown, (isVisible) => {
  if (isVisible) {
    showNotifications.value = false
    showUserMenu.value = false
  }
})
</script>

<style scoped>
/* Ensure dropdowns appear above other elements */
.z-50 {
  z-index: 50 !important;
}

/* Smooth transitions for buttons */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Custom search input focus styles */
input[type="text"]:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Notification badge pulse animation */
@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Dropdown arrow rotation */
.rotate-180 {
  transform: rotate(180deg);
}

/* Hover effects for better UX */
.hover\:bg-gray-50:hover {
  background-color: rgb(249 250 251);
}

.dark .hover\:bg-gray-700:hover {
  background-color: rgb(55 65 81);
}

.hover\:bg-red-50:hover {
  background-color: rgb(254 242 242);
}

.dark .hover\:bg-red-900\/20:hover {
  background-color: rgb(127 29 29 / 0.2);
}

/* Custom scrollbar for dropdown menus */
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

/* Focus states for accessibility */
button:focus-visible,
a:focus-visible {
  outline: 2px solid rgb(59 130 246);
  outline-offset: 2px;
}
</style>