import { ref, computed, watch, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

export function useTheme() {
  // State
  const currentTheme = ref('light')
  const isTransitioning = ref(false)
  const systemTheme = ref('light')
  const userPreference = ref('system')

  // Computed
  const page = usePage()
  
  const isDark = computed(() => {
    if (userPreference.value === 'system') {
      return systemTheme.value === 'dark'
    }
    return currentTheme.value === 'dark'
  })

  const themeIcon = computed(() => {
    return isDark.value ? 'SunIcon' : 'MoonIcon'
  })

  const themeLabel = computed(() => {
    return isDark.value ? 'Açık Tema' : 'Koyu Tema'
  })

  const availableThemes = computed(() => [
    {
      key: 'light',
      name: 'Açık Tema',
      icon: 'SunIcon',
      description: 'Parlak ve temiz görünüm'
    },
    {
      key: 'dark',
      name: 'Koyu Tema', 
      icon: 'MoonIcon',
      description: 'Gözleri yormayan karanlık tema'
    },
    {
      key: 'system',
      name: 'Sistem Teması',
      icon: 'ComputerDesktopIcon',
      description: 'Sistem ayarlarını takip eder'
    }
  ])

  // Theme detection
  const detectSystemTheme = () => {
    if (typeof window !== 'undefined' && window.matchMedia) {
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }
    return 'light'
  }

  // Apply theme to DOM
  const applyTheme = (theme) => {
    if (typeof document === 'undefined') return

    const root = document.documentElement
    const body = document.body

    // Start transition
    isTransitioning.value = true

    // Remove existing theme classes
    root.classList.remove('light', 'dark')
    body.classList.remove('light', 'dark')

    // Add new theme class
    if (theme === 'dark') {
      root.classList.add('dark')
      body.classList.add('dark')
    } else {
      root.classList.add('light')
      body.classList.add('light')
    }

    // Update meta theme-color for mobile browsers
    const metaThemeColor = document.querySelector('meta[name="theme-color"]')
    if (metaThemeColor) {
      metaThemeColor.setAttribute('content', theme === 'dark' ? '#1f2937' : '#ffffff')
    }

    // Update currentTheme
    currentTheme.value = theme

    // End transition after a short delay
    setTimeout(() => {
      isTransitioning.value = false
    }, 300)
  }

  // Save preference to localStorage and server
  const saveThemePreference = async (theme) => {
    try {
      // Save to localStorage for immediate persistence
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem('theme', theme)
      }

      // Save to server for cross-device sync
      await fetch(route('admin.profile.update-theme'), {
        method: 'PATCH',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({ theme }),
        credentials: 'same-origin'
      })
    } catch (error) {
      console.warn('Failed to save theme preference:', error)
    }
  }

  // Load preference from localStorage or user settings
  const loadThemePreference = () => {
    // Check page props first (from server)
    if (page.props.auth?.user?.theme) {
      return page.props.auth.user.theme
    }

    // Fallback to localStorage
    if (typeof localStorage !== 'undefined') {
      const stored = localStorage.getItem('theme')
      if (stored && ['light', 'dark', 'system'].includes(stored)) {
        return stored
      }
    }

    // Default to system
    return 'system'
  }

  // Main theme setter
  const setTheme = async (theme) => {
    if (!['light', 'dark', 'system'].includes(theme)) {
      console.warn('Invalid theme:', theme)
      return
    }

    userPreference.value = theme

    let actualTheme = theme
    if (theme === 'system') {
      actualTheme = systemTheme.value
    }

    applyTheme(actualTheme)
    await saveThemePreference(theme)

    // Emit custom event for other components
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('theme-changed', {
        detail: { theme: actualTheme, preference: theme }
      }))
    }
  }

  // Toggle between light and dark (skip system)
  const toggleTheme = async () => {
    const newTheme = isDark.value ? 'light' : 'dark'
    await setTheme(newTheme)
  }

  // Cycle through all themes including system
  const cycleTheme = async () => {
    const themes = ['light', 'dark', 'system']
    const currentIndex = themes.indexOf(userPreference.value)
    const nextIndex = (currentIndex + 1) % themes.length
    await setTheme(themes[nextIndex])
  }

  // Initialize theme system
  const initializeTheme = () => {
    // Detect system theme
    systemTheme.value = detectSystemTheme()

    // Load user preference
    userPreference.value = loadThemePreference()

    // Determine actual theme to apply
    let themeToApply = userPreference.value
    if (themeToApply === 'system') {
      themeToApply = systemTheme.value
    }

    // Apply theme
    applyTheme(themeToApply)

    // Listen for system theme changes
    if (typeof window !== 'undefined' && window.matchMedia) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
      
      const handleSystemThemeChange = (e) => {
        systemTheme.value = e.matches ? 'dark' : 'light'
        
        // If user preference is system, update applied theme
        if (userPreference.value === 'system') {
          applyTheme(systemTheme.value)
        }
      }

      mediaQuery.addEventListener('change', handleSystemThemeChange)
      
      // Store cleanup function
      window.themeMediaQueryCleanup = () => {
        mediaQuery.removeEventListener('change', handleSystemThemeChange)
      }
    }
  }

  // Cleanup function
  const cleanup = () => {
    if (typeof window !== 'undefined' && window.themeMediaQueryCleanup) {
      window.themeMediaQueryCleanup()
    }
  }

  // Auto-save theme when it changes
  watch(userPreference, async (newTheme) => {
    if (newTheme) {
      await saveThemePreference(newTheme)
    }
  })

  // Theme animation utilities
  const enableThemeTransition = () => {
    if (typeof document === 'undefined') return

    const style = document.createElement('style')
    style.textContent = `
      * {
        transition: background-color 0.3s ease, 
                   border-color 0.3s ease, 
                   color 0.3s ease !important;
      }
    `
    document.head.appendChild(style)

    // Remove transition styles after animation
    setTimeout(() => {
      document.head.removeChild(style)
    }, 300)
  }

  const disableThemeTransition = () => {
    if (typeof document === 'undefined') return

    const style = document.createElement('style')
    style.textContent = `
      * {
        transition: none !important;
      }
    `
    document.head.appendChild(style)

    // Force reflow
    document.body.offsetHeight

    setTimeout(() => {
      document.head.removeChild(style)
    }, 100)
  }

  // Theme-aware color utilities
  const getThemeColor = (lightColor, darkColor) => {
    return isDark.value ? darkColor : lightColor
  }

  const getThemeClass = (lightClass, darkClass) => {
    return isDark.value ? darkClass : lightClass
  }

  // Accessibility helpers
  const announceThemeChange = () => {
    if (typeof document === 'undefined') return

    const announcement = `Tema ${isDark.value ? 'koyu' : 'açık'} olarak değiştirildi`
    
    // Create temporary element for screen readers
    const announcer = document.createElement('div')
    announcer.setAttribute('aria-live', 'polite')
    announcer.setAttribute('aria-atomic', 'true')
    announcer.className = 'sr-only'
    announcer.textContent = announcement
    
    document.body.appendChild(announcer)
    
    setTimeout(() => {
      document.body.removeChild(announcer)
    }, 1000)
  }

  // Watch for theme changes to announce them
  watch(isDark, () => {
    announceThemeChange()
  })

  // Export theme state for debugging
  const getThemeState = () => ({
    currentTheme: currentTheme.value,
    userPreference: userPreference.value,
    systemTheme: systemTheme.value,
    isDark: isDark.value,
    isTransitioning: isTransitioning.value
  })

  // Initialize on mount
  onMounted(() => {
    initializeTheme()
  })

  // Return the composable interface
  return {
    // State
    currentTheme,
    userPreference,
    systemTheme,
    isTransitioning,

    // Computed
    isDark,
    themeIcon,
    themeLabel,
    availableThemes,

    // Methods
    setTheme,
    toggleTheme,
    cycleTheme,
    initializeTheme,
    cleanup,

    // Animation utilities
    enableThemeTransition,
    disableThemeTransition,

    // Color utilities
    getThemeColor,
    getThemeClass,

    // Accessibility
    announceThemeChange,

    // Debug
    getThemeState
  }
}