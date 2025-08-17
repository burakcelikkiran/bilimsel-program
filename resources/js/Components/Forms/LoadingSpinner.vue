<!-- resources/js/Components/UI/LoadingSpinner.vue -->
<template>
  <div :class="containerClasses">
    <div :class="spinnerClasses">
      <div class="animate-spin rounded-full border-solid" :class="borderClasses"></div>
    </div>
    <div v-if="text" :class="textClasses">
      {{ text }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'white', 'gray'].includes(value)
  },
  text: {
    type: String,
    default: null
  },
  centered: {
    type: Boolean,
    default: false
  },
  overlay: {
    type: Boolean,
    default: false
  }
})

const containerClasses = computed(() => {
  const classes = []
  
  if (props.centered) {
    classes.push('flex items-center justify-center')
  }
  
  if (props.overlay) {
    classes.push('fixed inset-0 bg-white bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 z-50')
  }
  
  if (props.text) {
    classes.push('flex flex-col items-center space-y-2')
  }
  
  return classes.join(' ')
})

const spinnerClasses = computed(() => {
  const sizes = {
    xs: 'h-3 w-3',
    sm: 'h-4 w-4',
    md: 'h-6 w-6',
    lg: 'h-8 w-8',
    xl: 'h-12 w-12'
  }
  
  return sizes[props.size]
})

const borderClasses = computed(() => {
  const variants = {
    primary: 'border-blue-600 border-t-transparent',
    secondary: 'border-gray-600 border-t-transparent',
    white: 'border-white border-t-transparent',
    gray: 'border-gray-400 border-t-transparent'
  }
  
  const sizes = {
    xs: 'border',
    sm: 'border',
    md: 'border-2',
    lg: 'border-2',
    xl: 'border-4'
  }
  
  return `${variants[props.variant]} ${sizes[props.size]} h-full w-full`
})

const textClasses = computed(() => {
  return 'text-sm text-gray-600 dark:text-gray-400'
})
</script>