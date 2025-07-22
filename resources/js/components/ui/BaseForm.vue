<template>
  <form
    :class="formClasses"
    @submit.prevent="handleSubmit"
    v-bind="$attrs"
  >
    <slot :errors="formErrors" :loading="loading" :valid="isValid" />
  </form>
</template>

<script>
export default {
  name: 'BaseForm',
  inheritAttrs: false,
  emits: ['submit', 'validate', 'error'],
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    validateOnSubmit: {
      type: Boolean,
      default: true
    },
    spacing: {
      type: String,
      default: 'md',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    layout: {
      type: String,
      default: 'vertical',
      validator: (value) => ['vertical', 'horizontal', 'inline'].includes(value)
    },
    errors: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      formErrors: { ...this.errors },
      fieldValidations: new Map()
    }
  },
  computed: {
    formClasses() {
      return [
        // Base classes
        'form-container',

        // Layout classes
        this.layoutClasses,

        // Spacing classes
        this.spacingClasses
      ]
    },
    layoutClasses() {
      const layouts = {
        vertical: 'space-y-4',
        horizontal: 'grid grid-cols-1 md:grid-cols-2 gap-4',
        inline: 'flex flex-wrap items-end gap-4'
      }
      return layouts[this.layout]
    },
    spacingClasses() {
      const spacings = {
        none: '',
        sm: 'space-y-2',
        md: 'space-y-4',
        lg: 'space-y-6',
        xl: 'space-y-8'
      }
      return this.layout === 'vertical' ? spacings[this.spacing] : ''
    },
    isValid() {
      return Object.keys(this.formErrors).length === 0
    }
  },
  watch: {
    errors: {
      handler(newErrors) {
        this.formErrors = { ...newErrors }
      },
      deep: true
    }
  },
  methods: {
    handleSubmit() {
      if (this.loading) return

      if (this.validateOnSubmit) {
        this.validateForm()
      }

      if (this.isValid) {
        this.$emit('submit')
      } else {
        this.$emit('error', this.formErrors)
      }
    },
    validateForm() {
      // Validate all registered fields
      for (const [fieldName, validation] of this.fieldValidations) {
        const error = this.validateField(fieldName, validation)
        if (error) {
          this.setFieldError(fieldName, error)
        } else {
          this.clearFieldError(fieldName)
        }
      }

      this.$emit('validate', this.formErrors)
    },
    validateField(fieldName, validation) {
      const { value, rules } = validation

      if (!rules || rules.length === 0) return null

      for (const rule of rules) {
        const error = this.applyRule(value, rule, fieldName)
        if (error) return error
      }

      return null
    },
    applyRule(value, rule, fieldName) {
      if (typeof rule === 'function') {
        return rule(value, fieldName)
      }

      if (typeof rule === 'object') {
        const { type, message, ...params } = rule

        switch (type) {
          case 'required':
            return !value || value.trim() === '' ? (message || 'این فیلد الزامی است') : null

          case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            return value && !emailRegex.test(value) ? (message || 'ایمیل معتبر وارد کنید') : null

          case 'minLength':
            return value && value.length < params.min ? (message || `حداقل ${params.min} کاراکتر وارد کنید`) : null

          case 'maxLength':
            return value && value.length > params.max ? (message || `حداکثر ${params.max} کاراکتر مجاز است`) : null

          case 'pattern':
            return value && !params.regex.test(value) ? (message || 'فرمت وارد شده صحیح نیست') : null

          case 'min':
            return value && Number(value) < params.min ? (message || `حداقل مقدار ${params.min} است`) : null

          case 'max':
            return value && Number(value) > params.max ? (message || `حداکثر مقدار ${params.max} است`) : null

          default:
            return null
        }
      }

      return null
    },
    registerField(fieldName, validation) {
      this.fieldValidations.set(fieldName, validation)
    },
    unregisterField(fieldName) {
      this.fieldValidations.delete(fieldName)
      this.clearFieldError(fieldName)
    },
    setFieldError(fieldName, error) {
      this.formErrors = {
        ...this.formErrors,
        [fieldName]: error
      }
    },
    clearFieldError(fieldName) {
      const newErrors = { ...this.formErrors }
      delete newErrors[fieldName]
      this.formErrors = newErrors
    },
    clearAllErrors() {
      this.formErrors = {}
    },
    reset() {
      this.clearAllErrors()
      this.fieldValidations.clear()
    }
  },
  provide() {
    return {
      form: this
    }
  }
}
</script>

<style scoped>
.form-container {
  direction: rtl;
  font-family: 'Iran Sans', 'Tahoma', sans-serif;
}

/* Horizontal layout specific styles */
.grid.grid-cols-1.md\\:grid-cols-2 .form-group:last-child:nth-child(odd) {
  grid-column: 1 / -1;
}

/* Inline layout specific styles */
.flex.flex-wrap .form-group {
  flex: 1;
  min-width: 200px;
}

/* Loading state */
.form-container[aria-busy="true"] {
  opacity: 0.7;
  pointer-events: none;
}

/* Animation for error messages */
.error-enter-active,
.error-leave-active {
  transition: all 0.3s ease;
}

.error-enter-from,
.error-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
