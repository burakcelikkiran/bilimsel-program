<!-- Admin/ProgramSessions/Edit.vue - Full Cascade Selection Version -->
<template>
    <AdminLayout
        :page-title="pageTitle"
        page-subtitle="Program oturum bilgilerini güncelleyin"
        :breadcrumbs="breadcrumbs"
    >
        <Head :title="pageTitle" />

        <div class="w-full space-y-8">
            <!-- Header Section -->
            <div
                class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
                <div
                    class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-800 to-gray-900"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="h-10 w-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center"
                                >
                                    <PencilSquareIcon
                                        class="h-6 w-6 text-white"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-white">
                                    Oturum Düzenle
                                </h3>
                                <p class="text-sm text-gray-300">
                                    {{ sessionTitle }}
                                </p>
                            </div>
                        </div>
                        <!-- Status Info -->
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                <p class="text-sm font-medium text-white">
                                    {{
                                        selectedVenue?.display_name ||
                                        "Salon Seçiniz"
                                    }}
                                </p>
                                <p class="text-xs text-gray-300">
                                    {{
                                        formatTimeRange(
                                            form.start_time,
                                            form.end_time
                                        )
                                    }}
                                </p>
                            </div>

                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                :class="
                                    form.is_break
                                        ? 'bg-gray-100 text-gray-800 dark:bg-gray-200 dark:text-gray-900'
                                        : 'bg-gray-600 text-white dark:bg-gray-500 dark:text-gray-100'
                                "
                            >
                                <span
                                    class="w-1.5 h-1.5 mr-1.5 rounded-full bg-current opacity-75"
                                ></span>
                                {{
                                    form.is_break
                                        ? "Ara"
                                        : form.session_type || "Oturum"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div
                class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
                <form
                    @submit.prevent="updateSession"
                    class="divide-y divide-gray-200 dark:divide-gray-700"
                >
                    <!-- Event Context Selection -->
                    <div class="p-6 space-y-6 bg-gray-50 dark:bg-gray-800/50">
                        <div>
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                            >
                                Etkinlik Seçimi
                            </h3>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Event Selection -->
                                <div>
                                    <label
                                        for="event_id"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Etkinlik *
                                    </label>
                                    <select
                                        id="event_id"
                                        v-model="selectedEventId"
                                        @change="onEventChange"
                                        required
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                    >
                                        <option value="">Etkinlik seçin</option>
                                        <option
                                            v-for="event in events"
                                            :key="event.id"
                                            :value="event.id"
                                        >
                                            {{ event.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Event Day Selection -->
                                <div>
                                    <label
                                        for="event_day_id"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Etkinlik Günü *
                                    </label>
                                    <select
                                        id="event_day_id"
                                        v-model="selectedEventDayId"
                                        @change="onEventDayChange"
                                        :disabled="
                                            !selectedEventId ||
                                            isLoadingEventDays
                                        "
                                        required
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <option value="">
                                            {{
                                                isLoadingEventDays
                                                    ? "Yükleniyor..."
                                                    : "Gün seçin"
                                            }}
                                        </option>
                                        <option
                                            v-for="day in availableEventDays"
                                            :key="day.id"
                                            :value="day.id"
                                        >
                                            {{ day.display_name }} ({{
                                                formatDate(day.date)
                                            }})
                                        </option>
                                    </select>
                                </div>

                                <!-- Venue Selection -->
                                <div>
                                    <label
                                        for="venue_id"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Salon *
                                    </label>
                                    <select
                                        id="venue_id"
                                        v-model="form.venue_id"
                                        :disabled="
                                            !selectedEventDayId ||
                                            isLoadingVenues
                                        "
                                        required
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="
                                            errors.venue_id
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                    >
                                        <option value="">
                                            {{
                                                isLoadingVenues
                                                    ? "Yükleniyor..."
                                                    : "Salon seçin"
                                            }}
                                        </option>
                                        <option
                                            v-for="venue in availableVenues"
                                            :key="venue.id"
                                            :value="venue.id"
                                        >
                                            {{ venue.display_name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.venue_id"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.venue_id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="p-6 space-y-6">
                        <div>
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                            >
                                Oturum Bilgileri
                            </h3>

                            <div
                                class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                            >
                                <!-- Title - Full Width -->
                                <div class="lg:col-span-3 xl:col-span-2">
                                    <label
                                        for="title"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Oturum Başlığı *
                                    </label>
                                    <input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        required
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            errors.title
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                        placeholder="Örn: Ana Oturum 1"
                                    />
                                    <p
                                        v-if="errors.title"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.title }}
                                    </p>
                                </div>

                                <!-- Session Type -->
                                <div class="lg:col-span-1 xl:col-span-1">
                                    <label
                                        for="session_type"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Oturum Türü *
                                    </label>
                                    <select
                                        id="session_type"
                                        v-model="form.session_type"
                                        required
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            errors.session_type
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                    >
                                        <option value="">
                                            Oturum türü seçin
                                        </option>
                                        <option
                                            v-for="type in sessionTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.session_type"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.session_type }}
                                    </p>
                                </div>

                                <!-- Quick Actions -->
                                <div class="lg:col-span-1 xl:col-span-1">
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Hızlı İşlemler
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input
                                                v-model="form.is_break"
                                                type="checkbox"
                                                class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded transition-colors"
                                            />
                                            <span
                                                class="ml-3 text-sm text-gray-700 dark:text-gray-300"
                                            >
                                                Bu bir ara
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Time and Duration -->
                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                            <!-- Start Time -->
                            <div>
                                <label
                                    for="start_time"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    Başlangıç Saati *
                                </label>
                                <div class="relative">
                                    <ClockIcon
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
                                    />
                                    <input
                                        id="start_time"
                                        v-model="form.start_time"
                                        type="time"
                                        required
                                        class="block w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            errors.start_time
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                    />
                                </div>
                                <p
                                    v-if="errors.start_time"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ errors.start_time }}
                                </p>
                            </div>

                            <!-- End Time -->
                            <div>
                                <label
                                    for="end_time"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    Bitiş Saati *
                                </label>
                                <div class="relative">
                                    <ClockIcon
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
                                    />
                                    <input
                                        id="end_time"
                                        v-model="form.end_time"
                                        type="time"
                                        required
                                        class="block w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            errors.end_time
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                        :min="form.start_time"
                                    />
                                </div>
                                <p
                                    v-if="errors.end_time"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ errors.end_time }}
                                </p>
                            </div>

                            <!-- Duration Display -->
                            <div v-if="form.start_time && form.end_time">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    Süre
                                </label>
                                <div
                                    class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
                                >
                                    <div
                                        class="flex items-center text-gray-700 dark:text-gray-300"
                                    >
                                        <ClockIcon class="h-5 w-5 mr-2" />
                                        <span class="font-semibold"
                                            >{{
                                                calculateDuration(
                                                    form.start_time,
                                                    form.end_time
                                                )
                                            }}
                                            dk</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description - Full Width -->
                        <div>
                            <label
                                for="description"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Oturum Açıklaması
                            </label>
                            <div class="relative">
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="6"
                                    class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md resize-none"
                                    :class="
                                        errors.description
                                            ? 'border-red-300 focus:ring-red-500'
                                            : ''
                                    "
                                    placeholder="Oturum hakkında detaylı bilgi verin. Bu açıklama katılımcılara gösterilecektir..."
                                ></textarea>
                                <div
                                    class="absolute bottom-3 right-3 text-xs text-gray-400"
                                >
                                    {{ (form.description || "").length }}/2000
                                </div>
                            </div>
                            <p
                                v-if="errors.description"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ errors.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Moderators Section -->
                    <div class="p-6 space-y-6">
                        <div>
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                            >
                                Moderatörler
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Moderator Title -->
                                <div class="md:col-span-2">
                                    <label
                                        for="moderator_title"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Moderatör Unvanı
                                    </label>
                                    <select
                                        id="moderator_title"
                                        v-model="form.moderator_title"
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            errors.moderator_title
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                    >
                                        <option value="">
                                            Moderatör unvanı seçin
                                        </option>
                                        <option
                                            v-for="title in moderatorTitles"
                                            :key="title.value"
                                            :value="title.value"
                                        >
                                            {{ title.label }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.moderator_title"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.moderator_title }}
                                    </p>
                                </div>

                                <!-- Moderators Selection -->
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Moderatörler
                                    </label>

                                    <div
                                        v-if="participants.length > 0"
                                        class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3"
                                    >
                                        <div
                                            v-for="participant in participants"
                                            :key="participant.id"
                                            class="flex items-center space-x-3"
                                        >
                                            <input
                                                :id="`moderator_${participant.id}`"
                                                v-model="form.moderator_ids"
                                                :value="participant.id"
                                                type="checkbox"
                                                class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                                            />
                                            <label
                                                :for="`moderator_${participant.id}`"
                                                class="text-sm text-gray-700 dark:text-gray-300 flex-1"
                                            >
                                                {{ participant.full_name }}
                                                <span
                                                    v-if="participant.title"
                                                    class="text-gray-500"
                                                >
                                                    -
                                                    {{
                                                        participant.title
                                                    }}</span
                                                >
                                                <span
                                                    v-if="
                                                        participant.affiliation
                                                    "
                                                    class="text-gray-400 text-xs block"
                                                >
                                                    {{
                                                        participant.affiliation
                                                    }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        v-else
                                        class="text-center py-8 text-gray-500 dark:text-gray-400"
                                    >
                                        <UsersIcon
                                            class="h-12 w-12 mx-auto mb-2"
                                        />
                                        <p>Henüz katılımcı eklenmemiş</p>
                                        <Link
                                            :href="
                                                route(
                                                    'admin.participants.create'
                                                )
                                            "
                                            class="text-gray-600 dark:text-gray-400 hover:underline text-sm"
                                        >
                                            Yeni katılımcı ekle
                                        </Link>
                                    </div>

                                    <p
                                        v-if="errors.moderator_ids"
                                        class="text-sm text-red-600 dark:text-red-400 mt-1"
                                    >
                                        {{ errors.moderator_ids }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories and Sponsor -->
                    <div class="p-6 space-y-6">
                        <div>
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center"
                            >
                                <TagIcon class="h-5 w-5 mr-2 text-gray-600" />
                                Kategoriler ve Sponsor
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Categories -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Kategoriler
                                    </label>

                                    <div
                                        v-if="availableCategories.length > 0"
                                        class="space-y-2 max-h-32 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3"
                                    >
                                        <div
                                            v-for="category in availableCategories"
                                            :key="category.id"
                                            class="flex items-center space-x-3"
                                        >
                                            <input
                                                :id="`category_${category.id}`"
                                                v-model="form.category_ids"
                                                :value="category.id"
                                                type="checkbox"
                                                class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
                                            />
                                            <label
                                                :for="`category_${category.id}`"
                                                class="text-sm text-gray-700 dark:text-gray-300 flex items-center space-x-2"
                                            >
                                                <div
                                                    class="w-3 h-3 rounded-full"
                                                    :style="{
                                                        backgroundColor:
                                                            category.color,
                                                    }"
                                                ></div>
                                                <span>{{ category.name }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        v-else
                                        class="text-center py-4 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 rounded-lg"
                                    >
                                        <TagIcon class="h-8 w-8 mx-auto mb-1" />
                                        <p class="text-sm">
                                            {{
                                                selectedEventId
                                                    ? "Bu etkinlik için kategori yok"
                                                    : "Önce etkinlik seçin"
                                            }}
                                        </p>
                                    </div>

                                    <p
                                        v-if="errors.category_ids"
                                        class="text-sm text-red-600 dark:text-red-400 mt-1"
                                    >
                                        {{ errors.category_ids }}
                                    </p>
                                </div>

                                <!-- Sponsor -->
                                <div>
                                    <label
                                        for="sponsor_id"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Sponsor
                                    </label>
                                    <select
                                        id="sponsor_id"
                                        v-model="form.sponsor_id"
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            errors.sponsor_id
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                    >
                                        <option value="">
                                            Sponsor seçin (opsiyonel)
                                        </option>
                                        <option
                                            v-for="sponsor in sponsors"
                                            :key="sponsor.id"
                                            :value="sponsor.id"
                                        >
                                            {{ sponsor.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.sponsor_id"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.sponsor_id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div
                        class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-700"
                    >
                        <Link
                            :href="
                                sessionId
                                    ? route(
                                          'admin.program-sessions.show',
                                          sessionId
                                      )
                                    : route('admin.program-sessions.index')
                            "
                            class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm"
                        >
                            <ArrowLeftIcon class="h-4 w-4 mr-2" />
                            İptal
                        </Link>

                        <div class="flex space-x-3">
                            <!-- Clear Form -->
                            <button
                                type="button"
                                @click="resetForm"
                                :disabled="processing"
                                class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <ArrowPathIcon class="h-4 w-4 mr-2" />
                                Sıfırla
                            </button>

                            <!-- Update Session -->
                            <button
                                type="submit"
                                :disabled="processing || !canSubmit"
                                class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-gray-700 to-gray-800 text-white text-sm font-medium rounded-lg hover:from-gray-800 hover:to-gray-900 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <template v-if="processing">
                                    <div
                                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
                                    ></div>
                                    Güncelleniyor...
                                </template>
                                <template v-else>
                                    <CheckIcon class="h-4 w-4 mr-2" />
                                    Oturumu Güncelle
                                </template>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Change Summary -->
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
                                Formu değiştirdiniz ancak henüz kaydetmediniz.
                                Sayfadan ayrılmadan önce değişikliklerinizi
                                kaydetmeyi unutmayın.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div
                class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
            >
                <div class="flex items-start">
                    <InformationCircleIcon
                        class="h-5 w-5 text-gray-600 dark:text-gray-400 mt-0.5 flex-shrink-0"
                    />
                    <div class="ml-3">
                        <h4
                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            Program Oturum Düzenleme İpuçları
                        </h4>
                        <div
                            class="mt-2 text-sm text-gray-700 dark:text-gray-200"
                        >
                            <ul class="list-disc list-inside space-y-1">
                                <li>
                                    Etkinlik, gün ve salon değişikliği
                                    yapabilirsiniz
                                </li>
                                <li>
                                    Zaman değişikliği yaparken çakışma kontrolü
                                    yapın
                                </li>
                                <li>
                                    Moderatör seçimini oturum türüne göre yapın
                                </li>
                                <li>Değişiklikleri kaydetmeyi unutmayın</li>
                                <li>
                                    Açıklama kısmında güncel bilgileri tutun
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import {
    ref,
    computed,
    watch,
    onBeforeUnmount,
    reactive,
    onMounted,
} from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import axios from "axios";
import {
    ArrowLeftIcon,
    PencilSquareIcon,
    TagIcon,
    ClockIcon,
    CheckIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon,
    UsersIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/outline";

// Props - Backend'den gelen veriler
const props = defineProps({
    programSession: {
        type: Object,
        required: false,
        default: () => ({
            id: null,
            title: "Oturum",
            description: "",
            start_time: "",
            end_time: "",
            session_type: "",
            moderator_title: "",
            sponsor_id: null,
            is_break: false,
            venue_id: null,
            moderator_ids: [],
            category_ids: [],
            venue: {
                id: null,
                name: "Salon",
                event_day: {
                    id: null,
                    title: "Gün",
                    event: {
                        id: null,
                        name: "Etkinlik",
                        slug: "",
                    },
                },
            },
        }),
    },

    // Form options - Cascade için gerekli tüm veriler
    events: {
        type: Array,
        default: () => [],
    },
    venues: {
        type: Array,
        default: () => [],
    },
    participants: {
        type: Array,
        default: () => [],
    },
    sponsors: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    sessionTypes: {
        type: Array,
        default: () => [
            { value: "main", label: "Ana Oturum" },
            { value: "satellite", label: "Uydu Sempozyumu" },
            { value: "oral_presentation", label: "Sözlü Bildiri" },
            { value: "special", label: "Özel Oturum" },
            { value: "break", label: "Ara" },
        ],
    },
    moderatorTitles: {
        type: Array,
        default: () => [
            { value: "Oturum Başkanı", label: "Oturum Başkanı" },
            { value: "Oturum Başkanları", label: "Oturum Başkanları" },
            { value: "Kolaylaştırıcı", label: "Kolaylaştırıcı" },
            { value: "Moderatör", label: "Moderatör" },
            { value: "Başkan", label: "Başkan" },
        ],
    },

    // Errors
    errors: {
        type: Object,
        default: () => ({}),
    },

    // Backend'den gelen preselected değerler
    selectedEventId: {
        type: [Number, String, null],
        default: null,
    },
    selectedEventDayId: {
        type: [Number, String, null],
        default: null,
    },
    selectedVenueId: {
        type: [Number, String, null],
        default: null,
    },
    eventDays: {
        type: Array,
        default: () => [],
    },
});

// Reactive state for cascade selection - backend'den gelen değerlerle initialize et
const selectedEventId = ref(props.selectedEventId);
const selectedEventDayId = ref(props.selectedEventDayId);
const availableEventDays = ref(props.eventDays || []);
const availableVenues = ref(props.venues || []);
const availableCategories = ref(props.categories || []);
const isLoadingEventDays = ref(false);
const isLoadingVenues = ref(false);
const isLoadingCategories = ref(false);

// Güvenli data access ile tarih formatını da düzeltelim
const safeProgramSession = computed(() => {
    const session = props.programSession || {};

    // Zaman formatını düzelt (2025-06-22T09:00:00.000000Z → 09:00)
    const formatTime = (timeString) => {
        if (!timeString) return "";

        // ISO string ise parse et
        if (timeString.includes("T")) {
            const date = new Date(timeString);
            return date.toLocaleTimeString("tr-TR", {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            });
        }

        // Zaten HH:MM formatındaysa olduğu gibi döndür
        if (timeString.match(/^\d{2}:\d{2}$/)) {
            return timeString;
        }

        return timeString;
    };

    return {
        id: session.id || null,
        title: session.title || "Oturum",
        description: session.description || "",
        start_time: formatTime(session.start_time),
        end_time: formatTime(session.end_time),
        session_type: session.session_type || "",
        moderator_title: session.moderator_title || "",
        sponsor_id: session.sponsor_id || null,
        is_break: Boolean(session.is_break),
        venue_id: session.venue_id || null,
        moderator_ids: Array.isArray(session.moderator_ids)
            ? session.moderator_ids
            : [],
        category_ids: Array.isArray(session.category_ids)
            ? session.category_ids
            : [],
        venue: {
            id: session.venue?.id || null,
            name: session.venue?.name || session.venue?.display_name || "Salon",
            event_day: {
                id: session.venue?.event_day?.id || null,
                title:
                    session.venue?.event_day?.title ||
                    session.venue?.event_day?.display_name ||
                    "Gün",
                event: {
                    id: session.venue?.event_day?.event?.id || null,
                    name: session.venue?.event_day?.event?.name || "Etkinlik",
                    slug: session.venue?.event_day?.event?.slug || "",
                },
            },
        },
    };
});

// Computed properties for template safety
const pageTitle = computed(() => {
    return safeProgramSession.value.title + " - Düzenle";
});

const sessionTitle = computed(() => {
    return safeProgramSession.value.title;
});

const sessionId = computed(() => {
    return safeProgramSession.value.id;
});

const selectedEvent = computed(() => {
    return props.events.find((event) => event.id == selectedEventId.value);
});

const selectedEventDay = computed(() => {
    return availableEventDays.value.find(
        (day) => day.id == selectedEventDayId.value
    );
});

const selectedVenue = computed(() => {
    return availableVenues.value.find((venue) => venue.id == form.venue_id);
});

const canSubmit = computed(() => {
    return (
        selectedEventId.value &&
        selectedEventDayId.value &&
        form.venue_id &&
        form.title.trim() &&
        form.session_type
    );
});

// Simple reactive form - Initialize with empty values first
const form = reactive({
    title: "",
    description: "",
    start_time: "",
    end_time: "",
    session_type: "",
    moderator_title: "",
    sponsor_id: null,
    is_break: false,
    venue_id: null,
    moderator_ids: [],
    category_ids: [],
});

// Function to initialize form with actual data
const initializeForm = () => {
    const sessionData = safeProgramSession.value;

    console.log("🔥 Initializing form with session data:", {
        "props.programSession": props.programSession,
        "sessionData.session_type": sessionData.session_type,
        "sessionData.venue_id": sessionData.venue_id,
        "sessionData.venue": sessionData.venue,
        sessionData: sessionData,
    });

    form.title = sessionData.title;
    form.description = sessionData.description;
    form.start_time = sessionData.start_time;
    form.end_time = sessionData.end_time;
    form.session_type = sessionData.session_type;
    form.moderator_title = sessionData.moderator_title;
    form.sponsor_id = sessionData.sponsor_id;
    form.is_break = sessionData.is_break;
    form.venue_id = sessionData.venue_id;
    form.moderator_ids = [...sessionData.moderator_ids];
    form.category_ids = [...sessionData.category_ids];

    // Also initialize cascade selection values from backend props
    if (props.selectedEventId) {
        selectedEventId.value = props.selectedEventId;
        console.log(
            "✅ Set selectedEventId from props:",
            props.selectedEventId
        );
    }

    if (props.selectedEventDayId) {
        selectedEventDayId.value = props.selectedEventDayId;
        console.log(
            "✅ Set selectedEventDayId from props:",
            props.selectedEventDayId
        );
    }

    if (props.eventDays && props.eventDays.length > 0) {
        availableEventDays.value = props.eventDays;
        console.log(
            "✅ Set availableEventDays from props:",
            props.eventDays.length,
            "days"
        );
    }

    if (props.venues && props.venues.length > 0) {
        availableVenues.value = props.venues;
        console.log(
            "✅ Set availableVenues from props:",
            props.venues.length,
            "venues"
        );
    }

    console.log("✅ Form initialized with session_type:", form.session_type);
    console.log(
        "✅ Cascade values set - eventId:",
        selectedEventId.value,
        "dayId:",
        selectedEventDayId.value,
        "venueId:",
        form.venue_id
    );
};

// Processing state
const processing = ref(false);

// Original form values for reset - will be set in onMounted
let originalForm = {};

// Breadcrumbs
const breadcrumbs = computed(() => {
    const crumbs = [
        { label: "Etkinlikler", href: route("admin.events.index") },
    ];

    if (selectedEvent.value?.slug) {
        crumbs.push({
            label: selectedEvent.value.name,
            href: route("admin.events.show", selectedEvent.value.slug),
        });

        if (selectedEventDay.value?.id) {
            crumbs.push({
                label: selectedEventDay.value.title,
                href: "#",
            });
        }
    }

    crumbs.push(
        {
            label: "Program Oturumları",
            href: route("admin.program-sessions.index"),
        },
        {
            label: sessionTitle.value,
            href: sessionId.value
                ? route("admin.program-sessions.show", sessionId.value)
                : null,
        },
        { label: "Düzenle", href: null }
    );

    return crumbs;
});

// Computed
const hasChanges = computed(() => {
    return Object.keys(originalForm).some((key) => {
        if (Array.isArray(form[key])) {
            return (
                JSON.stringify(form[key]) !== JSON.stringify(originalForm[key])
            );
        }
        return form[key] !== originalForm[key];
    });
});

// Methods
const calculateDuration = (startTime, endTime) => {
    if (!startTime || !endTime) return 0;
    const start = new Date(`1970-01-01T${startTime}:00`);
    const end = new Date(`1970-01-01T${endTime}:00`);
    const diffMs = Math.abs(end - start);
    return Math.ceil(diffMs / (1000 * 60));
};

const formatDate = (dateString) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleDateString("tr-TR", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatTimeRange = (startTime, endTime) => {
    if (!startTime || !endTime) return "";

    // Her iki zamanı da formatla
    const formatSingleTime = (timeString) => {
        if (!timeString) return "";

        // ISO string ise parse et
        if (timeString.includes("T")) {
            const date = new Date(timeString);
            return date.toLocaleTimeString("tr-TR", {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            });
        }

        // Zaten HH:MM formatındaysa olduğu gibi döndür
        if (timeString.match(/^\d{2}:\d{2}$/)) {
            return timeString;
        }

        return timeString;
    };

    const start = formatSingleTime(startTime);
    const end = formatSingleTime(endTime);

    return `${start} - ${end}`;
};

const onEventChange = async () => {
    console.log(
        "🔥 onEventChange called, selectedEventId:",
        selectedEventId.value
    );

    if (!selectedEventId.value) {
        console.log("❌ No event selected, clearing data");
        availableEventDays.value = [];
        availableVenues.value = [];
        availableCategories.value = [];
        selectedEventDayId.value = null;
        form.venue_id = "";
        return;
    }

    isLoadingEventDays.value = true;
    console.log("🔄 Loading event days for event:", selectedEventId.value);

    try {
        const response = await axios.get(
            "/admin/ajax/program-sessions/event-days",
            {
                params: { event_id: selectedEventId.value },
            }
        );

        console.log("✅ Event days response:", response.data);

        if (response.data.success) {
            availableEventDays.value = response.data.data;
            selectedEventDayId.value = null;
            availableVenues.value = [];
            availableCategories.value = [];
            form.venue_id = "";

            console.log(
                "✅ Event days loaded:",
                availableEventDays.value.length,
                "days"
            );

            // Load categories for selected event
            await loadCategoriesForEvent();
        } else {
            console.error(
                "❌ Event days loading failed:",
                response.data.message
            );
        }
    } catch (error) {
        console.error("❌ Error loading event days:", error);

        // Show user-friendly error
        if (error.response) {
            console.error(
                "Server responded with:",
                error.response.status,
                error.response.data
            );
            alert(
                `Hata: ${error.response.status} - ${
                    error.response.data.message || "Sunucu hatası"
                }`
            );
        } else {
            console.error("Network error:", error.message);
            alert("Ağ hatası: " + error.message);
        }
    } finally {
        isLoadingEventDays.value = false;
        console.log("✅ Event days loading finished");
    }
};

const onEventDayChange = async () => {
    console.log(
        "🔥 onEventDayChange called, selectedEventDayId:",
        selectedEventDayId.value
    );

    if (!selectedEventDayId.value) {
        console.log("❌ No event day selected, clearing venues");
        availableVenues.value = [];
        form.venue_id = "";
        return;
    }

    isLoadingVenues.value = true;
    console.log("🔄 Loading venues for event day:", selectedEventDayId.value);

    try {
        const response = await axios.get(
            "/admin/ajax/program-sessions/venues-for-event-day",
            {
                params: { event_day_id: selectedEventDayId.value },
            }
        );

        console.log("✅ Venues response:", response.data);

        if (response.data.success) {
            availableVenues.value = response.data.data;
            form.venue_id = "";

            console.log(
                "✅ Venues loaded:",
                availableVenues.value.length,
                "venues"
            );
        } else {
            console.error("❌ Venues loading failed:", response.data.message);
        }
    } catch (error) {
        console.error("❌ Error loading venues:", error);
        if (error.response) {
            alert(
                `Salon yükleme hatası: ${error.response.status} - ${
                    error.response.data.message || "Sunucu hatası"
                }`
            );
        }
    } finally {
        isLoadingVenues.value = false;
        console.log("✅ Venues loading finished");
    }
};

const loadCategoriesForEvent = async () => {
    if (!selectedEventId.value) {
        console.log("❌ No event selected for categories");
        availableCategories.value = [];
        return;
    }

    isLoadingCategories.value = true;
    console.log("🔄 Loading categories for event:", selectedEventId.value);

    try {
        const response = await axios.get(
            "/admin/ajax/program-sessions/categories-for-event",
            {
                params: { event_id: selectedEventId.value },
            }
        );

        console.log("✅ Categories response:", response.data);

        if (response.data.success) {
            availableCategories.value = response.data.data;
            console.log(
                "✅ Categories loaded:",
                availableCategories.value.length,
                "categories"
            );
        } else {
            console.error(
                "❌ Categories loading failed:",
                response.data.message
            );
        }
    } catch (error) {
        console.error("❌ Error loading categories:", error);
    } finally {
        isLoadingCategories.value = false;
        console.log("✅ Categories loading finished");
    }
};

const updateSession = async () => {
    if (!sessionId.value) {
        console.error("Program session ID missing");
        return;
    }

    processing.value = true;

    try {
        await router.put(
            route("admin.program-sessions.update", sessionId.value),
            form,
            {
                onSuccess: () => {
                    // Update original form values
                    Object.keys(originalForm).forEach((key) => {
                        if (Array.isArray(form[key])) {
                            originalForm[key] = [...form[key]];
                        } else {
                            originalForm[key] = form[key];
                        }
                    });
                    processing.value = false;
                },
                onError: () => {
                    processing.value = false;
                },
                onFinish: () => {
                    processing.value = false;
                },
            }
        );
    } catch (error) {
        console.error("Submit error:", error);
        processing.value = false;
    }
};

const resetForm = () => {
    Object.keys(originalForm).forEach((key) => {
        if (Array.isArray(originalForm[key])) {
            form[key] = [...originalForm[key]];
        } else {
            form[key] = originalForm[key];
        }
    });

    // Reset cascade selections to original values
    selectedEventId.value =
        safeProgramSession.value.venue?.event_day?.event?.id || null;
    selectedEventDayId.value =
        safeProgramSession.value.venue?.event_day?.id || null;

    // Trigger cascade loading if needed
    if (selectedEventId.value) {
        onEventChange();
    }
};

// Initialize cascade selections with current session values
const initializeCascadeSelections = () => {
    console.log("🔄 Initializing cascade selections");

    // Backend'den gelen değerler zaten reactive state'lerde set edilmiş
    console.log("Initial values from backend:", {
        eventId: selectedEventId.value,
        eventDayId: selectedEventDayId.value,
        venueId: form.venue_id,
        availableEventDays: availableEventDays.value.length,
        availableVenues: availableVenues.value.length,
    });

    // Backend'den eventDays ve venues zaten geliyorsa cascade loading'e gerek yok
    if (selectedEventId.value && availableEventDays.value.length === 0) {
        // Sadece event days yüklenmemiş ise yükle
        onEventChange().then(() => {
            if (
                selectedEventDayId.value &&
                availableVenues.value.length === 0
            ) {
                // Sadece venues yüklenmemiş ise yükle
                onEventDayChange();
            }
        });
    } else if (selectedEventDayId.value && availableVenues.value.length === 0) {
        // Event days var ama venues yok ise sadece venues yükle
        onEventDayChange();
    }
};

// Warn user about unsaved changes
let isSubmitting = false;

watch(
    () => form,
    () => {
        // Add beforeunload listener if there are changes
        if (hasChanges.value && !isSubmitting) {
            window.addEventListener("beforeunload", handleBeforeUnload);
        } else {
            window.removeEventListener("beforeunload", handleBeforeUnload);
        }
    },
    { deep: true }
);

const handleBeforeUnload = (e) => {
    if (hasChanges.value && !isSubmitting) {
        e.preventDefault();
        e.returnValue = "";
    }
};

// Remove listener before form submission
watch(
    () => processing,
    (newVal) => {
        if (newVal) {
            isSubmitting = true;
            window.removeEventListener("beforeunload", handleBeforeUnload);
        } else {
            isSubmitting = false;
        }
    }
);

// Watchers for cascade selections
watch(
    selectedEventId,
    (newValue, oldValue) => {
        console.log("👀 selectedEventId changed:", oldValue, "->", newValue);
        if (newValue) {
            onEventChange();
        }
    },
    { immediate: false }
);

watch(
    selectedEventDayId,
    (newValue, oldValue) => {
        console.log("👀 selectedEventDayId changed:", oldValue, "->", newValue);
        if (newValue) {
            onEventDayChange();
        }
    },
    { immediate: false }
);

// Cleanup
onBeforeUnmount(() => {
    window.removeEventListener("beforeunload", handleBeforeUnload);
});

// Lifecycle
onMounted(() => {
    console.log("🚀 ProgramSession Edit page mounted with ALL props:", {
        programSession: props.programSession,
        selectedEventId: props.selectedEventId,
        selectedEventDayId: props.selectedEventDayId,
        selectedVenueId: props.selectedVenueId,
        events: props.events,
        eventDays: props.eventDays,
        venues: props.venues,
        sponsors: props.sponsors,
        participants: props.participants,
        categories: props.categories,
        sessionTypes: props.sessionTypes,
        moderatorTitles: props.moderatorTitles,
        sponsors_count: props.sponsors?.length,
        participants_count: props.participants?.length,
        sessionTypes_count: props.sessionTypes?.length,
    });

    console.log("🎯 Backend ProgramSession Data:", {
        id: props.programSession?.id,
        title: props.programSession?.title,
        session_type: props.programSession?.session_type,
        venue_id: props.programSession?.venue_id,
        start_time: props.programSession?.start_time,
        end_time: props.programSession?.end_time,
        moderator_ids: props.programSession?.moderator_ids,
        sponsor_id: props.programSession?.sponsor_id,
        is_break: props.programSession?.is_break,
    });

    // Initialize form with actual data
    initializeForm();

    console.log("🔥 Form State After Initialization:", {
        title: form.title,
        session_type: form.session_type,
        venue_id: form.venue_id,
        start_time: form.start_time,
        end_time: form.end_time,
        moderator_ids: form.moderator_ids,
        sponsor_id: form.sponsor_id,
        is_break: form.is_break,
    });

    console.log("🎛️ Cascade Selection State After Initialization:", {
        selectedEventId: selectedEventId.value,
        selectedEventDayId: selectedEventDayId.value,
        availableEventDays: availableEventDays.value,
        availableVenues: availableVenues.value,
    });

    // Set original form values after initialization
    originalForm = {
        title: form.title,
        description: form.description,
        start_time: form.start_time,
        end_time: form.end_time,
        session_type: form.session_type,
        moderator_title: form.moderator_title,
        sponsor_id: form.sponsor_id,
        is_break: form.is_break,
        venue_id: form.venue_id,
        moderator_ids: [...form.moderator_ids],
        category_ids: [...form.category_ids],
    };

    // Initialize cascade selections
    initializeCascadeSelections();

    console.log("🔧 After Cascade Initialization:", {
        selectedEventId: selectedEventId.value,
        selectedEventDayId: selectedEventDayId.value,
        availableEventDays: availableEventDays.value?.length,
        availableVenues: availableVenues.value?.length,
    });

    // Enable debug mode in development
    if (import.meta.env.DEV) {
        console.log("🐛 Development mode detected");
    }
});
</script>

<style scoped>
/* Gray theme form styling */
input[type="checkbox"]:checked {
    background-color: rgb(107 114 128);
    border-color: rgb(107 114 128);
}

input[type="checkbox"]:focus {
    --tw-ring-color: rgb(107 114 128);
}

.transition-colors {
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
