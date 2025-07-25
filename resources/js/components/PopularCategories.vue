<template>
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    دسته‌بندی‌های محبوب
                </h3>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                    </path>
                </svg>
            </div>

            <button
                v-if="!isLoading && !errors.fetchPopular"
                @click="refreshCategories"
                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                :disabled="isRefreshing"
            >
                <svg class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex flex-wrap gap-2">
            <div v-for="n in 15" :key="`loading-${n}`"
                 class="animate-pulse bg-gray-200 dark:bg-gray-700 rounded-full h-7 px-3 py-1"
                 :style="{ width: `${Math.random() * 60 + 80}px` }">
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="errors.fetchPopular" class="text-center py-4">
            <BaseAlert variant="error" :message="errors.fetchPopular" />
        </div>

        <!-- Categories -->
        <div v-else-if="popularCategories.length > 0" class="flex flex-wrap gap-2">
            <TransitionGroup name="category" tag="div" class="flex flex-wrap gap-2">
                <BaseBadge
                    v-for="category in popularCategories"
                    :key="category.id"
                    :variant="getCategoryVariant(category)"
                    size="sm"
                    class="cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
                    @click="handleCategoryClick(category)"
                >
                    <template #icon>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                    </template>

                    {{ category.name }}

                    <span v-if="getTotalActivity(category) > 0" class="text-xs opacity-75 mr-1">
                        ({{ formatCount(getTotalActivity(category)) }})
                    </span>
                </BaseBadge>
            </TransitionGroup>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-4">
            <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                </path>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                هنوز دسته‌بندی محبوبی یافت نشد
            </p>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCategories } from '../composables'
import { BaseAlert, BaseBadge } from './ui'

export default {
    name: 'PopularCategories',
    components: {
        BaseAlert,
        BaseBadge
    },
    emits: ['category-click'],
    props: {
        limit: {
            type: Number,
            default: 15
        },
        selectedCategory: {
            type: Object,
            default: null
        }
    },

    setup(props, { emit }) {
        const router = useRouter()
        const popularCategories = ref([])
        const isRefreshing = ref(false)

        const {
            isLoading,
            errors,
            fetchPopularCategories,
            clearErrors
        } = useCategories()

        const getCategoryVariant = (category) => {
            if (props.selectedCategory && props.selectedCategory.id === category.id) {
                return 'primary';
            }
            const total = getTotalActivity(category)
            if (total > 100) return 'success'
            if (total > 50) return 'info'
            if (total > 20) return 'warning'
            return 'default'
        }

        const getTotalActivity = (category) => {
            const questions = category.questions_count || 0
            const answers = category.answers_count || 0
            const comments = category.comments_count || 0
            return questions + answers + comments
        }

        const formatCount = (count) => {
            if (count >= 1000) {
                return (count / 1000).toFixed(1) + 'k'
            }
            return count.toString()
        }

        const loadPopularCategories = async () => {
            clearErrors()
            const result = await fetchPopularCategories(props.limit)
            if (result.success) {
                popularCategories.value = result.data
            }
        }

        const refreshCategories = async () => {
            isRefreshing.value = true
            await loadPopularCategories()
            isRefreshing.value = false
        }

        const handleCategoryClick = (category) => {
            emit('category-click', category)
        }

        onMounted(async () => {
            await loadPopularCategories()
        })

        return {
            popularCategories,
            isLoading,
            isRefreshing,
            errors,
            getCategoryVariant,
            getTotalActivity,
            formatCount,
            refreshCategories,
            handleCategoryClick
        }
    }
}
</script>

<style scoped>
/* Transition animations for categories */
.category-enter-active {
    transition: all 0.3s ease;
}

.category-leave-active {
    transition: all 0.2s ease;
}

.category-enter-from {
    opacity: 0;
    transform: translateY(-10px) scale(0.9);
}

.category-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.9);
}

.category-move {
    transition: transform 0.3s ease;
}

/* Loading shimmer effect */
@keyframes shimmer {
    0% {
        background-position: -200px 0;
    }
    100% {
        background-position: calc(200px + 100%) 0;
    }
}

.animate-pulse {
    animation: shimmer 1.5s ease-in-out infinite;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200px 100%;
}

.dark .animate-pulse {
    background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
    background-size: 200px 100%;
}

/* RTL support for count spacing */
:global(.rtl) .mr-1 {
    margin-right: 0;
    margin-left: 0.25rem;
}
</style>
