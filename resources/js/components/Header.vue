<template>
  <header class="header-container bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 px-4 py-3 transition-colors duration-300">
    <div class="flex items-center justify-between max-w-7xl mx-auto">
      <!-- Left side - Menu button (hidden on large screens) -->
      <div class="flex items-center gap-4 lg:hidden">
        <BaseButton
          variant="ghost"
          size="sm"
          @click="$emit('toggle-sidebar')"
          :aria-label="sidebarOpen ? 'بستن منو' : 'باز کردن منو'"
        >
          <template #icon>
            <svg v-if="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </template>
        </BaseButton>
      </div>

      <!-- Center - Search bar -->
      <div class="flex-1 max-w-2xl mx-4">
        <BaseInput
          v-model="searchQuery"
          placeholder="سوال یا کلمه موردنظر خود را جستجو کنید"
          variant="filled"
          rounded="xl"
          @enter="handleSearch"
        >
          <template #prefix>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </template>
        </BaseInput>
      </div>

      <!-- Right side - Action buttons -->
      <div class="flex items-center gap-3">
        <!-- Main action button -->
        <BaseButton
          variant="primary"
          size="lg"
          rounded="xl"
          @click="handleMainAction"
        >
          <template #icon>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
          </template>
          بپرس
        </BaseButton>
      </div>
    </div>

    <!-- Mobile search (shown when mobile search is active) -->
    <div v-if="showMobileSearch" class="md:hidden mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
      <BaseInput
        v-model="searchQuery"
        placeholder="سوال یا کلمه موردنظر خود را جستجو کنید"
        variant="filled"
        rounded="xl"
        @enter="handleSearch"
      >
        <template #prefix>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </template>
      </BaseInput>
    </div>
  </header>
</template>

<script>
import { BaseButton, BaseInput } from './ui'

export default {
  name: 'Header',
  components: {
    BaseButton,
    BaseInput
  },
  props: {
    sidebarOpen: {
      type: Boolean,
      default: false
    }
  },
  emits: ['toggle-sidebar', 'search', 'main-action'],
  data() {
    return {
      searchQuery: '',
      showMobileSearch: false
    };
  },
  methods: {
    handleSearch() {
      if (this.searchQuery.trim()) {
        this.$emit('search', this.searchQuery.trim());
      }
    },
    handleMainAction() {
      this.$emit('main-action');
    },
    toggleMobileSearch() {
      this.showMobileSearch = !this.showMobileSearch;
    }
  }
};
</script>

<style scoped>
.header-container {
  direction: rtl;
  font-family: 'Iran Sans', 'Tahoma', sans-serif;
}

/* Custom focus styles */
input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

:global(.dark) input:focus {
  box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
}

/* Button hover effects */
button {
  transition: all 0.2s ease-in-out;
}

button:hover {
  transform: translateY(-1px);
}

/* Search input animations */
input {
  transition: all 0.3s ease-in-out;
}

input:focus {
  background-color: #ffffff;
  border-color: #3b82f6;
}

:global(.dark) input:focus {
  background-color: #374151;
  border-color: #60a5fa;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .header-container {
    padding: 0.75rem 1rem;
  }

  .flex-1.max-w-2xl {
    max-width: 100%;
  }
}

/* Animation for mobile search */
.mobile-search-enter-active,
.mobile-search-leave-active {
  transition: all 0.3s ease-in-out;
}

.mobile-search-enter-from,
.mobile-search-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
