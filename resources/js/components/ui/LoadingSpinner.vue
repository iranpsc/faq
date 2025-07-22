<template>
  <div :class="containerClasses" v-bind="$attrs">
    <!-- Overlay for full screen -->
    <div v-if="overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl">
        <div class="flex items-center gap-3">
          <div :class="spinnerClasses">
            <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          <span v-if="text" class="text-gray-700 dark:text-gray-300">{{ text }}</span>
        </div>
      </div>
    </div>

    <!-- Inline spinner -->
    <div v-else class="flex items-center gap-2">
      <div :class="spinnerClasses">
        <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
      <span v-if="text" :class="textClasses">{{ text }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoadingSpinner',
  inheritAttrs: false,
  props: {
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => ['primary', 'secondary', 'white'].includes(value)
    },
    text: {
      type: String,
      default: ''
    },
    overlay: {
      type: Boolean,
      default: false
    },
    center: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    containerClasses() {
      return [
        { 'flex justify-center items-center': this.center && !this.overlay }
      ]
    },
    spinnerClasses() {
      return [
        // Size classes
        this.sizeClasses,

        // Color classes
        this.variantClasses
      ]
    },
    sizeClasses() {
      const sizes = {
        xs: 'w-4 h-4',
        sm: 'w-6 h-6',
        md: 'w-8 h-8',
        lg: 'w-12 h-12',
        xl: 'w-16 h-16'
      }
      return sizes[this.size]
    },
    variantClasses() {
      const variants = {
        primary: 'text-blue-600 dark:text-blue-400',
        secondary: 'text-gray-600 dark:text-gray-400',
        white: 'text-white'
      }
      return variants[this.variant]
    },
    textClasses() {
      const textSizes = {
        xs: 'text-xs',
        sm: 'text-sm',
        md: 'text-base',
        lg: 'text-lg',
        xl: 'text-xl'
      }

      return [
        textSizes[this.size],
        'text-gray-600 dark:text-gray-400'
      ]
    }
  }
}
</script>

<style scoped>
/* Custom animation timing */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Pulse animation for overlay */
.fixed.inset-0 {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* RTL support */
:global(.rtl) .flex.items-center.gap-2 {
  direction: rtl;
}
</style>
