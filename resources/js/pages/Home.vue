<template>
    <ContentArea layout="with-sidebar" :show-sidebar="true" main-width="3/4" sidebar-width="1/4">
        <!-- Hero Section -->
        <template #hero>
            <h1 class="text-center">انجمن حم بزرگترین انجمن پرسش و پاسخ ایران</h1>
            <div class="relative overflow-hidden rounded-lg shadow-sm">
                <img :src="landingImageUrl" alt="خوش آمدید به سیستم پرسش و پاسخ" class="w-full h-auto object-cover"
                    loading="eager" fetchpriority="high" width="1200" height="480" />
            </div>
        </template>

        <!-- Filters Section -->
        <template #filters>
            <!-- Popular Categories Section -->
            <PopularCategories :limit="15" @category-click="handleCategoryClick" :selected-category="selectedCategory"
                class="mb-6" />
            <!-- Header -->
            <FilterQuestion @filters-changed="handleFiltersChanged" />
        </template>

        <!-- Main Content -->
        <template #main>
            <!-- Initial Loading State -->
            <div v-if="isLoading" class="grid grid-cols-1 gap-4">
                <div v-for="n in 10" :key="n" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-pulse">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-4"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-6"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-full mb-2"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-5/6 mb-4"></div>
                    <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/6"></div>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div v-else-if="errors.fetch">
                <BaseAlert variant="error" :message="errors.fetch" />
            </div>

            <!-- Question List -->
            <div v-else-if="questions.length > 0">
                <!-- Questions -->
                <div class="space-y-4">
                    <TransitionGroup name="question" tag="div" class="space-y-4">
                        <QuestionCard v-for="question in questions" :key="question.id" :question="question"
                            @click="handleQuestionClick(question)" />
                    </TransitionGroup>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.meta" class="mt-8">
                    <BasePagination :current-page="pagination.meta.current_page"
                        :total-pages="pagination.meta.last_page" :total="pagination.meta.total"
                        :per-page="pagination.meta.per_page" @page-changed="handlePageChange" />
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">سوالی یافت نشد</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">با پرسیدن اولین سوال شروع کنید.</p>
            </div>
        </template>

        <!-- Sidebar -->
        <template #sidebar>
            <HomeSidebar />
        </template>

        <!-- Footer Section -->
        <template #footer>
            <!-- Most Active Users Section -->
            <div v-if="!isLoading && questions.length > 0">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        فعالان انجمن
                    </h2>
                    <BaseButton class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center"
                        @click="$router.push('/authors')">
                        مشاهده بیشتر
                    </BaseButton>
                </div>

                <!-- Loading State for Active Users -->
                <div v-if="isLoadingUsers" class="grid gap-4"
                    style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div v-for="n in 5" :key="n"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-pulse">
                        <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full mx-auto mb-4"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded mb-4"></div>
                        <div class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </div>
                    </div>
                </div>

                <!-- Error State for Active Users -->
                <div v-else-if="userErrors.activeUsers">
                    <BaseAlert variant="error" :message="userErrors.activeUsers" />
                </div>

                <!-- Active Users Grid -->
                <div v-else-if="activeUsers.length > 0" class="grid gap-2"
                    style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <TransitionGroup name="user" tag="div" class="contents">
                        <router-link
                            v-for="user in activeUsers"
                            :key="user.id"
                            :to="`/authors/${user.id}`"
                            class="block"
                        >
                            <UserCard :user="user" />
                        </router-link>
                    </TransitionGroup>
                </div>

                <!-- Empty State for Active Users -->
                <div v-else class="text-center py-8">
                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                        </path>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">هنوز کاربر فعالی یافت نشد</p>
                </div>
            </div>
        </template>
    </ContentArea>
</template>

<script>
import { onMounted, ref, computed, getCurrentInstance, onBeforeUnmount, defineAsyncComponent } from 'vue'
import { useRouter } from 'vue-router'
import { useQuestions, useUsers } from '../composables'
import { usePageTitle } from '../composables/usePageTitle'
const QuestionCard = defineAsyncComponent(() => import('../components/QuestionCard.vue'))
const UserCard = defineAsyncComponent(() => import('../components/UserCard.vue'))
const HomeSidebar = defineAsyncComponent(() => import('../components/sidebar/HomeSidebar.vue'))
const PopularCategories = defineAsyncComponent(() => import('../components/PopularCategories.vue'))
const FilterQuestion = defineAsyncComponent(() => import('../components/FilterQuestion.vue'))
const BaseButton = defineAsyncComponent(() => import('../components/ui/BaseButton.vue'))
const BaseAlert = defineAsyncComponent(() => import('../components/ui/BaseAlert.vue'))
const BasePagination = defineAsyncComponent(() => import('../components/ui/BasePagination.vue'))
const ContentArea = defineAsyncComponent(() => import('../components/ContentArea.vue'))
import questionService from '../services/questionService.js'

export default {
    name: 'Home',
    components: {
        QuestionCard,
        UserCard,
        HomeSidebar,
        PopularCategories,
        FilterQuestion,
        BaseButton,
        BaseAlert,
        BasePagination,
        ContentArea
    },
    emits: [],
    setup(props) {
        const router = useRouter()
        const instance = getCurrentInstance()
        const { setTitle } = usePageTitle()

        const currentFilters = ref({})
        const selectedCategory = ref(null)

        // Set home page title
        setTitle('صفحه اصلی')

        // Computed property for landing image URL
        const landingImageUrl = computed(() => {
            return '/assets/images/landing.png'
        })

        // Use questions composable for pagination
        const {
            questions,
            isLoading,
            pagination,
            errors,
            fetchQuestions,
            clearErrors,
        } = useQuestions()

        // Use users composable for active users
        const {
            activeUsers,
            isLoading: isLoadingUsers,
            errors: userErrors,
            fetchActiveUsers,
        } = useUsers()

        const handleFiltersChanged = async (filters) => {
            clearErrors()
            currentFilters.value = filters
            await fetchQuestions(filters)
        }

        // Handle page change
        const handlePageChange = async (page) => {
            if (pagination.value.meta && page === pagination.value.meta.current_page) return

            clearErrors()

            const params = { ...currentFilters.value, page }

            // Scroll to top smoothly
            window.scrollTo({ top: 400, behavior: 'smooth' });

            await fetchQuestions(params)
        }

        const handleQuestionClick = (question) => {
            router.push(`/questions/${question.slug}`)
        }

        const handleUserClick = (user) => {
            // For now, just log the user click. You can implement user profile page navigation later
            console.log('User clicked:', user)
            // TODO: Implement user profile page navigation
            // router.push(`/users/${user.id}`)
        }

        const handleCategoryClick = (category) => {
            if (selectedCategory.value && selectedCategory.value.id === category.id) {
                // If the same category is clicked again, deselect it and show all questions
                selectedCategory.value = null
                handleFiltersChanged({ ...currentFilters.value, category_id: undefined })
            } else {
                selectedCategory.value = category
                handleFiltersChanged({ ...currentFilters.value, category_id: category.id })
            }
        }

        const refreshQuestions = async () => {
            clearErrors()
            await fetchQuestions(currentFilters.value)
        }

        // Add a new question to the beginning of the list (for external use)
        const prependQuestion = (question) => {
            // If we're on the first page and no specific sorting is applied,
            // or if we're sorting by newest first, add the question to the beginning
            const isFirstPage = !pagination.value.meta || pagination.value.meta.current_page === 1
            const isSortedByNewest = !currentFilters.value.sort ||
                (currentFilters.value.sort === 'created_at' &&
                    (!currentFilters.value.order || currentFilters.value.order === 'desc'))

            if (isFirstPage && isSortedByNewest) {
                questions.value.unshift(question)
            } else {
                // Otherwise, refresh the questions to ensure proper ordering
                refreshQuestions()
            }
        }        // Update a question in the list (for external use)
        const updateQuestion = (questionId, updatedQuestion) => {
            const index = questions.value.findIndex(q => q.id === questionId)
            if (index > -1) {
                questions.value[index] = { ...questions.value[index], ...updatedQuestion }
            }
        }

        onMounted(async () => {
            // Load initial questions and active users in parallel
            const [questionsResult, usersResult] = await Promise.allSettled([
                fetchQuestions(),
                fetchActiveUsers(5)
            ])

            // Log any errors but don't block the UI
            if (questionsResult.status === 'rejected') {
                console.error('Failed to load questions:', questionsResult.reason)
            }
            if (usersResult.status === 'rejected') {
                console.error('Failed to load active users:', usersResult.reason)
            }

            // Subscribe to question service events
            const unsubscribeQuestionCreated = questionService.subscribe('question-created', (newQuestion) => {
                prependQuestion(newQuestion)
            })

            const unsubscribeQuestionUpdated = questionService.subscribe('question-updated', (updatedQuestion) => {
                updateQuestion(updatedQuestion.id, updatedQuestion)
            })

            // Store unsubscribe functions to clean up later
            questionServiceCleanup.value = [unsubscribeQuestionCreated, unsubscribeQuestionUpdated]
        })

        const questionServiceCleanup = ref([])

        onBeforeUnmount(() => {
            // Clean up question service subscriptions
            if (questionServiceCleanup.value) {
                questionServiceCleanup.value.forEach(unsubscribe => unsubscribe())
            }
        })

        return {
            questions,
            isLoading,
            pagination,
            errors,
            activeUsers,
            isLoadingUsers,
            userErrors,
            landingImageUrl,
            handleFiltersChanged,
            // Other methods
            handleQuestionClick,
            handleUserClick,
            handleCategoryClick,
            handlePageChange,
            refreshQuestions,
            prependQuestion,
            updateQuestion,
            selectedCategory
        }
    }
}
</script>

<style scoped>
/* Transition animations for questions */
.question-enter-active {
    transition: all 0.5s ease;
}

.question-leave-active {
    transition: all 0.3s ease;
}

.question-enter-from {
    opacity: 0;
    transform: translateY(-20px);
}

.question-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

.question-move {
    transition: transform 0.3s ease;
}

/* Transition animations for user cards */
.user-enter-active {
    transition: all 0.5s ease;
}

.user-leave-active {
    transition: all 0.3s ease;
}

.user-enter-from {
    opacity: 0;
    transform: translateY(-20px) scale(0.9);
}

.user-leave-to {
    opacity: 0;
    transform: translateY(20px) scale(0.9);
}

.user-move {
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
</style>
