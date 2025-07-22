<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="buttonClasses"
    @click="$emit('click', $event)"
    v-bind="$attrs"
  >
    <!-- Loading spinner -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-3 h-5 w-5"
      :class="{ 'text-white': variant !== 'ghost' && variant !== 'outline' }"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>

    <!-- Icon slot -->
    <slot name="icon" v-if="!loading" />

    <!-- Button text -->
    <span v-if="$slots.default">
      <slot />
    </span>
  </button>
</template>

<script>
export default {
  name: 'BaseButton',
  inheritAttrs: false,
  emits: ['click'],
  props: {
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'outline', 'ghost'].includes(value)
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    type: {
      type: String,
      default: 'button'
    },
    disabled: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    rounded: {
      type: String,
      default: 'md',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
    },
    block: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    buttonClasses() {
      return [
        // Base classes
        'inline-flex items-center justify-center font-medium transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800',

        // Size classes
        this.sizeClasses,

        // Variant classes
        this.variantClasses,

        // Rounded classes
        this.roundedClasses,

        // Block class
        { 'w-full': this.block },

        // Disabled state
        { 'opacity-50 cursor-not-allowed': this.disabled || this.loading },

        // Hover effects
        { 'transform hover:-translate-y-0.5': !this.disabled && !this.loading && this.variant !== 'ghost' }
      ]
    },
    sizeClasses() {
      const sizes = {
        xs: 'px-2.5 py-1.5 text-xs',
        sm: 'px-3 py-2 text-sm',
        md: 'px-4 py-2.5 text-sm',
        lg: 'px-6 py-3 text-base',
        xl: 'px-8 py-4 text-lg'
      }
      return sizes[this.size]
    },
    variantClasses() {
      const variants = {
        primary: 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white focus:ring-blue-500 dark:focus:ring-blue-400',
        secondary: 'bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white focus:ring-gray-500 dark:focus:ring-gray-400',
        success: 'bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white focus:ring-green-500 dark:focus:ring-green-400',
        danger: 'bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white focus:ring-red-500 dark:focus:ring-red-400',
        warning: 'bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-600 dark:hover:bg-yellow-700 text-white focus:ring-yellow-500 dark:focus:ring-yellow-400',
        info: 'bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500 text-white focus:ring-blue-400 dark:focus:ring-blue-300',
        outline: 'border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-gray-500 dark:focus:ring-gray-400',
        ghost: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-gray-500 dark:focus:ring-gray-400'
      }
      return variants[this.variant]
    },
    roundedClasses() {
      const rounded = {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-lg',
        lg: 'rounded-xl',
        xl: 'rounded-2xl',
        full: 'rounded-full'
      }
      return rounded[this.rounded]
    }
  }
}
</script>

<style scoped>
/* RTL support */
.rtl {
  direction: rtl;
}

/* Icon margin adjustments for RTL */
.rtl svg:first-child {
  margin-left: 0.75rem;
  margin-right: -0.25rem;
}

.rtl svg:last-child {
  margin-right: 0.75rem;
  margin-left: -0.25rem;
}
</style>
