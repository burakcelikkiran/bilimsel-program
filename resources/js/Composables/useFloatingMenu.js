import { ref, watch, onUnmounted, nextTick } from 'vue'
import { Z_INDEX } from '@/Support/zIndex'

export function useFloatingMenu(options = {}) {
    const menuWidth = options.menuWidth ?? 192
    const align = options.align ?? 'right'

    const isOpen = ref(false)
    const triggerRef = ref(null)
    const menuStyle = ref({})

    let scrollListener = null

    const bindTrigger = (element) => {
        triggerRef.value = element
    }

    const updatePosition = () => {
        if (!triggerRef.value) {
            return
        }

        const rect = triggerRef.value.getBoundingClientRect()
        const left = align === 'right'
            ? Math.max(8, rect.right - menuWidth)
            : Math.max(8, rect.left)

        menuStyle.value = {
            top: `${rect.bottom + 8}px`,
            left: `${left}px`,
            zIndex: Z_INDEX.dropdown,
        }
    }

    const attachListeners = () => {
        scrollListener = () => updatePosition()
        window.addEventListener('resize', scrollListener)
        window.addEventListener('scroll', scrollListener, true)
    }

    const detachListeners = () => {
        if (!scrollListener) {
            return
        }

        window.removeEventListener('resize', scrollListener)
        window.removeEventListener('scroll', scrollListener, true)
        scrollListener = null
    }

    const open = async () => {
        isOpen.value = true
        await nextTick()
        updatePosition()
    }

    const close = () => {
        isOpen.value = false
    }

    const toggle = async () => {
        if (isOpen.value) {
            close()
        } else {
            await open()
        }
    }

    watch(isOpen, (open) => {
        if (open) {
            attachListeners()
        } else {
            detachListeners()
        }
    })

    onUnmounted(() => {
        detachListeners()
    })

    return {
        isOpen,
        menuStyle,
        bindTrigger,
        open,
        close,
        toggle,
        updatePosition,
    }
}
