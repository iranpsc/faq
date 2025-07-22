<template>
  <transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 transform scale-95"
    enter-to-class="opacity-100 transform scale-100"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100 transform scale-100"
    leave-to-class="opacity-0 transform scale-95"
  >
    <div v-if="visible" :class="alertClasses" role="alert">
      <!-- Icon -->
      <div v-if="showIcon" class="flex-shrink-0">
        <slot name="icon">
          <component :is="iconComponent" :class="iconClasses" />
        </slot>
      </div>

      <!-- Content -->
      <div class="flex-1" :class="{ 'mr-3': showIcon && !dismissible, 'mx-3': showIcon && dismissible, 'ml-3': !showIcon && dismissible }">
        <div v-if="title" class="font-medium" :class="titleClasses">
          {{ title }}
        </div>
        <div :class="messageClasses">
          <slot>{{ message }}</slot>
        </div>
      </div>

      <!-- Dismiss button -->
      <div v-if="dismissible" class="flex-shrink-0">
        <button
          @click="dismiss"
          :class="dismissButtonClasses"
          type="button"
        >
          <span class="sr-only">بستن</span>
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'BaseAlert',
  emits: ['dismiss'],
  props: {
    variant: {
      type: String,
      default: 'info',
      validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    title: {
      type: String,
      default: ''
    },
    message: {
      type: String,
      default: ''
    },
    dismissible: {
      type: Boolean,
      default: false
    },
    showIcon: {
      type: Boolean,
      default: true
    },
    autoHide: {
      type: Boolean,
      default: false
    },
    autoHideDelay: {
      type: Number,
      default: 5000
    },
    rounded: {
      type: String,
      default: 'lg',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
    }
  },
  data() {
    return {
      visible: true,
      autoHideTimer: null
    }
  },
  computed: {
    alertClasses() {
      return [
        'flex items-start p-4 transition-all duration-300',
        this.variantClasses,
        this.roundedClasses
      ]
    },
    variantClasses() {
      const variants = {
        success: 'bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800',
        error: 'bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800',
        warning: 'bg-yellow-50 border border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800',
        info: 'bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-800'
      }
      return variants[this.variant]
    },
    roundedClasses() {
      const rounded = {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl'
      }
      return rounded[this.rounded]
    },
    iconComponent() {
      const icons = {
        success: 'CheckCircleIcon',
        error: 'XCircleIcon',
        warning: 'ExclamationTriangleIcon',
        info: 'InformationCircleIcon'
      }
      return icons[this.variant]
    },
    iconClasses() {
      const iconColors = {
        success: 'text-green-400 dark:text-green-300',
        error: 'text-red-400 dark:text-red-300',
        warning: 'text-yellow-400 dark:text-yellow-300',
        info: 'text-blue-400 dark:text-blue-300'
      }
      return ['h-5 w-5', iconColors[this.variant]]
    },
    titleClasses() {
      const titleColors = {
        success: 'text-green-800 dark:text-green-200',
        error: 'text-red-800 dark:text-red-200',
        warning: 'text-yellow-800 dark:text-yellow-200',
        info: 'text-blue-800 dark:text-blue-200'
      }
      return titleColors[this.variant]
    },
    messageClasses() {
      const messageColors = {
        success: 'text-green-700 dark:text-green-300',
        error: 'text-red-700 dark:text-red-300',
        warning: 'text-yellow-700 dark:text-yellow-300',
        info: 'text-blue-700 dark:text-blue-300'
      }
      return [messageColors[this.variant], { 'mt-1': this.title }]
    },
    dismissButtonClasses() {
      const buttonColors = {
        success: 'text-green-400 hover:text-green-600 dark:text-green-300 dark:hover:text-green-500',
        error: 'text-red-400 hover:text-red-600 dark:text-red-300 dark:hover:text-red-500',
        warning: 'text-yellow-400 hover:text-yellow-600 dark:text-yellow-300 dark:hover:text-yellow-500',
        info: 'text-blue-400 hover:text-blue-600 dark:text-blue-300 dark:hover:text-blue-500'
      }
      return [
        'inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors',
        buttonColors[this.variant]
      ]
    }
  },
  mounted() {
    if (this.autoHide) {
      this.autoHideTimer = setTimeout(() => {
        this.dismiss()
      }, this.autoHideDelay)
    }
  },
  beforeUnmount() {
    if (this.autoHideTimer) {
      clearTimeout(this.autoHideTimer)
    }
  },
  methods: {
    dismiss() {
      this.visible = false
      this.$emit('dismiss')
      if (this.autoHideTimer) {
        clearTimeout(this.autoHideTimer)
      }
    }
  },
  components: {
    CheckCircleIcon: {
      template: `
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
      `
    },
    XCircleIcon: {
      template: `
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
      `
    },
    ExclamationTriangleIcon: {
      template: `
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      `
    },
    InformationCircleIcon: {
      template: `
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
      `
    }
  }
}
</script>

<style scoped>
/* RTL support */
:global(.rtl) {
  direction: rtl;
}

:global(.rtl) .flex-shrink-0:first-child {
  margin-left: 0.75rem;
  margin-right: 0;
}

:global(.rtl) .flex-shrink-0:last-child {
  margin-right: 0.75rem;
  margin-left: 0;
}
</style>
