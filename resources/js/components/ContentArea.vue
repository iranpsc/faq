<template>
  <main class="flex-grow p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900/50 overflow-y-auto main-content-container">
    <!-- Hero Section (optional) -->
    <div v-if="$slots.hero" class="w-full max-w-7xl mx-auto mb-6 sm:mb-8 lg:mb-12">
      <slot name="hero" />
    </div>

    <div class="max-w-7xl mx-auto">
      <!-- Categories/Filter Section (optional) -->
      <div v-if="$slots.filters" class="mb-6">
        <slot name="filters" />
      </div>

      <!-- Main Layout -->
      <div :class="layoutClasses">
        <!-- Main Content Area -->
        <div :class="mainContentClasses">
          <slot name="main" />
          <slot /> <!-- Default slot for backward compatibility -->
        </div>

        <!-- Sidebar (optional) -->
        <div v-if="$slots.sidebar && showSidebar" :class="sidebarClasses">
          <slot name="sidebar" />
        </div>
      </div>

      <!-- Footer Section (optional) -->
      <div v-if="$slots.footer" class="mt-12">
        <slot name="footer" />
      </div>
    </div>
  </main>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'ContentArea',
  props: {
    // Layout configuration
    layout: {
      type: String,
      default: 'with-sidebar', // 'with-sidebar', 'full-width', 'centered'
      validator: (value) => ['with-sidebar', 'full-width', 'centered'].includes(value)
    },
    // Sidebar visibility
    showSidebar: {
      type: Boolean,
      default: true
    },
    // Content width ratios for sidebar layout
    mainWidth: {
      type: String,
      default: '3/4', // '1/2', '2/3', '3/4', 'full'
      validator: (value) => ['1/2', '2/3', '3/4', 'full'].includes(value)
    },
    sidebarWidth: {
      type: String,
      default: '1/4', // '1/4', '1/3', '1/2'
      validator: (value) => ['1/4', '1/3', '1/2'].includes(value)
    },
    // Gap between main content and sidebar
    gap: {
      type: String,
      default: '6', // Tailwind spacing values
      validator: (value) => ['2', '4', '6', '8', '12'].includes(value)
    },
    // Container max width
    maxWidth: {
      type: String,
      default: '7xl', // 'none', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'].includes(value)
    },
    // Background variant
    background: {
      type: String,
      default: 'default', // 'default', 'white', 'gray', 'transparent'
      validator: (value) => ['default', 'white', 'gray', 'transparent'].includes(value)
    }
  },
  setup(props) {
    // Compute layout classes based on props
    const layoutClasses = computed(() => {
      const classes = []

      switch (props.layout) {
        case 'with-sidebar':
          classes.push('flex')
          classes.push(`gap-${props.gap}`)
          break
        case 'full-width':
          classes.push('w-full')
          break
        case 'centered':
          classes.push('max-w-4xl mx-auto')
          break
      }

      return classes.join(' ')
    })

    // Compute main content classes
    const mainContentClasses = computed(() => {
      const classes = []

      if (props.layout === 'with-sidebar' && props.showSidebar) {
        classes.push('flex-1')
        classes.push(`md:w-${props.mainWidth}`)
      } else {
        classes.push('w-full')
      }

      return classes.join(' ')
    })

    // Compute sidebar classes
    const sidebarClasses = computed(() => {
      const classes = []

      if (props.layout === 'with-sidebar') {
        classes.push(`w-${props.sidebarWidth}`)
        classes.push('hidden md:block')
      }

      return classes.join(' ')
    })

    // Compute container classes
    const containerClasses = computed(() => {
      const classes = ['w-full mx-auto']

      if (props.maxWidth !== 'none') {
        classes.push(`max-w-${props.maxWidth}`)
      }

      return classes.join(' ')
    })

    // Compute background classes
    const backgroundClasses = computed(() => {
      switch (props.background) {
        case 'white':
          return 'bg-white dark:bg-gray-800'
        case 'gray':
          return 'bg-gray-100 dark:bg-gray-800'
        case 'transparent':
          return 'bg-transparent'
        default:
          return 'bg-gray-50 dark:bg-gray-900/50'
      }
    })

    return {
      layoutClasses,
      mainContentClasses,
      sidebarClasses,
      containerClasses,
      backgroundClasses
    }
  }
}
</script>

<style scoped>
.main-content-container {
  min-height: calc(100vh - 4rem); /* Adjust based on your header height */
}

/* Responsive utilities for better mobile experience */
@media (max-width: 768px) {
  .main-content-container {
    padding: 1rem;
  }
}

/* Smooth transitions for layout changes */
.flex,
.w-full,
.flex-1 {
  transition: all 0.3s ease;
}
</style>
