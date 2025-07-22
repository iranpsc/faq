<template>
  <span :class="badgeClasses" v-bind="$attrs">
    <!-- Icon slot -->
    <slot name="icon" v-if="$slots.icon" />

    <!-- Badge content -->
    <slot />

    <!-- Dot indicator -->
    <span v-if="dot" :class="dotClasses" />

    <!-- Remove button -->
    <button
      v-if="removable"
      @click="$emit('remove')"
      :class="removeButtonClasses"
      type="button"
    >
      <span class="sr-only">حذف</span>
      <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </button>
  </span>
</template>

<script>
export default {
  name: 'BaseBadge',
  inheritAttrs: false,
  emits: ['remove'],
  props: {
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'outline'].includes(value)
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
    },
    rounded: {
      type: String,
      default: 'full',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
    },
    dot: {
      type: Boolean,
      default: false
    },
    removable: {
      type: Boolean,
      default: false
    },
    outlined: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    badgeClasses() {
      return [
        // Base classes
        'inline-flex items-center font-medium transition-all duration-200',

        // Size classes
        this.sizeClasses,

        // Variant classes
        this.variantClasses,

        // Rounded classes
        this.roundedClasses,

        // Interactive classes
        { 'cursor-pointer hover:opacity-80': this.removable }
      ]
    },
    sizeClasses() {
      const sizes = {
        xs: 'px-2 py-0.5 text-xs gap-1',
        sm: 'px-2.5 py-0.5 text-xs gap-1',
        md: 'px-3 py-1 text-sm gap-1.5',
        lg: 'px-4 py-1.5 text-sm gap-2'
      }
      return sizes[this.size]
    },
    variantClasses() {
      if (this.outlined) {
        const outlinedVariants = {
          default: 'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800',
          primary: 'border border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300 bg-white dark:bg-gray-800',
          secondary: 'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800',
          success: 'border border-green-300 dark:border-green-600 text-green-700 dark:text-green-300 bg-white dark:bg-gray-800',
          danger: 'border border-red-300 dark:border-red-600 text-red-700 dark:text-red-300 bg-white dark:bg-gray-800',
          warning: 'border border-yellow-300 dark:border-yellow-600 text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800',
          info: 'border border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300 bg-white dark:bg-gray-800'
        }
        return outlinedVariants[this.variant]
      }

      const variants = {
        default: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
        primary: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
        secondary: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
        success: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
        danger: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
        warning: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
        info: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'
      }
      return variants[this.variant]
    },
    roundedClasses() {
      const rounded = {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        full: 'rounded-full'
      }
      return rounded[this.rounded]
    },
    dotClasses() {
      const dotColors = {
        default: 'bg-gray-400 dark:bg-gray-500',
        primary: 'bg-blue-400 dark:bg-blue-500',
        secondary: 'bg-gray-400 dark:bg-gray-500',
        success: 'bg-green-400 dark:bg-green-500',
        danger: 'bg-red-400 dark:bg-red-500',
        warning: 'bg-yellow-400 dark:bg-yellow-500',
        info: 'bg-blue-400 dark:bg-blue-500'
      }

      return [
        'w-1.5 h-1.5 rounded-full',
        dotColors[this.variant]
      ]
    },
    removeButtonClasses() {
      const buttonColors = {
        default: 'text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300',
        primary: 'text-blue-400 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300',
        secondary: 'text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300',
        success: 'text-green-400 hover:text-green-600 dark:text-green-400 dark:hover:text-green-300',
        danger: 'text-red-400 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300',
        warning: 'text-yellow-400 hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-300',
        info: 'text-blue-400 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300'
      }

      return [
        'ml-1 p-0.5 rounded-full hover:bg-black hover:bg-opacity-10 dark:hover:bg-white dark:hover:bg-opacity-10 transition-colors',
        buttonColors[this.variant]
      ]
    }
  }
}
</script>

<style scoped>
/* RTL support */
:global(.rtl) .inline-flex {
  direction: rtl;
}

:global(.rtl) .ml-1 {
  margin-left: 0;
  margin-right: 0.25rem;
}

/* Animation for badge appearance */
.inline-flex {
  animation: badge-appear 0.2s ease-out;
}

@keyframes badge-appear {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Hover effects */
.cursor-pointer:hover {
  transform: translateY(-1px);
}

/* Focus styles for remove button */
button:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
  border-radius: 9999px;
}

/* Dark mode focus */
:global(.dark) button:focus {
  box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.5);
}
</style>
