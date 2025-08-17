<!-- resources/js/Components/Layout/Footer.vue -->
<template>
  <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <!-- Left Side - Copyright -->
        <div class="flex items-center space-x-4">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            © {{ currentYear }} {{ appName }}. Tüm hakları saklıdır.
          </div>
        </div>

        <!-- Center - Links -->
        <div class="flex items-center space-x-6">
          <a href="#" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            Gizlilik Politikası
          </a>
          <a href="#" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            Kullanım Şartları
          </a>
          <a href="#" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            Destek
          </a>
        </div>

        <!-- Right Side - Version -->
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <span>v{{ version }}</span>
            <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
            <div class="flex items-center space-x-1">
              <div class="w-2 h-2 bg-green-500 rounded-full"></div>
              <span>Sistem Normal</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  appName: {
    type: String,
    default: 'Etkinlik Yönetim Sistemi'
  },
  version: {
    type: String,
    default: '1.0.0'
  }
})

const currentYear = computed(() => new Date().getFullYear())
</script>