<!--
📁 File Structure:
resources/js/Components/Forms/FormColorPicker.vue

📁 Usage:
<FormColorPicker 
  v-model="form.brand_color"
  label="Brand Color"
  placeholder="Select a color"
  :presets="brandColors"
  show-palette
  show-recent
  allow-transparency
  required
  :error-message="form.errors.brand_color"
  help-text="This color will be used for event branding"
/>

📁 Dependencies:
- @heroicons/vue/24/outline
- @heroicons/vue/24/solid
-->
<template>
  <div class="form-group">
    <!-- Label -->
    <label 
      v-if="label" 
      :for="colorPickerId" 
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Color Input Container -->
    <div class="relative">
      <!-- Color Display and Input -->
      <div class="flex items-center space-x-2">
        <!-- Color Preview -->
        <button
          type="button"
          class="color-preview"
          :class="{
            'border-red-300 dark:border-red-600': hasError,
            'border-green-300 dark:border-green-600': hasSuccess && !hasError,
            'opacity-50 cursor-not-allowed': disabled
          }"
          :style="{ backgroundColor: displayColor }"
          :disabled="disabled"
          @click="togglePicker"
        >
          <!-- Transparency Pattern Background -->
          <div 
            v-if="!displayColor || (allowTransparency && transparency < 1)"
            class="absolute inset-0 opacity-30"
            style="background-image: repeating-conic-gradient(#808080 0% 25%, transparent 0% 50%) 50% / 8px 8px;"
          />
          
          <!-- No Color Indicator -->
          <div v-if="!displayColor" class="absolute inset-0 flex items-center justify-center">
            <XMarkIcon class="h-4 w-4 text-gray-400" />
          </div>
        </button>

        <!-- Hex Input -->
        <div class="flex-1">
          <input
            :id="colorPickerId"
            ref="hexInputRef"
            v-model="hexValue"
            type="text"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :required="required"
            class="hex-input"
            :class="[
              inputClasses,
              {
                'border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500': hasError,
                'border-green-300 dark:border-green-600 focus:border-green-500 focus:ring-green-500': hasSuccess && !hasError,
                'opacity-50 cursor-not-allowed': disabled
              }
            ]"
            @focus="handleFocus"
            @blur="handleBlur"
            @input="handleHexInput"
            @keydown="handleKeydown"
          />
        </div>

        <!-- Eyedropper Button (if supported) -->
        <button
          v-if="showEyedropper && supportsEyedropper"
          type="button"
          class="eyedropper-btn"
          :disabled="disabled"
          @click="openEyedropper"
        >
          <EyeDropperIcon class="h-4 w-4" />
        </button>

        <!-- Clear Button -->
        <button
          v-if="clearable && selectedColor"
          type="button"
          class="clear-btn"
          :disabled="disabled"
          @click="clearColor"
        >
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>

      <!-- Color Picker Dropdown -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="isOpen"
          class="absolute z-50 mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 p-4"
          :class="dropdownPosition === 'up' ? 'bottom-full mb-2' : 'top-full'"
          style="min-width: 280px;"
        >
          <!-- Color Canvas -->
          <div v-if="showPalette" class="mb-4">
            <div class="relative">
              <!-- Saturation/Lightness Canvas -->
              <canvas
                ref="colorCanvasRef"
                class="color-canvas cursor-crosshair"
                width="240"
                height="160"
                @mousedown="startColorDrag"
                @mousemove="handleColorDrag"
                @mouseup="stopColorDrag"
                @mouseleave="stopColorDrag"
              />
              
              <!-- Color Picker Cursor -->
              <div
                class="color-cursor"
                :style="{
                  left: `${saturation * 240}px`,
                  top: `${(1 - lightness) * 160}px`
                }"
              />
            </div>

            <!-- Hue Slider -->
            <div class="mt-3">
              <canvas
                ref="hueCanvasRef"
                class="hue-slider cursor-pointer"
                width="240"
                height="12"
                @mousedown="startHueDrag"
                @mousemove="handleHueDrag"
                @mouseup="stopHueDrag"
                @mouseleave="stopHueDrag"
              />
              <div
                class="hue-cursor"
                :style="{ left: `${(hue / 360) * 240}px` }"
              />
            </div>

            <!-- Alpha Slider (if transparency allowed) -->
            <div v-if="allowTransparency" class="mt-3">
              <canvas
                ref="alphaCanvasRef"
                class="alpha-slider cursor-pointer"
                width="240"
                height="12"
                @mousedown="startAlphaDrag"
                @mousemove="handleAlphaDrag"
                @mouseup="stopAlphaDrag"
                @mouseleave="stopAlphaDrag"
              />
              <div
                class="alpha-cursor"
                :style="{ left: `${transparency * 240}px` }"
              />
            </div>
          </div>

          <!-- Color Values -->
          <div class="mb-4 grid grid-cols-2 gap-3 text-sm">
            <!-- HEX -->
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">HEX</label>
              <input
                v-model="hexValue"
                type="text"
                class="color-value-input"
                placeholder="#000000"
                @input="handleHexInput"
              />
            </div>

            <!-- RGB -->
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">RGB</label>
              <input
                v-model="rgbValue"
                type="text"
                class="color-value-input"
                placeholder="255, 255, 255"
                @input="handleRgbInput"
              />
            </div>

            <!-- HSL -->
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">HSL</label>
              <input
                v-model="hslValue"
                type="text"
                class="color-value-input"
                placeholder="0, 100%, 50%"
                @input="handleHslInput"
              />
            </div>

            <!-- Alpha -->
            <div v-if="allowTransparency">
              <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Alpha</label>
              <input
                v-model.number="transparency"
                type="number"
                min="0"
                max="1"
                step="0.01"
                class="color-value-input"
                @input="updateFromAlpha"
              />
            </div>
          </div>

          <!-- Preset Colors -->
          <div v-if="presetColors.length > 0" class="mb-4">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Presets</label>
            <div class="grid grid-cols-8 gap-1">
              <button
                v-for="(color, index) in presetColors"
                :key="`preset-${index}`"
                type="button"
                class="preset-color"
                :class="{ 'ring-2 ring-blue-500': color === selectedColor }"
                :style="{ backgroundColor: color }"
                :title="color"
                @click="selectPresetColor(color)"
              />
            </div>
          </div>

          <!-- Recent Colors -->
          <div v-if="showRecent && recentColors.length > 0" class="mb-4">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Recent</label>
            <div class="grid grid-cols-8 gap-1">
              <button
                v-for="(color, index) in recentColors"
                :key="`recent-${index}`"
                type="button"
                class="preset-color"
                :class="{ 'ring-2 ring-blue-500': color === selectedColor }"
                :style="{ backgroundColor: color }"
                :title="color"
                @click="selectPresetColor(color)"
              />
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-600">
            <button
              type="button"
              class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
              @click="closePicker"
            >
              Cancel
            </button>
            <button
              type="button"
              class="px-4 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
              @click="confirmColor"
            >
              OK
            </button>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Help Text -->
    <p v-if="helpText && !hasError" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ helpText }}
    </p>

    <!-- Error Message -->
    <p v-if="hasError" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ errorMessage }}
    </p>

    <!-- Success Message -->
    <p v-if="hasSuccess && !hasError && successMessage" class="mt-1 text-sm text-green-600 dark:text-green-400">
      {{ successMessage }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

// Mock EyeDropper icon
const EyeDropperIcon = { template: '<div class="w-4 h-4 bg-gray-400 rounded"></div>' }

// Props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: '#000000'
  },
  helpText: {
    type: String,
    default: ''
  },
  errorMessage: {
    type: String,
    default: ''
  },
  successMessage: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: true
  },
  showPalette: {
    type: Boolean,
    default: true
  },
  showRecent: {
    type: Boolean,
    default: true
  },
  showEyedropper: {
    type: Boolean,
    default: true
  },
  allowTransparency: {
    type: Boolean,
    default: false
  },
  presets: {
    type: Array,
    default: () => []
  },
  format: {
    type: String,
    default: 'hex',
    validator: (value) => ['hex', 'rgb', 'hsl'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'change',
  'focus',
  'blur',
  'open',
  'close'
])

// Refs
const hexInputRef = ref(null)
const colorCanvasRef = ref(null)
const hueCanvasRef = ref(null)
const alphaCanvasRef = ref(null)
const isOpen = ref(false)
const dropdownPosition = ref('down')

// Color state
const hue = ref(0)
const saturation = ref(1)
const lightness = ref(0.5)
const transparency = ref(1)

// Drag state
const isDraggingColor = ref(false)
const isDraggingHue = ref(false)
const isDraggingAlpha = ref(false)

// Recent colors storage
const recentColors = ref([])
const maxRecentColors = 16

// Computed
const colorPickerId = computed(() => `colorpicker-${Math.random().toString(36).substr(2, 9)}`)

const selectedColor = computed(() => props.modelValue)

const displayColor = computed(() => {
  if (!selectedColor.value) return ''
  
  if (props.allowTransparency && transparency.value < 1) {
    const rgb = hslToRgb(hue.value, saturation.value, lightness.value)
    return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${transparency.value})`
  }
  
  return selectedColor.value
})

const hexValue = computed({
  get() {
    if (!selectedColor.value) return ''
    return selectedColor.value.startsWith('#') ? selectedColor.value : `#${selectedColor.value}`
  },
  set(value) {
    if (isValidHexColor(value)) {
      updateFromHex(value)
    }
  }
})

const rgbValue = computed({
  get() {
    if (!selectedColor.value) return ''
    const rgb = hexToRgb(selectedColor.value)
    return rgb ? `${rgb.r}, ${rgb.g}, ${rgb.b}` : ''
  },
  set(value) {
    updateFromRgb(value)
  }
})

const hslValue = computed({
  get() {
    return `${Math.round(hue.value)}, ${Math.round(saturation.value * 100)}%, ${Math.round(lightness.value * 100)}%`
  },
  set(value) {
    updateFromHsl(value)
  }
})

const hasError = computed(() => Boolean(props.errorMessage))
const hasSuccess = computed(() => Boolean(props.successMessage))

const inputClasses = computed(() => {
  const baseClasses = [
    'block', 'w-full', 'rounded-md', 'border', 'shadow-sm', 'transition-colors', 'duration-200',
    'placeholder-gray-400', 'dark:placeholder-gray-500',
    'bg-white', 'dark:bg-gray-900',
    'text-gray-900', 'dark:text-gray-100',
    'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-0',
    'focus:border-blue-500', 'focus:ring-blue-500',
    'font-mono', 'text-sm'
  ]

  const sizeClasses = {
    sm: 'px-3 py-1.5',
    md: 'px-3 py-2',
    lg: 'px-4 py-3'
  }

  return [
    ...baseClasses,
    ...sizeClasses[props.size].split(' '),
    'border-gray-300', 'dark:border-gray-600'
  ]
})

const presetColors = computed(() => {
  const defaultPresets = [
    '#FF0000', '#FF8000', '#FFFF00', '#80FF00', '#00FF00', '#00FF80', '#00FFFF', '#0080FF',
    '#0000FF', '#8000FF', '#FF00FF', '#FF0080', '#000000', '#404040', '#808080', '#C0C0C0'
  ]
  
  return props.presets.length > 0 ? props.presets : defaultPresets
})

const supportsEyedropper = computed(() => {
  return 'EyeDropper' in window
})

// Methods
const togglePicker = () => {
  if (props.disabled) return
  
  if (isOpen.value) {
    closePicker()
  } else {
    openPicker()
  }
}

const openPicker = () => {
  isOpen.value = true
  emit('open')
  
  // Initialize color values from current selection
  if (selectedColor.value) {
    updateHslFromHex(selectedColor.value)
  }
  
  nextTick(() => {
    calculateDropdownPosition()
    drawColorCanvas()
    drawHueSlider()
    if (props.allowTransparency) {
      drawAlphaSlider()
    }
  })
}

const closePicker = () => {
  isOpen.value = false
  emit('close')
}

const confirmColor = () => {
  const color = getCurrentColor()
  if (color) {
    addToRecentColors(color)
    emit('update:modelValue', color)
    emit('change', color)
  }
  closePicker()
}

const clearColor = () => {
  emit('update:modelValue', '')
  emit('change', '')
}

const getCurrentColor = () => {
  const rgb = hslToRgb(hue.value, saturation.value, lightness.value)
  
  if (props.format === 'rgb') {
    return props.allowTransparency && transparency.value < 1
      ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${transparency.value})`
      : `rgb(${rgb.r}, ${rgb.g}, ${rgb.b})`
  }
  
  if (props.format === 'hsl') {
    return props.allowTransparency && transparency.value < 1
      ? `hsla(${Math.round(hue.value)}, ${Math.round(saturation.value * 100)}%, ${Math.round(lightness.value * 100)}%, ${transparency.value})`
      : `hsl(${Math.round(hue.value)}, ${Math.round(saturation.value * 100)}%, ${Math.round(lightness.value * 100)}%)`
  }
  
  // Default to hex
  return rgbToHex(rgb.r, rgb.g, rgb.b)
}

const selectPresetColor = (color) => {
  updateFromHex(color)
  updateCanvas()
}

const addToRecentColors = (color) => {
  if (!color || recentColors.value.includes(color)) return
  
  recentColors.value.unshift(color)
  if (recentColors.value.length > maxRecentColors) {
    recentColors.value = recentColors.value.slice(0, maxRecentColors)
  }
  
  // Store in localStorage
  try {
    localStorage.setItem('colorpicker-recent', JSON.stringify(recentColors.value))
  } catch (e) {
    // Ignore localStorage errors
  }
}

const loadRecentColors = () => {
  try {
    const stored = localStorage.getItem('colorpicker-recent')
    if (stored) {
      recentColors.value = JSON.parse(stored)
    }
  } catch (e) {
    // Ignore localStorage errors
  }
}

// Color conversion utilities
const hslToRgb = (h, s, l) => {
  const c = (1 - Math.abs(2 * l - 1)) * s
  const x = c * (1 - Math.abs((h / 60) % 2 - 1))
  const m = l - c / 2
  
  let r, g, b
  
  if (h >= 0 && h < 60) {
    r = c; g = x; b = 0
  } else if (h >= 60 && h < 120) {
    r = x; g = c; b = 0
  } else if (h >= 120 && h < 180) {
    r = 0; g = c; b = x
  } else if (h >= 180 && h < 240) {
    r = 0; g = x; b = c
  } else if (h >= 240 && h < 300) {
    r = x; g = 0; b = c
  } else {
    r = c; g = 0; b = x
  }
  
  return {
    r: Math.round((r + m) * 255),
    g: Math.round((g + m) * 255),
    b: Math.round((b + m) * 255)
  }
}

const rgbToHex = (r, g, b) => {
  const toHex = (n) => {
    const hex = n.toString(16)
    return hex.length === 1 ? '0' + hex : hex
  }
  return `#${toHex(r)}${toHex(g)}${toHex(b)}`
}

const hexToRgb = (hex) => {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null
}

const rgbToHsl = (r, g, b) => {
  r /= 255; g /= 255; b /= 255
  const max = Math.max(r, g, b)
  const min = Math.min(r, g, b)
  const diff = max - min
  const sum = max + min
  const l = sum / 2
  
  if (diff === 0) {
    return { h: 0, s: 0, l }
  }
  
  const s = l > 0.5 ? diff / (2 - sum) : diff / sum
  
  let h
  switch (max) {
    case r: h = ((g - b) / diff + (g < b ? 6 : 0)) / 6; break
    case g: h = ((b - r) / diff + 2) / 6; break
    case b: h = ((r - g) / diff + 4) / 6; break
  }
  
  return { h: h * 360, s, l }
}

const isValidHexColor = (hex) => {
  return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex)
}

// Canvas drawing
const drawColorCanvas = () => {
  const canvas = colorCanvasRef.value
  if (!canvas) return
  
  const ctx = canvas.getContext('2d')
  const { width, height } = canvas
  
  // Create gradient
  const gradient1 = ctx.createLinearGradient(0, 0, width, 0)
  gradient1.addColorStop(0, '#ffffff')
  gradient1.addColorStop(1, `hsl(${hue.value}, 100%, 50%)`)
  
  ctx.fillStyle = gradient1
  ctx.fillRect(0, 0, width, height)
  
  const gradient2 = ctx.createLinearGradient(0, 0, 0, height)
  gradient2.addColorStop(0, 'rgba(0, 0, 0, 0)')
  gradient2.addColorStop(1, '#000000')
  
  ctx.fillStyle = gradient2
  ctx.fillRect(0, 0, width, height)
}

const drawHueSlider = () => {
  const canvas = hueCanvasRef.value
  if (!canvas) return
  
  const ctx = canvas.getContext('2d')
  const { width, height } = canvas
  
  const gradient = ctx.createLinearGradient(0, 0, width, 0)
  
  for (let i = 0; i <= 360; i += 30) {
    gradient.addColorStop(i / 360, `hsl(${i}, 100%, 50%)`)
  }
  
  ctx.fillStyle = gradient
  ctx.fillRect(0, 0, width, height)
}

const drawAlphaSlider = () => {
  const canvas = alphaCanvasRef.value
  if (!canvas) return
  
  const ctx = canvas.getContext('2d')
  const { width, height } = canvas
  
  // Checkerboard pattern
  const checkerSize = 8
  for (let x = 0; x < width; x += checkerSize) {
    for (let y = 0; y < height; y += checkerSize) {
      ctx.fillStyle = ((x / checkerSize) + (y / checkerSize)) % 2 ? '#ffffff' : '#cccccc'
      ctx.fillRect(x, y, checkerSize, checkerSize)
    }
  }
  
  // Alpha gradient
  const rgb = hslToRgb(hue.value, saturation.value, lightness.value)
  const gradient = ctx.createLinearGradient(0, 0, width, 0)
  gradient.addColorStop(0, `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0)`)
  gradient.addColorStop(1, `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 1)`)
  
  ctx.fillStyle = gradient
  ctx.fillRect(0, 0, width, height)
}

// Event handlers
const startColorDrag = (event) => {
  isDraggingColor.value = true
  handleColorDrag(event)
}

const handleColorDrag = (event) => {
  if (!isDraggingColor.value) return
  
  const canvas = colorCanvasRef.value
  if (!canvas) return
  
  const rect = canvas.getBoundingClientRect()
  const x = Math.max(0, Math.min(canvas.width, event.clientX - rect.left))
  const y = Math.max(0, Math.min(canvas.height, event.clientY - rect.top))
  
  saturation.value = x / canvas.width
  lightness.value = 1 - (y / canvas.height)
}

const stopColorDrag = () => {
  isDraggingColor.value = false
}

const startHueDrag = (event) => {
  isDraggingHue.value = true
  handleHueDrag(event)
}

const handleHueDrag = (event) => {
  if (!isDraggingHue.value) return
  
  const canvas = hueCanvasRef.value
  if (!canvas) return
  
  const rect = canvas.getBoundingClientRect()
  const x = Math.max(0, Math.min(canvas.width, event.clientX - rect.left))
  
  hue.value = (x / canvas.width) * 360
  updateCanvas()
}

const stopHueDrag = () => {
  isDraggingHue.value = false
}

const startAlphaDrag = (event) => {
  isDraggingAlpha.value = true
  handleAlphaDrag(event)
}

const handleAlphaDrag = (event) => {
  if (!isDraggingAlpha.value) return
  
  const canvas = alphaCanvasRef.value
  if (!canvas) return
  
  const rect = canvas.getBoundingClientRect()
  const x = Math.max(0, Math.min(canvas.width, event.clientX - rect.left))
  
  transparency.value = x / canvas.width
  updateCanvas()
}

const stopAlphaDrag = () => {
  isDraggingAlpha.value = false
}

const updateCanvas = () => {
  nextTick(() => {
    drawColorCanvas()
    if (props.allowTransparency) {
      drawAlphaSlider()
    }
  })
}

const updateFromHex = (hex) => {
  if (!isValidHexColor(hex)) return
  
  const rgb = hexToRgb(hex)
  if (rgb) {
    const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b)
    hue.value = hsl.h
    saturation.value = hsl.s
    lightness.value = hsl.l
    updateCanvas()
  }
}

const updateHslFromHex = (hex) => {
  if (!isValidHexColor(hex)) return
  
  const rgb = hexToRgb(hex)
  if (rgb) {
    const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b)
    hue.value = hsl.h
    saturation.value = hsl.s
    lightness.value = hsl.l
  }
}

const updateFromRgb = (rgbString) => {
  const match = rgbString.match(/(\d+),\s*(\d+),\s*(\d+)/)
  if (match) {
    const [, r, g, b] = match.map(Number)
    const hsl = rgbToHsl(r, g, b)
    hue.value = hsl.h
    saturation.value = hsl.s
    lightness.value = hsl.l
    updateCanvas()
  }
}

const updateFromHsl = (hslString) => {
  const match = hslString.match(/(\d+),\s*(\d+)%,\s*(\d+)%/)
  if (match) {
    const [, h, s, l] = match
    hue.value = Number(h)
    saturation.value = Number(s) / 100
    lightness.value = Number(l) / 100
    updateCanvas()
  }
}

const updateFromAlpha = () => {
  updateCanvas()
}

const handleHexInput = (event) => {
  hexValue.value = event.target.value
}

const handleRgbInput = (event) => {
  rgbValue.value = event.target.value
}

const handleHslInput = (event) => {
  hslValue.value = event.target.value
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleBlur = (event) => {
  emit('blur', event)
}

const handleKeydown = (event) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    openPicker()
  } else if (event.key === 'Escape') {
    closePicker()
  }
}

const openEyedropper = async () => {
  if (!supportsEyedropper.value) return
  
  try {
    const eyeDropper = new EyeDropper()
    const result = await eyeDropper.open()
    updateFromHex(result.sRGBHex)
    updateCanvas()
  } catch (e) {
    // User cancelled or error occurred
  }
}

const calculateDropdownPosition = () => {
  if (!hexInputRef.value) return
  
  const rect = hexInputRef.value.getBoundingClientRect()
  const spaceBelow = window.innerHeight - rect.bottom
  const spaceAbove = rect.top
  
  dropdownPosition.value = spaceBelow < 400 && spaceAbove > spaceBelow ? 'up' : 'down'
}

const handleClickOutside = (event) => {
  if (hexInputRef.value && !hexInputRef.value.contains(event.target) && !event.target.closest('.color-canvas')) {
    closePicker()
  }
}

// Lifecycle
onMounted(() => {
  loadRecentColors()
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('mouseup', () => {
    stopColorDrag()
    stopHueDrag()
    stopAlphaDrag()
  })
  window.addEventListener('resize', calculateDropdownPosition)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', calculateDropdownPosition)
})

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  if (newValue && isValidHexColor(newValue)) {
    updateHslFromHex(newValue)
    updateCanvas()
  }
}, { immediate: true })

// Expose methods
defineExpose({
  open: openPicker,
  close: closePicker,
  focus: () => hexInputRef.value?.focus()
})
</script>

<style scoped>
.color-preview {
  @apply relative w-10 h-10 rounded-md border-2 border-gray-300 dark:border-gray-600 cursor-pointer transition-colors duration-200 hover:border-gray-400 dark:hover:border-gray-500;
}

.hex-input {
  text-transform: uppercase;
}

.eyedropper-btn,
.clear-btn {
  @apply p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors;
}

.color-canvas {
  @apply rounded border border-gray-200 dark:border-gray-600;
}

.color-cursor {
  @apply absolute w-3 h-3 border-2 border-white rounded-full shadow-md pointer-events-none transform -translate-x-1/2 -translate-y-1/2;
}

.hue-slider,
.alpha-slider {
  @apply rounded border border-gray-200 dark:border-gray-600 relative;
}

.hue-cursor,
.alpha-cursor {
  @apply absolute top-0 w-2 h-full bg-white border border-gray-300 dark:border-gray-600 rounded-sm shadow-sm pointer-events-none transform -translate-x-1/2;
}

.color-value-input {
  @apply w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500;
}

.preset-color {
  @apply w-6 h-6 rounded border border-gray-200 dark:border-gray-600 cursor-pointer transition-all duration-150 hover:scale-110;
}

.preset-color:focus {
  @apply outline-none ring-2 ring-blue-500 ring-opacity-50;
}

/* Disable text selection on canvas */
.color-canvas,
.hue-slider,
.alpha-slider {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}
</style>