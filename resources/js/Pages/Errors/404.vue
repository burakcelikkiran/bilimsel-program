<!-- resources/js/Pages/Errors/404.vue -->
<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
    <!-- Error Icon -->
    <div class="max-w-md w-full text-center">
      <div class="mx-auto h-32 w-32 text-gray-400 dark:text-gray-600 mb-8">
        <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>

      <!-- Error Content -->
      <div class="mb-8">
        <h1 class="text-6xl font-bold text-gray-900 dark:text-white mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
          Sayfa Bulunamadı
        </h2>
        <p class="text-gray-500 dark:text-gray-400 text-lg mb-8">
          Aradığınız sayfa mevcut değil veya taşınmış olabilir.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button
          @click="goBack"
          class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
        >
          <ArrowLeftIcon class="w-5 h-5 mr-2" />
          Geri Dön
        </button>
        
        <Link
          :href="route('admin.dashboard')"
          class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <HomeIcon class="w-5 h-5 mr-2" />
          Ana Sayfaya Dön
        </Link>
      </div>

      <!-- Alternative Links -->
      <div class="mt-12">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          Ya da şu sayfaları ziyaret edebilirsiniz:
        </p>
        <div class="flex flex-wrap justify-center gap-4">
          <Link
            :href="route('admin.events.index')"
            class="text-blue-600 hover:text-blue-500 dark:text-blue-400 text-sm font-medium"
          >
            Etkinlikler
          </Link>
          <Link
            :href="route('admin.organizations.index')"
            class="text-blue-600 hover:text-blue-500 dark:text-blue-400 text-sm font-medium"
          >
            Organizasyonlar
          </Link>
          <Link
            :href="route('admin.participants.index')"
            class="text-blue-600 hover:text-blue-500 dark:text-blue-400 text-sm font-medium"
          >
            Katılımcılar
          </Link>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-16 text-center">
      <p class="text-xs text-gray-400 dark:text-gray-600">
        Hata Kodu: 404 | Etkinlik Programı Sistemi
      </p>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ArrowLeftIcon, HomeIcon } from '@heroicons/vue/24/outline'

// Go back function
const goBack = () => {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    // Fallback to dashboard if no history
    window.location.href = route('admin.dashboard')
  }
}
</script>

<style scoped>
/* Custom animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.max-w-md {
  animation: fadeIn 0.6s ease-out;
}

/* Hover effects */
.text-blue-600:hover {
  transition: color 0.2s ease;
}

button:hover,
.bg-blue-600:hover {
  transition: all 0.2s ease;
}
</style>