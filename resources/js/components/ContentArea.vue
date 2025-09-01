<template>
  <main
    class="flex-grow p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900/50 relative main-content-container"
  >
    <!-- Hero Section -->
    <div
      v-if="$slots.hero"
      class="w-full max-w-7xl mx-auto mb-6 sm:mb-8 lg:mb-12"
    >
      <slot name="hero" />
    </div>

    <div class="max-w-7xl mx-auto">
      <!-- Filters Section -->
      <div v-if="$slots.filters" class="mb-6">
        <slot name="filters1" />
      </div>

      <!-- Main Layout -->
      <div :class="layoutClasses">
              
        <!-- Main Content -->
        <div :class="mainContentClasses">
          <div v-if="$slots.filters" class="mb-6">
        <slot name="filters" />
      </div>
          <slot name="main" />
          <slot /> <!-- Default slot -->
        </div>

        <!-- Sidebar -->
        <div
          v-if="$slots.sidebar && showSidebar"
          :class="sidebarClasses"
          class="lg:sticky lg:top-5 h-fit"
        >
          <slot name="sidebar" />
        </div>
      </div>

      <!-- Footer -->
      <div v-if="$slots.footer" class="mt-12">
        <slot name="footer" />
      </div>
    </div>
  </main>
</template>

<script>
import { computed } from "vue";

export default {
  name: "ContentArea",
  props: {
    layout: {
      type: String,
      default: "with-sidebar",
      validator: (v) =>
        ["with-sidebar", "full-width", "centered"].includes(v),
    },
    showSidebar: { type: Boolean, default: true },
    mainWidth: {
      type: String,
      default: "3/4",
      validator: (v) => ["1/2", "2/3", "3/4", "full"].includes(v),
    },
    sidebarWidth: {
      type: String,
      default: "1/4",
      validator: (v) => ["1/4", "1/3", "1/2"].includes(v),
    },
    gap: {
      type: String,
      default: "6",
      validator: (v) => ["2", "4", "6", "8", "12"].includes(v),
    },
    maxWidth: {
      type: String,
      default: "7xl",
      validator: (v) =>
        [
          "none",
          "sm",
          "md",
          "lg",
          "xl",
          "2xl",
          "3xl",
          "4xl",
          "5xl",
          "6xl",
          "7xl",
        ].includes(v),
    },
    background: {
      type: String,
      default: "default",
      validator: (v) =>
        ["default", "white", "gray", "transparent"].includes(v),
    },
  },
  setup(props) {
    // Layout wrapper classes
    const layoutClasses = computed(() => {
      const gaps = {
        "2": "gap-2",
        "4": "gap-4",
        "6": "gap-6",
        "8": "gap-8",
        "12": "gap-12",
      };
      if (props.layout === "with-sidebar") {
        return [
          "flex",
          "flex-col-reverse lg:flex-row",
          "items-start",
          gaps[props.gap] || "gap-6",
        ].join(" ");
      }
      if (props.layout === "full-width") return "w-full";
      if (props.layout === "centered") return "max-w-4xl mx-auto";
      return "";
    });

    // Main content classes
    const mainContentClasses = computed(() => {
      if (props.layout === "with-sidebar" && props.showSidebar) {
        const widthMap = {
          "1/2": "lg:w-1/2",
          "2/3": "lg:w-2/3",
          "3/4": "lg:w-3/4",
          full: "w-full",
        };
        return [
          "flex-1",
          "w-full",
          widthMap[props.mainWidth] || "lg:w-3/4",
        ].join(" ");
      }
      return "w-full";
    });

    // Sidebar classes
    const sidebarClasses = computed(() => {
      if (props.layout === "with-sidebar") {
        const widthMap = {
          "1/4": "lg:w-1/4",
          "1/3": "lg:w-1/3",
          "1/2": "lg:w-1/2",
        };
        return [
          "shrink-0",
          "w-full", // full width in mobile
          widthMap[props.sidebarWidth] || "lg:w-1/4",
        ].join(" ");
      }
      return "";
    });

    return { layoutClasses, mainContentClasses, sidebarClasses };
  },
};
</script>

<style scoped>
.main-content-container {
  min-height: calc(100vh - 4rem); /* adjust if header height differs */
}

@media (max-width: 768px) {
  .main-content-container {
    padding: 1rem;
  }
}

.flex,
.w-full,
.flex-1 {
  transition: all 0.3s ease;
}
</style>
