<template>
    <div class="flex flex-col items-start justify-between mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">
            فیلترها
        </h1>

        <!-- Filter Section -->
        <div class="w-full space-y-4">
            <!-- Filter Categories -->
            <div class="flex gap-4">
                <!-- Sort Filter -->
                <div class="relative">
                    <button @click="toggleSortFilter"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': showSortFilter }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                        مرتب سازی بر اساس
                    </button>

                    <!-- Sort Dropdown -->
                    <div v-if="showSortFilter"
                        class="absolute top-full right-0 mt-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-10">
                        <div class="p-4">
                            <div class="space-y-2">
                                <label v-for="option in sortOptions" :key="option.value"
                                    class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded">
                                    <input type="radio" :value="option.value" v-model="selectedSortOptions"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                        :name="'sortOptionRadio'">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ option.label }}</span>
                                </label>
                            </div>

                            <div class="flex gap-2 mt-4">
                                <button @click="applySortFilters"
                                    class="w-full px-2 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    اعمال
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tags Filter -->
                <div class="relative">
                    <button @click="toggleTagsFilter"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': showTagsFilter }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                        برچسب ها
                    </button>

                    <!-- Tags Dropdown -->
                    <div v-if="showTagsFilter"
                        class="absolute top-full left-0 mt-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-10">
                        <div class="p-4">
                            <div class="relative mb-3">
                                <input v-model="tagSearchQuery" type="text" placeholder="جستجو..."
                                    class="w-full px-3 py-1.5 pr-10 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                <label v-for="tag in filteredTags" :key="tag.id"
                                    class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded">
                                    <input type="checkbox" :value="tag.id" v-model="selectedTags"
                                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ tag.name }}</span>
                                </label>
                            </div>

                            <div class="flex gap-2 mt-4">
                                <button @click="applyTagFilters"
                                    class="flex-1 px-2 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    انتخاب
                                </button>
                                <button @click="clearTagFilters"
                                    class="flex-1 px-2 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">
                                    حذف
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Filters -->
            <div v-if="hasActiveFilters" class="space-y-3">
                <!-- Selected Tags -->
                <div v-if="appliedTags.length > 0">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">برچسب ها:</div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="tag in appliedTags" :key="tag.id"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm">
                            {{ tag.name }}
                            <button @click="removeTagFilter(tag.id)"
                                class="text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </span>
                    </div>
                </div>

                <!-- Selected Sort Options -->
                <div v-if="appliedSortOptions.length > 0">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">مرتب سازی بر اساس:</div>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="option in appliedSortOptions" :key="option.value"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm">
                            {{ option.label }}
                            <button @click="removeSortFilter(option.value)"
                                class="text-green-600 dark:text-green-300 hover:text-green-800 dark:hover:text-green-100">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import api from '../services/api.js'

export default {
    name: 'FilterQuestion',
    emits: ['filters-changed'],
    setup(props, { emit }) {
        // Filter states
        const showTagsFilter = ref(false)
        const showSortFilter = ref(false)
        const tagSearchQuery = ref('')
        const selectedTags = ref([])
        const selectedSortOptions = ref('')
        const appliedTags = ref([])
        const appliedSortOptions = ref([])

        // Available tags
        const availableTags = ref([])

        const fetchTags = async () => {
            try {
                const response = await api.get('/tags', {
                    params: { query: tagSearchQuery.value }
                })
                availableTags.value = response.data.data || response.data
            } catch (error) {
                console.error('Error fetching tags:', error)
            }
        }

        onMounted(fetchTags)

        let debounceTimer
        watch(tagSearchQuery, () => {
            clearTimeout(debounceTimer)
            debounceTimer = setTimeout(() => {
                fetchTags()
            }, 300)
        })

        // Sort options
        const sortOptions = ref([
            { value: 'newest', label: 'جدید' },
            { value: 'oldest', label: 'قدیمی' },
            { value: 'most_votes', label: 'رای' },
            { value: 'most_answers', label: 'پاسخ ها' },
            { value: 'most_views', label: 'بازدید ها' },
            { value: 'unanswered', label: 'بی پاسخ' },
            { value: 'solved', label: 'حل شده'},
            { value: 'unsolved', label: 'حل نشده' }
        ])

        // Computed properties
        const filteredTags = computed(() => {
            return availableTags.value
        })

        const hasActiveFilters = computed(() => {
            return appliedTags.value.length > 0 || appliedSortOptions.value.length > 0
        })

        // Filter methods
        const toggleTagsFilter = () => {
            showTagsFilter.value = !showTagsFilter.value
            if (showSortFilter.value) showSortFilter.value = false
        }

        const toggleSortFilter = () => {
            showSortFilter.value = !showSortFilter.value
            if (showTagsFilter.value) showTagsFilter.value = false
        }

        const applyTagFilters = () => {
            appliedTags.value = availableTags.value.filter(tag =>
                selectedTags.value.includes(tag.id)
            )
            showTagsFilter.value = false
            applyFilters()
        }

        const clearTagFilters = () => {
            selectedTags.value = []
            appliedTags.value = []
            showTagsFilter.value = false
            applyFilters()
        }

        const applySortFilters = () => {
            appliedSortOptions.value = sortOptions.value.filter(option =>
                option.value === selectedSortOptions.value
            )
            showSortFilter.value = false
            applyFilters()
        }

        const clearSortFilters = () => {
            selectedSortOptions.value = []
            appliedSortOptions.value = []
            showSortFilter.value = false
            applyFilters()
        }

        const removeTagFilter = (tagId) => {
            selectedTags.value = selectedTags.value.filter(id => id !== tagId)
            appliedTags.value = appliedTags.value.filter(tag => tag.id !== tagId)
            applyFilters()
        }

        const removeSortFilter = (sortValue) => {
            if (selectedSortOptions.value === sortValue) {
                selectedSortOptions.value = ''
            }
            appliedSortOptions.value = appliedSortOptions.value.filter(option => option.value !== sortValue)
            applyFilters()
        }

        const applyFilters = () => {
            let params = { page: 1 }

            // Apply tag filters
            if (appliedTags.value.length > 0) {
                params.tags = appliedTags.value.map(tag => tag.id).join(',')
            }

            // Apply sort filters (use the first selected sort option)
            if (appliedSortOptions.value.length > 0) {
                const primarySort = appliedSortOptions.value[0].value
                switch (primarySort) {
                    case 'newest':
                        params.sort = 'created_at'
                        params.order = 'desc'
                        break
                    case 'oldest':
                        params.sort = 'created_at'
                        params.order = 'asc'
                        break
                    case 'most_votes':
                        params.sort = 'votes'
                        params.order = 'desc'
                        break
                    case 'most_answers':
                        params.sort = 'answers_count'
                        params.order = 'desc'
                        break
                    case 'most_views':
                        params.sort = 'views_count'
                        params.order = 'desc'
                        break
                    case 'unanswered':
                        params.filter = 'unanswered'
                        break
                    case 'solved':
                        params.filter = 'solved'
                        break
                    case 'unsolved':
                        params.filter = 'unsolved'
                        break
                }
            }
            emit('filters-changed', params)
        }

        // Close dropdowns when clicking outside
        const closeDropdowns = (event) => {
            const filterContainer = event.target.closest('.relative')
            if (!filterContainer) {
                showTagsFilter.value = false
                showSortFilter.value = false
            }
        }

        onMounted(() => {
            document.addEventListener('click', closeDropdowns)
        })

        onBeforeUnmount(() => {
            document.removeEventListener('click', closeDropdowns)
        })

        return {
            showTagsFilter,
            showSortFilter,
            tagSearchQuery,
            selectedTags,
            selectedSortOptions,
            appliedTags,
            appliedSortOptions,
            availableTags,
            sortOptions,
            filteredTags,
            hasActiveFilters,
            toggleTagsFilter,
            toggleSortFilter,
            applyTagFilters,
            clearTagFilters,
            applySortFilters,
            clearSortFilters,
            removeTagFilter,
            removeSortFilter,
        }
    }
}
</script>

<style scoped>
/* Filter dropdown styles */
.filter-dropdown {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.dark .filter-dropdown {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

/* Custom scrollbar for filter dropdowns */
.filter-dropdown .space-y-2::-webkit-scrollbar {
    width: 6px;
}

.filter-dropdown .space-y-2::-webkit-scrollbar-track {
    background: transparent;
}

.filter-dropdown .space-y-2::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.4);
    border-radius: 3px;
}

.filter-dropdown .space-y-2::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.6);
}

.dark .filter-dropdown .space-y-2::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.6);
}

.dark .filter-dropdown .space-y-2::-webkit-scrollbar-thumb:hover {
    background: rgba(75, 85, 99, 0.8);
}

/* Filter chip animations */
.filter-chip-enter-active {
    transition: all 0.3s ease;
}

.filter-chip-leave-active {
    transition: all 0.3s ease;
}

.filter-chip-enter-from {
    opacity: 0;
    transform: scale(0.8);
}

.filter-chip-leave-to {
    opacity: 0;
    transform: scale(0.8);
}
</style>
