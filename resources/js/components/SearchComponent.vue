<template>
  <div class="relative" ref="dropdownRef">
    <!-- Search Input -->
    <BaseInput
      v-model="searchQuery"
      :placeholder="placeholder"
      :variant="variant"
      :rounded="rounded"
      @update:modelValue="handleInput"
      @focus="handleFocus"
      @keydown="handleKeydown"
      :class="inputClass"
    >
      <template #prefix>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </template>
      <template #suffix v-if="isLoading">
        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
      </template>
    </BaseInput>

    <!-- Search Results Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="showDropdown && (searchResults.length > 0 || showNoResults)"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-96 overflow-y-auto"
      >
        <!-- Loading state -->
        <div v-if="isLoading" class="p-4 text-center text-gray-500 dark:text-gray-400">
          <div class="flex items-center justify-center gap-2">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
            <span>در حال جستجو...</span>
          </div>
        </div>

        <!-- No results -->
        <div v-else-if="showNoResults" class="p-4 text-center text-gray-500 dark:text-gray-400">
          <div class="flex flex-col items-center gap-2">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.386 0-4.569-.831-6.293-2.209"></path>
            </svg>
            <span>نتیجه‌ای یافت نشد</span>
          </div>
        </div>

        <!-- Search results -->
        <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
          <li
            v-for="(question, index) in searchResults"
            :key="question.id"
            :class="[
              'cursor-pointer transition-colors duration-150',
              selectedIndex === index
                ? 'bg-blue-50 dark:bg-blue-900/20'
                : 'hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
            @click="selectQuestion(question)"
            @mouseenter="selectedIndex = index"
          >
            <div class="p-4">
              <!-- Question title -->
              <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1 line-clamp-2">
                {{ question.title }}
              </h4>

              <!-- Question metadata -->
              <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-3">
                  <!-- Category -->
                  <span v-if="question.category" class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    {{ question.category.name }}
                  </span>

                  <!-- Author -->
                  <span v-if="question.user" class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ question.user.name }}
                  </span>
                </div>

                <!-- Stats -->
                <div class="flex items-center gap-3">
                  <!-- Answers count -->
                  <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    {{ question.answers_count || 0 }}
                  </span>

                  <!-- Votes count -->
                  <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                    </svg>
                    {{ question.votes_count || 0 }}
                  </span>

                  <!-- Views -->
                  <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    {{ question.views || 0 }}
                  </span>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<script>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { BaseInput } from './ui'
import { useQuestions } from '../composables/useQuestions'

export default {
  name: 'SearchComponent',
  components: {
    BaseInput
  },
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'سوال یا کلمه موردنظر خود را جستجو کنید'
    },
    variant: {
      type: String,
      default: 'filled'
    },
    rounded: {
      type: String,
      default: 'xl'
    },
    inputClass: {
      type: String,
      default: ''
    },
    searchLimit: {
      type: Number,
      default: 8
    },
    debounceMs: {
      type: Number,
      default: 300
    }
  },
  emits: ['update:modelValue', 'search', 'select'],
  setup(props, { emit }) {
    const { searchQuestions } = useQuestions()
    const router = useRouter()

    // Refs
    const dropdownRef = ref(null)
    const searchQuery = ref(props.modelValue)
    const searchResults = ref([])
    const isLoading = ref(false)
    const showDropdown = ref(false)
    const selectedIndex = ref(-1)
    const searchTimeout = ref(null)

    // Computed
    const showNoResults = computed(() => {
      return !isLoading.value && searchQuery.value.trim().length > 0 && searchResults.value.length === 0
    })

    // Methods
    const performSearch = async (query) => {
      if (!query || query.trim().length < 2) {
        searchResults.value = []
        showDropdown.value = false
        return
      }

      isLoading.value = true
      showDropdown.value = true

      try {
        const result = await searchQuestions(query.trim(), props.searchLimit)
        searchResults.value = result.data || []
      } catch (error) {
        console.error('Search failed:', error)
        searchResults.value = []
      } finally {
        isLoading.value = false
        selectedIndex.value = -1
      }
    }

    const handleInput = (value) => {
      searchQuery.value = value
      emit('update:modelValue', value)
      clearTimeout(searchTimeout.value)
      searchTimeout.value = setTimeout(() => {
        performSearch(value)
      }, props.debounceMs)
    }

    const handleFocus = () => {
      if (searchQuery.value.trim().length > 0 && searchResults.value.length > 0) {
        showDropdown.value = true
      }
    }

    const hideDropdown = () => {
      showDropdown.value = false
    }

    const handleSearch = (query) => {
      if (query && query.trim()) {
        // For now, you can implement a search page or just log the search
        console.log('Searching for:', query.trim())
        // TODO: Implement search page navigation
        // router.push({ name: 'Search', query: { q: query.trim() } })
      }
    }

    const selectQuestion = (question) => {
      if (question && question.id) {
        // Keep the search query as is, don't replace with question title
        showDropdown.value = false
        emit('select', question)

        // Navigate to the question page
        const targetRoute = `/questions/${question.id}`

        // Use router.push and handle potential navigation errors
        router.push(targetRoute).catch(error => {
          // Handle navigation errors (e.g., navigating to the same route)
          if (error.name !== 'NavigationDuplicated') {
            console.error('Navigation error:', error)
          }
        })
      }
    }

    const selectOnEnter = () => {
      if (selectedIndex.value >= 0 && searchResults.value[selectedIndex.value]) {
        selectQuestion(searchResults.value[selectedIndex.value])
      } else {
        handleSearch(searchQuery.value)
        hideDropdown()
      }
    }

    const handleKeydown = (e) => {
      if (!showDropdown.value) return

      switch (e.key) {
        case 'ArrowDown':
          e.preventDefault()
          selectedIndex.value = (selectedIndex.value + 1) % searchResults.value.length
          break
        case 'ArrowUp':
          e.preventDefault()
          selectedIndex.value = (selectedIndex.value - 1 + searchResults.value.length) % searchResults.value.length
          break
        case 'Enter':
          e.preventDefault()
          selectOnEnter()
          break
        case 'Escape':
          e.preventDefault()
          hideDropdown()
          break
      }
    }

    const handleClickOutside = (event) => {
      if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        hideDropdown()
      }
    }

    // Lifecycle hooks
    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
      clearTimeout(searchTimeout.value)
    })

    // Watch for modelValue changes from parent
    watch(() => props.modelValue, (newValue) => {
      if (newValue !== searchQuery.value) {
        searchQuery.value = newValue
      }
    })

    return {
      dropdownRef,
      searchQuery,
      searchResults,
      isLoading,
      showDropdown,
      selectedIndex,
      showNoResults,
      handleInput,
      handleFocus,
      handleKeydown,
      selectQuestion
    }
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
