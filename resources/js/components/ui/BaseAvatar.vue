<template>
  <div :class="avatarClasses" v-bind="$attrs">
    <!-- Image -->
    <img
      v-if="src && !imageError"
      :src="src"
      :alt="alt"
      :class="imageClasses"
      @error="handleImageError"
      @load="handleImageLoad"
    />

    <!-- Initials -->
    <span
      v-else-if="initials"
      :class="initialsClasses"
    >
      {{ displayInitials }}
    </span>

    <!-- Icon fallback -->
    <svg
      v-else
      :class="iconClasses"
      fill="currentColor"
      viewBox="0 0 24 24"
    >
      <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>

    <!-- Status indicator -->
    <span v-if="status" :class="statusClasses" />

    <!-- Badge -->
    <div v-if="badge" :class="badgeClasses">
      <slot name="badge">
        <span class="badge-content">{{ badge }}</span>
      </slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseAvatar',
  inheritAttrs: false,
  props: {
    src: {
      type: String,
      default: ''
    },
    alt: {
      type: String,
      default: 'آواتار کاربر'
    },
    name: {
      type: String,
      default: ''
    },
    initials: {
      type: String,
      default: ''
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value)
    },
    shape: {
      type: String,
      default: 'circle',
      validator: (value) => ['circle', 'square', 'rounded'].includes(value)
    },
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'primary', 'secondary', 'success', 'danger', 'warning', 'info'].includes(value)
    },
    status: {
      type: String,
      default: '',
      validator: (value) => ['', 'online', 'offline', 'away', 'busy'].includes(value)
    },
    badge: {
      type: [String, Number],
      default: ''
    },
    clickable: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      imageError: false,
      imageLoaded: false
    }
  },
  computed: {
    avatarClasses() {
      return [
        // Base classes
        'relative inline-flex items-center justify-center overflow-hidden transition-all duration-200',

        // Size classes
        this.sizeClasses,

        // Shape classes
        this.shapeClasses,

        // Variant classes (for background when no image)
        !this.src || this.imageError ? this.variantClasses : '',

        // Interactive classes
        { 'cursor-pointer hover:opacity-80 hover:scale-105': this.clickable }
      ]
    },
    sizeClasses() {
      const sizes = {
        xs: 'w-6 h-6 text-xs',
        sm: 'w-8 h-8 text-sm',
        md: 'w-10 h-10 text-base',
        lg: 'w-12 h-12 text-lg',
        xl: 'w-16 h-16 text-xl',
        '2xl': 'w-20 h-20 text-2xl'
      }
      return sizes[this.size]
    },
    shapeClasses() {
      const shapes = {
        circle: 'rounded-full',
        square: 'rounded-none',
        rounded: 'rounded-lg'
      }
      return shapes[this.shape]
    },
    variantClasses() {
      const variants = {
        default: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
        primary: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300',
        secondary: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
        success: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-300',
        danger: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-300',
        warning: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-300',
        info: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300'
      }
      return variants[this.variant]
    },
    imageClasses() {
      return [
        'w-full h-full object-cover',
        this.shapeClasses
      ]
    },
    initialsClasses() {
      return [
        'font-medium select-none',
        this.getInitialsSize()
      ]
    },
    iconClasses() {
      return [
        'w-3/5 h-3/5'
      ]
    },
    statusClasses() {
      const statusColors = {
        online: 'bg-green-400',
        offline: 'bg-gray-400',
        away: 'bg-yellow-400',
        busy: 'bg-red-400'
      }

      const statusSizes = {
        xs: 'w-1.5 h-1.5',
        sm: 'w-2 h-2',
        md: 'w-2.5 h-2.5',
        lg: 'w-3 h-3',
        xl: 'w-3.5 h-3.5',
        '2xl': 'w-4 h-4'
      }

      return [
        'absolute bottom-0 right-0 rounded-full border-2 border-white dark:border-gray-800',
        statusColors[this.status],
        statusSizes[this.size]
      ]
    },
    badgeClasses() {
      const badgeSizes = {
        xs: 'w-4 h-4 text-xs -top-1 -left-1',
        sm: 'w-5 h-5 text-xs -top-1 -left-1',
        md: 'w-6 h-6 text-xs -top-1 -left-1',
        lg: 'w-7 h-7 text-sm -top-2 -left-2',
        xl: 'w-8 h-8 text-sm -top-2 -left-2',
        '2xl': 'w-9 h-9 text-base -top-2 -left-2'
      }

      return [
        'absolute rounded-full bg-red-500 text-white flex items-center justify-center font-medium min-w-0',
        badgeSizes[this.size]
      ]
    },
    displayInitials() {
      if (this.initials) {
        return this.initials.substring(0, 2).toUpperCase()
      }

      if (this.name) {
        return this.generateInitials(this.name)
      }

      return ''
    }
  },
  watch: {
    src() {
      this.imageError = false
      this.imageLoaded = false
    }
  },
  methods: {
    handleImageError() {
      this.imageError = true
      this.imageLoaded = false
    },
    handleImageLoad() {
      this.imageError = false
      this.imageLoaded = true
    },
    generateInitials(name) {
      if (!name) return ''

      const words = name.trim().split(' ')
      if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase()
      }

      return (words[0][0] + words[words.length - 1][0]).toUpperCase()
    },
    getInitialsSize() {
      const sizes = {
        xs: 'text-xs',
        sm: 'text-xs',
        md: 'text-sm',
        lg: 'text-base',
        xl: 'text-lg',
        '2xl': 'text-xl'
      }
      return sizes[this.size]
    }
  }
}
</script>

<style scoped>
/* Loading animation */
.avatar-loading {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Dark mode loading */
:global(.dark) .avatar-loading {
  background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
  background-size: 200% 100%;
}

/* Badge content styling */
.badge-content {
  line-height: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

/* Status indicator pulse animation */
.status-online {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Hover effects */
.cursor-pointer:hover {
  filter: brightness(0.95);
}

/* Focus styles for clickable avatars */
.cursor-pointer:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

:global(.dark) .cursor-pointer:focus {
  box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.3);
}

/* RTL support */
:global(.rtl) .absolute.right-0 {
  right: auto;
  left: 0;
}

:global(.rtl) .absolute.-left-1,
:global(.rtl) .absolute.-left-2 {
  left: auto;
  right: -0.25rem;
}

:global(.rtl) .absolute.-left-2 {
  right: -0.5rem;
}
</style>
