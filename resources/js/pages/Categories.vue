<template>
    <div class="container mx-auto p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">دسته بندی ها</h1>
            <div v-if="pagination.meta && !loading" class="mt-2 sm:mt-0 text-sm text-gray-600 dark:text-gray-400">
                مجموع {{ pagination.meta.total }} دسته‌بندی
            </div>
        </div>
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <div v-for="n in 8" :key="n" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 animate-pulse">
                <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-4"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
        </div>
        <div v-else-if="error" class="text-center text-red-500 dark:text-red-400">
            <p>Failed to load categories. Please try again later.</p>
        </div>
        <div v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <router-link
                    v-for="category in categories"
                    :key="category.id"
                    :to="`/categories/${category.slug}`"
                    class="block bg-gradient-to-br from-blue-50 via-white to-blue-100 dark:from-gray-800 dark:via-gray-900 dark:to-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-shadow duration-300 border border-blue-100 dark:border-gray-700 group"
                >
                    <div class="p-6 flex flex-col h-full">
                        <h2 class="text-2xl font-bold text-blue-900 dark:text-blue-200 mb-2 group-hover:text-blue-700 transition-colors duration-200">
                            {{ category.name }}
                        </h2>
                        <div class="flex-1"></div>
                        <div class="flex flex-col gap-2 mt-4">
                            <div v-if="category.children_count > 0" class="flex items-center text-sm text-blue-600 dark:text-blue-300">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8m-8 6h16"/>
                                </svg>
                                {{ category.children_count }} زیردسته
                            </div>
                            <div class="flex items-center text-sm text-green-600 dark:text-green-300">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2z"/>
                                </svg>
                                {{ category.questions_count || 0 }} سوال
                            </div>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.meta && pagination.meta.last_page > 1" class="mt-8">
                <BasePagination
                    :current-page="pagination.meta.current_page"
                    :total-pages="pagination.meta.last_page"
                    :total="pagination.meta.total"
                    :per-page="pagination.meta.per_page"
                    @page-changed="handlePageChange"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePageTitle } from '../composables/usePageTitle';
import api from '../services/api';
import { BasePagination } from '../components/ui';

export default {
    name: 'Categories',
    components: {
        BasePagination,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const { setTitle } = usePageTitle();

        // Set page title
        setTitle('دسته‌بندی‌ها');

        const categories = ref([]);
        const loading = ref(true);
        const error = ref(null);
        const pagination = ref({
            meta: null,
            links: null
        });

        const fetchCategories = async (page = 1) => {
            try {
                loading.value = true;
                const response = await api.get('/categories', {
                    params: { page }
                });

                categories.value = response.data.data;
                pagination.value = {
                    meta: response.data.meta,
                    links: response.data.links
                };
            } catch (e) {
                error.value = e;
                console.error('Failed to fetch categories:', e);
            } finally {
                loading.value = false;
            }
        };

        const handlePageChange = async (page) => {
            if (pagination.value.meta && page === pagination.value.meta.current_page) return;

            // Update URL with page parameter
            router.push({
                path: '/categories',
                query: { page: page > 1 ? page : undefined }
            });

            // Scroll to top smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });

            await fetchCategories(page);
        };

        // Watch for URL changes to handle browser back/forward
        watch(() => route.query.page, (newPage) => {
            const page = parseInt(newPage) || 1;
            if (!pagination.value.meta || page !== pagination.value.meta.current_page) {
                fetchCategories(page);
            }
        });

        onMounted(() => {
            const page = parseInt(route.query.page) || 1;
            fetchCategories(page);
        });

        return {
            categories,
            loading,
            error,
            pagination,
            handlePageChange,
        };
    },
};
</script>
