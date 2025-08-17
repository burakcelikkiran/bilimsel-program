<template>
    <AdminLayout
        page-title="Yeni Bilimsel Etkinlik"
        page-subtitle="Akademik etkinlik oluşturun ve programlayın"
        :breadcrumbs="breadcrumbs"
    >
        <Head title="Yeni Etkinlik" />

        <div
            class="min-h-screen bg-gray-50 dark:bg-gray-900 -mx-8 -my-6 px-4 py-3"
        >
            <div class="max-w-7xl mx-auto">
                <!-- Corporate Header Section -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6"
                >
                    <div class="relative">
                        <!-- Professional Corporate Header -->
                        <div class="bg-gray-800 dark:bg-gray-900 px-6 py-6">
                            <div class="flex items-center space-x-8">
                                <div class="flex-shrink-0">
                                    <div
                                        class="h-12 w-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center ring-2 ring-white/30 shadow-lg"
                                    >
                                        <CalendarIcon
                                            class="h-6 w-6 text-white"
                                        />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h1
                                        class="text-2xl font-bold text-white mb-2"
                                    >
                                        Bilimsel Etkinlik Oluştur
                                    </h1>
                                    <p
                                        class="text-gray-300 text-sm font-medium"
                                    >
                                        Kongre, sempozyum veya konferans
                                        programınızı profesyonelce organize edin
                                    </p>
                                </div>
                            </div>

                            <!-- Professional Progress -->
                            <div class="mt-4 flex items-center space-x-3">
                                <div
                                    class="h-2 w-2 bg-gray-400 rounded-full animate-pulse shadow-sm"
                                ></div>
                                <span class="text-gray-300 text-sm font-medium"
                                    >Temel bilgiler aşaması</span
                                >
                            </div>
                        </div>

                        <!-- Corporate Border -->
                        <div class="h-1 bg-gray-600 dark:bg-gray-700"></div>
                    </div>

                    <!-- Main Form Section -->
                    <form @submit.prevent="submitForm" class="p-6 space-y-8">
                        <!-- Event Information Section -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-4 mb-6">
                                <div
                                    class="h-8 w-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center"
                                >
                                    <DocumentTextIcon
                                        class="h-4 w-4 text-gray-600 dark:text-gray-300"
                                    />
                                </div>
                                <h3
                                    class="text-lg font-bold text-gray-900 dark:text-white"
                                >
                                    Etkinlik Bilgileri
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Event Name -->
                                <div class="lg:col-span-2">
                                    <label
                                        for="title"
                                        class="block text-sm font-bold text-gray-900 dark:text-white mb-2"
                                    >
                                        Etkinlik Adı *
                                        <span
                                            class="text-xs font-normal text-gray-600 dark:text-gray-400 ml-2"
                                            >(Resmi kongre/konferans adı)</span
                                        >
                                    </label>
                                    <input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        required
                                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-gray-500/30 focus:border-gray-500 transition-all duration-200 text-sm"
                                        :class="
                                            errors.title
                                                ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                : ''
                                        "
                                        placeholder="Örn: 25. Ulusal Pediatri Kongresi"
                                    />
                                    <p
                                        v-if="errors.title"
                                        class="mt-3 text-base text-red-600 flex items-center"
                                    >
                                        <ExclamationCircleIcon
                                            class="h-5 w-5 mr-2"
                                        />
                                        {{ errors.title }}
                                    </p>
                                </div>

                                <!-- Organization -->
                                <div class="lg:col-span-1">
                                    <label
                                        for="organization_id"
                                        class="block text-sm font-bold text-gray-900 dark:text-white mb-2"
                                    >
                                        Organizasyon *
                                        <span
                                            class="text-xs font-normal text-gray-600 dark:text-gray-400 ml-2"
                                            >(Düzenleyen kurum)</span
                                        >
                                    </label>
                                    <select
                                        id="organization_id"
                                        v-model="form.organization_id"
                                        required
                                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500/30 focus:border-gray-500 transition-all duration-200 text-sm"
                                        :class="
                                            errors.organization_id
                                                ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                : ''
                                        "
                                    >
                                        <option value="">
                                            Organizasyon Seçiniz
                                        </option>
                                        <option
                                            v-for="org in organizations"
                                            :key="org.id"
                                            :value="org.id"
                                        >
                                            {{ org.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.organization_id"
                                        class="mt-3 text-base text-red-600 flex items-center"
                                    >
                                        <ExclamationCircleIcon
                                            class="h-5 w-5 mr-2"
                                        />
                                        {{ errors.organization_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Date and Location Section -->
                        <div
                            class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700"
                        >
                            <div class="flex items-center space-x-4 mb-6">
                                <div
                                    class="h-8 w-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center"
                                >
                                    <ClockIcon
                                        class="h-4 w-4 text-gray-600 dark:text-gray-300"
                                    />
                                </div>
                                <h3
                                    class="text-lg font-bold text-gray-900 dark:text-white"
                                >
                                    Tarih ve Konum
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                                <!-- Start Date -->
                                <div class="lg:col-span-1">
                                    <label
                                        for="start_date"
                                        class="block text-sm font-bold text-gray-900 dark:text-white mb-2"
                                    >
                                        Başlangıç Tarihi *
                                    </label>
                                    <div class="relative">
                                        <CalendarDaysIcon
                                            class="absolute left-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-500"
                                        />
                                        <input
                                            id="start_date"
                                            v-model="form.start_date"
                                            type="date"
                                            required
                                            class="block w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500/30 focus:border-gray-500 transition-all duration-200 text-sm"
                                            :class="
                                                errors.start_date
                                                    ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                    : ''
                                            "
                                            :min="minDate"
                                        />
                                    </div>
                                    <p
                                        v-if="errors.start_date"
                                        class="mt-3 text-base text-red-600"
                                    >
                                        {{ errors.start_date }}
                                    </p>
                                </div>

                                <!-- End Date -->
                                <div class="lg:col-span-1">
                                    <label
                                        for="end_date"
                                        class="block text-sm font-bold text-gray-900 dark:text-white mb-2"
                                    >
                                        Bitiş Tarihi *
                                    </label>
                                    <div class="relative">
                                        <CalendarDaysIcon
                                            class="absolute left-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-500"
                                        />
                                        <input
                                            id="end_date"
                                            v-model="form.end_date"
                                            type="date"
                                            required
                                            class="block w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500/30 focus:border-gray-500 transition-all duration-200 text-sm"
                                            :class="
                                                errors.end_date
                                                    ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                    : ''
                                            "
                                            :min="form.start_date || minDate"
                                        />
                                    </div>
                                    <p
                                        v-if="errors.end_date"
                                        class="mt-3 text-base text-red-600"
                                    >
                                        {{ errors.end_date }}
                                    </p>
                                </div>

                                <!-- Duration Display -->
                                <div
                                    v-if="form.start_date && form.end_date"
                                    class="lg:col-span-1"
                                >
                                    <label
                                        class="block text-sm font-bold text-gray-900 dark:text-white mb-2"
                                    >
                                        Süre
                                    </label>
                                    <div
                                        class="px-3 py-2 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg"
                                    >
                                        <div
                                            class="flex items-center justify-center text-gray-800 dark:text-gray-100"
                                        >
                                            <span class="text-xl font-bold">{{
                                                calculateDuration(
                                                    form.start_date,
                                                    form.end_date
                                                )
                                            }}</span>
                                            <span class="text-sm font-bold ml-2"
                                                >gün</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="lg:col-span-2">
                                    <label
                                        for="location"
                                        class="block text-sm font-bold text-gray-900 dark:text-white mb-2"
                                    >
                                        Etkinlik Konumu
                                    </label>
                                    <div class="relative">
                                        <MapPinIcon
                                            class="absolute left-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-500"
                                        />
                                        <input
                                            id="location"
                                            v-model="form.location"
                                            type="text"
                                            class="block w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-gray-500/30 focus:border-gray-500 transition-all duration-200 text-sm"
                                            :class="
                                                errors.location
                                                    ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                    : ''
                                            "
                                            placeholder="Örn: Antalya Convention Center"
                                        />
                                    </div>
                                    <p
                                        v-if="errors.location"
                                        class="mt-3 text-base text-red-600"
                                    >
                                        {{ errors.location }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <div
                            class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700"
                        >
                            <div class="flex items-center space-x-4 mb-6">
                                <div
                                    class="h-8 w-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center"
                                >
                                    <DocumentTextIcon
                                        class="h-4 w-4 text-gray-600 dark:text-gray-300"
                                    />
                                </div>
                                <h3
                                    class="text-lg font-bold text-gray-900 dark:text-white"
                                >
                                    Akademik İçerik
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                                <!-- Description -->
                                <div class="lg:col-span-3">
                                    <label
                                        for="description"
                                        class="block text-sm font-bold text-gray-900 dark:text-white mb-2"
                                    >
                                        Etkinlik Açıklaması
                                        <span
                                            class="text-xs font-normal text-gray-600 dark:text-gray-400 ml-2"
                                            >(Bilimsel amaç, hedef kitle ve
                                            program hakkında)</span
                                        >
                                    </label>
                                    <div class="relative">
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="4"
                                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-gray-500/30 focus:border-gray-500 transition-all duration-200 resize-none text-sm leading-relaxed"
                                            :class="
                                                errors.description
                                                    ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                    : ''
                                            "
                                            placeholder="Bu etkinlik hakkında detaylı bilgi verin. Bilimsel amaç, hedef kitle, ana konular ve beklenen katılımcı profili gibi akademik bilgileri içerebilir..."
                                            maxlength="2000"
                                        ></textarea>
                                        <div
                                            class="absolute bottom-4 right-4 flex items-center space-x-2"
                                        >
                                            <span
                                                class="text-base text-gray-500 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg font-semibold"
                                                >{{
                                                    (form.description || "")
                                                        .length
                                                }}/2000</span
                                            >
                                        </div>
                                    </div>
                                    <p
                                        v-if="errors.description"
                                        class="mt-3 text-base text-red-600"
                                    >
                                        {{ errors.description }}
                                    </p>
                                </div>

                                <!-- Settings Panel -->
                                <div class="lg:col-span-1">
                                    <label
                                        class="block text-sm font-bold text-gray-900 dark:text-white mb-2"
                                    >
                                        Program Ayarları
                                    </label>
                                    <div
                                        class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 border border-gray-300 dark:border-gray-600"
                                    >
                                        <h4
                                            class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center"
                                        >
                                            <Cog6ToothIcon
                                                class="h-4 w-4 mr-2 text-gray-600 dark:text-gray-400"
                                            />
                                            Otomatik İşlemler
                                        </h4>
                                        <div class="space-y-3">
                                            <label
                                                class="flex items-start space-x-3 cursor-pointer"
                                            >
                                                <input
                                                    v-model="
                                                        form.auto_create_days
                                                    "
                                                    type="checkbox"
                                                    class="mt-0.5 h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded transition-colors"
                                                />
                                                <div>
                                                    <span
                                                        class="text-sm font-bold text-gray-900 dark:text-white"
                                                    >
                                                        Günleri otomatik oluştur
                                                    </span>
                                                    <p
                                                        class="text-xs text-gray-700 dark:text-gray-300 mt-1 leading-relaxed"
                                                    >
                                                        Belirlenen tarih
                                                        aralığında her gün için
                                                        otomatik program günleri
                                                        oluşturulur
                                                    </p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div
                            class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700"
                        >
                            <Link
                                :href="route('admin.events.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500/30 transition-all duration-200"
                            >
                                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                                Vazgeç
                            </Link>

                            <div class="flex space-x-3">
                                <!-- Save as Draft -->
                                <button
                                    type="button"
                                    @click="submitForm(false)"
                                    :disabled="processing"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500/30 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <DocumentIcon class="h-4 w-4 mr-2" />
                                    Taslak Kaydet
                                </button>

                                <!-- Create Event -->
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="inline-flex items-center px-6 py-2 bg-gray-800 hover:bg-gray-900 dark:bg-gray-600 dark:hover:bg-gray-700 text-white text-sm font-medium rounded-lg focus:ring-2 focus:ring-gray-500/30 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <template v-if="processing">
                                        <div
                                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
                                        ></div>
                                        Oluşturuluyor...
                                    </template>
                                    <template v-else>
                                        <PlusIcon class="h-4 w-4 mr-2" />
                                        Bilimsel Etkinlik Oluştur
                                    </template>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Professional Tips Section -->
                <div
                    class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6 border border-gray-300 dark:border-gray-600"
                >
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div
                                class="h-8 w-8 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center"
                            >
                                <InformationCircleIcon
                                    class="h-4 w-4 text-gray-700 dark:text-gray-300"
                                />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4
                                class="text-lg font-bold text-gray-900 dark:text-white mb-4"
                            >
                                Akademik Etkinlik Oluşturma Rehberi
                            </h4>
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-800 dark:text-gray-300"
                            >
                                <div class="space-y-3">
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="h-2 w-2 bg-gray-600 rounded-full mt-1.5 flex-shrink-0"
                                        ></div>
                                        <span
                                            ><strong>Resmi Ad:</strong>
                                            Kongre/konferans tam adını
                                            kullanın</span
                                        >
                                    </div>
                                    <div class="flex items-start space-x-4">
                                        <div
                                            class="h-3 w-3 bg-gray-600 rounded-full mt-3 flex-shrink-0"
                                        ></div>
                                        <span
                                            ><strong>Organizasyon:</strong> Ana
                                            düzenleyen kurumu seçin</span
                                        >
                                    </div>
                                    <div class="flex items-start space-x-4">
                                        <div
                                            class="h-3 w-3 bg-gray-600 rounded-full mt-3 flex-shrink-0"
                                        ></div>
                                        <span
                                            ><strong>Tarih Seçimi:</strong>
                                            Resmi etkinlik tarihlerini
                                            girin</span
                                        >
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="h-2 w-2 bg-gray-600 rounded-full mt-1.5 flex-shrink-0"
                                        ></div>
                                        <span
                                            ><strong>Açıklama:</strong> Bilimsel
                                            amaç ve hedefleri belirtin</span
                                        >
                                    </div>
                                    <div class="flex items-start space-x-4">
                                        <div
                                            class="h-3 w-3 bg-gray-600 rounded-full mt-3 flex-shrink-0"
                                        ></div>
                                        <span
                                            ><strong>Konum:</strong> Detaylı
                                            adres bilgisi ekleyin</span
                                        >
                                    </div>
                                    <div class="flex items-start space-x-4">
                                        <div
                                            class="h-3 w-3 bg-gray-600 rounded-full mt-3 flex-shrink-0"
                                        ></div>
                                        <span
                                            ><strong>Otomatik Gün:</strong>
                                            Program günlerini hızlı
                                            oluştur</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    CalendarIcon,
    CalendarDaysIcon,
    MapPinIcon,
    PlusIcon,
    ArrowLeftIcon,
    DocumentIcon,
    InformationCircleIcon,
    ClockIcon,
    DocumentTextIcon,
    ExclamationCircleIcon,
    Cog6ToothIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    organizations: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

// Form
const form = useForm({
    title: "",
    description: "",
    start_date: "",
    end_date: "",
    location: "",
    organization_id: "",
    auto_create_days: true,
});

const processing = ref(false);

// Computed
const breadcrumbs = computed(() => [
    { label: "Ana Sayfa", href: route("admin.dashboard") },
    { label: "Etkinlikler", href: route("admin.events.index") },
    { label: "Yeni Etkinlik", href: null },
]);

const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split("T")[0];
});

// Methods
const calculateDuration = (startDate, endDate) => {
    if (!startDate || !endDate) return 0;
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays + 1;
};

const submitForm = (publish = true) => {
    processing.value = true;

    if (
        !form.title.trim() ||
        !form.organization_id ||
        !form.start_date ||
        !form.end_date
    ) {
        processing.value = false;
        return;
    }

    if (new Date(form.start_date) > new Date(form.end_date)) {
        processing.value = false;
        alert("Bitiş tarihi başlangıç tarihinden önce olamaz.");
        return;
    }

    form.transform((data) => ({
        ...data,
        is_published: false,
    }));

    form.post(route("admin.events.store"), {
        onSuccess: () => {
            processing.value = false;
        },
        onError: (errors) => {
            processing.value = false;
            console.error("Form submission errors:", errors);

            const firstError = Object.values(errors)[0];
            if (firstError && firstError[0]) {
                alert(firstError[0]);
            }
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<style scoped>
/* Enhanced corporate styling */
input:focus,
select:focus,
textarea:focus {
    transform: translateY(-2px);
}

.hover-lift:hover {
    transform: translateY(-3px);
}

input[type="checkbox"]:checked {
    background-color: rgb(37 99 235);
    border-color: rgb(37 99 235);
}

@keyframes corporateSpin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: corporateSpin 1s linear infinite;
}

.shadow-3xl {
    box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.25);
}

button:focus-visible,
input:focus-visible,
select:focus-visible,
textarea:focus-visible {
    outline: 3px solid #3b82f6;
    outline-offset: 3px;
}
</style>
