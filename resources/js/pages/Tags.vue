<template>
    <ContentArea layout="with-sidebar" :show-sidebar="true" main-width="3/4" sidebar-width="1/4">
        <template #main>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">برچسب ها</h1>
                <div v-if="pagination.meta && !loading" class="mt-2 sm:mt-0 text-sm text-gray-600 dark:text-gray-400">
                    مجموع {{ pagination.meta.total }} برچسب
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="n in 12" :key="n" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 animate-pulse">
                    <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="text-center text-red-500 dark:text-red-400">
                <p>Failed to load tags. Please try again later.</p>
            </div>

            <!-- Tags Grid -->
            <div v-else>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    <router-link
                        v-for="tag in tags"
                        :key="tag.id"
                        :to="`/tags/${tag.slug}`"
                        class="block bg-gradient-to-br from-blue-50 via-white to-blue-100 dark:from-gray-800 dark:via-gray-900 dark:to-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-shadow duration-300 border border-blue-100 dark:border-gray-700 group"
                    >
                        <div class="p-6 flex flex-col h-full">
                            <h2 class="text-2xl font-bold text-blue-900 dark:text-blue-200 mb-2 group-hover:text-blue-700 transition-colors duration-200">
                                {{ tag.name }}
                            </h2>
                            <div class="flex-1"></div>
                            <div class="flex flex-col gap-2 mt-4">
                                <div v-if="tag.questions_count > 0" class="flex items-center text-sm text-blue-600 dark:text-blue-300">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ tag.questions_count || 0 }} سوال
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
        </template>

        <template #sidebar>
            <HomeSidebar />
        </template>
    </ContentArea>
</template>

<script>
import { ref, onMounted, watch, defineAsyncComponent } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePageTitle } from '../composables/usePageTitle';
import { useTags } from '../composables/useTags';
const BasePagination = defineAsyncComponent(() => import('../components/ui/BasePagination.vue'))
const ContentArea = defineAsyncComponent(() => import('../components/ContentArea.vue'))
const HomeSidebar = defineAsyncComponent(() => import('../components/sidebar/HomeSidebar.vue'))

export default {
    name: 'Tags',
    components: {
        BasePagination,
        ContentArea,
        HomeSidebar,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const { setTitle } = usePageTitle();
        const { fetchTags, isLoading, errors } = useTags();

        // Set page title
        setTitle('برچسب‌ها');

        const tags = ref([]);
        const loading = ref(true);
        const error = ref(null);
        const pagination = ref({
            meta: null,
            links: null
        });

        const fetchTagsData = async (page = 1) => {
            try {
                loading.value = true;
                const result = await fetchTags({
                    page,
                    per_page: 12
                });

                if (result.success) {
                    // Handle different response structures
                    if (result.data.data) {
                        // Laravel paginated response
                        tags.value = result.data.data;
                        pagination.value = {
                            meta: result.data.meta,
                            links: result.data.links
                        };
                    } else if (Array.isArray(result.data)) {
                        // Simple array response
                        tags.value = result.data;
                        pagination.value = {
                            meta: {
                                current_page: page,
                                last_page: 1,
                                total: result.data.length,
                                per_page: 12
                            }
                        };
                    } else {
                        // Single object response
                        tags.value = [result.data];
                        pagination.value = {
                            meta: {
                                current_page: page,
                                last_page: 1,
                                total: 1,
                                per_page: 12
                            }
                        };
                    }
                } else {
                    error.value = result.error || 'Failed to load tags';
                }
            } catch (e) {
                error.value = e;
                console.error('Failed to fetch tags:', e);
            } finally {
                loading.value = false;
            }
        };

        const handlePageChange = async (page) => {
            if (pagination.value.meta && page === pagination.value.meta.current_page) return;

            // Update URL with page parameter
            router.push({
                path: '/tags',
                query: { page: page > 1 ? page : undefined }
            });

            // Scroll to top smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });

            await fetchTagsData(page);
        };

        // Watch for URL changes to handle browser back/forward
        watch(() => route.query.page, (newPage) => {
            const page = parseInt(newPage) || 1;
            if (!pagination.value.meta || page !== pagination.value.meta.current_page) {
                fetchTagsData(page);
            }
        });

        onMounted(() => {
            const page = parseInt(route.query.page) || 1;
            fetchTagsData(page);
        });

        return {
            tags,
            loading,
            error,
            pagination,
            handlePageChange,
        };
    },
};
</script>
