<template>
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    دسته‌بندی‌ها
                </h3>
            </div>
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
                    class="cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 px-6 py-2 border-2 light:border-gray-400 dark:border-gray-200"
                    @click="handleCategoryClick(category)"
                >
                    {{ category.name }}
                </BaseBadge>
            </TransitionGroup>
            <button
                class="px-4 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-sm font-medium bg-blue-200 hover:bg-blue-400 dark:hover:bg-gray-700 transition-colors"
                @click="goToCategories"
                type="button"
            >
                مشاهده بیشتر
            </button>
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
import { ref, onMounted, defineAsyncComponent } from 'vue'
import { useRouter } from 'vue-router'
import { useCategories } from '../composables'
const BaseAlert = defineAsyncComponent(() => import('./ui/BaseAlert.vue'))
const BaseBadge = defineAsyncComponent(() => import('./ui/BaseBadge.vue'))

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
            return 'default'
        }

        const loadPopularCategories = async () => {
            clearErrors()
            const result = await fetchPopularCategories(props.limit)
            if (result.success) {
                popularCategories.value = result.data
            }
        }

        const handleCategoryClick = (category) => {
            emit('category-click', category)
        }

        const goToCategories = () => {
            router.push('/categories')
        }

        onMounted(async () => {
            await loadPopularCategories()
        })

        return {
            popularCategories,
            isLoading,
            errors,
            getCategoryVariant,
            handleCategoryClick,
            goToCategories
        }
    }
}
</script>
