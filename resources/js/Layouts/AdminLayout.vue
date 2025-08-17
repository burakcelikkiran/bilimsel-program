<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside 
      class="fixed inset-y-0 left-0 z-40 transition-all duration-300 ease-in-out"
      :class="sidebarOpen ? 'w-72' : 'w-20'"
    >
      <div class="flex h-full flex-col bg-gray-900 shadow-2xl border-r border-gray-800">
        <!-- Logo Section -->
        <div class="flex h-20 items-center justify-center border-b border-gray-800">
          <div v-if="sidebarOpen" class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gray-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-lg font-bold text-white">EPS Admin</h1>
              <p class="text-xs text-gray-300">Etkinlik Yönetimi</p>
            </div>
          </div>
          <div v-else class="w-10 h-10 bg-gray-600 rounded-xl flex items-center justify-center shadow-lg">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto sidebar-nav">
          <div v-for="section in navigation" :key="section.name" class="space-y-1">
            <!-- Section Header -->
            <div v-if="sidebarOpen && section.title" class="px-3 py-2">
              <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                {{ section.title }}
              </h3>
            </div>

            <!-- Navigation Items -->
            <template v-for="item in section.items" :key="item.name">
              <!-- Simple Link -->
              <Link
                v-if="!item.children"
                :href="item.href"
                class="group flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden"
                :class="item.current 
                  ? 'bg-gray-700 text-white border border-gray-600' 
                  : 'text-gray-300 hover:text-white hover:bg-gray-800 border border-transparent hover:border-gray-700'"
              >
                <component 
                  :is="item.icon" 
                  class="h-5 w-5 flex-shrink-0 relative z-10"
                  :class="item.current ? 'text-gray-200' : 'text-gray-400 group-hover:text-gray-200'"
                />
                <span v-if="sidebarOpen" class="ml-3 truncate relative z-10">{{ item.name }}</span>
                
                <!-- Badge -->
                <span v-if="sidebarOpen && item.badge" 
                  :class="[
                    'ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium shadow-lg relative z-10',
                    item.badgeColor || 'bg-gray-600 text-white'
                  ]">
                  {{ item.badge }}
                </span>

                <!-- Active indicator -->
                <div v-if="item.current" 
                     class="absolute right-2 w-2 h-2 bg-gray-400 rounded-full shadow-lg relative z-10"></div>
              </Link>

              <!-- Expandable Menu -->
              <div v-else>
                <button
                  @click="toggleSubmenu(item.name)"
                  class="group w-full flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 transition-all duration-200 relative overflow-hidden"
                  :class="item.current ? 'bg-gray-700 text-white border border-gray-600' : ''"
                >
                  <component 
                    :is="item.icon" 
                    class="h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-200"
                    :class="item.current ? 'text-gray-200' : ''"
                  />
                  <span v-if="sidebarOpen" class="ml-3 truncate">{{ item.name }}</span>

                  <!-- Badge for expandable items -->
                  <span v-if="sidebarOpen && item.badge" 
                    :class="[
                      'ml-2 px-2 py-0.5 text-xs font-medium rounded-full',
                      item.badgeColor || 'bg-orange-100 text-orange-800'
                    ]">
                    {{ item.badge }}
                  </span>

                  <ChevronDownIcon 
                    v-if="sidebarOpen"
                    class="ml-auto h-4 w-4 transition-transform duration-200 text-gray-400 group-hover:text-gray-200"
                    :class="openSubmenus.includes(item.name) ? 'rotate-180' : ''"
                  />
                </button>
                
                <!-- Submenu -->
                <transition
                  enter-active-class="transition-all duration-200 ease-out"
                  enter-from-class="opacity-0 max-h-0"
                  enter-to-class="opacity-100 max-h-96"
                  leave-active-class="transition-all duration-200 ease-in"
                  leave-from-class="opacity-100 max-h-96"
                  leave-to-class="opacity-0 max-h-0"
                >
                  <div v-if="sidebarOpen && openSubmenus.includes(item.name)" class="mt-1 ml-8 space-y-1 overflow-hidden">
                    <template v-for="subItem in item.children" :key="subItem.name">
                      <!-- Skip disabled items (separators) -->
                      <div v-if="subItem.disabled" class="px-3 py-1 text-xs font-medium text-gray-500 border-b border-gray-800/50 mb-1">
                        {{ subItem.name.replace(/^--- | ---$/g, '') }}
                      </div>
                      
                      <!-- Regular submenu items -->
                      <Link
                        v-else
                        :href="subItem.href"
                        class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 relative"
                        :class="subItem.current 
                          ? 'text-gray-200 font-medium' 
                          : 'text-gray-400 hover:text-gray-200'"
                      >
                        <!-- Background for active child -->
                        <div v-if="subItem.current" 
                             class="absolute inset-0 bg-gray-800 rounded-lg"></div>
                        
                        <!-- Child Icon (if available) -->
                        <component v-if="subItem.icon" 
                                   :is="subItem.icon" 
                                   :class="[
                                     'h-4 w-4 mr-3 relative z-10',
                                     subItem.current ? 'text-gray-200' : 'text-gray-400'
                                   ]" />
                        <!-- Indicator line (if no icon) -->
                        <div v-else class="w-2 h-0.5 bg-gray-600 rounded-full mr-3 relative z-10"
                             :class="subItem.current ? 'bg-gray-400' : 'bg-gray-600'"></div>
                        
                        <span class="truncate relative z-10">{{ subItem.name }}</span>

                        <!-- Badge for child items -->
                        <span v-if="subItem.badge"
                              :class="[
                                'ml-auto px-1.5 py-0.5 text-xs font-medium rounded-full relative z-10',
                                subItem.badgeColor || 'bg-orange-100 text-orange-800'
                              ]">
                          {{ subItem.badge }}
                        </span>
                      </Link>
                    </template>
                  </div>
                </transition>
              </div>
            </template>
          </div>
        </nav>

        <!-- User Profile -->
        <div class="border-t border-gray-800 p-4">
          <div class="flex items-center">
            <div class="h-10 w-10 rounded-xl bg-gray-600 flex items-center justify-center shadow-lg border-2 border-gray-700">
              <span class="text-sm font-semibold text-white">
                {{ currentUser?.name?.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div v-if="sidebarOpen" class="ml-3 flex-1 min-w-0">
              <p class="text-sm font-medium text-white truncate">
                {{ currentUser?.name }}
              </p>
              <p class="text-xs text-gray-400 truncate">
                {{ currentUser?.email }}
              </p>
            </div>
            <button v-if="sidebarOpen" class="ml-2 p-1 rounded-lg hover:bg-gray-800 transition-colors">
              <Cog6ToothIcon class="h-4 w-4 text-gray-400 hover:text-gray-200" />
            </button>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="transition-all duration-300 ease-in-out" :class="sidebarOpen ? 'ml-72' : 'ml-20'">
      <!-- Top Header -->
      <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-sm border-b border-gray-200 shadow-sm">
        <div class="flex h-20 items-center justify-between px-6">
          <div class="flex items-center space-x-4">
            <!-- Sidebar Toggle -->
            <button
              @click="toggleSidebar"
              class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all duration-200 shadow-sm border border-gray-200"
            >
              <Bars3Icon class="h-5 w-5 text-gray-600" />
            </button>

            <!-- Page Title -->
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h1>
              <p v-if="pageSubtitle" class="text-sm text-gray-500">{{ pageSubtitle }}</p>
            </div>
          </div>

          <!-- Header Actions -->
          <div class="flex items-center space-x-4">
            <!-- Search -->
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input
                type="text"
                placeholder="Ara..."
                class="w-80 pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 hover:bg-white"
              />
            </div>

            <!-- Notifications -->
            <div class="relative dropdown-container" ref="notificationsRef">
              <button 
                @click="toggleNotifications"
                class="relative p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all duration-200 shadow-sm border border-gray-200"
                :class="{ 'bg-gray-200': showNotifications }"
              >
                <BellIcon class="h-5 w-5 text-gray-600" />
                <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center shadow-lg">3</span>
              </button>

              <!-- Notifications Dropdown -->
              <teleport to="body">
                <div 
                  v-if="showNotifications" 
                  ref="notificationsDropdown"
                  class="fixed bg-white rounded-xl shadow-2xl border border-gray-200 py-2 z-[99999] min-w-[20rem]"
                  :style="notificationsStyle"
                  @click.stop
                >
                  <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Bildirimler</h3>
                  </div>
                  <div class="max-h-64 overflow-y-auto">
                    <div class="p-4 text-center text-gray-500">
                      Henüz bildirim yok
                    </div>
                  </div>
                </div>
              </teleport>
            </div>

            <!-- Theme Toggle -->
            <button
              @click="toggleTheme"
              class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all duration-200 shadow-sm border border-gray-200"
            >
              <SunIcon v-if="isDark" class="h-5 w-5 text-gray-600" />
              <MoonIcon v-else class="h-5 w-5 text-gray-600" />
            </button>

            <!-- User Menu -->
            <div class="relative dropdown-container" ref="userMenuRef">
              <button 
                @click="toggleUserMenu"
                class="flex items-center space-x-2 p-2 rounded-xl hover:bg-gray-100 transition-all duration-200"
                :class="{ 'bg-gray-100': showUserMenu }"
              >
                <div class="h-8 w-8 rounded-lg bg-gray-600 flex items-center justify-center shadow-lg">
                  <span class="text-xs font-semibold text-white">
                    {{ currentUser?.name?.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <ChevronDownIcon 
                  class="h-4 w-4 text-gray-400 transition-transform duration-200" 
                  :class="{ 'rotate-180': showUserMenu }"
                />
              </button>

              <!-- User Dropdown -->
              <teleport to="body">
                <div 
                  v-if="showUserMenu" 
                  ref="userDropdown"
                  class="fixed bg-white rounded-xl shadow-2xl border border-gray-200 py-2 z-[99999] min-w-[14rem]"
                  :style="userMenuStyle"
                  @click.stop
                >
                  <div class="px-4 py-3 border-b border-gray-200">
                    <p class="text-sm font-medium text-gray-900">{{ currentUser?.name }}</p>
                    <p class="text-xs text-gray-500">{{ currentUser?.email }}</p>
                  </div>
                  
                  <div class="py-1">
                    <Link 
                      :href="createRoute('profile.show', '/profile')" 
                      @click="closeUserMenu"
                      class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200"
                    >
                      <UserCircleIcon class="mr-3 h-4 w-4 text-gray-400" />
                      Profil Ayarları
                    </Link>
                    
                    <div class="border-t border-gray-200 mt-2 pt-2">
                      <Link 
                        href="/logout" 
                        method="post" 
                        @click="closeUserMenu"
                        class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-200"
                      >
                        <ArrowRightOnRectangleIcon class="mr-3 h-4 w-4 text-red-500" />
                        Çıkış Yap
                      </Link>
                    </div>
                  </div>
                </div>
              </teleport>
            </div>
          </div>
        </div>
      </header>

      <!-- Breadcrumbs -->
      <div v-if="breadcrumbs && breadcrumbs.length > 0" class="bg-white/80 backdrop-blur-sm border-b border-gray-200 px-6 py-4">
        <nav class="flex" aria-label="Breadcrumb">
          <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li v-for="(crumb, index) in breadcrumbs" :key="index" class="inline-flex items-center">
              <ChevronRightIcon v-if="index > 0" class="w-4 h-4 text-gray-400 mx-2" />
              <Link
                v-if="crumb.href"
                :href="crumb.href"
                class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors duration-200"
              >
                {{ crumb.label }}
              </Link>
              <span v-else class="text-sm font-medium text-gray-900">
                {{ crumb.label }}
              </span>
            </li>
          </ol>
        </nav>
      </div>

      <!-- Page Content -->
      <main class="flex-1 px-6 py-8">
        <!-- Flash Messages -->
        <div v-if="$page.props.flash && Object.keys($page.props.flash).length > 0" class="mb-6">
          <div v-for="(message, type) in $page.props.flash" :key="type" 
            class="p-4 rounded-xl border shadow-sm"
            :class="{
              'bg-green-50 border-green-200 text-green-800': type === 'success',
              'bg-red-50 border-red-200 text-red-800': type === 'error',
              'bg-blue-50 border-blue-200 text-blue-800': type === 'info',
              'bg-yellow-50 border-yellow-200 text-yellow-800': type === 'warning'
            }"
          >
            {{ message }}
          </div>
        </div>

        <!-- Main Content Slot -->
        <slot />
      </main>
    </div>

    <!-- Mobile Overlay -->
    <div 
      v-if="sidebarOpen && isMobile" 
      @click="sidebarOpen = false"
      class="fixed inset-0 z-30 bg-gray-900/50 backdrop-blur-sm lg:hidden"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  HomeIcon,
  CalendarIcon,
  UsersIcon,
  BuildingOfficeIcon,
  MapPinIcon,
  TagIcon,
  CogIcon,
  Bars3Icon,
  MagnifyingGlassIcon,
  BellIcon,
  SunIcon,
  MoonIcon,
  ChevronDownIcon,
  ChevronRightIcon,
  Cog6ToothIcon,
  DocumentTextIcon,
  SpeakerWaveIcon,
  UserCircleIcon,
  ArrowRightOnRectangleIcon,
  ClockIcon,
  CursorArrowRaysIcon,
  CalendarDaysIcon,
  QuestionMarkCircleIcon,
  PresentationChartLineIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  pageTitle: {
    type: String,
    default: 'Dashboard'
  },
  pageSubtitle: {
    type: String,
    default: ''
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  recentTimelineEvents: {
    type: Array,
    default: () => []
  }
})

// Page data
const page = usePage()
const currentUser = computed(() => page.props.auth?.user || null)

// Template refs
const userMenuRef = ref(null)
const notificationsRef = ref(null)
const userDropdown = ref(null)
const notificationsDropdown = ref(null)

// State
const sidebarOpen = ref(true)
const showUserMenu = ref(false)
const showNotifications = ref(false)
const openSubmenus = ref(['etkinlikler'])
const isDark = ref(false)
const isMobile = ref(false)

// Dropdown positioning
const userMenuStyle = ref({})
const notificationsStyle = ref({})

// Helper function to safely create routes
const createRoute = (routeName, fallback = '#') => {
  try {
    return route(routeName)
  } catch (error) {
    return fallback
  }
}

// Helper function to safely check current route
const isCurrentRoute = (routeName) => {
  try {
    return route().current(routeName)
  } catch (error) {
    return false
  }
}

// Navigation structure - Timeline menüsü güncellendi
const navigation = computed(() => [
  {
    name: 'main',
    items: [
      {
        name: 'Dashboard',
        href: createRoute('admin.dashboard', '/admin'),
        icon: HomeIcon,
        current: isCurrentRoute('admin.dashboard') || page.url === '/admin'
      }
    ]
  },
  {
    name: 'etkinlik',
    title: 'Etkinlik Yönetimi',
    items: [
      {
        name: 'Etkinlikler',
        href: createRoute('admin.events.index', '/admin/events'),
        icon: CalendarIcon,
        current: isCurrentRoute('admin.events.*') || page.url.startsWith('/admin/events'),
        badge: '3'
      },
      {
        name: 'Program Oturumları',
        href: createRoute('admin.program-sessions.index', '/admin/program-sessions'),
        icon: DocumentTextIcon,
        current: isCurrentRoute('admin.program-sessions.*') || page.url.startsWith('/admin/program-sessions')
      },
      {
        name: 'Sunumlar',
        href: createRoute('admin.presentations.index', '/admin/presentations'),
        icon: PresentationChartLineIcon,
        current: isCurrentRoute('admin.presentations.*') || page.url.startsWith('/admin/presentations')
      }
    ]
  },
  {
    name: 'timeline',
    title: 'Timeline & Editör',
    items: [
      {
        name: 'Timeline Yönetimi',
        href: createRoute('admin.events.index', '/admin/events'),
        icon: ClockIcon,
        current: isCurrentRoute('admin.timeline.*') || isCurrentRoute('admin.events.*'),
        badge: props.recentTimelineEvents?.length > 0 ? props.recentTimelineEvents.length.toString() : undefined
      }
    ]
  },
  {
    name: 'katilimci',
    title: 'Katılımcı Yönetimi',
    items: [
      {
        name: 'Katılımcılar',
        href: createRoute('admin.participants.index', '/admin/participants'),
        icon: UsersIcon,
        current: isCurrentRoute('admin.participants.*') || page.url.startsWith('/admin/participants')
      },
      {
        name: 'Sponsorlar',
        href: createRoute('admin.sponsors.index', '/admin/sponsors'),
        icon: TagIcon,
        current: isCurrentRoute('admin.sponsors.*') || page.url.startsWith('/admin/sponsors')
      }
    ]
  },
  {
    name: 'organizasyon',
    title: 'Organizasyon',
    items: [
      {
        name: 'Organizasyonlar',
        href: createRoute('admin.organizations.index', '/admin/organizations'),
        icon: BuildingOfficeIcon,
        current: isCurrentRoute('admin.organizations.*') || page.url.startsWith('/admin/organizations')
      },
      {
        name: 'Salonlar',
        href: createRoute('admin.venues.index', '/admin/venues'),
        icon: MapPinIcon,
        current: isCurrentRoute('admin.venues.*') || page.url.startsWith('/admin/venues')
      }
    ]
  }
])

// Dropdown positioning calculations
const calculateDropdownPosition = async (triggerRef, dropdownType) => {
  if (!triggerRef.value) return

  await nextTick()
  
  const rect = triggerRef.value.getBoundingClientRect()
  const windowHeight = window.innerHeight
  const windowWidth = window.innerWidth
  
  let top = rect.bottom + 8
  let left = rect.right - 224 // dropdown width

  // Adjust if dropdown would go off screen
  if (top + 300 > windowHeight) {
    top = rect.top - 300
  }
  
  if (left < 8) {
    left = 8
  }

  const style = {
    top: `${top}px`,
    left: `${left}px`
  }

  if (dropdownType === 'user') {
    userMenuStyle.value = style
  } else if (dropdownType === 'notifications') {
    notificationsStyle.value = style
  }
}

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const toggleSubmenu = (menuName) => {
  const index = openSubmenus.value.indexOf(menuName)
  if (index > -1) {
    openSubmenus.value.splice(index, 1)
  } else {
    openSubmenus.value.push(menuName)
  }
}

const toggleUserMenu = async () => {
  showUserMenu.value = !showUserMenu.value
  if (showUserMenu.value) {
    showNotifications.value = false
    await calculateDropdownPosition(userMenuRef, 'user')
  }
}

const closeUserMenu = () => {
  showUserMenu.value = false
}

const toggleNotifications = async () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    showUserMenu.value = false
    await calculateDropdownPosition(notificationsRef, 'notifications')
  }
}

const closeNotifications = () => {
  showNotifications.value = false
}

const toggleTheme = () => {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024
  if (isMobile.value) {
    sidebarOpen.value = false
  }
}

// Click outside handler
const handleClickOutside = (event) => {
  // Check user menu
  if (showUserMenu.value && 
      !userMenuRef.value?.contains(event.target) && 
      !userDropdown.value?.contains(event.target)) {
    showUserMenu.value = false
  }
  
  // Check notifications
  if (showNotifications.value && 
      !notificationsRef.value?.contains(event.target) && 
      !notificationsDropdown.value?.contains(event.target)) {
    showNotifications.value = false
  }
}

// Keyboard shortcuts
const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    showUserMenu.value = false
    showNotifications.value = false
  }
}

// Watch for dropdown changes
watch(showUserMenu, (newValue) => {
  if (newValue) {
    calculateDropdownPosition(userMenuRef, 'user')
  }
})

watch(showNotifications, (newValue) => {
  if (newValue) {
    calculateDropdownPosition(notificationsRef, 'notifications')
  }
})

// Lifecycle
onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  document.addEventListener('click', handleClickOutside, true)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  document.removeEventListener('click', handleClickOutside, true)
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
/* Sidebar navigation scrollbar */
.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.3);
  border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.5);
}

/* Z-index management */
.dropdown-container {
  position: relative;
  z-index: 50;
}

.z-\[99999\] {
  z-index: 99999 !important;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Rotation animation */
.rotate-180 {
  transform: rotate(180deg);
}

/* Enhanced shadows */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}

/* Focus styles */
button:focus-visible,
input:focus,
a:focus-visible {
  outline: none;
  box-shadow: 0 0 0 2px rgba(75, 85, 99, 0.5);
  border-radius: 0.75rem;
}

/* Badge animations */
.badge-beta {
  animation: pulse-orange 2s infinite;
}

@keyframes pulse-orange {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.4);
  }
  50% {
    box-shadow: 0 0 0 0.5rem rgba(249, 115, 22, 0);
  }
}

/* Separator styling for submenu items */
.submenu-separator {
  color: #6b7280;
  font-size: 0.75rem;
  font-weight: 500;
  pointer-events: none;
  padding: 0.25rem 0;
  margin: 0.5rem 0;
  border-bottom: 1px solid rgba(75, 85, 99, 0.3);
}

/* Backdrop blur support */
@supports (backdrop-filter: blur(16px)) {
  .backdrop-blur-lg {
    backdrop-filter: blur(16px);
  }
  
  .backdrop-blur-sm {
    backdrop-filter: blur(4px);
  }
}

/* Responsive design */
@media (max-width: 1024px) {
  .sidebar-container {
    transform: translateX(-100%);
  }
  
  .sidebar-container.open {
    transform: translateX(0);
  }
}
</style>