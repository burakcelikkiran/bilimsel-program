<template>
  <!-- Sidebar Container -->
  <div :class="[
    'flex flex-col h-full transition-all duration-300 ease-in-out',
    isOpen ? 'w-64' : 'w-16'
  ]">
    <!-- Sidebar Content -->
    <div class="flex flex-col h-full bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 border-r border-slate-700 shadow-xl sidebar-content">
      
      <!-- Header -->
      <div class="flex items-center justify-between h-16 px-4 border-b border-slate-700">
        <div v-if="isOpen" class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-lg flex items-center justify-center">
            <BuildingOfficeIcon class="h-5 w-5 text-white" />
          </div>
          <div class="text-white">
            <h1 class="text-lg font-bold">{{ currentOrganization?.name || 'Yönetim Paneli' }}</h1>
            <p class="text-xs text-cyan-300">{{ userRole }}</p>
          </div>
        </div>
        
        <!-- Toggle Button -->
        <button
          @click="$emit('close')"
          class="p-2 rounded-lg text-cyan-300 hover:text-white hover:bg-slate-700 transition-all duration-200"
        >
          <ChevronLeftIcon :class="['h-5 w-5 transform transition-transform duration-300', !isOpen && 'rotate-180']" />
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">
        <!-- Navigation Sections -->
        <div v-for="section in navigationSections" :key="section.name" class="space-y-1">
          
          <!-- Section Title -->
          <h3 v-if="section.title && isOpen" 
              class="px-3 py-2 text-xs font-semibold text-cyan-300 uppercase tracking-wider">
            {{ section.title }}
          </h3>

          <!-- Section Items -->
          <div v-for="item in section.items" :key="item.name">
            
            <!-- Parent Item without submenu -->
            <Link v-if="!item.children"
                  :href="item.href"
                  :class="getNavItemClasses(item)"
                  class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 relative overflow-hidden">
              
              <!-- Background gradient for active item -->
              <div v-if="item.current" 
                   class="absolute inset-0 bg-gradient-to-r from-cyan-500/20 to-blue-500/20 rounded-xl"></div>
              
              <!-- Icon -->
              <component :is="item.icon" 
                         :class="[
                           'h-5 w-5 flex-shrink-0 transition-colors duration-200 relative z-10',
                           item.current ? 'text-cyan-400' : 'text-slate-400 group-hover:text-cyan-300'
                         ]" />
              
              <!-- Label -->
              <span v-if="isOpen" 
                    :class="[
                      'ml-3 transition-colors duration-200 relative z-10',
                      item.current ? 'text-white font-semibold' : 'text-slate-300 group-hover:text-white'
                    ]">
                {{ item.name }}
              </span>

              <!-- Badge -->
              <span v-if="item.badge && isOpen"
                    :class="[
                      'ml-auto px-2 py-0.5 text-xs font-medium rounded-full relative z-10',
                      item.badgeColor || 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                    ]">
                {{ item.badge }}
              </span>

              <!-- Active indicator -->
              <div v-if="item.current" 
                   class="absolute right-2 w-2 h-2 bg-cyan-400 rounded-full shadow-lg shadow-cyan-400/50 relative z-10"></div>
            </Link>

            <!-- Parent Item with submenu -->
            <div v-else>
              <button
                @click="toggleSubmenu(item.name)"
                :class="getNavItemClasses(item)"
                class="group w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 relative overflow-hidden"
              >
                <!-- Background gradient for active item -->
                <div v-if="item.current" 
                     class="absolute inset-0 bg-gradient-to-r from-cyan-500/20 to-blue-500/20 rounded-xl"></div>
                
                <div class="flex items-center relative z-10">
                  <!-- Icon -->
                  <component :is="item.icon" 
                             :class="[
                               'h-5 w-5 flex-shrink-0 transition-colors duration-200',
                               item.current ? 'text-cyan-400' : 'text-slate-400 group-hover:text-cyan-300'
                             ]" />
                  
                  <!-- Label -->
                  <span v-if="isOpen" 
                        :class="[
                          'ml-3 transition-colors duration-200',
                          item.current ? 'text-white font-semibold' : 'text-slate-300 group-hover:text-white'
                        ]">
                    {{ item.name }}
                  </span>

                  <!-- Badge for expandable items -->
                  <span v-if="item.badge && isOpen"
                        :class="[
                          'ml-2 px-2 py-0.5 text-xs font-medium rounded-full',
                          item.badgeColor || 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                        ]">
                    {{ item.badge }}
                  </span>
                </div>

                <!-- Chevron -->
                <ChevronDownIcon v-if="isOpen" 
                                 :class="[
                                   'h-4 w-4 transition-transform duration-200 relative z-10',
                                   expandedMenus.includes(item.name) ? 'rotate-180' : '',
                                   item.current ? 'text-cyan-400' : 'text-slate-400 group-hover:text-cyan-300'
                                 ]" />
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
                <div v-if="expandedMenus.includes(item.name) && isOpen" 
                     class="mt-1 ml-8 space-y-1 overflow-hidden">
                  <Link v-for="child in item.children" 
                        :key="child.name"
                        :href="child.href"
                        :class="getNavItemClasses(child, true)"
                        class="group flex items-center px-3 py-2 text-sm rounded-lg transition-all duration-200 relative">
                    
                    <!-- Background for active child -->
                    <div v-if="child.current" 
                         class="absolute inset-0 bg-gradient-to-r from-cyan-500/10 to-blue-500/10 rounded-lg"></div>
                    
                    <!-- Child Icon (if available) -->
                    <component v-if="child.icon" 
                               :is="child.icon" 
                               :class="[
                                 'h-4 w-4 mr-3 relative z-10',
                                 child.current ? 'text-cyan-400' : 'text-slate-500 group-hover:text-cyan-300'
                               ]" />
                    <!-- Indicator line (if no icon) -->
                    <div v-else class="w-2 h-0.5 bg-slate-600 rounded-full mr-3 relative z-10"
                         :class="child.current ? 'bg-cyan-400' : 'bg-slate-600'"></div>
                    
                    <span :class="[
                      'transition-colors duration-200 relative z-10',
                      child.current ? 'text-cyan-300 font-medium' : 'text-slate-400 group-hover:text-slate-200'
                    ]">
                      {{ child.name }}
                    </span>

                    <!-- Badge for child items -->
                    <span v-if="child.badge"
                          :class="[
                            'ml-auto px-1.5 py-0.5 text-xs font-medium rounded-full relative z-10',
                            child.badgeColor || 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
                          ]">
                      {{ child.badge }}
                    </span>
                  </Link>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </nav>

      <!-- User Profile Section -->
      <div class="border-t border-slate-700 p-4">
        <div class="flex items-center space-x-3">
          <!-- Avatar -->
          <div class="relative">
            <img :src="currentUser?.profile_photo_url || '/images/default-avatar.png'" 
                 :alt="currentUser?.name"
                 class="w-10 h-10 rounded-full object-cover border-2 border-cyan-400/30 shadow-lg" />
            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 border-2 border-slate-800 rounded-full"></div>
          </div>
          
          <!-- User Info -->
          <div v-if="isOpen" class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate">
              {{ currentUser?.name }}
            </p>
            <p class="text-xs text-cyan-300 truncate">
              {{ currentUser?.email }}
            </p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div v-if="isOpen" class="mt-4 flex space-x-2">
          <Link :href="route('profile.show')"
                class="flex-1 px-3 py-2 text-xs font-medium text-slate-200 bg-slate-700/50 rounded-lg hover:bg-slate-600/50 text-center transition-all duration-200 border border-slate-600 hover:border-cyan-400/30">
            Profil
          </Link>
          <Link :href="route('logout')" method="post" as="button"
                class="flex-1 px-3 py-2 text-xs font-medium text-slate-300 bg-gradient-to-r from-red-500/20 to-red-600/20 rounded-lg hover:from-red-500/30 hover:to-red-600/30 text-center transition-all duration-200 border border-red-500/30 hover:border-red-400/50">
            Çıkış
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  HomeIcon,
  CalendarIcon,
  BuildingOfficeIcon,
  UsersIcon,
  SpeakerWaveIcon,
  MapPinIcon,
  TagIcon,
  ChartBarIcon,
  CogIcon,
  DocumentArrowDownIcon,
  DocumentArrowUpIcon,
  ChevronLeftIcon,
  ChevronDownIcon,
  ClockIcon,
  CursorArrowRaysIcon,
  CalendarDaysIcon,
  QuestionMarkCircleIcon,
  Cog6ToothIcon,
  PresentationChartLineIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: true
  },
  currentOrganization: {
    type: Object,
    default: null
  },
  recentTimelineEvents: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['close', 'organization-changed'])

// Page data
const page = usePage()
const currentUser = computed(() => page.props.auth?.user)
const userRole = computed(() => page.props.auth?.user?.role || 'Kullanıcı')
const availableOrganizations = computed(() => page.props.auth?.organizations || [])

// State
const expandedMenus = ref(['events', 'management', 'timeline'])

// Navigation configuration
const navigationSections = computed(() => [
  {
    name: 'main',
    title: null,
    items: [
      {
        name: 'Dashboard',
        href: route('admin.dashboard'),
        icon: HomeIcon,
        current: route().current('admin.dashboard')
      }
    ]
  },
  {
    name: 'events',
    title: 'Etkinlik Yönetimi',
    items: [
      {
        name: 'Etkinlikler',
        href: route('admin.events.index'),
        icon: CalendarIcon,
        current: route().current('admin.events.*')
      },
      {
        name: 'Salonlar',
        href: route('admin.venues.index'),
        icon: MapPinIcon,
        current: route().current('admin.venues.*')
      },
      {
        name: 'Program Oturumları',
        href: route('admin.program-sessions.index'),
        icon: SpeakerWaveIcon,
        current: route().current('admin.program-sessions.*')
      },
      {
        name: 'Sunumlar',
        href: route('admin.presentations.index'),
        icon: PresentationChartLineIcon,
        current: route().current('admin.presentations.*')
      }
    ]
  },
  {
    name: 'timeline',
    title: 'Timeline & Editör',
    items: [
      {
        name: 'Timeline Yönetimi',
        icon: ClockIcon,
        current: route().current('admin.timeline.*'),
        children: [
          {
            name: 'Tüm Etkinlikler',
            href: route('admin.events.index'),
            icon: CalendarDaysIcon,
            current: route().current('admin.events.index')
          },
          // Recent Timeline Events (dinamik)
          ...(props.recentTimelineEvents?.length > 0 ? [
            // Separator for recent events
            {
              name: '--- Son Görüntülenen ---',
              href: '#',
              disabled: true,
              current: false
            },
            // Recent events
            ...props.recentTimelineEvents.slice(0, 3).map(event => ({
              name: event.name,
              href: route('admin.timeline.show', event.slug),
              current: route().current('admin.timeline.show', event.slug)
            }))
          ] : []),
          // Tools section
          {
            name: '--- Araçlar ---',
            href: '#',
            disabled: true,
            current: false
          },
          {
            name: 'Timeline Yardımı',
            href: route('admin.timeline.help'),
            icon: QuestionMarkCircleIcon,
            current: route().current('admin.timeline.help')
          },
          {
            name: 'Timeline Ayarları',
            href: route('admin.timeline.settings'),
            icon: Cog6ToothIcon,
            current: route().current('admin.timeline.settings')
          }
        ]
      }
    ]
  },
  {
    name: 'management',
    title: 'Katılımcı Yönetimi',
    items: [
      {
        name: 'Organizasyonlar',
        href: route('admin.organizations.index'),
        icon: BuildingOfficeIcon,
        current: route().current('admin.organizations.*')
      },
      {
        name: 'Katılımcılar',
        href: route('admin.participants.index'),
        icon: UsersIcon,
        current: route().current('admin.participants.*')
      },
      {
        name: 'Sponsorlar',
        href: route('admin.sponsors.index'),
        icon: TagIcon,
        current: route().current('admin.sponsors.*')
      }
    ]
  },
  {
    name: 'data',
    title: 'Veri Yönetimi',
    items: [
      {
        name: 'İçe/Dışa Aktarım',
        icon: DocumentArrowUpIcon,
        current: route().current('admin.import.*') || route().current('admin.export.*'),
        children: [
          {
            name: 'Veri İçe Aktarımı',
            href: route('admin.import.index'),
            icon: DocumentArrowUpIcon,
            current: route().current('admin.import.*')
          },
          {
            name: 'Raporlar',
            href: route('admin.export.index'),
            icon: DocumentArrowDownIcon,
            current: route().current('admin.export.*')
          }
        ]
      },
      {
        name: 'Raporlar',
        icon: ChartBarIcon,
        current: route().current('admin.reports.*'),
        children: [
          {
            name: 'Katılımcı Raporları',
            href: route('admin.reports.participants'),
            current: route().current('admin.reports.participants')
          },
          {
            name: 'Etkinlik Raporları',
            href: route('admin.reports.events'),
            current: route().current('admin.reports.events')
          },
          {
            name: 'İstatistikler',
            href: route('admin.reports.statistics'),
            current: route().current('admin.reports.statistics')
          }
        ]
      },
      {
        name: 'Ayarlar',
        href: route('admin.settings.index'),
        icon: CogIcon,
        current: route().current('admin.settings.*')
      }
    ]
  }
])

// Methods
const toggleSubmenu = (menuName) => {
  const index = expandedMenus.value.indexOf(menuName)
  if (index > -1) {
    expandedMenus.value.splice(index, 1)
  } else {
    expandedMenus.value.push(menuName)
  }
}

const getNavItemClasses = (item, isChild = false) => {
  // Skip styling for disabled items (separators)
  if (item.disabled) {
    return 'text-slate-500 text-xs font-medium pointer-events-none py-1'
  }

  if (isChild) {
    return item.current 
      ? 'text-cyan-300 font-medium' 
      : 'text-slate-400 hover:text-slate-200'
  }
  
  return item.current 
    ? 'text-white bg-slate-700/30 border-cyan-400/30' 
    : 'text-slate-300 hover:text-white hover:bg-slate-700/50 border-transparent hover:border-slate-600'
}

// Timeline menu auto-expand on timeline pages
const checkTimelineExpansion = () => {
  if (route().current('admin.timeline.*') || route().current('admin.drag-drop.*')) {
    if (!expandedMenus.value.includes('timeline')) {
      expandedMenus.value.push('timeline')
    }
  }
}

// Lifecycle
onMounted(() => {
  checkTimelineExpansion()
})
</script>

<style scoped>
/* Custom scrollbar for navigation */
nav::-webkit-scrollbar {
  width: 4px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}

nav::-webkit-scrollbar-thumb {
  background: rgba(6, 182, 212, 0.3);
  border-radius: 2px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: rgba(6, 182, 212, 0.5);
}

/* Sidebar gradient background - FORCE OVERRIDE */
.sidebar-content {
  background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #0f172a 100%) !important;
}

/* Enhanced gradient effects */
.bg-gradient-to-b {
  background: linear-gradient(to bottom, #0f172a, #1e293b, #0f172a) !important;
}

/* Hover effects with cyan theme */
.group:hover .transform {
  transform: translateX(2px);
}

/* Z-index fix for dropdown menus */
.relative {
  z-index: auto;
}

/* Ensure sidebar doesn't interfere with dropdowns */
.sidebar-container {
  z-index: 40;
}

/* Dropdown menu z-index */
.dropdown-menu {
  z-index: 9999 !important;
}

/* Cyan-themed active states */
.nav-item-active {
  background: linear-gradient(90deg, rgba(6, 182, 212, 0.2), rgba(59, 130, 246, 0.2)) !important;
  color: white !important;
  border-color: rgba(6, 182, 212, 0.3) !important;
}

/* Cyan hover states */
.nav-item-hover:hover {
  background: rgba(71, 85, 105, 0.5) !important;
  color: white !important;
  border-color: rgba(71, 85, 105, 1) !important;
}

/* Badge styling */
.badge-beta {
  animation: pulse-orange 2s infinite;
}

@keyframes pulse-orange {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.4);
  }
  50% {
    box-shadow: 0 0 0 0.5rem rgba(251, 146, 60, 0);
  }
}

/* Separator styling for submenu items */
.submenu-separator {
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 500;
  pointer-events: none;
  padding: 0.25rem 0;
  margin: 0.5rem 0;
  border-bottom: 1px solid rgba(71, 85, 105, 0.3);
}

/* Recent timeline events special styling */
.recent-timeline-item {
  position: relative;
}

.recent-timeline-item::before {
  content: '';
  position: absolute;
  left: -0.5rem;
  top: 50%;
  width: 0.375rem;
  height: 0.375rem;
  background: #06b6d4;
  border-radius: 50%;
  transform: translateY(-50%);
  box-shadow: 0 0 0.5rem rgba(6, 182, 212, 0.5);
}
</style>