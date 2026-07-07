<template>
    <AdminLayout
        :page-title="pageTitle"
        page-subtitle="Akademik etkinlik bilgilerini güncelleyin"
        :breadcrumbs="breadcrumbs"
    >
        <Head :title="pageTitle" />

        <div
            class="min-h-screen bg-slate-50 dark:bg-slate-950 -mx-8 -my-6 px-4 py-3"
        >
            <div class="max-w-5xl mx-auto">
                <!-- Corporate Header Section -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden mb-6"
                >
                    <!-- Professional Corporate Header with Status -->
                    <div class="relative">
                        <div class="bg-slate-800 dark:bg-slate-900 px-6 py-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="h-12 w-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center ring-2 ring-white/30 shadow-lg"
                                        >
                                            <PencilSquareIcon
                                                class="h-6 w-6 text-white"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h1
                                            class="text-2xl font-bold text-white mb-2"
                                        >
                                            Bilimsel Etkinlik Düzenle
                                        </h1>
                                        <p
                                            class="text-slate-300 text-sm font-medium"
                                        >
                                            {{ eventName }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Enhanced Corporate Status Badge -->
                                <div class="flex items-center space-x-3">
                                    <div class="text-right">
                                        <div
                                            class="text-xs text-slate-300 mb-1 font-medium"
                                        >
                                            Durum
                                        </div>
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium"
                                            :class="
                                                isPublished
                                                    ? 'bg-green-500/20 text-green-200 ring-1 ring-green-500/40'
                                                    : 'bg-orange-500/20 text-orange-200 ring-1 ring-orange-500/40'
                                            "
                                        >
                                            <span
                                                class="w-2 h-2 mr-2 rounded-full bg-current animate-pulse"
                                            ></span>
                                            {{
                                                isPublished
                                                    ? "Yayında"
                                                    : "Taslak"
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Progress -->
                            <div class="mt-4 flex items-center space-x-3">
                                <div
                                    class="h-2 w-2 bg-slate-400 rounded-full animate-pulse shadow-sm"
                                ></div>
                                <span class="text-slate-300 text-sm font-medium"
                                    >Bilgileri düzenleme aşaması</span
                                >
                            </div>
                        </div>

                        <!-- Corporate Border -->
                        <div class="h-1 bg-slate-600 dark:bg-slate-700"></div>
                    </div>

                    <!-- Main Form Section -->
                    <form @submit.prevent="submitForm" class="p-6 space-y-8">
                        <div
                            v-if="form.errors.error"
                            class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-700 dark:bg-red-950/40 dark:text-red-300"
                        >
                            {{ form.errors.error }}
                        </div>

                        <!-- Event Information Section -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-4 mb-6">
                                <div
                                    class="h-8 w-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center"
                                >
                                    <DocumentTextIcon
                                        class="h-4 w-4 text-slate-600 dark:text-slate-300"
                                    />
                                </div>
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-slate-100"
                                >
                                    Etkinlik Bilgileri
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Event Name with Slug Preview -->
                                <div class="lg:col-span-2">
                                    <label
                                        for="title"
                                        class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2"
                                    >
                                        Etkinlik Adı *
                                        <span
                                            class="text-xs font-normal text-slate-600 ml-2"
                                            >(Resmi kongre/konferans adı)</span
                                        >
                                    </label>
                                    <input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        required
                                        class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm placeholder-slate-400 focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all duration-200"
                                        :class="
                                            form.errors.title
                                                ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                : ''
                                        "
                                        placeholder="Örn: 25. Ulusal Pediatri Kongresi"
                                    />
                                    <p
                                        v-if="form.errors.title"
                                        class="mt-1 text-xs text-red-600 flex items-center"
                                    >
                                        <ExclamationCircleIcon
                                            class="h-3 w-3 mr-1"
                                        />
                                        {{ form.errors.title }}
                                    </p>

                                    <!-- Enhanced Corporate Slug Preview -->
                                    <div
                                        v-if="form.title"
                                        class="mt-3 p-3 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-300 dark:border-slate-600"
                                    >
                                        <div class="flex items-center text-sm">
                                            <LinkIcon
                                                class="h-4 w-4 mr-2 text-slate-600"
                                            />
                                            <span
                                                class="text-slate-800 dark:text-slate-200 mr-2 font-medium"
                                                >URL Adı:</span
                                            >
                                            <code
                                                class="bg-white dark:bg-slate-700 px-2 py-1 rounded text-slate-700 dark:text-slate-200 font-mono text-xs font-medium"
                                            >
                                                {{ generateSlug(form.title) }}
                                            </code>
                                        </div>
                                    </div>
                                </div>

                                <!-- Organization -->
                                <div class="lg:col-span-1">
                                    <label
                                        for="organization_id"
                                        class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2"
                                    >
                                        Organizasyon *
                                        <span
                                            class="text-xs font-normal text-slate-600 ml-2"
                                            >(Düzenleyen kurum)</span
                                        >
                                    </label>
                                    <select
                                        id="organization_id"
                                        v-model="form.organization_id"
                                        required
                                        class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all duration-200"
                                        :class="
                                            form.errors.organization_id
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
                                        v-if="form.errors.organization_id"
                                        class="mt-1 text-xs text-red-600 flex items-center"
                                    >
                                        <ExclamationCircleIcon
                                            class="h-3 w-3 mr-1"
                                        />
                                        {{ form.errors.organization_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Date and Location Section -->
                        <div
                            class="space-y-6 pt-6 border-t border-slate-200 dark:border-slate-700"
                        >
                            <div class="flex items-center space-x-4 mb-6">
                                <div
                                    class="h-8 w-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center"
                                >
                                    <ClockIcon
                                        class="h-4 w-4 text-slate-600 dark:text-slate-300"
                                    />
                                </div>
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-slate-100"
                                >
                                    Tarih ve Konum
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                                <!-- Start Date -->
                                <div class="lg:col-span-1">
                                    <label
                                        for="start_date"
                                        class="block text-sm font-bold text-purple-900 dark:text-purple-100 mb-2"
                                    >
                                        Başlangıç Tarihi *
                                    </label>
                                    <div class="relative">
                                        <CalendarDaysIcon
                                            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-500"
                                        />
                                        <input
                                            id="start_date"
                                            v-model="form.start_date"
                                            type="date"
                                            required
                                            class="block w-full pl-8 pr-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all duration-200"
                                            :class="
                                                form.errors.start_date
                                                    ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                    : ''
                                            "
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.start_date"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors.start_date }}
                                    </p>
                                </div>

                                <!-- End Date -->
                                <div class="lg:col-span-1">
                                    <label
                                        for="end_date"
                                        class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2"
                                    >
                                        Bitiş Tarihi *
                                    </label>
                                    <div class="relative">
                                        <CalendarDaysIcon
                                            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-500"
                                        />
                                        <input
                                            id="end_date"
                                            v-model="form.end_date"
                                            type="date"
                                            required
                                            class="block w-full pl-8 pr-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all duration-200"
                                            :class="
                                                form.errors.end_date
                                                    ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                    : ''
                                            "
                                            :min="form.start_date"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.end_date"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors.end_date }}
                                    </p>
                                </div>

                                <!-- Duration Display -->
                                <div
                                    v-if="form.start_date && form.end_date"
                                    class="lg:col-span-1"
                                >
                                    <label
                                        class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2"
                                    >
                                        Süre
                                    </label>
                                    <div
                                        class="px-1 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg"
                                    >
                                        <div
                                            class="flex items-center justify-center text-slate-800 dark:text-slate-100"
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
                                        class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2"
                                    >
                                        Etkinlik Konumu
                                    </label>
                                    <div class="relative">
                                        <MapPinIcon
                                            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-500"
                                        />
                                        <input
                                            id="location"
                                            v-model="form.location"
                                            type="text"
                                            class="block w-full pl-8 pr-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm placeholder-slate-400 focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all duration-200"
                                            :class="
                                                form.errors.location
                                                    ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                    : ''
                                            "
                                            placeholder="Örn: Antalya Convention Center"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.location"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors.location }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Description and Publication Section -->
                        <div
                            class="space-y-6 pt-6 border-t border-slate-200 dark:border-slate-700"
                        >
                            <div class="flex items-center space-x-4 mb-6">
                                <div
                                    class="h-8 w-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center"
                                >
                                    <DocumentTextIcon
                                        class="h-4 w-4 text-slate-600 dark:text-slate-300"
                                    />
                                </div>
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-slate-100"
                                >
                                    Akademik İçerik ve Yayın
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                                <!-- Description -->
                                <div class="lg:col-span-3">
                                    <label
                                        for="description"
                                        class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2"
                                    >
                                        Etkinlik Açıklaması
                                        <span
                                            class="text-xs font-normal text-slate-600 ml-2"
                                            >(Bilimsel amaç, hedef kitle ve
                                            program hakkında)</span
                                        >
                                    </label>
                                    <div class="relative">
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="4"
                                            class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm placeholder-slate-400 focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all duration-200 resize-none leading-normal font-normal"
                                            :class="
                                                form.errors.description
                                                    ? 'border-red-400 focus:ring-red-500/30 focus:border-red-500'
                                                    : ''
                                            "
                                            placeholder="Bu etkinlik hakkında detaylı bilgi verin. Bilimsel amaç, hedef kitle, ana konular ve beklenen katılımcı profili gibi akademik bilgileri içerebilir..."
                                            maxlength="2000"
                                        ></textarea>
                                        <div
                                            class="absolute bottom-2 right-2 flex items-center space-x-1"
                                        >
                                            <span
                                                class="text-xs text-slate-500 bg-white dark:bg-slate-800 px-2 py-1 rounded font-medium"
                                                >{{
                                                    (form.description || "")
                                                        .length
                                                }}/2000</span
                                            >
                                        </div>
                                    </div>
                                    <p
                                        v-if="form.errors.description"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors.description }}
                                    </p>
                                </div>

                                <!-- Publication Status Enhanced Corporate Panel -->
                                <div class="lg:col-span-1">
                                    <label
                                        class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2"
                                    >
                                        Yayın Durumu
                                    </label>
                                    <div
                                        class="bg-slate-100 dark:bg-slate-800 rounded-lg p-4 border border-slate-300 dark:border-slate-600"
                                    >
                                        <label
                                            class="flex items-start space-x-3 cursor-pointer"
                                        >
                                            <input
                                                v-model="form.is_published"
                                                type="checkbox"
                                                class="mt-0.5 h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded transition-colors"
                                            />
                                            <div>
                                                <span
                                                    class="text-sm font-bold text-slate-900 dark:text-slate-100"
                                                >
                                                    Etkinliği yayınla
                                                </span>
                                                <p
                                                    class="text-xs text-slate-700 dark:text-slate-300 mt-1 leading-normal"
                                                >
                                                    Yayınlandığında katılımcılar
                                                    tarafından görülebilir
                                                    olacak ve kayıt işlemleri
                                                    başlayacak
                                                </p>
                                            </div>
                                        </label>

                                        <!-- Corporate Status Preview -->
                                        <div
                                            class="mt-4 pt-4 border-t border-slate-300 dark:border-slate-600"
                                        >
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <div
                                                    class="h-3 w-3 rounded-full"
                                                    :class="
                                                        form.is_published
                                                            ? 'bg-green-500'
                                                            : 'bg-orange-500'
                                                    "
                                                ></div>
                                                <span
                                                    class="text-sm font-bold"
                                                    :class="
                                                        form.is_published
                                                            ? 'text-green-700 dark:text-green-300'
                                                            : 'text-orange-700 dark:text-orange-300'
                                                    "
                                                >
                                                    {{
                                                        form.is_published
                                                            ? "Yayında"
                                                            : "Taslak"
                                                    }}
                                                </span>
                                            </div>
                                            <p
                                                class="text-xs text-slate-600 dark:text-slate-300 mt-2"
                                            >
                                                {{
                                                    form.is_published
                                                        ? "Etkinlik aktif olarak yayında"
                                                        : "Etkinlik henüz yayınlanmadı"
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div
                            class="flex items-center justify-between pt-6 border-t border-slate-200 dark:border-slate-700"
                        >
                            <div class="flex items-center space-x-3">
                                <Link
                                    :href="
                                        route('admin.events.show', eventSlug)
                                    "
                                    class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 transition-all duration-200"
                                >
                                    <ArrowLeftIcon class="h-4 w-4 mr-1.5" />
                                    Geri Dön
                                </Link>

                                <Link
                                    :href="route('admin.events.index')"
                                    class="text-sm text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-slate-100 transition-colors font-medium"
                                >
                                    Listeye Dön
                                </Link>
                            </div>

                            <div class="flex space-x-3">
                                <!-- Reset Changes -->
                                <button
                                    type="button"
                                    @click="resetForm"
                                    :disabled="form.processing || !hasChanges"
                                    class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ArrowPathIcon class="h-4 w-4 mr-1.5" />
                                    Sıfırla
                                </button>

                                <!-- Update Event -->
                                <button
                                    type="submit"
                                    :disabled="form.processing || !hasChanges"
                                    class="inline-flex items-center px-6 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-600 dark:hover:bg-slate-700 text-white text-sm font-medium rounded-lg focus:ring-2 focus:ring-slate-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <template v-if="form.processing">
                                        <div
                                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
                                        ></div>
                                        Güncelleniyor...
                                    </template>
                                    <template v-else>
                                        <CheckIcon class="h-4 w-4 mr-1.5" />
                                        Değişiklikleri Kaydet
                                    </template>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Change Summary - Enhanced Corporate Version -->
                <div
                    v-if="hasChanges"
                    class="bg-gradient-to-br from-orange-100 to-red-100 dark:from-orange-800 dark:to-red-800 rounded-3xl p-12 border-3 border-orange-300 dark:border-orange-600 shadow-2xl"
                >
                    <div class="flex items-start space-x-8">
                        <div class="flex-shrink-0">
                            <div
                                class="h-16 w-16 bg-orange-200 dark:bg-orange-700 rounded-2xl flex items-center justify-center"
                            >
                                <ExclamationTriangleIcon
                                    class="h-10 w-10 text-orange-700 dark:text-orange-200"
                                />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4
                                class="text-2xl font-bold text-orange-900 dark:text-orange-100 mb-6"
                            >
                                Kaydedilmemiş Akademik Değişiklikler
                            </h4>
                            <p
                                class="text-lg text-orange-800 dark:text-orange-200 leading-relaxed mb-8"
                            >
                                Etkinlik bilgilerinde değişiklikler yaptınız
                                ancak henüz kaydetmediniz. Akademik program
                                verilerinin kaybolmaması için değişikliklerinizi
                                kaydetmeyi unutmayın.
                            </p>
                            <div
                                class="flex items-center space-x-8 text-base text-orange-700 dark:text-orange-300"
                            >
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="h-3 w-3 bg-orange-600 rounded-full"
                                    ></div>
                                    <span class="font-semibold"
                                        >Otomatik kaydetme kapalı</span
                                    >
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="h-3 w-3 bg-orange-600 rounded-full"
                                    ></div>
                                    <span class="font-semibold"
                                        >Manuel kaydetme gerekli</span
                                    >
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
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    PencilSquareIcon,
    CalendarDaysIcon,
    MapPinIcon,
    ArrowLeftIcon,
    CheckIcon,
    ArrowPathIcon,
    LinkIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    DocumentTextIcon,
    ExclamationCircleIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
    organizations: {
        type: Array,
        default: () => [],
    },
});

// Computed properties
const pageTitle = computed(() => {
    return (props.event?.name || "Event") + " - Düzenle";
});

const eventName = computed(() => {
    return props.event?.name || "Event";
});

const eventSlug = computed(() => {
    return props.event?.slug || "unknown";
});

const isPublished = computed(() => {
    return Boolean(props.event?.is_published);
});

const breadcrumbs = computed(() => [
    { label: "Etkinlikler", href: route("admin.events.index") },
    {
        label: eventName.value,
        href: route("admin.events.show", eventSlug.value),
    },
    { label: "Düzenle", href: null },
]);

// Form state
const initialFormState = () => ({
    title: props.event?.name || "",
    description: props.event?.description || "",
    start_date: props.event?.start_date || "",
    end_date: props.event?.end_date || "",
    location: props.event?.location || "",
    organization_id: props.event?.organization_id ?? "",
    is_published: Boolean(props.event?.is_published),
});

const form = useForm(initialFormState());

const hasChanges = computed(() => {
    const initial = initialFormState();

    return (
        form.title !== initial.title ||
        form.description !== initial.description ||
        form.start_date !== initial.start_date ||
        form.end_date !== initial.end_date ||
        form.location !== initial.location ||
        String(form.organization_id) !== String(initial.organization_id) ||
        form.is_published !== initial.is_published
    );
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

const generateSlug = (name) => {
    if (!name) return "";

    // Türkçe karakterleri dönüştür
    const turkishChars = {
        ş: "s",
        Ş: "S",
        ğ: "g",
        Ğ: "G",
        ü: "u",
        Ü: "U",
        ö: "o",
        Ö: "O",
        ı: "i",
        İ: "I",
        ç: "c",
        Ç: "C",
    };

    let slug = name;
    for (const [turkish, latin] of Object.entries(turkishChars)) {
        slug = slug.replaceAll(turkish, latin);
    }

    return slug
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-")
        .trim("-");
};

const submitForm = () => {
    form.put(route("admin.events.update", eventSlug.value), {
        preserveScroll: true,
        onSuccess: () => {
            form.clearErrors();
        },
    });
};

const resetForm = () => {
    const initial = initialFormState();

    form.title = initial.title;
    form.description = initial.description;
    form.start_date = initial.start_date;
    form.end_date = initial.end_date;
    form.location = initial.location;
    form.organization_id = initial.organization_id;
    form.is_published = initial.is_published;
    form.clearErrors();
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
    background-color: rgb(79 70 229);
    border-color: rgb(79 70 229);
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
