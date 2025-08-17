// resources/js/Composables/useConfirmDialog.js
// Bu dosyayı C:\nisyan\projects\eps\resources\js\Composables\useConfirmDialog.js konumuna kaydedin
import { ref, reactive, nextTick } from 'vue'

export function useConfirmDialog() {
  // Dialog state
  const isOpen = ref(false)
  const loading = ref(false)
  
  // Dialog configuration
  const config = reactive({
    title: '',
    message: '',
    type: 'warning', // 'info', 'warning', 'danger', 'success'
    confirmText: 'Onayla',
    cancelText: 'İptal',
    confirmVariant: 'primary', // 'primary', 'danger', 'warning', 'success'
    showCancel: true,
    persistent: false, // If true, clicking outside won't close
    loading: false
  })

  // Promise resolver and rejecter
  let resolver = null
  let rejecter = null

  // Dialog types with default configurations
  const dialogTypes = {
    delete: {
      title: 'Silmeyi Onayla',
      type: 'danger',
      confirmText: 'Sil',
      confirmVariant: 'danger'
    },
    save: {
      title: 'Değişiklikleri Kaydet',
      type: 'info',
      confirmText: 'Kaydet',
      confirmVariant: 'primary'
    },
    discard: {
      title: 'Değişiklikleri İptal Et',
      message: 'Kaydedilmemiş değişiklikler kaybolacak. Devam etmek istiyor musunuz?',
      type: 'warning',
      confirmText: 'İptal Et',
      confirmVariant: 'warning'
    },
    logout: {
      title: 'Oturumu Kapat',
      message: 'Oturumunuzu kapatmak istediğinizden emin misiniz?',
      type: 'info',
      confirmText: 'Çıkış Yap',
      confirmVariant: 'primary'
    },
    publish: {
      title: 'Yayınlamayı Onayla',
      type: 'success',
      confirmText: 'Yayınla',
      confirmVariant: 'success'
    },
    unpublish: {
      title: 'Yayından Kaldır',
      type: 'warning',
      confirmText: 'Yayından Kaldır',
      confirmVariant: 'warning'
    },
    archive: {
      title: 'Arşivlemeyi Onayla',
      type: 'warning',
      confirmText: 'Arşivle',
      confirmVariant: 'warning'
    },
    restore: {
      title: 'Geri Yüklemeyi Onayla',
      type: 'info',
      confirmText: 'Geri Yükle',
      confirmVariant: 'primary'
    },
    duplicate: {
      title: 'Kopyalamayı Onayla',
      type: 'info',
      confirmText: 'Kopyala',
      confirmVariant: 'primary'
    },
    reset: {
      title: 'Sıfırlamayı Onayla',
      message: 'Tüm veriler sıfırlanacak ve bu işlem geri alınamaz.',
      type: 'danger',
      confirmText: 'Sıfırla',
      confirmVariant: 'danger'
    }
  }

  // Show confirmation dialog
  const confirm = (options = {}) => {
    return new Promise((resolve, reject) => {
      // Store promise handlers
      resolver = resolve
      rejecter = reject

      // Apply configuration
      if (typeof options === 'string') {
        // If options is a string, treat it as the message
        config.message = options
        config.title = 'Onay Gerekli'
        config.type = 'warning'
        config.confirmText = 'Onayla'
        config.cancelText = 'İptal'
        config.confirmVariant = 'primary'
        config.showCancel = true
        config.persistent = false
      } else {
        // Apply default values
        Object.assign(config, {
          title: 'Onay Gerekli',
          message: '',
          type: 'warning',
          confirmText: 'Onayla',
          cancelText: 'İptal',
          confirmVariant: 'primary',
          showCancel: true,
          persistent: false,
          loading: false
        }, options)
      }

      // Ensure confirmVariant matches type for consistency
      if (!options.confirmVariant) {
        const typeVariantMap = {
          danger: 'danger',
          warning: 'warning',
          success: 'success',
          info: 'primary'
        }
        config.confirmVariant = typeVariantMap[config.type] || 'primary'
      }

      // Open dialog
      isOpen.value = true
    })
  }

  // Predefined confirmation dialogs
  const confirmDelete = (itemName = 'bu öğeyi') => {
    return confirm({
      ...dialogTypes.delete,
      message: `${itemName} silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.`
    })
  }

  const confirmSave = (message = 'Değişiklikleri kaydetmek istiyor musunuz?') => {
    return confirm({
      ...dialogTypes.save,
      message
    })
  }

  const confirmDiscard = () => {
    return confirm(dialogTypes.discard)
  }

  const confirmLogout = () => {
    return confirm(dialogTypes.logout)
  }

  const confirmPublish = (itemName = 'bu öğeyi') => {
    return confirm({
      ...dialogTypes.publish,
      message: `${itemName} yayınlamak istediğinizden emin misiniz?`
    })
  }

  const confirmUnpublish = (itemName = 'bu öğeyi') => {
    return confirm({
      ...dialogTypes.unpublish,
      message: `${itemName} yayından kaldırmak istediğinizden emin misiniz?`
    })
  }

  const confirmArchive = (itemName = 'bu öğeyi') => {
    return confirm({
      ...dialogTypes.archive,
      message: `${itemName} arşivlemek istediğinizden emin misiniz?`
    })
  }

  const confirmRestore = (itemName = 'bu öğeyi') => {
    return confirm({
      ...dialogTypes.restore,
      message: `${itemName} geri yüklemek istediğinizden emin misiniz?`
    })
  }

  const confirmDuplicate = (itemName = 'bu öğeyi') => {
    return confirm({
      ...dialogTypes.duplicate,
      message: `${itemName} kopyalamak istediğinizden emin misiniz?`
    })
  }

  const confirmReset = () => {
    return confirm(dialogTypes.reset)
  }

  // Custom confirmation with async action
  const confirmAsync = async (options, asyncAction) => {
    try {
      await confirm(options)
      
      // Set loading state
      setLoading(true)
      
      // Execute async action
      const result = await asyncAction()
      
      // Close dialog and resolve
      close()
      return result
    } catch (error) {
      // If user cancelled or action failed
      setLoading(false)
      throw error
    }
  }

  // Handle confirmation
  const handleConfirm = () => {
    if (resolver) {
      resolver(true)
      resolver = null
      rejecter = null
    }
    
    if (!config.loading) {
      close()
    }
  }

  // Handle cancellation
  const handleCancel = () => {
    if (rejecter) {
      rejecter(new Error('User cancelled'))
      resolver = null
      rejecter = null
    }
    close()
  }

  // Close dialog
  const close = () => {
    isOpen.value = false
    loading.value = false
    config.loading = false
    
    // Clean up promise handlers
    if (rejecter) {
      rejecter(new Error('Dialog closed'))
      resolver = null
      rejecter = null
    }
  }

  // Set loading state
  const setLoading = (state) => {
    loading.value = state
    config.loading = state
  }

  // Reset configuration
  const reset = () => {
    close()
    Object.assign(config, {
      title: '',
      message: '',
      type: 'warning',
      confirmText: 'Onayla',
      cancelText: 'İptal',
      confirmVariant: 'primary',
      showCancel: true,
      persistent: false,
      loading: false
    })
  }

  // Check if dialog can be closed
  const canClose = () => {
    return !config.persistent && !config.loading
  }

  // Handle backdrop click
  const handleBackdropClick = () => {
    if (canClose()) {
      handleCancel()
    }
  }

  // Utility functions for checking dialog state
  const isConfirmDialog = () => isOpen.value
  const isLoading = () => loading.value || config.loading
  const getDialogType = () => config.type
  const getDialogTitle = () => config.title
  const getDialogMessage = () => config.message

  // Create a composable with multiple confirmation types
  const createConfirmation = (type, options = {}) => {
    const baseConfig = dialogTypes[type]
    if (!baseConfig) {
      console.warn(`Unknown confirmation type: ${type}`)
      return confirm(options)
    }
    
    return confirm({
      ...baseConfig,
      ...options
    })
  }

  // Batch confirmations for multiple items
  const confirmBatch = (items, actionName = 'işlem yapmak') => {
    const itemCount = Array.isArray(items) ? items.length : items
    const message = `Seçili ${itemCount} öğe için ${actionName} istediğinizden emin misiniz?`
    
    return confirm({
      title: 'Toplu İşlem Onayı',
      message,
      type: 'warning',
      confirmText: 'Devam Et',
      confirmVariant: 'warning'
    })
  }

  // Confirm with custom input
  const confirmWithReason = (title, placeholder = 'Sebep belirtiniz...') => {
    return new Promise((resolve, reject) => {
      // This would need a special dialog component with input field
      // For now, we'll use the standard confirm
      confirm({
        title,
        message: 'Bu işlem için bir sebep belirtmeniz gerekmektedir.',
        type: 'info'
      }).then(() => {
        // In a real implementation, you'd collect the reason from an input
        resolve({ confirmed: true, reason: '' })
      }).catch(reject)
    })
  }

  // Return the composable interface
  return {
    // State
    isOpen,
    loading,
    config,

    // Main methods
    confirm,
    close,
    reset,
    setLoading,

    // Event handlers
    handleConfirm,
    handleCancel,
    handleBackdropClick,

    // Predefined confirmations
    confirmDelete,
    confirmSave,
    confirmDiscard,
    confirmLogout,
    confirmPublish,
    confirmUnpublish,
    confirmArchive,
    confirmRestore,
    confirmDuplicate,
    confirmReset,

    // Advanced methods
    confirmAsync,
    confirmBatch,
    confirmWithReason,
    createConfirmation,

    // Utility methods
    canClose,
    isConfirmDialog,
    isLoading,
    getDialogType,
    getDialogTitle,
    getDialogMessage
  }
}