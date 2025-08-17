<!-- Admin/Venues/Edit.vue - Gray Theme -->
<template>
    <AdminLayout
        page-title="Salon Düzenle"
        :page-subtitle="`${venue.name} salon bilgilerini düzenleyin`"
        :breadcrumbs="breadcrumbs"
    >
        <Head :title="`${venue.name} - Düzenle`" />

        <div class="w-full space-y-8">
            <!-- Header Section -->
            <div
                class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
                <div class="relative">
                    <!-- Banner with venue color -->
                    <div
                        class="h-48 relative overflow-hidden"
                        :style="{ backgroundColor: form.color || venue.color }"
                    >
                        <div class="absolute inset-0 bg-black/20"></div>
                        <div class="absolute inset-0 flex items-end">
                            <div class="p-8 text-white w-full">
                                <div class="flex items-end justify-between">
                                    <div class="flex items-center space-x-6">
                                        <!-- Icon -->
                                        <div
                                            class="h-20 w-20 bg-white/20 backdrop-blur-sm rounded-xl overflow-hidden flex items-center justify-center"
                                        >
                                            <BuildingOfficeIcon
                                                class="h-12 w-12 text-white/90"
                                            />
                                        </div>
                                        <div>
                                            <h1 class="text-3xl font-bold mb-1">
                                                {{ form.name || venue.name }}
                                            </h1>
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white"
                                                >
                                                    <MapPinIcon
                                                        class="w-4 h-4 mr-2"
                                                    />
                                                    {{
                                                        venue.event_day
                                                            ?.display_name ||
                                                        "Etkinlik Günü"
                                                    }}
                                                </span>
                                                <span
                                                    v-if="
                                                        form.capacity ||
                                                        venue.capacity
                                                    "
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white"
                                                >
                                                    <UserGroupIcon
                                                        class="w-4 h-4 mr-2"
                                                    />
                                                    {{
                                                        form.capacity ||
                                                        venue.capacity
                                                    }}
                                                    kişi
                                                </span>
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-500/20 text-blue-100"
                                                >
                                                    <PencilIcon
                                                        class="w-4 h-4 mr-2"
                                                    />
                                                    Düzenleniyor
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Stats -->
                                    <div
                                        class="flex items-center space-x-8 text-white/90"
                                    >
                                        <div class="text-center">
                                            <div class="text-2xl font-bold">
                                                {{
                                                    venue.program_sessions_count ||
                                                    0
                                                }}
                                            </div>
                                            <div class="text-sm">Oturum</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold">
                                                {{ venue.sort_order || 0 }}
                                            </div>
                                            <div class="text-sm">Sıra</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Bar -->
                    <div
                        class="px-8 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex flex-wrap items-center gap-6">
                            <!-- Event Day Info -->
                            <div class="flex items-center space-x-2">
                                <span
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                    >Etkinlik Günü:</span
                                >
                                <span
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    {{
                                        venue.event_day?.display_name ||
                                        "Belirtilmemiş"
                                    }}
                                </span>
                            </div>

                            <!-- Event Info -->
                            <div class="flex items-center space-x-2">
                                <CalendarIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        {{
                                            venue.event_day?.event?.name ||
                                            "Etkinlik yok"
                                        }}
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Etkinlik
                                    </div>
                                </div>
                            </div>

                            <!-- Display Name Preview -->
                            <div class="flex items-center space-x-2">
                                <EyeIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <div
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        {{
                                            form.display_name ||
                                            form.name ||
                                            venue.display_name
                                        }}
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Görünen Ad (Önizleme)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Bar -->
                    <div
                        class="px-8 py-4 flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-900"
                    >
                        <div class="flex items-center space-x-3">
                            <!-- Back to List -->
                            <Link
                                :href="route('admin.venues.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
                            >
                                <ArrowLeftIcon class="h-4 w-4 mr-2" />
                                Salon Listesi
                            </Link>

                            <!-- View Original -->
                            <Link
                                :href="route('admin.venues.show', venue.id)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
                            >
                                <EyeIcon class="h-4 w-4 mr-2" />
                                Orijinali Görüntüle
                            </Link>
                        </div>

                        <div class="flex items-center space-x-3">
                            <!-- Reset Button -->
                            <button
                                type="button"
                                @click="resetForm"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
                            >
                                <ArrowPathIcon class="h-4 w-4 mr-2" />
                                Sıfırla
                            </button>

                            <!-- Save Button -->
                            <button
                                type="submit"
                                form="edit-venue-form"
                                :disabled="form.processing"
                                class="inline-flex items-center px-6 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm border border-gray-700"
                            >
                                <span
                                    v-if="form.processing"
                                    class="inline-flex items-center"
                                >
                                    <svg
                                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Kaydediliyor...
                                </span>
                                <span v-else>
                                    <CheckIcon class="h-4 w-4 mr-2" />
                                    Değişiklikleri Kaydet
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                <!-- Main Content -->
                <div class="xl:col-span-3 space-y-8">
                    <!-- Form Card -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <form
                            id="edit-venue-form"
                            @submit.prevent="updateVenue"
                            class="divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <!-- Basic Information -->
                            <div class="p-6 space-y-6">
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Temel Bilgiler
                                    </h3>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                    >
                                        <!-- Event Day -->
                                        <div class="md:col-span-2">
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                            >
                                                Etkinlik Günü *
                                            </label>
                                            <select
                                                v-model="form.event_day_id"
                                                required
                                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-gray-500 focus:ring-gray-500"
                                                :class="
                                                    form.errors.event_day_id
                                                        ? 'border-red-500'
                                                        : ''
                                                "
                                            >
                                                <option value="">
                                                    Etkinlik günü seçin
                                                </option>
                                                <template
                                                    v-for="group in groupedEventDays"
                                                    :key="group.event_id"
                                                >
                                                    <optgroup
                                                        :label="
                                                            group.event_name
                                                        "
                                                    >
                                                        <option
                                                            v-for="eventDay in group.options"
                                                            :key="eventDay.id"
                                                            :value="eventDay.id"
                                                        >
                                                            {{
                                                                eventDay.display_name
                                                            }}
                                                        </option>
                                                    </optgroup>
                                                </template>
                                            </select>
                                            <p
                                                v-if="form.errors.event_day_id"
                                                class="text-sm text-red-600 dark:text-red-400 mt-1"
                                            >
                                                {{ form.errors.event_day_id }}
                                            </p>
                                        </div>

                                        <!-- Name -->
                                        <div class="md:col-span-2">
                                            <FormInput
                                                v-model="form.name"
                                                label="Salon Adı"
                                                placeholder="Salon adını girin"
                                                required
                                                :error-message="
                                                    form.errors.name
                                                "
                                                :maxlength="255"
                                                show-counter
                                            />
                                        </div>

                                        <!-- Display Name -->
                                        <div>
                                            <FormInput
                                                v-model="form.display_name"
                                                label="Görünen Adı"
                                                placeholder="Görünen adı girin"
                                                :error-message="
                                                    form.errors.display_name
                                                "
                                                :maxlength="255"
                                                show-counter
                                            >
                                                <template #helper>
                                                    <p
                                                        class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                                    >
                                                        Programda görünecek
                                                        isim. Boş bırakılırsa
                                                        salon adı kullanılır.
                                                    </p>
                                                </template>
                                            </FormInput>
                                        </div>

                                        <!-- Capacity -->
                                        <div>
                                            <FormInput
                                                v-model="form.capacity"
                                                type="number"
                                                label="Kapasite"
                                                placeholder="Maksimum kişi sayısı"
                                                :error-message="
                                                    form.errors.capacity
                                                "
                                                min="1"
                                                max="50000"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Design & Settings -->
                            <div class="p-6 space-y-6">
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Tasarım ve Ayarlar
                                    </h3>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                    >
                                        <!-- Color -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                            >
                                                Salon Rengi
                                            </label>
                                            <div class="space-y-3">
                                                <input
                                                    v-model="form.color"
                                                    type="color"
                                                    class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 cursor-pointer"
                                                />
                                                <input
                                                    v-model="form.color"
                                                    type="text"
                                                    placeholder="#3B82F6"
                                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-gray-500 focus:ring-gray-500"
                                                />
                                            </div>
                                            <p
                                                v-if="form.errors.color"
                                                class="text-sm text-red-600 dark:text-red-400 mt-1"
                                            >
                                                {{ form.errors.color }}
                                            </p>
                                        </div>

                                        <!-- Sort Order -->
                                        <div>
                                            <FormInput
                                                v-model="form.sort_order"
                                                type="number"
                                                label="Sıralama"
                                                placeholder="Sıralama numarası"
                                                :error-message="
                                                    form.errors.sort_order
                                                "
                                                min="0"
                                                max="9999"
                                            >
                                                <template #helper>
                                                    <p
                                                        class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                                    >
                                                        Salonların
                                                        sıralanmasında
                                                        kullanılır.
                                                    </p>
                                                </template>
                                            </FormInput>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Venue Templates -->
                            <div class="p-6 space-y-6">
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                                    >
                                        Hızlı Şablonlar
                                    </h3>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400 mb-4"
                                    >
                                        Salon türüne göre önceden tanımlanmış
                                        ayarları kullanabilirsiniz.
                                    </p>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-4 gap-4"
                                    >
                                        <!-- Auditorium Template -->
                                        <button
                                            type="button"
                                            @click="applyTemplate('auditorium')"
                                            class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-left group"
                                        >
                                            <div class="flex items-center mb-2">
                                                <div
                                                    class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3"
                                                >
                                                    <BuildingOfficeIcon
                                                        class="h-5 w-5 text-blue-600 dark:text-blue-400"
                                                    />
                                                </div>
                                                <h4
                                                    class="font-medium text-gray-900 dark:text-white"
                                                >
                                                    Auditorium
                                                </h4>
                                            </div>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                Büyük konferans salonu
                                            </p>
                                        </button>

                                        <!-- Conference Template -->
                                        <button
                                            type="button"
                                            @click="applyTemplate('conference')"
                                            class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-left group"
                                        >
                                            <div class="flex items-center mb-2">
                                                <div
                                                    class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3"
                                                >
                                                    <UserGroupIcon
                                                        class="h-5 w-5 text-green-600 dark:text-green-400"
                                                    />
                                                </div>
                                                <h4
                                                    class="font-medium text-gray-900 dark:text-white"
                                                >
                                                    Konferans
                                                </h4>
                                            </div>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                Orta boy salon
                                            </p>
                                        </button>

                                        <!-- Meeting Template -->
                                        <button
                                            type="button"
                                            @click="applyTemplate('meeting')"
                                            class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-left group"
                                        >
                                            <div class="flex items-center mb-2">
                                                <div
                                                    class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-3"
                                                >
                                                    <UsersIcon
                                                        class="h-5 w-5 text-purple-600 dark:text-purple-400"
                                                    />
                                                </div>
                                                <h4
                                                    class="font-medium text-gray-900 dark:text-white"
                                                >
                                                    Toplantı
                                                </h4>
                                            </div>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                Küçük toplantı salonu
                                            </p>
                                        </button>

                                        <!-- Workshop Template -->
                                        <button
                                            type="button"
                                            @click="applyTemplate('workshop')"
                                            class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-left group"
                                        >
                                            <div class="flex items-center mb-2">
                                                <div
                                                    class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center mr-3"
                                                >
                                                    <WrenchScrewdriverIcon
                                                        class="h-5 w-5 text-orange-600 dark:text-orange-400"
                                                    />
                                                </div>
                                                <h4
                                                    class="font-medium text-gray-900 dark:text-white"
                                                >
                                                    Atölye
                                                </h4>
                                            </div>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                Workshop salonu
                                            </p>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div
                                class="px-6 py-4 bg-gray-50 dark:bg-gray-800 flex items-center justify-between"
                            >
                                <div class="flex items-center space-x-4">
                                    <Link
                                        :href="route('admin.venues.index')"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                                    >
                                        İptal
                                    </Link>

                                    <button
                                        type="button"
                                        @click="resetForm"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                                    >
                                        Sıfırla
                                    </button>
                                </div>

                                <div class="flex items-center space-x-4">
                                    <!-- View Venue -->
                                    <Link
                                        :href="
                                            route('admin.venues.show', venue.id)
                                        "
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                                    >
                                        <EyeIcon class="h-4 w-4 mr-2" />
                                        Görüntüle
                                    </Link>

                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="inline-flex items-center px-6 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors border border-gray-700"
                                    >
                                        <span
                                            v-if="form.processing"
                                            class="inline-flex items-center"
                                        >
                                            <svg
                                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    class="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"
                                                ></circle>
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                ></path>
                                            </svg>
                                            Güncelleniyor...
                                        </span>
                                        <span v-else>Salon Güncelle</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Live Preview -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Önizleme
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <BuildingOfficeIcon
                                        class="h-5 w-5 text-gray-500"
                                    />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Salon Adı</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{ form.name || "Belirtilmemiş" }}</span
                                >
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <EyeIcon class="h-5 w-5 text-gray-500" />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Görünen Ad</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{
                                        form.display_name ||
                                        form.name ||
                                        "Belirtilmemiş"
                                    }}</span
                                >
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <UserGroupIcon
                                        class="h-5 w-5 text-gray-500"
                                    />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Kapasite</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{
                                        form.capacity || "Belirtilmemiş"
                                    }}</span
                                >
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div
                                        class="h-5 w-5 rounded border border-gray-300"
                                        :style="{ backgroundColor: form.color }"
                                    ></div>
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Renk</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{ form.color }}</span
                                >
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <HashtagIcon
                                        class="h-5 w-5 text-gray-500"
                                    />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Sıra</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{ form.sort_order || "Otomatik" }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Changes Summary -->
                    <div
                        v-if="hasChanges"
                        class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-start">
                            <ExclamationTriangleIcon
                                class="h-5 w-5 text-gray-600 dark:text-gray-400 mt-0.5 flex-shrink-0"
                            />
                            <div class="ml-3">
                                <h4
                                    class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                >
                                    Kaydedilmemiş Değişiklikler
                                </h4>
                                <div
                                    class="mt-2 text-sm text-gray-700 dark:text-gray-200"
                                >
                                    <p>
                                        Formu değiştirdiniz ancak henüz
                                        kaydetmediniz. Sayfadan ayrılmadan önce
                                        değişikliklerinizi kaydetmeyi unutmayın.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Venue Details -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Mevcut Salon Bilgileri
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <CalendarIcon
                                        class="h-5 w-5 text-gray-500"
                                    />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Kayıt Tarihi</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{ formatDate(venue.created_at) }}</span
                                >
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <ClockIcon class="h-5 w-5 text-gray-500" />
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                        >Son Güncelleme</span
                                    >
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"
                                    >{{ formatDate(venue.updated_at) }}</span
                                >
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
import FormInput from "@/Components/Forms/FormInput.vue";
import FormSelect from "@/Components/Forms/FormSelect.vue";
import {
    ArrowLeftIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    UsersIcon,
    WrenchScrewdriverIcon,
    EyeIcon,
    PencilIcon,
    CheckIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon,
    CalendarIcon,
    ClockIcon,
    MapPinIcon,
    HashtagIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    venue: {
        type: Object,
        required: true,
    },
    eventDays: {
        type: Array,
        default: () => [],
    },
});

// Form
const form = useForm({
    event_day_id: props.venue.event_day_id,
    name: props.venue.name,
    display_name: props.venue.display_name || "",
    capacity: props.venue.capacity || "",
    color: props.venue.color || "#3B82F6",
    sort_order: props.venue.sort_order || "",
});

// Computed
const breadcrumbs = computed(() => [
    { label: "Ana Sayfa", href: route("admin.dashboard") },
    { label: "Salonlar", href: route("admin.venues.index") },
    {
        label: props.venue.name,
        href: route("admin.venues.show", props.venue.id),
    },
    { label: "Düzenle", href: null },
]);

// Group event days by event name for better UX
const groupedEventDays = computed(() => {
    if (!props.eventDays || props.eventDays.length === 0) return [];

    // Create a map to group by event
    const eventGroups = new Map();

    props.eventDays.forEach((eventDay) => {
        const eventName = eventDay.event?.name || "Etkinlik Belirtilmemiş";
        const eventId = eventDay.event?.id || "unknown";
        const groupKey = `${eventId}-${eventName}`;

        if (!eventGroups.has(groupKey)) {
            eventGroups.set(groupKey, {
                event_name: eventName,
                event_id: eventId,
                options: [],
            });
        }

        eventGroups.get(groupKey).options.push({
            id: eventDay.id,
            display_name: eventDay.display_name,
            date: eventDay.date,
            sort_order: eventDay.sort_order,
        });
    });

    // Convert to array and sort
    const result = Array.from(eventGroups.values()).map((group) => ({
        ...group,
        options: group.options.sort((a, b) => a.sort_order - b.sort_order),
    }));

    // Sort groups by event name
    return result.sort((a, b) => a.event_name.localeCompare(b.event_name));
});

// Templates
const templates = {
    auditorium: {
        capacity: 500,
        color: "#3B82F6",
    },
    conference: {
        capacity: 150,
        color: "#10B981",
    },
    meeting: {
        capacity: 50,
        color: "#8B5CF6",
    },
    workshop: {
        capacity: 25,
        color: "#F59E0B",
    },
};

// Track changes
const hasChanges = computed(() => {
    return (
        form.event_day_id !== props.venue.event_day_id ||
        form.name !== props.venue.name ||
        form.display_name !== (props.venue.display_name || "") ||
        form.capacity !== (props.venue.capacity || "") ||
        form.color !== props.venue.color ||
        form.sort_order !== (props.venue.sort_order || "")
    );
});

const formatDate = (dateString) => {
    if (!dateString) return "-";

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return "-";

        const day = date.getDate().toString().padStart(2, "0");
        const month = (date.getMonth() + 1).toString().padStart(2, "0");
        const year = date.getFullYear();

        return `${day}/${month}/${year}`;
    } catch (error) {
        console.error("Date formatting error:", error);
        return "-";
    }
};

// Methods
const updateVenue = () => {
    form.put(route("admin.venues.update", props.venue.id), {
        onSuccess: () => {
            // Success message will be handled by the backend
        },
        onError: () => {
            // Errors will be handled by the form validation
        },
    });
};

const resetForm = () => {
    form.reset();
    form.event_day_id = props.venue.event_day_id;
    form.name = props.venue.name;
    form.display_name = props.venue.display_name || "";
    form.capacity = props.venue.capacity || "";
    form.color = props.venue.color || "#3B82F6";
    form.sort_order = props.venue.sort_order || "";
};

const applyTemplate = (templateKey) => {
    const template = templates[templateKey];
    if (!template) return;

    form.capacity = template.capacity;
    form.color = template.color;
};
</script>

<style scoped>
/* Smooth transitions */
.transition-colors {
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Template button hover effects */
.group:hover .w-8 {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}
</style>
