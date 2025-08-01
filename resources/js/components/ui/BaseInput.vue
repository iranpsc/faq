<template>
  <div class="form-group">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      :class="{ 'text-red-600 dark:text-red-400': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Input wrapper -->
    <div class="relative" :class="wrapperClasses">
      <!-- Prefix icon -->
      <div v-if="$slots.prefix || prefixIcon" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <slot name="prefix">
          <component v-if="prefixIcon" :is="prefixIcon" class="h-5 w-5 text-gray-400" />
        </slot>
      </div>

      <!-- Input -->
      <input
        :id="inputId"
        :type="inputType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :class="inputClasses"
        :dir="dir"
        v-bind="$attrs"
        @input="handleInput"
        @focus="handleFocus"
        @blur="handleBlur"
        @keyup.enter="$emit('enter', $event)"
      />

      <!-- Suffix/Actions -->
      <div v-if="$slots.suffix || suffixIcon || showPassword || clearable" class="absolute inset-y-0 left-0 flex items-center pl-3">
        <!-- Clear button -->
        <button
          v-if="clearable && modelValue && !disabled && !readonly"
          @click="clearInput"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded transition-colors"
          type="button"
        >
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>

        <!-- Password toggle -->
        <button
          v-if="showPassword && type === 'password'"
          @click="togglePasswordVisibility"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded transition-colors ml-1"
          type="button"
        >
          <svg v-if="passwordVisible" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
          </svg>
          <svg v-else class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
            <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
          </svg>
        </button>

        <!-- Suffix slot -->
        <slot name="suffix">
          <component v-if="suffixIcon" :is="suffixIcon" class="h-5 w-5 text-gray-400" />
        </slot>
      </div>

      <!-- Loading spinner -->
      <div v-if="loading" class="absolute inset-y-0 left-0 flex items-center pl-3">
        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
  name: 'BaseInput',
  inheritAttrs: false,
  emits: ['update:modelValue', 'focus', 'blur', 'enter', 'clear'],
  props: {
    modelValue: {
      type: [String, Number],
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
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
    prefixIcon: {
      type: String,
      default: ''
    },
    suffixIcon: {
      type: String,
      default: ''
    },
    clearable: {
      type: Boolean,
      default: false
    },
    showPassword: {
      type: Boolean,
      default: false
    },
    loading: {
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
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
    }
  },
  data() {
    return {
      focused: false,
      passwordVisible: false,
      inputId: `input-${Math.random().toString(36).substr(2, 9)}`
    }
  },
  computed: {
    hasError() {
      return this.error !== false
    },
    errorMessage() {
      return typeof this.error === 'string' ? this.error : 'این فیلد دارای خطا است'
    },
    inputType() {
      if (this.type === 'password' && this.passwordVisible) {
        return 'text'
      }
      return this.type
    },
    wrapperClasses() {
      return {
        'opacity-50': this.disabled
      }
    },
    inputClasses() {
      return [
        // Base classes
        'block w-full transition-all border-2 border-gray-300 dark:border-gray-600 duration-200 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100',

        // Size classes
        this.sizeClasses,

        // Variant classes
        this.variantClasses,

        // Rounded classes
        this.roundedClasses,

        // State classes
        this.stateClasses,

        // Padding adjustments for icons
        this.paddingClasses
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
        xl: 'rounded-xl',
        full: 'rounded-full'
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
    paddingClasses() {
      const hasPrefix = this.$slots.prefix || this.prefixIcon
      const hasSuffix = this.$slots.suffix || this.suffixIcon || this.clearable || (this.showPassword && this.type === 'password') || this.loading

      let classes = []

      if (hasPrefix) {
        classes.push(this.dir === 'rtl' ? 'pr-10' : 'pl-10')
      }

      if (hasSuffix) {
        classes.push(this.dir === 'rtl' ? 'pl-10' : 'pr-10')
      }

      return classes
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
    clearInput() {
      this.$emit('update:modelValue', '')
      this.$emit('clear')
    },
    togglePasswordVisibility() {
      this.passwordVisible = !this.passwordVisible
    }
  }
}
</script>

<style scoped>
/* Remove default focus styles */
input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Dark mode focus styles */
:global(.dark) input:focus {
  box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
}

/* Error focus styles */
input.border-red-300:focus,
input.border-red-600:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* RTL specific styles */
:global(.rtl) input {
  direction: rtl;
}

/* Disabled styles */
input:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Readonly styles */
input:read-only {
  cursor: default;
  background-color: #f9fafb;
}

:global(.dark) input:read-only {
  background-color: #374151;
}
</style>
