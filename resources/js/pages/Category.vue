<template>
    <div class="container mx-auto p-4">
        <div v-if="loading">
            <p>Loading...</p>
        </div>
        <div v-else-if="error">
            <p class="text-red-500">Error loading data.</p>
        </div>
        <div v-else>
            <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-200">{{ category.name }}</h1>

            <!-- Subcategories -->
            <div v-if="category.children && category.children.length > 0">
                <h2 class="text-2xl font-semibold mb-4">زیردسته ها</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <router-link v-for="child in category.children" :key="child.id" :to="`/categories/${child.slug}`" class="block bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                        <div class="p-5">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ child.name }}</h3>
                        </div>
                        <div v-if="child.children_count > 0" class="bg-gray-50 dark:bg-gray-700 px-5 py-3 rounded-b-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ child.children_count }} زیردسته</p>
                        </div>
                    </router-link>
                </div>
            </div>

            <!-- Questions -->
            <div v-if="questions.length > 0">
                <h2 class="text-2xl font-semibold mb-4 mt-8">سوالات</h2>
                <div class="space-y-4">
                    <QuestionCard
                        v-for="question in questions"
                        :key="question.id"
                        :question="question"
                        @click="handleQuestionClick(question)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';
import QuestionCard from '../components/QuestionCard.vue';

export default {
    name: 'Category',
    components: {
        QuestionCard,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const category = ref(null);
        const questions = ref([]);
        const loading = ref(true);
        const error = ref(null);

        const handleQuestionClick = (question) => {
            router.push({ name: 'QuestionShow', params: { id: question.id } });
        };

        const fetchData = async (slug) => {
            try {
                loading.value = true;
                error.value = null;
                const categoryResponse = await api.get(`/categories/${slug}`);
                category.value = categoryResponse.data.data;

                if (category.value.children.length === 0) {
                    const questionsResponse = await api.get(`/categories/${slug}/questions`);
                    questions.value = questionsResponse.data.data;
                } else {
                    questions.value = [];
                }
            } catch (e) {
                error.value = e;
                console.error('Failed to fetch category data:', e);
            } finally {
                loading.value = false;
            }
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
            category,
            questions,
            loading,
            error,
            handleQuestionClick,
        };
    },
};
</script>
