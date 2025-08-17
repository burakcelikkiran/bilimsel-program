<!-- resources/js/Components/ApplicationLogo.vue -->
<template>
  <svg viewBox="0 0 200 48" fill="none" xmlns="http://www.w3.org/2000/svg" :class="$attrs.class">
    <!-- Background Circle -->
    <circle cx="24" cy="24" r="22" :fill="circleColor" stroke="currentColor" stroke-width="2"/>
    
    <!-- Calendar Icon -->
    <g transform="translate(8, 8)">
      <!-- Calendar Base -->
      <rect x="2" y="6" width="28" height="20" rx="2" :fill="iconColor" stroke="currentColor" stroke-width="1.5"/>
      
      <!-- Calendar Header -->
      <rect x="2" y="6" width="28" height="6" rx="2" :fill="headerColor"/>
      
      <!-- Calendar Rings -->
      <rect x="8" y="2" width="2" height="8" rx="1" :fill="iconColor"/>
      <rect x="22" y="2" width="2" height="8" rx="1" :fill="iconColor"/>
      
      <!-- Calendar Grid -->
      <g :stroke="gridColor" stroke-width="1" opacity="0.6">
        <line x1="8" y1="16" x2="26" y2="16"/>
        <line x1="8" y1="20" x2="26" y2="20"/>
        <line x1="12" y1="12" x2="12" y2="24"/>
        <line x1="18" y1="12" x2="18" y2="24"/>
        <line x1="24" y1="12" x2="24" y2="24"/>
      </g>
      
      <!-- Active Dots -->
      <circle cx="15" cy="18" r="1.5" :fill="accentColor"/>
      <circle cx="21" cy="22" r="1.5" :fill="accentColor"/>
    </g>
    
    <!-- Text -->
    <g v-if="showText">
      <text x="58" y="20" :fill="textColor" font-family="Inter, system-ui, sans-serif" font-size="14" font-weight="700">
        EPS
      </text>
      <text x="58" y="34" :fill="subtextColor" font-family="Inter, system-ui, sans-serif" font-size="10" font-weight="500">
        Event Program System
      </text>
    </g>
    
    <!-- Alternative: Just Letters for compact version -->
    <g v-if="!showText && showLetters">
      <text x="16" y="30" :fill="letterColor" font-family="Inter, system-ui, sans-serif" font-size="12" font-weight="700" text-anchor="middle">
        EPS
      </text>
    </g>
  </svg>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'default', // 'default', 'white', 'compact', 'dark'
    validator: (value) => ['default', 'white', 'compact', 'dark'].includes(value)
  },
  showText: {
    type: Boolean,
    default: true
  },
  showLetters: {
    type: Boolean,
    default: false
  }
})

// Color schemes based on variant
const circleColor = computed(() => {
  const colors = {
    default: '#3B82F6', // blue-500
    white: '#FFFFFF',
    compact: '#1E40AF', // blue-700
    dark: '#1F2937' // gray-800
  }
  return colors[props.variant] || colors.default
})

const iconColor = computed(() => {
  const colors = {
    default: '#FFFFFF',
    white: '#3B82F6',
    compact: '#FFFFFF',
    dark: '#FFFFFF'
  }
  return colors[props.variant] || colors.default
})

const headerColor = computed(() => {
  const colors = {
    default: 'rgba(255, 255, 255, 0.9)',
    white: '#EFF6FF', // blue-50
    compact: 'rgba(255, 255, 255, 0.9)',
    dark: 'rgba(255, 255, 255, 0.9)'
  }
  return colors[props.variant] || colors.default
})

const gridColor = computed(() => {
  const colors = {
    default: '#FFFFFF',
    white: '#3B82F6',
    compact: '#FFFFFF',
    dark: '#FFFFFF'
  }
  return colors[props.variant] || colors.default
})

const accentColor = computed(() => {
  const colors = {
    default: '#EF4444', // red-500
    white: '#DC2626', // red-600
    compact: '#EF4444',
    dark: '#F87171' // red-400
  }
  return colors[props.variant] || colors.default
})

const textColor = computed(() => {
  const colors = {
    default: '#1F2937', // gray-800
    white: '#FFFFFF',
    compact: '#1F2937',
    dark: '#FFFFFF'
  }
  return colors[props.variant] || colors.default
})

const subtextColor = computed(() => {
  const colors = {
    default: '#6B7280', // gray-500
    white: 'rgba(255, 255, 255, 0.8)',
    compact: '#6B7280',
    dark: '#D1D5DB' // gray-300
  }
  return colors[props.variant] || colors.default
})

const letterColor = computed(() => {
  return iconColor.value
})
</script>

<style scoped>
/* Smooth transitions for color changes */
svg * {
  transition: fill 0.3s ease, stroke 0.3s ease;
}

/* Hover effect */
svg:hover circle:first-child {
  transform: scale(1.05);
  transform-origin: center;
  transition: transform 0.2s ease;
}
</style>