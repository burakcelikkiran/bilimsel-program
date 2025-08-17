<!-- ModeratorsSection.vue - Reusable Moderators Selection Component -->
<template>
  <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-8">
    <div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
        <UserGroupIcon class="h-5 w-5 mr-2 text-blue-600" />
        Moderatörler
      </h3>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Moderator Title -->
      <div>
        <label for="moderator_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Moderatör Unvanı
        </label>
        <select 
          id="moderator_title"
          v-model="form.moderator_title"
          class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
          :class="errors.moderator_title ? 'border-red-300 focus:ring-red-500' : ''"
        >
          <option value="">Unvan Seçiniz</option>
          <option v-for="title in moderatorTitles" :key="title.value" :value="title.value">
            {{ title.label }}
          </option>
        </select>
        <p v-if="errors.moderator_title" class="mt-2 text-sm text-red-600">{{ errors.moderator_title }}</p>
      </div>

      <!-- Enhanced Moderators Selection with Search -->
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
          Moderatörler
          <span v-if="selectedModerators.length > 0" class="ml-2 text-sm text-blue-600 dark:text-blue-400">
            ({{ selectedModerators.length }} seçildi)
          </span>
        </label>

        <!-- Search Input -->
        <div class="relative mb-4">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Moderatör ara (isim, unvan, kurum)..."
            class="block w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm hover:shadow-md focus:shadow-md"
          />
          <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <button
              @click="clearSearch"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Selected Moderators Display -->
        <div v-if="selectedModerators.length > 0" class="mb-4">
          <div class="flex flex-wrap gap-2">
            <div
              v-for="moderator in selectedModerators"
              :key="moderator.id"
              class="inline-flex items-center px-3 py-1.5 rounded-full text-sm bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border border-blue-200 dark:border-blue-700"
            >
              <span class="font-medium">{{ moderator.full_name }}</span>
              <button
                @click="removeModerator(moderator.id)"
                class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-100"
              >
                <XMarkIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Moderators List -->
        <div class="border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 max-h-64 overflow-y-auto">
          <!-- Quick Actions -->
          <div class="p-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">
                {{ filteredParticipants.length }} moderatör bulundu
              </span>
              <div class="flex space-x-2">
                <button
                  @click="selectAll"
                  :disabled="filteredParticipants.length === 0"
                  class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Tümünü Seç
                </button>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <button
                  @click="clearAll"
                  :disabled="selectedModerators.length === 0"
                  class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Temizle
                </button>
              </div>
            </div>
          </div>

          <!-- Moderators Options -->
          <div v-if="filteredParticipants.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
            <UserGroupIcon class="h-12 w-12 mx-auto mb-2 opacity-50" />
            <p>
              {{ searchQuery ? 'Arama kriterlerine uygun moderatör bulunamadı' : 'Henüz moderatör bulunmuyor' }}
            </p>
          </div>

          <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
            <div
              v-for="participant in filteredParticipants"
              :key="participant.id"
              class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer"
              @click="toggleModerator(participant)"
            >
              <div class="flex items-start space-x-3">
                <input
                  :id="`moderator_${participant.id}`"
                  :checked="isModeratorSelected(participant.id)"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1 cursor-pointer"
                  @click.stop
                  @change="toggleModerator(participant)"
                />
                <div class="flex-1 min-w-0">
                  <label
                    :for="`moderator_${participant.id}`"
                    class="block cursor-pointer"
                  >
                    <div class="font-medium text-gray-900 dark:text-white truncate">
                      {{ participant.full_name }}
                    </div>
                    <div v-if="participant.title" class="text-sm text-gray-600 dark:text-gray-400 truncate">
                      {{ participant.title }}
                    </div>
                    <div v-if="participant.affiliation" class="text-xs text-gray-500 dark:text-gray-500 truncate">
                      {{ participant.affiliation }}
                    </div>
                  </label>
                </div>
                
                <!-- Selection Indicator -->
                <div v-if="isModeratorSelected(participant.id)" class="flex-shrink-0">
                  <CheckIcon class="h-5 w-5 text-blue-600" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <p v-if="errors.moderator_ids" class="text-sm text-red-600 dark:text-red-400 mt-2">
          {{ errors.moderator_ids }}
        </p>

        <!-- Help Text -->
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
          Birden fazla moderatör seçebilirsiniz. Seçilen moderatörler oturum sırasına göre görüntülenecektir.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { 
  UserGroupIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

// Props from parent component
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
  }
})

// Reactive data
const searchQuery = ref('')

// Computed properties
const selectedModerators = computed(() => {
  return props.participants.filter(participant => 
    props.form.moderator_ids.includes(participant.id)
  )
})

const filteredParticipants = computed(() => {
  if (!searchQuery.value) return props.participants
  
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

// Methods
const isModeratorSelected = (participantId) => {
  return props.form.moderator_ids.includes(participantId)
}

const toggleModerator = (participant) => {
  const moderatorIds = [...props.form.moderator_ids]
  const index = moderatorIds.indexOf(participant.id)
  
  if (index > -1) {
    moderatorIds.splice(index, 1)
  } else {
    moderatorIds.push(participant.id)
  }
  
  props.form.moderator_ids = moderatorIds
}

const removeModerator = (participantId) => {
  props.form.moderator_ids = props.form.moderator_ids.filter(id => id !== participantId)
}

const selectAll = () => {
  const allIds = filteredParticipants.value.map(p => p.id)
  const existingIds = props.form.moderator_ids
  const newIds = [...new Set([...existingIds, ...allIds])]
  props.form.moderator_ids = newIds
}

const clearAll = () => {
  props.form.moderator_ids = []
}

const clearSearch = () => {
  searchQuery.value = ''
}

// Watch for search query changes
watch(searchQuery, (newQuery) => {
  // Optional: emit search event to parent if needed
  // emit('search', newQuery)
})
</script>

<style scoped>
/* Enhanced scrollbar for moderators list */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgb(156 163 175) transparent;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgb(156 163 175);
  border-radius: 3px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgb(75 85 99);
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Focus styles for accessibility */
input[type="checkbox"]:focus {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}
</style>