<template>
    <div class="container mx-auto p-4">
        <div v-if="loading">
            <p>Loading...</p>
        </div>
        <div v-else-if="error">
            <p class="text-red-500">Error loading data.</p>
        </div>
        <div v-else>
            <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-200">
                برچسب: {{ tagName }}
            </h1>

            <!-- Questions -->
            <div v-if="questions.length > 0">
                <h2 class="text-2xl font-semibold mb-4">سوالات</h2>
                <div class="space-y-4">
                    <QuestionCard
                        v-for="question in questions"
                        :key="question.id"
                        :question="question"
                        @click="handleQuestionClick(question)"
                    />
                </div>

                <!-- Pagination -->
                <div v-if="pagination.meta" class="mt-8">
                    <BasePagination
                        :current-page="pagination.meta.current_page"
                        :total-pages="pagination.meta.last_page"
                        :total="pagination.meta.total"
                        :per-page="pagination.meta.per_page"
                        @page-changed="handlePageChange"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <svg class="h-16 w-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    سوالی یافت نشد
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    هنوز سوالی با این برچسب ثبت نشده است.
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePageTitle } from '../composables/usePageTitle';
import api from '../services/api';
import QuestionCard from '../components/QuestionCard.vue';
import { BasePagination } from '../components/ui';

export default {
    name: 'Tag',
    components: {
        QuestionCard,
        BasePagination,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const { setTitle } = usePageTitle();
        const tagName = ref('');
        const questions = ref([]);
        const pagination = ref({});
        const loading = ref(true);
        const error = ref(null);

        const handleQuestionClick = (question) => {
            router.push({ name: 'QuestionShow', params: { id: question.id } });
        };

        const fetchData = async (tagSlug, page = 1) => {
            try {
                loading.value = true;
                error.value = null;

                // Fetch questions with this tag using the slug-based endpoint
                const questionsResponse = await api.get(`/tags/${tagSlug}/questions`, {
                    params: {
                        page
                    }
                });

                questions.value = questionsResponse.data.data;

                // Set the tag name from the response (assuming the API returns tag info)
                if (questionsResponse.data.tag) {
                    tagName.value = questionsResponse.data.tag.name;
                    setTitle(`برچسب ${questionsResponse.data.tag.name}`);
                } else {
                    // Fallback if tag info is not in response
                    tagName.value = tagSlug;
                    setTitle(`برچسب ${tagSlug}`);
                }

                // Handle pagination meta data from Laravel resource collection
                pagination.value = {
                    meta: questionsResponse.data.meta,
                    links: questionsResponse.data.links
                };
            } catch (e) {
                error.value = e;
                console.error('Failed to fetch tag data:', e);
            } finally {
                loading.value = false;
            }
        };

        const handlePageChange = async (page) => {
            if (pagination.value.meta && page === pagination.value.meta.current_page) return;

            await fetchData(route.params.slug, page);
        };

        onMounted(() => {
            fetchData(route.params.slug);
        });

        watch(() => route.params.slug, (newSlug) => {
            if (newSlug) {
                fetchData(newSlug);
            }
        });

        return {
            tagName,
            questions,
            pagination,
            loading,
            error,
            handleQuestionClick,
            handlePageChange,
        };
    },
};
</script>
