<template>
    <!-- Desktop Search (hidden on mobile) -->
    <div class="hidden md:block relative z-50" ref="dropdownRef">
        <!-- Search Input -->
        <BaseInput v-model="searchQuery" :placeholder="placeholder" :variant="variant" :rounded="rounded"
            @update:modelValue="handleInput" @focus="handleFocus" @keydown="handleKeydown" :class="inputClass">
            <template #prefix>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </template>
            <template #suffix v-if="isLoading">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
            </template>
        </BaseInput>

        <!-- Desktop Search Results Dropdown -->
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
            <div v-if="showDropdown && (displayedResults.length > 0 || showNoResults)"
                class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-96 overflow-y-auto">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.386 0-4.569-.831-6.293-2.209">
                            </path>
                        </svg>
                        <span>نتیجه‌ای یافت نشد</span>
                    </div>
                </div>

                <!-- Search results -->
                <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li v-for="(question, index) in displayedResults" :key="question.id" :class="[
                        'cursor-pointer transition-colors duration-150',
                        selectedIndex === index
                            ? 'bg-blue-50 dark:bg-blue-900/20'
                            : 'hover:bg-gray-50 dark:hover:bg-gray-700'
                    ]" @click="selectQuestion(question)" @mouseenter="selectedIndex = index">
                        <div class="p-4">
                            <!-- Question title -->
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1 line-clamp-2">
                                {{ question.title }}
                            </h4>

                            <!-- Stats row -->
                            <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                <!-- Solved badge -->
                                <span v-if="question.is_solved"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-sm font-semibold bg-green-100 text-green-700 ml-2">
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    حل شده
                                </span>
                                <!-- Category -->
                                <span v-if="question.category" class="flex items-center gap-1 text-sm ml-2">
                                    {{ question.category.name }}
                                </span>
                                <!-- Answers count -->
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                        </path>
                                    </svg>
                                    <span class="mt-[5px] text-sm">{{ question.answers_count || 0 }}</span>
                                </span>
                                <span class="mx-2">|</span>
                                <!-- Votes count -->
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                    <span class="mt-[5px] text-sm">{{ question.votes_count || 0 }}</span>
                                </span>
                                <span class="mx-2">|</span>
                                <!-- Views -->
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    <span class="mt-[5px] text-sm">{{ question.views || 0 }}</span>
                                </span>
                            </div>
                        </div>
                    </li>
                </ul>
                <div v-if="hasMoreResults" class="p-2 text-center">
                    <BaseButton size="sm" variant="primary" class="w-full" @click="showMoreQuestions" :disabled="isLoading">
                        نمایش بیشتر
                    </BaseButton>
                </div>
            </div>
        </Transition>
    </div>

    <!-- Mobile Search Icon (visible on mobile only) -->
    <div class="md:hidden">
        <button
            type="button"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 dark:focus:ring-blue-400"
            @click="toggleMobileSearch"
            :aria-label="isMobileSearchOpen ? 'بستن جستجو' : 'باز کردن جستجو'"
            data-mobile-search-trigger
        >
            <svg v-if="!isMobileSearchOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                </path>
            </svg>
        </button>
    </div>

    <!-- Blur Backdrop for Desktop and Mobile -->
    <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDropdown || isMobileSearchOpen"
                class="fixed inset-0 z-30 backdrop-blur-md bg-black/20 dark:bg-black/30"
                @click="hideBackdrop"></div>
        </Transition>
    </Teleport>

    <!-- Mobile Search Overlay (full width below header) -->
    <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 transform -translate-y-2" enter-to-class="opacity-100 transform translate-y-0"
            leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 transform translate-y-0"
            leave-to-class="opacity-0 transform -translate-y-2">
            <div v-if="isMobileSearchOpen"
                class="fixed inset-x-0 top-16 z-40 bg-white dark:bg-gray-800 shadow-lg border-b border-gray-200 dark:border-gray-700 md:hidden"
                ref="mobileSearchRef">
                <!-- Mobile Search Input -->
                <div class="p-4 relative z-50">
                    <BaseInput v-model="searchQuery" :placeholder="placeholder" :variant="variant" :rounded="rounded"
                        @update:modelValue="handleInput" @focus="handleFocus" @keydown="handleKeydown" class="w-full"
                        ref="mobileSearchInput">
                        <template #prefix>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </template>
                        <template #suffix v-if="isLoading">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                        </template>
                    </BaseInput>
                </div>

                <!-- Mobile Search Results -->
                <div v-if="displayedResults.length > 0 || showNoResults"
                    class="max-h-96 overflow-y-auto border-t border-gray-200 dark:border-gray-700">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.386 0-4.569-.831-6.293-2.209">
                                </path>
                            </svg>
                            <span>نتیجه‌ای یافت نشد</span>
                        </div>
                    </div>

                    <!-- Search results -->
                    <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="(question, index) in displayedResults" :key="question.id" :class="[
                            'cursor-pointer transition-colors duration-150',
                            selectedIndex === index
                                ? 'bg-blue-50 dark:bg-blue-900/20'
                                : 'hover:bg-gray-50 dark:hover:bg-gray-700'
                        ]" @click="selectQuestion(question)" @mouseenter="selectedIndex = index">
                            <div class="p-4">
                                <!-- Question title -->
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1 line-clamp-2">
                                    {{ question.title }}
                                </h4>

                                <!-- Stats row -->
                                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                    <!-- Solved badge -->
                                    <span v-if="question.is_solved"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700 ml-2">
                                        <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        حل شده
                                    </span>
                                    <!-- Category -->
                                    <span v-if="question.category" class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                            </path>
                                        </svg>
                                        {{ question.category.name }}
                                    </span>
                                    <!-- Answers count -->
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                            </path>
                                        </svg>
                                        {{ question.answers_count || 0 }}
                                    </span>
                                    <span class="mx-2">|</span>
                                    <!-- Votes count -->
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                        </svg>
                                        {{ question.votes_count || 0 }}
                                    </span>
                                    <span class="mx-2">|</span>
                                    <!-- Views -->
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                        {{ question.views || 0 }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div v-if="hasMoreResults" class="p-2 text-center border-t border-gray-200 dark:border-gray-700">
                        <BaseButton size="sm" variant="primary" class="w-full" @click="showMoreQuestions" :disabled="isLoading">
                            نمایش بیشتر
                        </BaseButton>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { BaseInput, BaseButton } from './ui'
import { useQuestions } from '../composables/useQuestions'

export default {
    name: 'SearchComponent',
    components: {
        BaseInput,
        BaseButton
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
        const mobileSearchRef = ref(null)
        const mobileSearchInput = ref(null)
        const searchQuery = ref(props.modelValue)
        const searchResults = ref([])
        const allSearchResults = ref([])
        const showLimit = ref(10)
        const isLoading = ref(false)
        const showDropdown = ref(false)
        const selectedIndex = ref(-1)
        const searchTimeout = ref(null)
        const isMobileSearchOpen = ref(false)

        // Computed
        const showNoResults = computed(() => {
            return !isLoading.value && searchQuery.value.trim().length > 0 && searchResults.value.length === 0
        })

        const displayedResults = computed(() => {
            return allSearchResults.value.slice(0, showLimit.value)
        })

        const hasMoreResults = computed(() => {
            return allSearchResults.value.length > showLimit.value
        })

        // Methods
        const performSearch = async (query) => {
            if (!query || query.trim().length < 2) {
                searchResults.value = []
                allSearchResults.value = []
                showDropdown.value = false
                showLimit.value = 10 // Reset show limit
                return
            }

            isLoading.value = true
            showDropdown.value = true

            try {
                const result = await searchQuestions(query.trim(), 50)
                allSearchResults.value = result.data || []
                searchResults.value = allSearchResults.value.slice(0, 10)
                showLimit.value = 10
            } catch (error) {
                console.error('Search failed:', error)
                searchResults.value = []
                allSearchResults.value = []
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
            if (searchQuery.value.trim().length > 0 && displayedResults.value.length > 0) {
                showDropdown.value = true
            }
        }

        const hideDropdown = () => {
            showDropdown.value = false
        }

        const hideBackdrop = () => {
            showDropdown.value = false
            isMobileSearchOpen.value = false
        }

        const selectQuestion = (question) => {
            if (question && question.id) {
                showDropdown.value = false
                isMobileSearchOpen.value = false
                emit('select', question)
                const targetRoute = `/questions/${question.slug}`
                router.push(targetRoute).catch(error => {
                    if (error.name !== 'NavigationDuplicated') {
                        console.error('Navigation error:', error)
                    }
                })
            }
        }

        const toggleMobileSearch = () => {
            isMobileSearchOpen.value = !isMobileSearchOpen.value
            if (isMobileSearchOpen.value) {
                nextTick(() => {
                    if (mobileSearchInput.value) {
                        const inputElement = mobileSearchInput.value.$el?.querySelector('input') || mobileSearchInput.value.$el
                        if (inputElement && inputElement.focus) {
                            inputElement.focus()
                        }
                    }
                })
            }
        }

        const showMoreQuestions = () => {
            showLimit.value = Math.min(showLimit.value + 10, allSearchResults.value.length)
            searchResults.value = allSearchResults.value.slice(0, showLimit.value)
        }

        const selectOnEnter = () => {
            if (selectedIndex.value >= 0 && displayedResults.value[selectedIndex.value]) {
                selectQuestion(displayedResults.value[selectedIndex.value])
            } else {
                handleSearch(searchQuery.value)
                hideDropdown()
                isMobileSearchOpen.value = false
            }
        }

        const handleKeydown = (e) => {
            if (!showDropdown.value && !isMobileSearchOpen.value) return

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault()
                    selectedIndex.value = (selectedIndex.value + 1) % displayedResults.value.length
                    break
                case 'ArrowUp':
                    e.preventDefault()
                    selectedIndex.value = (selectedIndex.value - 1 + displayedResults.value.length) % displayedResults.value.length
                    break
                case 'Enter':
                    e.preventDefault()
                    selectOnEnter()
                    break
                case 'Escape':
                    e.preventDefault()
                    hideDropdown()
                    isMobileSearchOpen.value = false
                    break
            }
        }

        const handleClickOutside = (event) => {
            if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
                hideDropdown()
            }
            if (mobileSearchRef.value && !mobileSearchRef.value.contains(event.target) && !event.target.closest('[data-mobile-search-trigger]')) {
                isMobileSearchOpen.value = false
            }
        }

        onMounted(() => {
            document.addEventListener('click', handleClickOutside)
        })

        onUnmounted(() => {
            document.removeEventListener('click', handleClickOutside)
            clearTimeout(searchTimeout.value)
        })

        watch(() => props.modelValue, (newValue) => {
            if (newValue !== searchQuery.value) {
                searchQuery.value = newValue
            }
        })

        return {
            dropdownRef,
            mobileSearchRef,
            mobileSearchInput,
            searchQuery,
            searchResults,
            displayedResults,
            hasMoreResults,
            isLoading,
            showDropdown,
            selectedIndex,
            showNoResults,
            isMobileSearchOpen,
            handleInput,
            handleFocus,
            handleKeydown,
            selectQuestion,
            toggleMobileSearch,
            showMoreQuestions,
            hideBackdrop
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

/* Mobile search overlay positioning */
.fixed.inset-x-0.top-16 {
    top: 4rem;
}

/* Smooth transitions for mobile search */
.transition.ease-out.duration-300 {
    transition-property: opacity, transform;
    transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Mobile search results scrolling */
.max-h-96.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.max-h-96.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.max-h-96.overflow-y-auto::-webkit-scrollbar-track {
    background: #f7fafc;
}

.max-h-96.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.max-h-96.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Dark mode scrollbar */
:global(.dark) .max-h-96.overflow-y-auto {
    scrollbar-color: #4a5568 #2d3748;
}

:global(.dark) .max-h-96.overflow-y-auto::-webkit-scrollbar-track {
    background: #2d3748;
}

:global(.dark) .max-h-96.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #4a5568;
}

:global(.dark) .max-h-96.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #718096;
}

/* Ensure mobile search and desktop input are above blur backdrop */
.fixed.z-40, .relative.z-50 {
    z-index: 40;
}

/* Blur backdrop styling */
.backdrop-blur-md {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px); /* برای سازگاری با Safari */
}

/* Fallback for browsers that don't support backdrop-filter */
@supports not (backdrop-filter: blur(10px)) {
    .backdrop-blur-md {
        background-color: rgba(0, 0, 0, 0.3); /* فال‌بک برای حالت روشن */
    }
    :global(.dark) .backdrop-blur-md {
        background-color: rgba(0, 0, 0, 0.4); /* فال‌بک برای حالت دارک */
    }
}
</style>