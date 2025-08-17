<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
    <div class="p-5">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <component
            v-if="icon"
            :is="icon"
            :class="[
              'h-6 w-6',
              iconColor || 'text-gray-400'
            ]"
            aria-hidden="true"
          />
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              {{ title }}
            </dt>
            <dd>
              <div class="flex items-baseline">
                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                  {{ value }}
                </div>
                <div
                  v-if="change"
                  :class="[
                    'ml-2 flex items-baseline text-sm font-semibold',
                    changeType === 'increase' ? 'text-green-600' : 'text-red-600'
                  ]"
                >
                  <component
                    :is="changeType === 'increase' ? 'ArrowUpIcon' : 'ArrowDownIcon'"
                    class="self-center flex-shrink-0 h-4 w-4"
                    aria-hidden="true"
                  />
                  <span class="sr-only">
                    {{ changeType === 'increase' ? 'Artış' : 'Azalış' }}
                  </span>
                  {{ change }}
                </div>
              </div>
            </dd>
          </dl>
        </div>
      </div>
    </div>
    <div v-if="description" class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
      <div class="text-sm">
        <span class="text-gray-500 dark:text-gray-400">{{ description }}</span>
        
          v-if="link"
          :href="link"
          class="font-medium text-blue-700 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 ml-2"
        >
          Detayları gör
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowUpIcon, ArrowDownIcon } from '@heroicons/vue/24/solid'

defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: Object,
    default: null
  },
  iconColor: {
    type: String,
    default: null
  },
  change: {
    type: String,
    default: null
  },
  changeType: {
    type: String,
    default: 'increase',
    validator: (value) => ['increase', 'decrease'].includes(value)
  },
  description: {
    type: String,
    default: null
  },
  link: {
    type: String,
    default: null
  }
})
</script>