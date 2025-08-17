<!-- resources/js/Pages/Welcome.vue -->
<template>
    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50 dark:from-slate-900 dark:via-blue-950/20 dark:to-indigo-950/30">
        <!-- Navigation -->
        <nav
            class="absolute top-0 w-full z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-lg border-b border-slate-200/50 dark:border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <ApplicationLogo class="h-10 w-auto" />
                        <span class="ml-3 text-lg font-semibold text-slate-800 dark:text-slate-200">
                            Etkinlik Programı Sistemi
                        </span>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#about"
                            class="text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 transition-colors font-medium">
                            Hakkında
                        </a>
                        <a href="#program"
                            class="text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 transition-colors font-medium">
                            Bilimsel Program
                        </a>
                        <a href="#speakers"
                            class="text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 transition-colors font-medium">
                            Konuşmacılar
                        </a>
                        <a href="#info"
                            class="text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 transition-colors font-medium">
                            Bilgiler
                        </a>
                    </div>

                    <!-- Auth Buttons -->
                    <div class="flex items-center space-x-4">
                        <Link v-if="canLogin" :href="route('login')"
                            class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white font-medium transition-colors">
                        Giriş Yap
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all duration-300 font-medium">
                        Kayıt Ol
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-32 overflow-hidden min-h-screen flex items-center">
            <!-- Background Elements -->
            <div class="absolute inset-0">
                <!-- Sophisticated gradient background -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-white via-blue-50/30 to-indigo-50/50 dark:from-slate-900 dark:via-blue-950/20 dark:to-indigo-950/30">
                </div>

                <!-- Subtle geometric patterns -->
                <div class="absolute top-0 left-0 w-full h-full opacity-5">
                    <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <defs>
                            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5" />
                            </pattern>
                        </defs>
                        <rect width="100" height="100" fill="url(#grid)" />
                    </svg>
                </div>

                <!-- Floating elements with better positioning -->
                <div class="absolute top-1/4 left-1/12 w-80 h-80 bg-blue-400/8 rounded-full blur-3xl animate-float">
                </div>
                <div
                    class="absolute bottom-1/4 right-1/12 w-96 h-96 bg-indigo-400/6 rounded-full blur-3xl animate-float-delayed">
                </div>
                <div
                    class="absolute top-2/3 left-1/3 w-64 h-64 bg-emerald-400/4 rounded-full blur-2xl animate-float-slow">
                </div>
            </div>

            <div class="relative w-full max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center max-w-5xl mx-auto">
                    <!-- Event Badge -->
                    <div
                        class="inline-flex items-center px-6 py-3 rounded-full bg-white/80 dark:bg-slate-800/80 border border-slate-200/50 dark:border-slate-700/50 backdrop-blur-lg text-blue-700 dark:text-blue-300 text-sm font-semibold mb-12 shadow-lg">
                        <CalendarDaysIcon class="w-4 h-4 mr-3" />
                        15-17 Mayıs 2025 • İstanbul, Türkiye
                    </div>

                    <!-- Main Heading -->
                    <h1
                        class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold mb-8 leading-[1.1] tracking-tight">
                        <span class="block text-slate-900 dark:text-white mb-3">
                            10. Ulusal
                        </span>
                        <span class="block text-slate-900 dark:text-white mb-3">
                            Pediatri Kongresi
                        </span>
                        <span
                            class="block bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Çocuk Sağlığında
                        </span>
                        <span
                            class="block bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 bg-clip-text text-transparent">
                            Geleceği İnşa Etmek
                        </span>
                    </h1>

                    <!-- Subtitle -->
                    <p
                        class="text-lg lg:text-xl text-slate-600 dark:text-slate-300 mb-12 max-w-3xl mx-auto leading-relaxed font-light">
                        Türk Pediatri Derneği'nin ev sahipliğinde düzenlenen prestijli kongreye katılın.
                        En son bilimsel gelişmeleri, yenilikçi tedavi yaklaşımlarını ve
                        çocuk sağlığının geleceğini şekillendiren önemli tartışmaları keşfedin.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <Link v-if="canRegister" :href="route('register')"
                            class="group relative px-10 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl font-semibold text-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-blue-500/25 transform hover:-translate-y-1">
                        <div class="flex items-center justify-center">
                            <UserPlusIcon class="w-6 h-6 mr-3" />
                            Kongreye Kayıt Ol
                            <ArrowRightIcon class="w-5 h-5 ml-3 group-hover:translate-x-1 transition-transform" />
                        </div>
                        </Link>

                        <a href="#program"
                            class="group px-10 py-5 bg-white/80 dark:bg-slate-800/80 text-slate-900 dark:text-white rounded-2xl font-semibold text-lg border border-slate-200/50 dark:border-slate-700/50 hover:bg-white dark:hover:bg-slate-800 backdrop-blur-lg transition-all duration-300 shadow-lg hover:shadow-xl">
                            <div class="flex items-center justify-center">
                                <DocumentTextIcon class="w-6 h-6 mr-3" />
                                Bilimsel Programı İncele
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Congress Info Section -->
        <section class="py-20 bg-white dark:bg-slate-800">
            <div class="max-w-6xl mx-auto px-6 lg:px-8">
                <!-- Organization Info -->
                <div class="flex flex-col lg:flex-row items-center justify-center gap-8 lg:gap-12 mb-16">
                    <div
                        class="flex items-center gap-4 px-6 py-4 bg-slate-50 dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600">
                        <div
                            class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                            <BuildingOfficeIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white">Türk Pediatri Derneği</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Ana Organizatör</div>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-4 px-6 py-4 bg-slate-50 dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600">
                        <div
                            class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                            <MapPinIcon class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white">Lütfi Kırdar Kongre Merkezi</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">İstanbul, Türkiye</div>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-4 px-6 py-4 bg-slate-50 dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600">
                        <div
                            class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                            <UsersIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white">2000+ Katılımcı</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">Beklenen Katılım</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                    <a href="#"
                        class="group p-8 bg-slate-50 dark:bg-slate-700/50 rounded-2xl hover:bg-white dark:hover:bg-slate-700 transition-all duration-300 border border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 hover:shadow-lg hover:-translate-y-1">
                        <div
                            class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <DocumentTextIcon class="w-7 h-7 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="font-semibold text-slate-900 dark:text-white mb-2">Bildiri Gönderimi</div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">Araştırmanızı sunun</div>
                    </a>

                    <a href="#"
                        class="group p-8 bg-slate-50 dark:bg-slate-700/50 rounded-2xl hover:bg-white dark:hover:bg-slate-700 transition-all duration-300 border border-slate-200 dark:border-slate-600 hover:border-emerald-300 dark:hover:border-emerald-500 hover:shadow-lg hover:-translate-y-1">
                        <div
                            class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <BuildingOffice2Icon class="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div class="font-semibold text-slate-900 dark:text-white mb-2">Konaklama</div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">Otel rezervasyonları</div>
                    </a>

                    <a href="#"
                        class="group p-8 bg-slate-50 dark:bg-slate-700/50 rounded-2xl hover:bg-white dark:hover:bg-slate-700 transition-all duration-300 border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 hover:shadow-lg hover:-translate-y-1">
                        <div
                            class="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <StarIcon class="w-7 h-7 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div class="font-semibold text-slate-900 dark:text-white mb-2">Sponsorluk</div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">Ortaklık fırsatları</div>
                    </a>

                    <a href="#"
                        class="group p-8 bg-slate-50 dark:bg-slate-700/50 rounded-2xl hover:bg-white dark:hover:bg-slate-700 transition-all duration-300 border border-slate-200 dark:border-slate-600 hover:border-orange-300 dark:hover:border-orange-500 hover:shadow-lg hover:-translate-y-1">
                        <div
                            class="w-14 h-14 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <PhoneIcon class="w-7 h-7 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div class="font-semibold text-slate-900 dark:text-white mb-2">İletişim</div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">Destek ve bilgi</div>
                    </a>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-20 bg-white dark:bg-slate-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white mb-6">
                            Kongreye Davet
                        </h2>
                        <div class="prose prose-lg text-slate-600 dark:text-slate-300">
                            <p class="mb-4">
                                Değerli Meslektaşlarımız,
                            </p>
                            <p class="mb-4">
                                Türk Pediatri Derneği olarak, pediatri alanındaki en son bilimsel gelişmeleri,
                                yenilikçi tedavi yaklaşımlarını ve çocuk sağlığının geleceğini şekillendiren
                                önemli konuları ele alacağımız <strong>10. Ulusal Pediatri Kongresi</strong>'ni
                                15-17 Mayıs 2025 tarihleri arasında İstanbul'da düzenlemenin gururunu yaşıyoruz.
                            </p>
                            <p class="mb-4">
                                Bu yılki kongre teması <em>"Çocuk Sağlığında Geleceği İnşa Etmek"</em>
                                olarak belirlenmiştir. Kongreye; pediatri alanında çalışan hekimler,
                                hemşireler, diyetisyenler, fizyoterapistler ve diğer sağlık profesyonelleri davetlidir.
                            </p>
                            <p>
                                Sizleri bu önemli bilimsel etkinlikte aramızda görmekten büyük mutluluk duyacağız.
                            </p>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-8 text-white">
                            <h3 class="text-2xl font-bold mb-6">Kongre Başkanları</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="font-semibold">Prof. Dr. Mehmet Nevzat Elgörmüş</div>
                                    <div class="text-blue-100">Kongre Başkanı</div>
                                    <div class="text-sm text-blue-100">İstanbul Üniversitesi, Pediatri Anabilim Dalı
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold">Prof. Dr. Ayşe Kılıçaslan Ayna</div>
                                    <div class="text-blue-100">Bilimsel Kurul Başkanı</div>
                                    <div class="text-sm text-blue-100">Ankara Üniversitesi, Pediatri Anabilim Dalı</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Program Highlights -->
        <section id="program" class="py-20 bg-slate-50 dark:bg-slate-900">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white mb-6">
                        Bilimsel Program Öne Çıkanları
                    </h2>
                    <p class="text-xl text-slate-600 dark:text-slate-300 max-w-3xl mx-auto">
                        3 gün boyunca 150+ konuşma, panel ve workshop ile çocuk sağlığının her alanını kapsıyoruz
                    </p>
                </div>

                <!-- Daily Program Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <!-- Day 1 -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="text-center mb-6">
                            <div
                                class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-semibold mb-4">
                                15 Mayıs • Perşembe
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Açılış Günü</h3>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
                            <li class="flex items-start">
                                <ClockIcon class="w-4 h-4 mr-2 mt-0.5 text-blue-500" />
                                <div>
                                    <div class="font-medium">09:00 - 10:00</div>
                                    <div>Açılış Töreni ve Anahtar Konuşma</div>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <ClockIcon class="w-4 h-4 mr-2 mt-0.5 text-blue-500" />
                                <div>
                                    <div class="font-medium">10:30 - 12:00</div>
                                    <div>Çocuk Nörolojisinde Yeni Yaklaşımlar</div>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <ClockIcon class="w-4 h-4 mr-2 mt-0.5 text-blue-500" />
                                <div>
                                    <div class="font-medium">14:00 - 16:00</div>
                                    <div>Pediatrik Onkoloji Güncellemeleri</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Day 2 -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="text-center mb-6">
                            <div
                                class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm font-semibold mb-4">
                                16 Mayıs • Cuma
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Ana Program</h3>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
                            <li class="flex items-start">
                                <ClockIcon class="w-4 h-4 mr-2 mt-0.5 text-emerald-500" />
                                <div>
                                    <div class="font-medium">09:00 - 10:30</div>
                                    <div>Sözlü Bildiri Oturumları</div>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <ClockIcon class="w-4 h-4 mr-2 mt-0.5 text-emerald-500" />
                                <div>
                                    <div class="font-medium">11:00 - 12:30</div>
                                    <div>Çocuk Kardiyolojisi Paneli</div>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <ClockIcon class="w-4 h-4 mr-2 mt-0.5 text-emerald-500" />
                                <div>
                                    <div class="font-medium">14:00 - 18:00</div>
                                    <div>Workshop Oturumları</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Day 3 -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="text-center mb-6">
                            <div
                                class="inline-flex items-center px-3 py-1 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-sm font-semibold mb-4">
                                17 Mayıs • Cumartesi
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Kapanış Günü</h3>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
                            <li class="flex items-start">
                                <ClockIcon class="w-4 h-4 mr-2 mt-0.5 text-purple-500" />
                                <div>
                                    <div class="font-medium">09:00 - 11:00</div>
                                    <div>En İyi Bildiri Sunumları</div>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <ClockIcon class="w-4 h-4 mr-2 mt-0.5 text-purple-500" />
                                <div>
                                    <div class="font-medium">11:30 - 13:00</div>
                                    <div>Poster Oturumları</div>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <ClockIcon class="w-4 h-4 mr-2 mt-0.5 text-purple-500" />
                                <div>
                                    <div class="font-medium">14:00 - 15:00</div>
                                    <div>Kapanış Töreni ve Ödül Töreni</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Program Download -->
                <div class="text-center">
                    <a href="#"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-colors">
                        <DocumentArrowDownIcon class="w-5 h-5 mr-2" />
                        Detaylı Bilimsel Programı İndir (PDF)
                    </a>
                </div>
            </div>
        </section>

        <!-- Speakers Section -->
        <section id="speakers" class="py-20 bg-white dark:bg-slate-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white mb-6">
                        Davetli Konuşmacılar
                    </h2>
                    <p class="text-xl text-slate-600 dark:text-slate-300 max-w-3xl mx-auto">
                        Ulusal ve uluslararası alanda tanınmış uzmanlardan öğrenme fırsatı
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Speaker 1 -->
                    <div class="text-center group">
                        <div
                            class="w-32 h-32 mx-auto mb-4 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center">
                            <UserIcon class="w-16 h-16 text-white" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">
                            Prof. Dr. Sarah Johnson
                        </h3>
                        <p class="text-blue-600 dark:text-blue-400 font-medium mb-2">
                            Harvard Medical School
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Pediatrik Kardiyoloji
                        </p>
                    </div>

                    <!-- Speaker 2 -->
                    <div class="text-center group">
                        <div
                            class="w-32 h-32 mx-auto mb-4 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center">
                            <UserIcon class="w-16 h-16 text-white" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">
                            Prof. Dr. Michael Chen
                        </h3>
                        <p class="text-blue-600 dark:text-blue-400 font-medium mb-2">
                            Johns Hopkins University
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Pediatrik Onkoloji
                        </p>
                    </div>

                    <!-- Speaker 3 -->
                    <div class="text-center group">
                        <div
                            class="w-32 h-32 mx-auto mb-4 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center">
                            <UserIcon class="w-16 h-16 text-white" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">
                            Prof. Dr. Ayşe Demir
                        </h3>
                        <p class="text-blue-600 dark:text-blue-400 font-medium mb-2">
                            İstanbul Üniversitesi
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Çocuk Nörolojisi
                        </p>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <a href="#" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">
                        Tüm konuşmacıları görüntüle →
                    </a>
                </div>
            </div>
        </section>

        <!-- Important Info Section -->
        <section id="info" class="py-20 bg-slate-50 dark:bg-slate-900">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white mb-6">
                        Önemli Bilgiler
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Registration Info -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center mb-6">
                            <div
                                class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-4">
                                <UserPlusIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Kayıt Bilgileri</h3>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-300">Erken Kayıt:</span>
                                <span class="font-semibold text-slate-900 dark:text-white">₺750</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-300">Normal Kayıt:</span>
                                <span class="font-semibold text-slate-900 dark:text-white">₺950</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-300">Öğrenci Kayıt:</span>
                                <span class="font-semibold text-slate-900 dark:text-white">₺400</span>
                            </div>
                            <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    <strong>Son Kayıt Tarihi:</strong> 10 Mayıs 2025
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center mb-6">
                            <div
                                class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center mr-4">
                                <PhoneIcon class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">İletişim</h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <div class="font-medium text-slate-900 dark:text-white">Kongre Sekreterliği</div>
                                <div class="text-slate-600 dark:text-slate-300">Med-Kongre Organizasyon</div>
                            </div>
                            <div>
                                <div class="font-medium text-slate-900 dark:text-white">Telefon</div>
                                <div class="text-slate-600 dark:text-slate-300">+90 212 123 45 67</div>
                            </div>
                            <div>
                                <div class="font-medium text-slate-900 dark:text-white">E-posta</div>
                                <div class="text-slate-600 dark:text-slate-300">info@pediatrikongre2025.org</div>
                            </div>
                            <div>
                                <div class="font-medium text-slate-900 dark:text-white">Web</div>
                                <div class="text-blue-600 dark:text-blue-400">www.pediatrikongre2025.org</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 dark:bg-black text-white py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Organization Info -->
                    <div>
                        <div class="flex items-center mb-4">
                            <ApplicationLogo variant="white" class="h-8 w-auto mr-3" />
                            <span class="text-lg font-semibold">Etkinlik Programı Sistemi</span>
                        </div>
                        <p class="text-slate-300 text-sm leading-relaxed">
                            Bilimsel kongre ve etkinliklerin profesyonel yönetimi için
                            geliştirilmiş modern platform.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="font-semibold text-lg mb-4">Hızlı Bağlantılar</h3>
                        <ul class="space-y-2 text-sm text-slate-300">
                            <li><a href="#about" class="hover:text-white transition-colors">Hakkında</a></li>
                            <li><a href="#program" class="hover:text-white transition-colors">Bilimsel Program</a></li>
                            <li><a href="#speakers" class="hover:text-white transition-colors">Konuşmacılar</a></li>
                            <li><a href="#info" class="hover:text-white transition-colors">Kayıt Bilgileri</a></li>
                        </ul>
                    </div>

                    <!-- Organizer -->
                    <div>
                        <h3 class="font-semibold text-lg mb-4">Düzenleyen</h3>
                        <div class="text-sm text-slate-300">
                            <div class="font-medium text-white mb-2">Türk Pediatri Derneği</div>
                            <div class="mb-1">Valikonağı Caddesi No: 123</div>
                            <div class="mb-1">Nişantaşı, İstanbul</div>
                            <div class="mb-1">Tel: +90 212 123 45 67</div>
                            <div>E-posta: info@turkpediatri.org</div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-800 mt-8 pt-8 text-center">
                    <p class="text-slate-400 text-sm">
                        © {{ new Date().getFullYear() }} 10. Ulusal Pediatri Kongresi. Tüm hakları saklıdır.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import {
    CalendarDaysIcon,
    BuildingOfficeIcon,
    MapPinIcon,
    UsersIcon,
    UserPlusIcon,
    ArrowRightIcon,
    DocumentTextIcon,
    ClockIcon,
    DocumentArrowDownIcon,
    UserIcon,
    PhoneIcon,
    BuildingOffice2Icon,
    StarIcon
} from '@heroicons/vue/24/outline'

defineProps({
    canLogin: Boolean,
    canRegister: Boolean
})
</script>

<style scoped>
/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Advanced animations with staggered timing */
@keyframes float {

    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
        opacity: 0.8;
    }

    50% {
        transform: translateY(-20px) rotate(1deg);
        opacity: 1;
    }
}

@keyframes float-delayed {

    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
        opacity: 0.6;
    }

    50% {
        transform: translateY(-30px) rotate(-1deg);
        opacity: 0.8;
    }
}

@keyframes float-slow {

    0%,
    100% {
        transform: translateY(0px) scale(1);
        opacity: 0.4;
    }

    50% {
        transform: translateY(-15px) scale(1.05);
        opacity: 0.6;
    }
}

.animate-float {
    animation: float 8s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float-delayed 10s ease-in-out infinite 2s;
}

.animate-float-slow {
    animation: float-slow 12s ease-in-out infinite 4s;
}

/* Enhanced backdrop blur support */
@supports (backdrop-filter: blur(20px)) {
    .backdrop-blur-lg {
        backdrop-filter: blur(20px);
    }

    .backdrop-blur-sm {
        backdrop-filter: blur(8px);
    }
}

/* Premium hover effects */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Text gradient animation */
@keyframes gradient-shift {

    0%,
    100% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }
}

.bg-gradient-to-r {
    background-size: 200% 200%;
    animation: gradient-shift 6s ease infinite;
}
</style>