<template>
  <div :class="cardClasses" v-bind="$attrs">
    <!-- Header -->
    <div v-if="$slots.header || title || subtitle" class="card-header" :class="headerClasses">
      <slot name="header">
        <div v-if="title || subtitle">
          <h3 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
          <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ subtitle }}</p>
        </div>
      </slot>
      <div v-if="$slots.actions" class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </div>

    <!-- Body -->
    <div v-if="$slots.default" class="card-body" :class="bodyClasses">
      <slot />
    </div>

    <!-- Footer -->
    <div v-if="$slots.footer" class="card-footer" :class="footerClasses">
      <slot name="footer" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseCard',
  inheritAttrs: false,
  props: {
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'bordered', 'shadow', 'elevated'].includes(value)
    },
    padding: {
      type: String,
      default: 'md',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    rounded: {
      type: String,
      default: 'lg',
      validator: (value) => ['none', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'].includes(value)
    },
    title: {
      type: String,
      default: ''
    },
    subtitle: {
      type: String,
      default: ''
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
    },
    hoverable: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    cardClasses() {
      return [
        // Base classes
        'bg-white dark:bg-gray-800 transition-all duration-300',

        // Variant classes
        this.variantClasses,

        // Rounded classes
        this.roundedClasses,

        // Hoverable
        { 'hover:shadow-lg hover:-translate-y-1 cursor-pointer': this.hoverable }
      ]
    },
    variantClasses() {
      const variants = {
        default: '',
        bordered: 'border border-gray-200 dark:border-gray-700',
        shadow: 'shadow-sm',
        elevated: 'shadow-lg'
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
        '2xl': 'rounded-2xl',
        '3xl': 'rounded-3xl'
      }
      return rounded[this.rounded]
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
        'border-t border-gray-200 dark:border-gray-700',
        this.getPaddingClass(this.footerPadding)
      ]
    }
  },
  methods: {
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
.card-header:empty {
  display: none;
}

.card-footer:empty {
  display: none;
}

/* RTL support */
:global(.rtl) .card-header {
  direction: rtl;
}

:global(.rtl) .card-body {
  direction: rtl;
}

:global(.rtl) .card-footer {
  direction: rtl;
}
</style>
