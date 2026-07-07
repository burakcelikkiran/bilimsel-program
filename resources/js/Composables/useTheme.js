import { ref } from 'vue'

const STORAGE_KEY = 'admin-theme'

const isDark = ref(
    typeof window !== 'undefined' && localStorage.getItem(STORAGE_KEY) === 'dark',
)

export function useTheme() {
    const toggleTheme = () => {
        isDark.value = !isDark.value
        localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light')
    }

    return {
        isDark,
        toggleTheme,
    }
}
