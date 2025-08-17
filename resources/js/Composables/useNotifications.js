// resources/js/Composables/useNotifications.js
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

export function useNotifications() {
  // State
  const notifications = ref([])
  const loading = ref(false)
  const error = ref(null)
  const isConnected = ref(false)
  const reconnectAttempts = ref(0)
  const maxReconnectAttempts = 5

  // Computed
  const page = usePage()
  const user = computed(() => page.props.auth?.user)
  
  const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read_at).length
  })

  const recentNotifications = computed(() => {
    return notifications.value
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .slice(0, 10)
  })

  const unreadNotifications = computed(() => {
    return notifications.value.filter(n => !n.read_at)
  })

  const readNotifications = computed(() => {
    return notifications.value.filter(n => n.read_at)
  })

  // Notification types and their icons
  const notificationTypes = {
    'event_starting_soon': {
      icon: 'CalendarIcon',
      color: 'blue',
      title: 'Etkinlik Yaklaşıyor'
    },
    'event_incomplete': {
      icon: 'ExclamationTriangleIcon',
      color: 'yellow',
      title: 'Eksik Program'
    },
    'session_conflict': {
      icon: 'ExclamationTriangleIcon',
      color: 'red',
      title: 'Oturum Çakışması'
    },
    'participant_registered': {
      icon: 'UserIcon',
      color: 'green',
      title: 'Yeni Katılımcı'
    },
    'speaker_assigned': {
      icon: 'UserIcon',
      color: 'blue',
      title: 'Konuşmacı Atandı'
    },
    'event_published': {
      icon: 'CheckCircleIcon',
      color: 'green',
      title: 'Etkinlik Yayınlandı'
    },
    'system_update': {
      icon: 'InformationCircleIcon',
      color: 'blue',
      title: 'Sistem Güncellemesi'
    },
    'backup_completed': {
      icon: 'CheckCircleIcon',
      color: 'green',
      title: 'Yedekleme Tamamlandı'
    },
    'export_ready': {
      icon: 'DocumentArrowDownIcon',
      color: 'blue',
      title: 'Dışa Aktarma Hazır'
    }
  }

  // Methods
  const fetchNotifications = async () => {
    if (!user.value) return

    loading.value = true
    error.value = null

    try {
      const response = await fetch(route('admin.notifications'), {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin'
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      notifications.value = data.notifications || []
    } catch (err) {
      error.value = err.message
      console.error('Failed to fetch notifications:', err)
    } finally {
      loading.value = false
    }
  }

  const markAsRead = async (notificationId) => {
    try {
      const response = await fetch(route('admin.notifications.mark-read', notificationId), {
        method: 'PATCH',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        credentials: 'same-origin'
      })

      if (response.ok) {
        // Update local state
        const notificationIndex = notifications.value.findIndex(n => n.id === notificationId)
        if (notificationIndex !== -1) {
          notifications.value[notificationIndex].read_at = new Date().toISOString()
        }
      }
    } catch (err) {
      console.error('Failed to mark notification as read:', err)
    }
  }

  const markAllAsRead = async () => {
    try {
      const response = await fetch(route('admin.notifications.mark-all-read'), {
        method: 'PATCH',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        credentials: 'same-origin'
      })

      if (response.ok) {
        // Update local state
        const now = new Date().toISOString()
        notifications.value.forEach(notification => {
          if (!notification.read_at) {
            notification.read_at = now
          }
        })
      }
    } catch (err) {
      console.error('Failed to mark all notifications as read:', err)
    }
  }

  const deleteNotification = async (notificationId) => {
    try {
      const response = await fetch(route('admin.notifications.destroy', notificationId), {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        credentials: 'same-origin'
      })

      if (response.ok) {
        // Remove from local state
        notifications.value = notifications.value.filter(n => n.id !== notificationId)
      }
    } catch (err) {
      console.error('Failed to delete notification:', err)
    }
  }

  const clearAll = async () => {
    try {
      const response = await fetch(route('admin.notifications.clear-all'), {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        credentials: 'same-origin'
      })

      if (response.ok) {
        notifications.value = []
      }
    } catch (err) {
      console.error('Failed to clear all notifications:', err)
    }
  }

  // Real-time notifications with Server-Sent Events or WebSocket
  const connectToNotificationStream = () => {
    if (!user.value || isConnected.value) return

    try {
      // Using Server-Sent Events for real-time notifications
      const eventSource = new EventSource(route('admin.notifications.stream'))

      eventSource.onopen = () => {
        isConnected.value = true
        reconnectAttempts.value = 0
        console.log('Notification stream connected')
      }

      eventSource.onmessage = (event) => {
        try {
          const notification = JSON.parse(event.data)
          addNotification(notification)
        } catch (err) {
          console.error('Failed to parse notification:', err)
        }
      }

      eventSource.onerror = () => {
        isConnected.value = false
        eventSource.close()

        // Attempt to reconnect with exponential backoff
        if (reconnectAttempts.value < maxReconnectAttempts) {
          const delay = Math.pow(2, reconnectAttempts.value) * 1000
          setTimeout(() => {
            reconnectAttempts.value++
            connectToNotificationStream()
          }, delay)
        }
      }

      // Store reference for cleanup
      window.notificationEventSource = eventSource
    } catch (err) {
      console.error('Failed to connect to notification stream:', err)
    }
  }

  const disconnectFromNotificationStream = () => {
    if (window.notificationEventSource) {
      window.notificationEventSource.close()
      window.notificationEventSource = null
      isConnected.value = false
    }
  }

  // Add new notification to the list
  const addNotification = (notification) => {
    // Avoid duplicates
    const exists = notifications.value.some(n => n.id === notification.id)
    if (!exists) {
      notifications.value.unshift(notification)
      
      // Show browser notification if permission granted
      showBrowserNotification(notification)
    }
  }

  // Browser notification
  const requestNotificationPermission = async () => {
    if ('Notification' in window && Notification.permission === 'default') {
      await Notification.requestPermission()
    }
  }

  const showBrowserNotification = (notification) => {
    if ('Notification' in window && Notification.permission === 'granted') {
      const notificationType = notificationTypes[notification.type] || notificationTypes.system_update
      
      new Notification(notificationType.title, {
        body: notification.data?.message || notification.message,
        icon: '/images/notification-icon.png',
        tag: notification.id,
        renotify: false
      })
    }
  }

  // Utility functions
  const getNotificationIcon = (type) => {
    return notificationTypes[type]?.icon || 'InformationCircleIcon'
  }

  const getNotificationColor = (type) => {
    return notificationTypes[type]?.color || 'blue'
  }

  const getNotificationTitle = (type) => {
    return notificationTypes[type]?.title || 'Bildirim'
  }

  const formatRelativeTime = (date) => {
    const now = new Date()
    const past = new Date(date)
    const diffInSeconds = Math.floor((now - past) / 1000)

    if (diffInSeconds < 60) return 'Az önce'
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} dakika önce`
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} saat önce`
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} gün önce`
    return past.toLocaleDateString('tr-TR')
  }

  // Initialize notifications
  const initialize = async () => {
    await requestNotificationPermission()
    await fetchNotifications()
    connectToNotificationStream()
  }

  // Cleanup
  const cleanup = () => {
    disconnectFromNotificationStream()
  }

  // Auto-refresh notifications periodically (fallback)
  let refreshInterval = null
  const startPeriodicRefresh = () => {
    refreshInterval = setInterval(() => {
      if (!isConnected.value) {
        fetchNotifications()
      }
    }, 60000) // Refresh every minute
  }

  const stopPeriodicRefresh = () => {
    if (refreshInterval) {
      clearInterval(refreshInterval)
      refreshInterval = null
    }
  }

  // Lifecycle hooks
  onMounted(() => {
    initialize()
    startPeriodicRefresh()
  })

  onUnmounted(() => {
    cleanup()
    stopPeriodicRefresh()
  })

  // Return the composable interface
  return {
    // State
    notifications,
    loading,
    error,
    isConnected,

    // Computed
    unreadCount,
    recentNotifications,
    unreadNotifications,
    readNotifications,

    // Methods
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    clearAll,
    addNotification,
    connectToNotificationStream,
    disconnectFromNotificationStream,

    // Utility functions
    getNotificationIcon,
    getNotificationColor,
    getNotificationTitle,
    formatRelativeTime,

    // Lifecycle
    initialize,
    cleanup
  }
}