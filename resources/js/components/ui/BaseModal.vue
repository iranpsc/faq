<template>
  <teleport to="body">
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="visible"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="title ? 'modal-title' : undefined"
      >
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
          @click="handleBackdropClick"
        ></div>

        <!-- Modal container -->
        <div class="flex min-h-full items-center justify-center p-4">
          <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 transform scale-95"
            enter-to-class="opacity-100 transform scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 transform scale-100"
            leave-to-class="opacity-0 transform scale-95"
          >
            <div
              v-if="visible"
              :class="modalClasses"
              @click.stop
            >
              <!-- Header -->
              <div v-if="$slots.header || title || closable" class="modal-header" :class="headerClasses">
                <slot name="header">
                  <div v-if="title">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                      {{ title }}
                    </h3>
                    <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                      {{ subtitle }}
                    </p>
                  </div>
                </slot>

                <button
                  v-if="closable"
                  @click="close"
                  class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                  type="button"
                >
                  <span class="sr-only">بستن</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>

              <!-- Body -->
              <div v-if="$slots.default" class="modal-body" :class="bodyClasses">
                <slot />
              </div>

              <!-- Footer -->
              <div v-if="$slots.footer" class="modal-footer" :class="footerClasses">
                <slot name="footer" />
              </div>
            </div>
          </transition>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script>
export default {
  name: 'BaseModal',
  emits: ['close', 'confirm', 'cancel'],
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: ''
    },
    subtitle: {
      type: String,
      default: ''
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', 'full'].includes(value)
    },
    closable: {
      type: Boolean,
      default: true
    },
    closeOnBackdrop: {
      type: Boolean,
      default: true
    },
    closeOnEscape: {
      type: Boolean,
      default: true
    },
    persistent: {
      type: Boolean,
      default: false
    },
    headerPadding: {
      type: String,
      default: 'md'
    },
    bodyPadding: {
      type: String,
      default: 'md'
    },
    footerPadding: {
      type: String,
      default: 'md'
    }
  },
  computed: {
    modalClasses() {
      return [
        'relative bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all',
        this.sizeClasses
      ]
    },
    sizeClasses() {
      const sizes = {
        xs: 'max-w-xs w-full',
        sm: 'max-w-sm w-full',
        md: 'max-w-md w-full',
        lg: 'max-w-lg w-full',
        xl: 'max-w-xl w-full',
        '2xl': 'max-w-2xl w-full',
        '3xl': 'max-w-3xl w-full',
        '4xl': 'max-w-4xl w-full',
        full: 'max-w-full w-full h-full'
      }
      return sizes[this.size]
    },
    headerClasses() {
      return [
        'flex items-center justify-between border-b border-gray-200 dark:border-gray-700',
        this.getPaddingClass(this.headerPadding)
      ]
    },
    bodyClasses() {
      return [
        this.getPaddingClass(this.bodyPadding)
      ]
    },
    footerClasses() {
      return [
        'flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700',
        this.getPaddingClass(this.footerPadding)
      ]
    }
  },
  watch: {
    visible(newVal) {
      if (newVal) {
        this.handleOpen()
      } else {
        this.handleClose()
      }
    }
  },
  mounted() {
    if (this.closeOnEscape) {
      document.addEventListener('keydown', this.handleEscape)
    }

    if (this.visible) {
      this.handleOpen()
    }
  },
  beforeUnmount() {
    document.removeEventListener('keydown', this.handleEscape)
    this.handleClose()
  },
  methods: {
    close() {
      this.$emit('close')
    },
    confirm() {
      this.$emit('confirm')
    },
    cancel() {
      this.$emit('cancel')
    },
    handleBackdropClick() {
      if (this.closeOnBackdrop && !this.persistent) {
        this.close()
      }
    },
    handleEscape(e) {
      if (e.key === 'Escape' && this.visible && !this.persistent) {
        this.close()
      }
    },
    handleOpen() {
      // Prevent body scroll
      document.body.style.overflow = 'hidden'

      // Focus management
      this.$nextTick(() => {
        const firstFocusable = this.$el.querySelector(
          'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        )
        if (firstFocusable) {
          firstFocusable.focus()
        }
      })
    },
    handleClose() {
      // Restore body scroll
      document.body.style.overflow = ''
    },
    getPaddingClass(size) {
      const paddings = {
        none: 'p-0',
        sm: 'p-3',
        md: 'p-4',
        lg: 'p-6',
        xl: 'p-8'
      }
      return paddings[size] || paddings.md
    }
  }
}
</script>

<style scoped>
/* RTL support */
:global(.rtl) .modal-header {
  direction: rtl;
}

:global(.rtl) .modal-body {
  direction: rtl;
}

:global(.rtl) .modal-footer {
  direction: rtl;
  justify-content: flex-start;
}

/* Ensure modal is above everything */
.fixed.inset-0.z-50 {
  z-index: 9999;
}

/* Smooth transitions */
.modal-header,
.modal-body,
.modal-footer {
  transition: all 0.2s ease-in-out;
}
</style>
