<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {
  EnvelopeIcon,
  ArrowRightIcon,
  ShieldCheckIcon,
  ClockIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Şifremi Unuttum" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50 dark:from-slate-900 dark:via-blue-950/20 dark:to-indigo-950/30 flex">
        <!-- Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <!-- Subtle geometric patterns -->
            <div class="absolute top-0 left-0 w-full h-full opacity-5">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)" />
                </svg>
            </div>
            
            <!-- Floating elements -->
            <div class="absolute top-1/4 left-1/12 w-80 h-80 bg-blue-400/8 rounded-full blur-3xl animate-float"></div>
            <div class="absolute bottom-1/4 right-1/12 w-96 h-96 bg-indigo-400/6 rounded-full blur-3xl animate-float-delayed"></div>
        </div>

        <!-- Left Side - Information -->
        <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-center px-12 xl:px-16">
            <div class="relative z-10 max-w-lg">
                <!-- Logo -->
                <div class="flex items-center mb-12">
                    <ApplicationLogo class="h-12 w-auto" />
                    <span class="ml-4 text-2xl font-bold text-slate-900 dark:text-white">
                        Etkinlik Programı Sistemi
                    </span>
                </div>

                <!-- Information -->
                <h1 class="text-4xl xl:text-5xl font-bold text-slate-900 dark:text-white mb-6 leading-tight">
                    Şifrenizi
                    <span class="block bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                        Sıfırlayın
                    </span>
                </h1>
                
                <p class="text-xl text-slate-600 dark:text-slate-300 mb-8 leading-relaxed">
                    Endişelenmeyin! E-posta adresinizi girin, size güvenli bir şifre sıfırlama 
                    bağlantısı gönderelim. Hesabınıza kısa sürede tekrar erişebileceksiniz.
                </p>

                <!-- Process Steps -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-4">
                            <EnvelopeIcon class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <span class="text-slate-700 dark:text-slate-300 font-medium">E-posta adresinizi girin</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center mr-4">
                            <ClockIcon class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Sıfırlama bağlantısı alın</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-4">
                            <ShieldCheckIcon class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                        </div>
                        <span class="text-slate-700 dark:text-slate-300 font-medium">Yeni şifrenizi oluşturun</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-12 xl:px-16">
            <div class="mx-auto w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center justify-center mb-8">
                    <ApplicationLogo class="h-10 w-auto" />
                    <span class="ml-3 text-xl font-bold text-slate-900 dark:text-white">
                        EPS
                    </span>
                </div>

                <!-- Form Header -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">
                        Şifrenizi Sıfırlayın
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400">
                        Hesabınıza tekrar erişim sağlamak için e-posta adresinizi girin
                    </p>
                </div>

                <!-- Success Message -->
                <div v-if="status" class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                    <div class="flex items-center">
                        <CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" />
                        <p class="text-sm text-green-600 dark:text-green-400 font-medium">
                            {{ status }}
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6 relative z-10">
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            E-posta Adresi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <EnvelopeIcon class="h-5 w-5 text-slate-400" />
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="username"
                                class="block w-full pl-12 pr-4 py-4 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="hesabiniz@email.com"
                            />
                        </div>
                        <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center py-4 px-6 border border-transparent text-lg font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <span v-if="!form.processing" class="flex items-center">
                            Sıfırlama Bağlantısı Gönder
                            <ArrowRightIcon class="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" />
                        </span>
                        <span v-else class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Gönderiliyor...
                        </span>
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="mt-8 text-center relative z-10">
                    <p class="text-slate-600 dark:text-slate-400">
                        Şifrenizi hatırladınız mı?
                        <Link
                            :href="route('login')"
                            class="font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors underline ml-1 relative z-10 inline-block"
                        >
                            Giriş yapın
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Advanced animations with staggered timing */
@keyframes float {
  0%, 100% { 
    transform: translateY(0px) rotate(0deg);
    opacity: 0.8;
  }
  50% { 
    transform: translateY(-20px) rotate(1deg);
    opacity: 1;
  }
}

@keyframes float-delayed {
  0%, 100% { 
    transform: translateY(0px) rotate(0deg);
    opacity: 0.6;
  }
  50% { 
    transform: translateY(-30px) rotate(-1deg);
    opacity: 0.8;
  }
}

.animate-float {
  animation: float 8s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float-delayed 10s ease-in-out infinite 2s;
}

/* Input focus animations */
input:focus {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
}

/* Button hover effect */
button:hover {
  box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
}
</style>