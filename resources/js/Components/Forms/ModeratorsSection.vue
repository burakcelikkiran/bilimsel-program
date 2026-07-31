<!-- ModeratorsSection.vue - Reusable session participant selection -->
<template>
  <div class="space-y-6 border-t border-slate-200 dark:border-slate-700 pt-8">
    <div>
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center">
        <UserGroupIcon class="h-5 w-5 mr-2 text-blue-600" />
        Oturum Katılımcıları
      </h3>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Moderator Title -->
      <div>
        <label for="moderator_title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
          Oturum Unvanı
        </label>
        <select
          id="moderator_title"
          v-model="form.moderator_title"
          class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
          :class="errors.moderator_title ? 'border-red-300 focus:ring-red-500' : ''"
        >
          <option value="">Unvan Seçiniz</option>
          <option v-for="title in moderatorTitles" :key="title.value" :value="title.value">
            {{ title.label }}
          </option>
        </select>
        <p v-if="errors.moderator_title" class="mt-2 text-sm text-red-600">{{ errors.moderator_title }}</p>
      </div>

      <!-- Participant Selection with Search -->
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
          Katılımcılar
          <span v-if="selectedParticipants.length > 0" class="ml-2 text-sm text-blue-600 dark:text-blue-400">
            ({{ selectedParticipants.length }} seçildi)
          </span>
        </label>

        <div
          v-if="!hasEventSelected"
          class="text-center py-8 text-slate-500 dark:text-slate-400 border border-dashed border-slate-200 dark:border-slate-600 rounded-lg"
        >
          <UserGroupIcon class="h-12 w-12 mx-auto mb-2 opacity-50" />
          <p>Önce etkinlik seçin</p>
        </div>

        <template v-else>
          <!-- Search Input -->
          <div class="relative mb-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-slate-400" />
            </div>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Katılımcı ara (isim, unvan, kurum)..."
              class="block w-full pl-10 pr-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
            />
            <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
              <button
                type="button"
                @click="clearSearch"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
              >
                <XMarkIcon class="h-5 w-5" />
              </button>
            </div>
          </div>

          <!-- Selected Participants -->
          <div v-if="selectedParticipants.length > 0" class="mb-4">
            <div class="flex flex-wrap gap-2">
              <div
                v-for="participant in selectedParticipants"
                :key="participant.id"
                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border border-blue-200 dark:border-blue-700"
              >
                <span class="font-medium">{{ participant.full_name }}</span>
                <button
                  type="button"
                  @click="removeParticipant(participant.id)"
                  class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-100"
                >
                  <XMarkIcon class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Participants List -->
          <div class="border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 max-h-64 overflow-y-auto">
            <div
              v-if="participants.length > 0"
              class="p-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700"
            >
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600 dark:text-slate-400">
                  {{ filteredParticipants.length }} katılımcı
                </span>
                <div class="flex space-x-2">
                  <button
                    type="button"
                    @click="selectAll"
                    :disabled="filteredParticipants.length === 0"
                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Tümünü Seç
                  </button>
                  <span class="text-slate-300 dark:text-slate-600">|</span>
                  <button
                    type="button"
                    @click="clearAll"
                    :disabled="selectedParticipants.length === 0"
                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Temizle
                  </button>
                </div>
              </div>
            </div>

            <div
              v-if="participants.length === 0"
              class="p-6 text-center text-slate-500 dark:text-slate-400"
            >
              <UserGroupIcon class="h-12 w-12 mx-auto mb-2 opacity-50" />
              <p>Bu organizasyonda katılımcı yok</p>
              <Link
                :href="route('admin.participants.create')"
                class="text-blue-600 dark:text-blue-400 hover:underline text-sm mt-2 inline-block"
              >
                Yeni katılımcı ekle
              </Link>
            </div>

            <div
              v-else-if="filteredParticipants.length === 0"
              class="p-6 text-center text-slate-500 dark:text-slate-400"
            >
              <UserGroupIcon class="h-12 w-12 mx-auto mb-2 opacity-50" />
              <p>Arama kriterlerine uygun katılımcı bulunamadı</p>
            </div>

            <div v-else class="divide-y divide-slate-200 dark:divide-slate-700">
              <div
                v-for="participant in filteredParticipants"
                :key="participant.id"
                class="p-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                @click="toggleParticipant(participant)"
              >
                <div class="flex items-start space-x-3">
                  <input
                    :id="`moderator_${participant.id}`"
                    :checked="isParticipantSelected(participant.id)"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded mt-1 cursor-pointer"
                    @click.stop
                    @change="toggleParticipant(participant)"
                  />
                  <div class="flex-1 min-w-0">
                    <label
                      :for="`moderator_${participant.id}`"
                      class="block cursor-pointer"
                    >
                      <div class="font-medium text-slate-900 dark:text-white truncate">
                        {{ participant.full_name }}
                      </div>
                      <div v-if="participant.title" class="text-sm text-slate-600 dark:text-slate-400 truncate">
                        {{ participant.title }}
                      </div>
                      <div v-if="participant.affiliation" class="text-xs text-slate-500 dark:text-slate-500 truncate">
                        {{ participant.affiliation }}
                      </div>
                    </label>
                  </div>

                  <div v-if="isParticipantSelected(participant.id)" class="flex-shrink-0">
                    <CheckIcon class="h-5 w-5 text-blue-600" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
            İstediğiniz kadar katılımcı seçebilirsiniz. Seçilen kişiler oturum sırasına göre görüntülenecektir.
          </p>
        </template>

        <p v-if="errors.moderator_ids" class="text-sm text-red-600 dark:text-red-400 mt-2">
          {{ errors.moderator_ids }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  UserGroupIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  form: {
    type: Object,
    required: true
  },
  participants: {
    type: Array,
    default: () => []
  },
  moderatorTitles: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  hasEventSelected: {
    type: Boolean,
    default: false
  }
})

const searchQuery = ref('')

const selectedParticipants = computed(() => {
  return props.participants.filter(participant =>
    props.form.moderator_ids.includes(participant.id)
  )
})

const filteredParticipants = computed(() => {
  if (!searchQuery.value) {
    return props.participants
  }

  const query = searchQuery.value.toLowerCase()

  return props.participants.filter(participant => {
    const fullName = participant.full_name?.toLowerCase() || ''
    const title = participant.title?.toLowerCase() || ''
    const affiliation = participant.affiliation?.toLowerCase() || ''

    return fullName.includes(query) ||
      title.includes(query) ||
      affiliation.includes(query)
  })
})

const isParticipantSelected = (participantId) => {
  return props.form.moderator_ids.includes(participantId)
}

const toggleParticipant = (participant) => {
  const moderatorIds = [...props.form.moderator_ids]
  const index = moderatorIds.indexOf(participant.id)

  if (index > -1) {
    moderatorIds.splice(index, 1)
  } else {
    moderatorIds.push(participant.id)
  }

  props.form.moderator_ids = moderatorIds
}

const removeParticipant = (participantId) => {
  props.form.moderator_ids = props.form.moderator_ids.filter(id => id !== participantId)
}

const selectAll = () => {
  const allIds = filteredParticipants.value.map(p => p.id)
  const existingIds = props.form.moderator_ids
  props.form.moderator_ids = [...new Set([...existingIds, ...allIds])]
}

const clearAll = () => {
  props.form.moderator_ids = []
}

const clearSearch = () => {
  searchQuery.value = ''
}
</script>

<style scoped>
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgb(148 163 184) transparent;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgb(148 163 184);
  border-radius: 3px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgb(71 85 105);
}

.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

input[type="checkbox"]:focus {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}
</style>
