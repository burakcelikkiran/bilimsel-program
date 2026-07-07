<!-- Admin/ProgramSessions/Create.vue - Event Cascade Selection Version -->
<template>
    <AdminLayout
        page-title="Yeni Program Oturumu"
        :page-subtitle="
            selectedEventDay
                ? `${selectedEvent?.name} - ${selectedEventDay.title}`
                : 'Program oturumu oluştur'
        "
        :breadcrumbs="breadcrumbs"
    >
        <Head title="Yeni Program Oturumu" />

        <div class="w-full space-y-8">
            <!-- Header Section -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
            >
                <div
                    class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-slate-800 to-slate-900"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div
                                class="h-10 w-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center"
                            >
                                <SpeakerWaveIcon class="h-6 w-6 text-white" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-white">
                                Program Oturum Bilgileri
                            </h3>
                            <p class="text-sm text-slate-300">
                                Yeni program oturum bilgilerini giriniz
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
            >
                <form
                    @submit.prevent="createSession"
                    class="divide-y divide-slate-200 dark:divide-slate-700"
                >
                    <!-- Event Context Selection -->
                    <div class="p-6 space-y-6 bg-slate-50 dark:bg-slate-800/50">
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 dark:text-white mb-4"
                            >
                                Etkinlik Seçimi
                            </h3>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Event Selection -->
                                <div>
                                    <label
                                        for="event_id"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                    >
                                        Etkinlik *
                                    </label>
                                    <select
                                        id="event_id"
                                        v-model="selectedEventId"
                                        @change="onEventChange"
                                        required
                                        class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
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
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
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
                                        class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
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
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
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
                                        class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="
                                            form.errors.venue_id
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
                                        v-if="form.errors.venue_id"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.venue_id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="p-6 space-y-6">
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 dark:text-white mb-4"
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
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                    >
                                        Oturum Başlığı *
                                    </label>
                                    <input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        required
                                        class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-500 focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            form.errors.title
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                        placeholder="Örn: Ana Oturum 1"
                                    />
                                    <p
                                        v-if="form.errors.title"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.title }}
                                    </p>
                                </div>

                                <!-- Session Type -->
                                <div class="lg:col-span-1 xl:col-span-1">
                                    <label
                                        for="session_type"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                    >
                                        Oturum Türü *
                                    </label>
                                    <select
                                        id="session_type"
                                        v-model="form.session_type"
                                        required
                                        class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            form.errors.session_type
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
                                        v-if="form.errors.session_type"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.session_type }}
                                    </p>
                                </div>

                                <!-- Quick Actions -->
                                <div class="lg:col-span-1 xl:col-span-1">
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                    >
                                        Hızlı İşlemler
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input
                                                v-model="form.is_break"
                                                type="checkbox"
                                                class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded transition-colors"
                                            />
                                            <span
                                                class="ml-3 text-sm text-slate-700 dark:text-slate-300"
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
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                >
                                    Başlangıç Saati *
                                </label>
                                <div class="relative">
                                    <ClockIcon
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-slate-400"
                                    />
                                    <input
                                        id="start_time"
                                        v-model="form.start_time"
                                        type="time"
                                        required
                                        class="block w-full pl-10 pr-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            form.errors.start_time
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                    />
                                </div>
                                <p
                                    v-if="form.errors.start_time"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.start_time }}
                                </p>
                            </div>

                            <!-- End Time -->
                            <div>
                                <label
                                    for="end_time"
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                >
                                    Bitiş Saati *
                                </label>
                                <div class="relative">
                                    <ClockIcon
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-slate-400"
                                    />
                                    <input
                                        id="end_time"
                                        v-model="form.end_time"
                                        type="time"
                                        required
                                        class="block w-full pl-10 pr-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            form.errors.end_time
                                                ? 'border-red-300 focus:ring-red-500'
                                                : ''
                                        "
                                        :min="form.start_time"
                                    />
                                </div>
                                <p
                                    v-if="form.errors.end_time"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.end_time }}
                                </p>
                            </div>

                            <!-- Duration Display -->
                            <div v-if="form.start_time && form.end_time">
                                <label
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                >
                                    Süre
                                </label>
                                <div
                                    class="px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg"
                                >
                                    <div
                                        class="flex items-center text-slate-700 dark:text-slate-300"
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
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                            >
                                Oturum Açıklaması
                            </label>
                            <div class="relative">
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="6"
                                    class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-500 focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md resize-none"
                                    :class="
                                        form.errors.description
                                            ? 'border-red-300 focus:ring-red-500'
                                            : ''
                                    "
                                    placeholder="Oturum hakkında detaylı bilgi verin. Bu açıklama katılımcılara gösterilecektir..."
                                ></textarea>
                                <div
                                    class="absolute bottom-3 right-3 text-xs text-slate-400"
                                >
                                    {{ (form.description || "").length }}/2000
                                </div>
                            </div>
                            <p
                                v-if="form.errors.description"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Moderators Section -->
                    <div class="p-6 space-y-6">
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 dark:text-white mb-4"
                            >
                                Moderatörler
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Moderator Title -->
                                <div class="md:col-span-2">
                                    <label
                                        for="moderator_title"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                    >
                                        Moderatör Unvanı
                                    </label>
                                    <select
                                        id="moderator_title"
                                        v-model="form.moderator_title"
                                        class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            form.errors.moderator_title
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
                                        v-if="form.errors.moderator_title"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.moderator_title }}
                                    </p>
                                </div>

                                <!-- Moderators Selection -->
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                    >
                                        Moderatörler
                                    </label>

                                    <div
                                        v-if="participants.length > 0"
                                        class="space-y-2 max-h-48 overflow-y-auto border border-slate-200 dark:border-slate-600 rounded-lg p-3"
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
                                                class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                            />
                                            <label
                                                :for="`moderator_${participant.id}`"
                                                class="text-sm text-slate-700 dark:text-slate-300 flex-1"
                                            >
                                                {{ participant.full_name }}
                                                <span
                                                    v-if="participant.title"
                                                    class="text-slate-500"
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
                                                    class="text-slate-400 text-xs block"
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
                                        class="text-center py-8 text-slate-500 dark:text-slate-400"
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
                                            class="text-slate-600 dark:text-slate-400 hover:underline text-sm"
                                        >
                                            Yeni katılımcı ekle
                                        </Link>
                                    </div>

                                    <p
                                        v-if="form.errors.moderator_ids"
                                        class="text-sm text-red-600 dark:text-red-400 mt-1"
                                    >
                                        {{ form.errors.moderator_ids }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories and Sponsor -->
                    <div class="p-6 space-y-6">
                        <div>
                            <h3
                                class="text-lg font-semibold text-slate-900 dark:text-white mb-4"
                            >
                                Kategoriler ve Sponsor
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Categories -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                    >
                                        Kategoriler
                                    </label>

                                    <div
                                        v-if="availableCategories.length > 0"
                                        class="space-y-2 max-h-32 overflow-y-auto border border-slate-200 dark:border-slate-600 rounded-lg p-3"
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
                                                class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                            />
                                            <label
                                                :for="`category_${category.id}`"
                                                class="text-sm text-slate-700 dark:text-slate-300 flex items-center space-x-2"
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
                                        class="text-center py-4 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600 rounded-lg"
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
                                        v-if="form.errors.category_ids"
                                        class="text-sm text-red-600 dark:text-red-400 mt-1"
                                    >
                                        {{ form.errors.category_ids }}
                                    </p>
                                </div>

                                <!-- Sponsor -->
                                <div>
                                    <label
                                        for="sponsor_id"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                    >
                                        Sponsor
                                    </label>
                                    <select
                                        id="sponsor_id"
                                        v-model="form.sponsor_id"
                                        class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
                                        :class="
                                            form.errors.sponsor_id
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
                                        v-if="form.errors.sponsor_id"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.sponsor_id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div
                        class="flex items-center justify-between p-6 border-t border-slate-200 dark:border-slate-700"
                    >
                        <Link
                            :href="
                                selectedEvent
                                    ? route(
                                          'admin.events.show',
                                          selectedEvent.slug
                                      )
                                    : route('admin.program-sessions.index')
                            "
                            class="inline-flex items-center px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors shadow-sm"
                        >
                            <ArrowLeftIcon class="h-4 w-4 mr-2" />
                            İptal
                        </Link>

                        <div class="flex space-x-3">
                            <!-- Clear Form -->
                            <button
                                type="button"
                                @click="resetForm"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <DocumentIcon class="h-4 w-4 mr-2" />
                                Temizle
                            </button>

                            <!-- Create Session -->
                            <button
                                type="submit"
                                :disabled="form.processing || !canSubmit"
                                class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-slate-700 to-slate-800 text-white text-sm font-medium rounded-lg hover:from-slate-800 hover:to-slate-900 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <template v-if="form.processing">
                                    <div
                                        class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"
                                    ></div>
                                    Oluşturuluyor...
                                </template>
                                <template v-else>
                                    <SpeakerWaveIcon class="h-4 w-4 mr-2" />
                                    Oturum Oluştur
                                </template>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Quick Tips -->
            <div
                class="bg-slate-50 dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700"
            >
                <div class="flex items-start">
                    <InformationCircleIcon
                        class="h-5 w-5 text-slate-600 dark:text-slate-400 mt-0.5 flex-shrink-0"
                    />
                    <div class="ml-3">
                        <h4
                            class="text-sm font-medium text-slate-900 dark:text-slate-100"
                        >
                            Program Oturum Oluşturma İpuçları
                        </h4>
                        <div
                            class="mt-2 text-sm text-slate-700 dark:text-slate-200"
                        >
                            <ul class="list-disc list-inside space-y-1">
                                <li>
                                    Önce etkinlik ve gün seçimi yapın, sonra
                                    salon seçin
                                </li>
                                <li>
                                    Oturum başlığını açık ve anlaşılır tutun
                                </li>
                                <li>
                                    Moderatör seçimini oturum türüne göre yapın
                                </li>
                                <li>
                                    Zaman aralıklarının çakışmadığından emin
                                    olun
                                </li>
                                <li>
                                    Açıklama kısmında oturum içeriğini belirtin
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
import { ref, computed, onMounted, watch } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    ArrowLeftIcon,
    UsersIcon,
    TagIcon,
    SpeakerWaveIcon,
    ClockIcon,
    DocumentIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
    selectedEvent: {
        type: Object,
        default: null,
    },
    eventDays: {
        type: Array,
        default: () => [],
    },
    selectedEventDay: {
        type: Object,
        default: null,
    },
    venues: {
        type: Array,
        default: () => [],
    },
    participants: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    sponsors: {
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
    selectedEventId: {
        type: [Number, String],
        default: null,
    },
    selectedEventDayId: {
        type: [Number, String],
        default: null,
    },
    selectedVenueId: {
        type: [Number, String],
        default: null,
    },
});

// Reactive state
const selectedEventId = ref(props.selectedEventId);
const selectedEventDayId = ref(props.selectedEventDayId);
const availableEventDays = ref(props.eventDays || []);
const availableVenues = ref(props.venues || []);
const availableCategories = ref(props.categories || []);
const isLoadingEventDays = ref(false);
const isLoadingVenues = ref(false);
const isLoadingCategories = ref(false);

// Form
const form = useForm({
    title: "",
    description: "",
    venue_id: props.selectedVenueId || "",
    start_time: "",
    end_time: "",
    session_type: "",
    moderator_title: "",
    sponsor_id: null,
    is_break: false,
    moderator_ids: [],
    category_ids: [],
});

// Computed
const selectedEvent = computed(() => {
    return (
        props.events.find((event) => event.id == selectedEventId.value) ||
        props.selectedEvent
    );
});

const selectedEventDay = computed(() => {
    return (
        availableEventDays.value.find(
            (day) => day.id == selectedEventDayId.value
        ) || props.selectedEventDay
    );
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

const breadcrumbs = computed(() => {
    const crumbs = [
        { label: "Etkinlikler", href: route("admin.events.index") },
    ];

    if (selectedEvent.value) {
        crumbs.push({
            label: selectedEvent.value.name,
            href: route("admin.events.show", selectedEvent.value.slug),
        });
    }

    if (selectedEventDay.value) {
        crumbs.push({ label: selectedEventDay.value.title, href: "#" });
    }

    crumbs.push(
        {
            label: "Program Oturumları",
            href: route("admin.program-sessions.index"),
        },
        { label: "Yeni Oturum", href: null }
    );

    return crumbs;
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

const cascadeOnly = [
    "events",
    "selectedEvent",
    "eventDays",
    "selectedEventDay",
    "venues",
    "categories",
    "selectedEventId",
    "selectedEventDayId",
    "selectedVenueId",
];

const reloadCascade = (params = {}, loadingRef = null) => {
    if (loadingRef) {
        loadingRef.value = true;
    }

    router.get(route("admin.program-sessions.create"), params, {
        only: cascadeOnly,
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            if (loadingRef) {
                loadingRef.value = false;
            }
        },
    });
};

watch(
    () => props.eventDays,
    (val) => {
        availableEventDays.value = val ?? [];
    }
);

watch(
    () => props.venues,
    (val) => {
        availableVenues.value = val ?? [];
    }
);

watch(
    () => props.categories,
    (val) => {
        availableCategories.value = val ?? [];
    }
);

watch(
    () => props.selectedEventDayId,
    (val) => {
        if (val !== undefined && val !== null) {
            selectedEventDayId.value = val;
        }
    }
);

const onEventChange = () => {
    if (!selectedEventId.value) {
        availableEventDays.value = [];
        availableVenues.value = [];
        availableCategories.value = [];
        selectedEventDayId.value = null;
        form.venue_id = "";
        return;
    }

    selectedEventDayId.value = null;
    form.venue_id = "";
    reloadCascade({ event_id: selectedEventId.value }, isLoadingEventDays);
};

const onEventDayChange = () => {
    if (!selectedEventDayId.value) {
        availableVenues.value = [];
        form.venue_id = "";
        return;
    }

    form.venue_id = "";
    reloadCascade(
        {
            event_id: selectedEventId.value,
            event_day_id: selectedEventDayId.value,
        },
        isLoadingVenues
    );
};

const createSession = () => {
    console.log("🚀 Creating session with data:", form.data());

    form.post(route("admin.program-sessions.store"), {
        onSuccess: () => {
            console.log("✅ Session created successfully");
        },
        onError: (errors) => {
            console.error("❌ Session creation failed:", errors);
        },
    });
};

const resetForm = () => {
    console.log("🔄 Resetting form");
    form.reset();
    selectedEventId.value = null;
    selectedEventDayId.value = null;
    availableEventDays.value = [];
    availableVenues.value = [];
    availableCategories.value = [];
};

// Lifecycle
onMounted(() => {
    console.log("🚀 Component mounted");
    console.log("📋 Props:", {
        events: props.events?.length,
        selectedEventId: props.selectedEventId,
        selectedEventDayId: props.selectedEventDayId,
        selectedVenueId: props.selectedVenueId,
        eventDays: props.eventDays?.length,
        venues: props.venues?.length,
    });

    // If event context is pre-selected from props, trigger loading
    if (props.selectedEventId) {
        console.log(
            "🔄 Pre-loading for selectedEventId:",
            props.selectedEventId
        );
        selectedEventId.value = props.selectedEventId;
        // onEventChange will be called by the watcher
    }
    if (props.selectedEventDayId) {
        console.log(
            "🔄 Pre-loading for selectedEventDayId:",
            props.selectedEventDayId
        );
        selectedEventDayId.value = props.selectedEventDayId;
        // onEventDayChange will be called by the watcher
    }
    if (props.selectedVenueId) {
        console.log(
            "🔄 Pre-loading for selectedVenueId:",
            props.selectedVenueId
        );
        form.venue_id = props.selectedVenueId;
    }

    // Enable debug mode in development
    if (import.meta.env.DEV) {
        console.log("🐛 Development mode detected");
    }
});

// Watchers
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

// Watch for props changes
watch(
    () => props.events,
    (newEvents) => {
        console.log("👀 props.events changed:", newEvents?.length);
    },
    { immediate: true }
);

watch(
    () => props.eventDays,
    (newEventDays) => {
        console.log("👀 props.eventDays changed:", newEventDays?.length);
        if (newEventDays && newEventDays.length > 0) {
            availableEventDays.value = newEventDays;
        }
    },
    { immediate: true }
);

watch(
    () => props.venues,
    (newVenues) => {
        console.log("👀 props.venues changed:", newVenues?.length);
        if (newVenues && newVenues.length > 0) {
            availableVenues.value = newVenues;
        }
    },
    { immediate: true }
);
</script>

<style scoped>
/* Gray theme form styling */
input[type="checkbox"]:checked {
    background-color: rgb(107 114 128);
    border-color: rgb(107 114 128);
}

input[type="checkbox"]:focus {
    ring-color: rgb(107 114 128);
}

.transition-colors {
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
