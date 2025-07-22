<template>
  <div class="form-group">
    <!-- Label -->
    <label
      v-if="label"
      :for="textareaId"
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Textarea wrapper -->
    <div class="relative" :class="wrapperClasses">
      <textarea
        :id="textareaId"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :rows="rows"
        :maxlength="maxLength"
        :class="textareaClasses"
        :dir="dir"
        v-bind="$attrs"
        @input="handleInput"
        @focus="handleFocus"
        @blur="handleBlur"
        ref="textarea"
      />

      <!-- Character count -->
      <div v-if="showCharCount && maxLength" class="absolute bottom-2 left-2 text-xs text-gray-400 dark:text-gray-500">
        {{ currentLength }}/{{ maxLength }}
      </div>

      <!-- Resize handle (if resizable) -->
      <div v-if="resizable" class="absolute bottom-0 right-0 w-4 h-4 cursor-se-resize opacity-50 hover:opacity-100">
        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
          <path d="M12 2l3 3-3 3v-2H9V4h3V2zM8 18l-3-3 3-3v2h3v2H8v2z"/>
        </svg>
      </div>
    </div>

    <!-- Helper text or error -->
    <div v-if="helperText || hasError" class="mt-2 text-sm">
      <p v-if="hasError" class="text-red-600 dark:text-red-400">
        {{ errorMessage }}
      </p>
      <p v-else-if="helperText" class="text-gray-500 dark:text-gray-400">
        {{ helperText }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseTextarea',
  inheritAttrs: false,
  emits: ['update:modelValue', 'focus', 'blur'],
  props: {
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
      default: ''
    },
    rows: {
      type: Number,
      default: 4
    },
    maxLength: {
      type: Number,
      default: null
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'filled', 'outlined'].includes(value)
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
    error: {
      type: [Boolean, String],
      default: false
    },
    helperText: {
      type: String,
      default: ''
    },
    resizable: {
      type: Boolean,
      default: true
    },
    autoResize: {
      type: Boolean,
      default: false
    },
    showCharCount: {
      type: Boolean,
      default: false
    },
    dir: {
      type: String,
      default: 'rtl',
      validator: (value) => ['ltr', 'rtl'].includes(value)
    },
    rounded: {
      type: String,
      default: 'lg',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
    }
  },
  data() {
    return {
      focused: false,
      textareaId: `textarea-${Math.random().toString(36).substr(2, 9)}`
    }
  },
  computed: {
    hasError() {
      return this.error !== false
    },
    errorMessage() {
      return typeof this.error === 'string' ? this.error : 'این فیلد دارای خطا است'
    },
    currentLength() {
      return this.modelValue ? this.modelValue.length : 0
    },
    wrapperClasses() {
      return {
        'opacity-50': this.disabled
      }
    },
    textareaClasses() {
      return [
        // Base classes
        'block w-full transition-all duration-200 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100',

        // Size classes
        this.sizeClasses,

        // Variant classes
        this.variantClasses,

        // Rounded classes
        this.roundedClasses,

        // State classes
        this.stateClasses,

        // Resize classes
        this.resizeClasses
      ]
    },
    sizeClasses() {
      const sizes = {
        sm: 'px-3 py-2 text-sm',
        md: 'px-4 py-3 text-sm',
        lg: 'px-5 py-4 text-base'
      }
      return sizes[this.size]
    },
    variantClasses() {
      const variants = {
        default: 'bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600',
        filled: 'bg-gray-50 dark:bg-gray-800 border border-transparent',
        outlined: 'bg-transparent border-2 border-gray-300 dark:border-gray-600'
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
    stateClasses() {
      if (this.hasError) {
        return 'border-red-300 dark:border-red-600 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400'
      }

      if (this.focused) {
        return 'border-blue-500 dark:border-blue-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400'
      }

      return 'focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400'
    },
    resizeClasses() {
      if (!this.resizable) {
        return 'resize-none'
      }
      return 'resize-y'
    }
  },
  watch: {
    modelValue() {
      if (this.autoResize) {
        this.$nextTick(() => {
          this.adjustHeight()
        })
      }
    }
  },
  mounted() {
    if (this.autoResize) {
      this.adjustHeight()
    }
  },
  methods: {
    handleInput(event) {
      this.$emit('update:modelValue', event.target.value)
    },
    handleFocus(event) {
      this.focused = true
      this.$emit('focus', event)
    },
    handleBlur(event) {
      this.focused = false
      this.$emit('blur', event)
    },
    adjustHeight() {
      if (!this.autoResize || !this.$refs.textarea) return

      const textarea = this.$refs.textarea
      textarea.style.height = 'auto'
      textarea.style.height = textarea.scrollHeight + 'px'
    },
    focus() {
      if (this.$refs.textarea) {
        this.$refs.textarea.focus()
      }
    },
    blur() {
      if (this.$refs.textarea) {
        this.$refs.textarea.blur()
      }
    }
  }
}
</script>

<style scoped>
/* Remove default focus styles */
textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Dark mode focus styles */
:global(.dark) textarea:focus {
  box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
}

/* Error focus styles */
textarea.border-red-300:focus,
textarea.border-red-600:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* RTL specific styles */
:global(.rtl) textarea {
  direction: rtl;
}

/* Disabled styles */
textarea:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Readonly styles */
textarea:read-only {
  cursor: default;
  background-color: #f9fafb;
}

:global(.dark) textarea:read-only {
  background-color: #374151;
}

/* Custom scrollbar for textarea */
textarea::-webkit-scrollbar {
  width: 6px;
}

textarea::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Dark mode scrollbar */
:global(.dark) textarea::-webkit-scrollbar-track {
  background: #374151;
}

:global(.dark) textarea::-webkit-scrollbar-thumb {
  background: #6b7280;
}

:global(.dark) textarea::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
