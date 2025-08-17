<!-- DataTable.vue - Tam dosya -->
<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
    

    <!-- Table Header with Search -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
      <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Left side: Title and Search -->
        <div class="flex-1 flex items-center space-x-4">
          <div v-if="title" class="flex-shrink-0">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ title }}</h3>
            <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
          </div>
          
          <!-- Search Input -->
          <div v-if="searchable" class="flex-1 max-w-md">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="searchPlaceholder"
                class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                @input="handleSearch"
              />
            </div>
          </div>
        </div>

        <!-- Right side: Actions -->
        <div class="flex items-center space-x-3">
          <slot name="create-button" />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <span class="ml-3 text-gray-600 dark:text-gray-400">Yükleniyor...</span>
    </div>

    <!-- Table Content -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <!-- Table Header -->
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <!-- Column Headers -->
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400"
              :class="column.sortable ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600' : ''"
              @click="column.sortable ? handleSort(column.key) : null"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <div v-if="column.sortable" class="flex flex-col">
                  <ChevronUpIcon
                    class="h-3 w-3"
                    :class="sortColumn === column.key && sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-400'"
                  />
                  <ChevronDownIcon
                    class="h-3 w-3 -mt-1"
                    :class="sortColumn === column.key && sortDirection === 'desc' ? 'text-blue-600' : 'text-gray-400'"
                  />
                </div>
              </div>
            </th>

            <!-- Actions Column -->
            <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
              İşlemler
            </th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
          

          <!-- Data Rows -->
          <tr
            v-for="(row, index) in displayedData"
            :key="getRowKey(row, index)"
            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
          >
            <!-- Data Cells -->
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100"
            >
              <!-- Custom Cell Content -->
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="getNestedValue(row, column.key)"
                :column="column"
                :index="index"
              >
                <!-- Default Cell Content -->
                <span>{{ formatCellValue(getNestedValue(row, column.key), column) }}</span>
              </slot>
            </td>

            <!-- Actions Cell -->
            <td v-if="hasActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <slot name="actions" :row="row" :index="index" />
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-if="displayedData.length === 0">
            <td :colspan="columns.length + (hasActions ? 1 : 0)" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ emptyMessage }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ emptyDescription }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, useSlots } from 'vue'
import { debounce } from 'lodash'
import {
  MagnifyingGlassIcon,
  ChevronDownIcon,
  ChevronUpIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    required: true
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  searchable: {
    type: Boolean,
    default: true
  },
  searchPlaceholder: {
    type: String,
    default: 'Ara...'
  },
  loading: {
    type: Boolean,
    default: false
  },
  emptyMessage: {
    type: String,
    default: 'Kayıt bulunamadı'
  },
  emptyDescription: {
    type: String,
    default: 'Henüz hiç kayıt eklenmemiş.'
  },
  rowKey: {
    type: String,
    default: 'id'
  }
})

// Emits
const emit = defineEmits(['search', 'sort'])

// Get slots
const slots = useSlots()

// State
const searchQuery = ref('')
const sortColumn = ref('')
const sortDirection = ref('asc')

// Computed
const displayedData = computed(() => {
  console.log('=== DataTable displayedData DEBUG ===')
  console.log('props.data:', props.data)
  console.log('typeof props.data:', typeof props.data)
  console.log('Array.isArray(props.data):', Array.isArray(props.data))
  console.log('props.data.length:', props.data?.length)
  console.log('first item:', props.data?.[0])

  if (!Array.isArray(props.data)) {
    console.warn('DataTable: props.data is not an array:', props.data)
    return []
  }

  let filtered = [...props.data]

  // Apply search
  if (searchQuery.value) {
    filtered = filtered.filter(row =>
      props.columns.some(column =>
        String(getNestedValue(row, column.key))
          .toLowerCase()
          .includes(searchQuery.value.toLowerCase())
      )
    )
  }

  // Apply sorting
  if (sortColumn.value) {
    filtered.sort((a, b) => {
      const aVal = getNestedValue(a, sortColumn.value)
      const bVal = getNestedValue(b, sortColumn.value)
      
      if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1
      if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1
      return 0
    })
  }

  console.log('filtered result:', filtered)
  console.log('filtered length:', filtered.length)
  return filtered
})

const hasActions = computed(() => !!slots.actions)

// Methods
const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj)
}

const getRowKey = (row, index) => {
  return getNestedValue(row, props.rowKey) || index
}

const formatCellValue = (value, column) => {
  if (value === null || value === undefined) return '-'
  if (column.formatter) {
    return column.formatter(value)
  }
  return value
}

const handleSearch = debounce(() => {
  emit('search', searchQuery.value)
}, 300)

const handleSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column
    sortDirection.value = 'asc'
  }
  emit('sort', { column: sortColumn.value, direction: sortDirection.value })
}
</script>