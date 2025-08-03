<template>
  <div class="w-full">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }}
    </label>

    <div class="relative" ref="dropdownRef">
      <!-- Selection Display -->
      <div
        @click="toggleDropdown"
        :class="[
          'w-full px-3 py-2 border rounded-lg cursor-pointer transition-colors',
          'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700',
          'focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800',
          disabled ? 'opacity-50 cursor-not-allowed' : 'hover:border-gray-400 dark:hover:border-gray-500',
          error ? 'border-red-500 dark:border-red-500' : ''
        ]"
        :id="id"
      >
        <div class="flex items-center justify-between min-h-[24px]">
          <!-- Selected Items Display -->
          <div class="flex-1">
            <!-- Multiple Selection -->
            <div v-if="multiple && selectedItems.length > 0" class="flex flex-wrap gap-1">
              <span
                v-for="item in selectedItems"
                :key="item[trackBy]"
                class="inline-flex items-center gap-1 px-2 py-1 bg-blue-500 text-white text-xs rounded"
              >
                {{ item[optionLabel] }}
                <button
                  @click.stop="removeItem(item)"
                  class="hover:bg-blue-600 rounded-full p-0.5"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </span>
            </div>

            <!-- Single Selection -->
            <span
              v-else-if="!multiple && selectedItems.length > 0"
              class="text-gray-900 dark:text-gray-100"
            >
              {{ selectedItems[0][optionLabel] }}
            </span>

            <!-- Placeholder -->
            <span v-else class="text-gray-500 dark:text-gray-400">
              {{ placeholder }}
            </span>
          </div>

          <!-- Dropdown Arrow -->
          <svg
            :class="['w-5 h-5 text-gray-400 transition-transform', isOpen ? 'rotate-180' : '']"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </div>
      </div>

      <!-- Dropdown Panel -->
      <div
        v-if="isOpen"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-64 overflow-hidden"
      >
        <!-- Search Input -->
        <div v-if="searchable" class="p-2 border-b border-gray-200 dark:border-gray-600">
          <input
            ref="searchInput"
            v-model="searchQuery"
            @input="handleSearch"
            @keydown.enter.prevent="handleEnterKey"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Options List -->
        <div
          ref="optionsListRef"
          @scroll="handleScroll"
          class="max-h-48 overflow-y-auto"
        >
          <!-- Loading -->
          <div v-if="loading && allOptions.length === 0" class="p-3 text-center text-gray-500 dark:text-gray-400">
            <div class="inline-flex items-center gap-2">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
              در حال بارگذاری...
            </div>
          </div>

          <!-- No Results -->
          <div v-else-if="filteredOptions.length === 0 && !canCreateTag" class="p-3 text-center text-gray-500 dark:text-gray-400">
            نتیجه‌ای یافت نشد
          </div>

          <!-- Create New Tag Option -->
          <div
            v-if="canCreateTag"
            @click="createNewTag"
            class="p-3 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-gray-200 dark:border-gray-600"
          >
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              <span class="text-blue-600 dark:text-blue-400">افزودن "{{ searchQuery }}"</span>
            </div>
          </div>

          <!-- Options -->
          <div
            v-for="option in filteredOptions"
            :key="option[trackBy]"
            @click="selectOption(option)"
            :class="[
              'p-3 cursor-pointer transition-colors',
              isSelected(option)
                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-900 dark:text-blue-100'
                : 'hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100'
            ]"
          >
            <div class="flex items-center justify-between">
              <span>{{ option[optionLabel] }}</span>
              <svg
                v-if="isSelected(option)"
                class="w-4 h-4 text-blue-600 dark:text-blue-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
          </div>

          <!-- Loading more items indicator -->
          <div v-if="isLoadingMore" class="p-3 text-center text-gray-500 dark:text-gray-400">
            <div class="inline-flex items-center gap-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-blue-600"></div>
              <span class="text-sm">بارگذاری بیشتر...</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'

export default {
  name: 'BaseSelect2',
  emits: ['update:modelValue', 'search', 'tag-add'],
  props: {
    modelValue: {
      type: [String, Number, Array, Object],
      default: null
    },
    options: {
      type: Array,
      default: () => []
    },
    multiple: {
      type: Boolean,
      default: false
    },
    placeholder: {
      type: String,
      default: 'انتخاب کنید...'
    },
    searchPlaceholder: {
      type: String,
      default: 'جستجو...'
    },
    optionLabel: {
      type: String,
      default: 'name'
    },
    trackBy: {
      type: String,
      default: 'id'
    },
    label: {
      type: String,
      default: ''
    },
    disabled: {
      type: Boolean,
      default: false
    },
    searchable: {
      type: Boolean,
      default: true
    },
    taggable: {
      type: Boolean,
      default: false
    },
    paginated: {
      type: Boolean,
      default: false
    },
    pageSize: {
      type: Number,
      default: 10
    },
    fetchFunction: {
      type: Function,
      default: null
    },
    error: {
      type: String,
      default: ''
    }
  },
  setup(props, { emit }) {
    const dropdownRef = ref(null)
    const optionsListRef = ref(null)
    const searchInput = ref(null)
    const isOpen = ref(false)
    const searchQuery = ref('')
    const loading = ref(false)
    const allOptions = ref([...props.options])
    const currentPage = ref(1)
    const hasMorePages = ref(true)
    const isLoadingMore = ref(false)
    const id = ref(`select-${Math.random().toString(36).substr(2, 9)}`)

    // Computed
    const selectedItems = computed(() => {
      if (!props.modelValue) return []

      if (props.multiple) {
        return Array.isArray(props.modelValue) ? props.modelValue : []
      } else {
        return props.modelValue ? [props.modelValue] : []
      }
    })

    const filteredOptions = computed(() => {
      // If paginated, show all options from server (already filtered by search)
      if (props.paginated) {
        return allOptions.value
      }

      // If not paginated, filter locally
      if (!searchQuery.value) return allOptions.value

      const query = searchQuery.value.toLowerCase()
      return allOptions.value.filter(option =>
        option[props.optionLabel].toLowerCase().includes(query)
      )
    })

    const canCreateTag = computed(() => {
      if (!props.taggable || !searchQuery.value.trim()) return false

      const query = searchQuery.value.trim().toLowerCase()
      const exists = allOptions.value.some(option =>
        option[props.optionLabel].toLowerCase() === query
      )

      return !exists
    })

    // Methods
    const isSelected = (option) => {
      return selectedItems.value.some(item => item[props.trackBy] === option[props.trackBy])
    }

    const toggleDropdown = () => {
      if (props.disabled) return

      isOpen.value = !isOpen.value

      if (isOpen.value && props.searchable) {
        nextTick(() => {
          searchInput.value?.focus()
        })
      }
    }

    const closeDropdown = () => {
      isOpen.value = false
      searchQuery.value = ''
    }

    const selectOption = (option) => {
      if (props.multiple) {
        const newSelection = [...selectedItems.value]
        const existingIndex = newSelection.findIndex(item => item[props.trackBy] === option[props.trackBy])

        if (existingIndex > -1) {
          newSelection.splice(existingIndex, 1)
        } else {
          newSelection.push(option)
        }

        emit('update:modelValue', newSelection)
      } else {
        emit('update:modelValue', option)
        closeDropdown()
      }
    }

    const removeItem = (item) => {
      if (!props.multiple) return

      const newSelection = selectedItems.value.filter(selected =>
        selected[props.trackBy] !== item[props.trackBy]
      )
      emit('update:modelValue', newSelection)
    }

    const createNewTag = () => {
      if (!props.taggable || !searchQuery.value.trim()) return

      const newTag = {
        [props.trackBy]: searchQuery.value.trim(),
        [props.optionLabel]: searchQuery.value.trim(),
        newTag: true
      }

      allOptions.value.unshift(newTag)
      selectOption(newTag)
      emit('tag-add', searchQuery.value.trim())
      searchQuery.value = ''

      if (!props.multiple) {
        closeDropdown()
      }
    }

    const handleSearch = async () => {
      emit('search', searchQuery.value)

      if (props.paginated && props.fetchFunction) {
        // Reset pagination when searching
        currentPage.value = 1
        hasMorePages.value = true
        await loadOptions(searchQuery.value, 1, true)
      }
    }

    const handleEnterKey = () => {
      if (canCreateTag.value) {
        createNewTag()
      } else if (filteredOptions.value.length > 0) {
        selectOption(filteredOptions.value[0])
      }
    }

    const loadOptions = async (query = '', page = 1, reset = false) => {
      if (!props.fetchFunction || loading.value) return

      if (reset) {
        currentPage.value = 1
        hasMorePages.value = true
      }

      if (!hasMorePages.value && !reset) return

      loading.value = true

      try {
        const result = await props.fetchFunction({
          query: query,
          page: page,
          per_page: props.pageSize
        })

        if (result.success) {
          const newOptions = result.data || []

          if (reset) {
            allOptions.value = newOptions
          } else {
            // Avoid duplicates when appending
            const existingIds = new Set(allOptions.value.map(opt => opt[props.trackBy]))
            const uniqueNewOptions = newOptions.filter(opt => !existingIds.has(opt[props.trackBy]))
            allOptions.value = [...allOptions.value, ...uniqueNewOptions]
          }

          // Update pagination state
          hasMorePages.value = newOptions.length >= props.pageSize
          currentPage.value = page
        } else {
          // On error, stop pagination
          hasMorePages.value = false
        }
      } catch (error) {
        console.error('Error loading options:', error)
        hasMorePages.value = false
      } finally {
        loading.value = false
      }
    }

    const loadMore = async () => {
      if (hasMorePages.value && !loading.value && !isLoadingMore.value) {
        isLoadingMore.value = true
        await loadOptions(searchQuery.value, currentPage.value + 1, false)
        isLoadingMore.value = false
      }
    }

    const handleScroll = (event) => {
      const { scrollTop, scrollHeight, clientHeight } = event.target

      // Check if user has scrolled to near the bottom (within 20px)
      const nearBottom = scrollTop + clientHeight >= scrollHeight - 20

      if (nearBottom && hasMorePages.value && !loading.value && !isLoadingMore.value && props.paginated) {
        loadMore()
      }
    }

    const handleClickOutside = (event) => {
      if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown()
      }
    }

    // Watch for option changes
    watch(() => props.options, (newOptions) => {
      if (!props.paginated) {
        allOptions.value = [...newOptions]
      }
    }, { deep: true })

    // Lifecycle
    onMounted(async () => {
      document.addEventListener('click', handleClickOutside)

      if (props.paginated && props.fetchFunction) {
        await loadOptions('', 1, true)
      }
    })

    onBeforeUnmount(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    return {
      dropdownRef,
      optionsListRef,
      searchInput,
      isOpen,
      searchQuery,
      loading,
      isLoadingMore,
      allOptions,
      hasMorePages,
      selectedItems,
      filteredOptions,
      canCreateTag,
      id,
      isSelected,
      toggleDropdown,
      closeDropdown,
      selectOption,
      removeItem,
      createNewTag,
      handleSearch,
      handleEnterKey,
      handleScroll,
      loadMore
    }
  }
}
</script>
