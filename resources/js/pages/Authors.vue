<template>
    <ContentArea layout="full-width" :show-sidebar="false">
        <!-- Page Header -->
        <template #filters>
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            فعالان انجمن
                        </h1>
                    </div>

                    <!-- Stats Summary -->
                    <div class="flex gap-4 text-sm">
                        <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg shadow-sm">
                            <span class="text-gray-600 dark:text-gray-400">کل اعضای انجمن:</span>
                            <span class="font-semibold text-blue-600 dark:text-blue-400 mr-1">
                                {{ pagination.total || 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm mb-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <input
                                type="text"
                                v-model="searchQuery"
                                @input="handleSearchInput"
                                placeholder="جستجو در نام یا ایمیل نویسندگان..."
                                class="w-full pr-10 pl-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                       focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div class="flex gap-3">
                        <select
                            v-model="sortBy"
                            @change="handleSortChange"
                            class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="score">مرتب‌سازی بر اساس امتیاز</option>
                            <option value="questions_count">بر اساس تعداد سوالات</option>
                            <option value="answers_count">بر اساس تعداد پاسخ‌ها</option>
                            <option value="name">بر اساس نام</option>
                            <option value="created_at">بر اساس تاریخ عضویت</option>
                        </select>

                        <select
                            v-model="sortOrder"
                            @change="handleSortChange"
                            class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="desc">نزولی</option>
                            <option value="asc">صعودی</option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <!-- Main Content -->
        <template #main>
            <!-- Loading State -->
            <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="n in 12" :key="n"
                     class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-pulse">
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-8 mx-auto mb-1"></div>
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-12 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-8 mx-auto mb-1"></div>
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-12 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-8 mx-auto mb-1"></div>
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-12 mx-auto"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div v-else-if="errors.fetch" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-red-800 dark:text-red-200">{{ errors.fetch }}</p>
                </div>
            </div>

            <!-- Authors Grid -->
            <div v-else-if="authors.length > 0">
                <TransitionGroup name="author" tag="div" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <AuthorCard
                        v-for="author in authors"
                        :key="author.id"
                        :author="author"
                        @click="handleAuthorClick(author)"
                    />
                </TransitionGroup>

                <!-- Pagination -->
                <div v-if="pagination && pagination.last_page > 1" class="mt-8">
                    <BasePagination
                        :current-page="pagination.current_page"
                        :total-pages="pagination.last_page"
                        :total="pagination.total"
                        :per-page="pagination.per_page"
                        @page-changed="handlePageChange"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <svg class="h-16 w-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    نویسنده‌ای یافت نشد
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ searchQuery ? 'هیچ نویسنده‌ای با این جستجو پیدا نشد.' : 'هنوز نویسنده‌ای در سیستم ثبت نشده است.' }}
                </p>
            </div>
        </template>
    </ContentArea>
</template>

<script>
import { ref, onMounted, defineAsyncComponent, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { usePageTitle } from '../composables/usePageTitle'
const AuthorCard = defineAsyncComponent(() => import('../components/AuthorCard.vue'))
const BasePagination = defineAsyncComponent(() => import('../components/ui/BasePagination.vue'))
const ContentArea = defineAsyncComponent(() => import('../components/ContentArea.vue'))
import { useAuthors } from '../composables/useAuthors.js'

export default {
    name: 'Authors',
    components: {
        AuthorCard,
        BasePagination,
        ContentArea,
    },
    setup() {
    const router = useRouter()
    const route = useRoute()
        const { setTitle } = usePageTitle()
        const {
            authors,
            pagination,
            isLoading,
            errors,
            fetchAuthors,
            clearErrors
        } = useAuthors()

        // Set page title
        setTitle('نویسندگان')

        // Filters and search
        const searchQuery = ref('')
        const sortBy = ref('score')
        const sortOrder = ref('desc')
        const currentPage = ref(1)
        const searchTimeout = ref(null)

        const loadAuthors = async (page = 1) => {
            const params = {
                page,
                per_page: 20,
                sort_by: sortBy.value,
                sort_order: sortOrder.value,
            }

            if (searchQuery.value.trim()) {
                params.search = searchQuery.value.trim()
            }

            await fetchAuthors(params)
        }

        const handlePageChange = (page) => {
            let target = parseInt(page) || 1
            if (target < 1) target = 1
            currentPage.value = target
            router.push({ query: { ...route.query, page: target > 1 ? target : undefined } })
            loadAuthors(target)
            window.scrollTo({ top: 0, behavior: 'smooth' })
        }

        const handleSearchInput = () => {
            // Debounce search with shorter timeout for better UX
            clearTimeout(searchTimeout.value)
            searchTimeout.value = setTimeout(() => {
                currentPage.value = 1
                loadAuthors(1)
            }, 300)
        }

        const handleSortChange = () => {
            currentPage.value = 1
            loadAuthors(1)
        }

        const handleAuthorClick = (author) => {
            router.push({ name: 'AuthorShow', params: { id: author.id } })
        }

        // Clear errors when component unmounts
        const clearAllErrors = () => {
            clearErrors()
        }

        onMounted(() => {
            const initial = parseInt(route.query.page) || 1
            currentPage.value = initial
            loadAuthors(initial)
        })

        watch(() => route.query.page, (val) => {
            const target = parseInt(val) || 1
            if (target !== currentPage.value) {
                currentPage.value = target
                loadAuthors(target)
            }
        })

        return {
            // Data
            authors,
            pagination,
            isLoading,
            errors,
            searchQuery,
            sortBy,
            sortOrder,
            currentPage,

            // Methods
            handlePageChange,
            handleSearchInput,
            handleSortChange,
            handleAuthorClick,
            clearAllErrors,
        }
    },
}
</script>

<style scoped>
/* Transition animations */
.author-enter-active,
.author-leave-active {
    transition: all 0.3s ease;
}

.author-enter-from {
    opacity: 0;
    transform: translateY(30px);
}

.author-leave-to {
    opacity: 0;
    transform: translateY(-30px);
}

.author-move {
    transition: transform 0.3s ease;
}

/* Main content container */
.main-content-container {
    min-height: calc(100vh - 64px); /* Adjust based on your header height */
}

/* Responsive grid adjustments */
@media (max-width: 768px) {
    .grid-cols-1.md\:grid-cols-2.lg\:grid-cols-3 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}

@media (min-width: 768px) and (max-width: 1024px) {
    .grid-cols-1.md\:grid-cols-2.lg\:grid-cols-3 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Custom scrollbar for main content */
.main-content-container::-webkit-scrollbar {
    width: 6px;
}

.main-content-container::-webkit-scrollbar-track {
    background: transparent;
}

.main-content-container::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.dark .main-content-container::-webkit-scrollbar-thumb {
    background: #4a5568;
}
</style>
