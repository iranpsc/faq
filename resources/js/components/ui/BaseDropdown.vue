<template>
  <div class="relative" ref="dropdown">
    <!-- Trigger -->
    <div @click="toggle" ref="trigger">
      <slot name="trigger" :open="isOpen" :toggle="toggle">
        <BaseButton
          :variant="variant"
          :size="size"
          :class="{ 'ring-2 ring-blue-500 dark:ring-blue-400': isOpen }"
        >
          <template #icon v-if="!$slots.trigger">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </template>
          {{ label }}
        </BaseButton>
      </slot>
    </div>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        :class="menuClasses"
        @click="handleMenuClick"
      >
        <slot :close="close" :isOpen="isOpen" />
      </div>
    </transition>
  </div>
</template>

<script>
import { BaseButton } from './index.js'

export default {
  name: 'BaseDropdown',
  components: {
    BaseButton
  },
  props: {
    label: {
      type: String,
      default: 'منو'
    },
    placement: {
      type: String,
      default: 'bottom-start',
      validator: (value) => [
        'top-start', 'top-end', 'bottom-start', 'bottom-end',
        'left-start', 'left-end', 'right-start', 'right-end'
      ].includes(value)
    },
    variant: {
      type: String,
      default: 'outline'
    },
    size: {
      type: String,
      default: 'md'
    },
    closeOnClick: {
      type: Boolean,
      default: true
    },
    disabled: {
      type: Boolean,
      default: false
    },
    width: {
      type: String,
      default: 'auto',
      validator: (value) => ['auto', 'trigger', 'sm', 'md', 'lg', 'xl'].includes(value)
    }
  },
  emits: ['open', 'close'],
  data() {
    return {
      isOpen: false
    }
  },
  computed: {
    menuClasses() {
      return [
        // Base classes
        'absolute z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1',

        // Placement classes
        this.placementClasses,

        // Width classes
        this.widthClasses
      ]
    },
    placementClasses() {
      const placements = {
        'top-start': 'bottom-full mb-2 right-0',
        'top-end': 'bottom-full mb-2 left-0',
        'bottom-start': 'top-full mt-2 right-0',
        'bottom-end': 'top-full mt-2 left-0',
        'left-start': 'right-full mr-2 top-0',
        'left-end': 'right-full mr-2 bottom-0',
        'right-start': 'left-full ml-2 top-0',
        'right-end': 'left-full ml-2 bottom-0'
      }
      return placements[this.placement]
    },
    widthClasses() {
      const widths = {
        auto: 'w-auto min-w-48',
        trigger: 'w-full',
        sm: 'w-32',
        md: 'w-48',
        lg: 'w-64',
        xl: 'w-80'
      }
      return widths[this.width]
    }
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside)
    document.addEventListener('keydown', this.handleKeydown)
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside)
    document.removeEventListener('keydown', this.handleKeydown)
  },
  methods: {
    toggle() {
      if (this.disabled) return

      if (this.isOpen) {
        this.close()
      } else {
        this.open()
      }
    },
    open() {
      if (this.disabled) return

      this.isOpen = true
      this.$emit('open')

      // Focus first menu item
      this.$nextTick(() => {
        const firstItem = this.$el.querySelector('[role="menuitem"], a, button')
        if (firstItem) {
          firstItem.focus()
        }
      })
    },
    close() {
      this.isOpen = false
      this.$emit('close')
    },
    handleClickOutside(event) {
      if (!this.$refs.dropdown.contains(event.target)) {
        this.close()
      }
    },
    handleKeydown(event) {
      if (!this.isOpen) return

      if (event.key === 'Escape') {
        this.close()
        this.$refs.trigger.focus()
      }
    },
    handleMenuClick(event) {
      if (this.closeOnClick) {
        // Check if clicked element is a menu item
        const menuItem = event.target.closest('[role="menuitem"], a, button')
        if (menuItem) {
          this.close()
        }
      }
    }
  }
}
</script>

<style scoped>
/* Ensure dropdown is above other elements */
.z-50 {
  z-index: 50;
}

/* RTL support */
:global(.rtl) .right-0 {
  right: 0;
  left: auto;
}

:global(.rtl) .left-0 {
  left: 0;
  right: auto;
}

:global(.rtl) .right-full {
  right: 100%;
  left: auto;
}

:global(.rtl) .left-full {
  left: 100%;
  right: auto;
}

/* Focus styles for menu items */
:deep([role="menuitem"]:focus),
:deep(a:focus),
:deep(button:focus) {
  outline: none;
  background-color: #f3f4f6;
}

:global(.dark) :deep([role="menuitem"]:focus),
:global(.dark) :deep(a:focus),
:global(.dark) :deep(button:focus) {
  background-color: #374151;
}

/* Menu item padding */
:deep([role="menuitem"]),
:deep(.dropdown-item) {
  padding: 0.5rem 1rem;
  display: block;
  width: 100%;
  text-align: right;
  color: #374151;
  transition: background-color 0.2s;
}

:global(.dark) :deep([role="menuitem"]),
:global(.dark) :deep(.dropdown-item) {
  color: #d1d5db;
}

:deep([role="menuitem"]:hover),
:deep(.dropdown-item:hover) {
  background-color: #f3f4f6;
}

:global(.dark) :deep([role="menuitem"]:hover),
:global(.dark) :deep(.dropdown-item:hover) {
  background-color: #374151;
}

/* Divider */
:deep(.dropdown-divider) {
  height: 1px;
  background-color: #e5e7eb;
  margin: 0.25rem 0;
}

:global(.dark) :deep(.dropdown-divider) {
  background-color: #4b5563;
}
</style>
