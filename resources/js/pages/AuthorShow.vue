<template>
    <ContentArea layout="with-sidebar" :show-sidebar="true" main-width="2/3" sidebar-width="1/3">
        <!-- Main Content -->
        <template #main>
            <!-- Loading State -->
            <div v-if="isLoading" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                <p class="mt-4 text-gray-600 dark:text-gray-400">در حال بارگذاری اطلاعات نویسنده...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-red-800 dark:text-red-200">{{ error }}</p>
                </div>
            </div>

            <!-- Questions Section -->
            <div v-else-if="author">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    سوالات پرسیده شده توسط {{ author.name }}
                </h2>
                <div v-if="author.questions && author.questions.length > 0">
                    <QuestionCard
                        v-for="question in author.questions"
                        :key="question.id"
                        :question="question"
                        @click="navigateToQuestion(question)"
                    />
                </div>
                <div v-else class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center">
                    <p class="text-gray-600 dark:text-gray-400">این نویسنده هنوز سوالی نپرسیده است.</p>
                </div>
            </div>
        </template>

        <!-- Sidebar -->
        <template #sidebar>
            <div v-if="author" class="sticky top-8">
                <AuthorCard :author="author" />
            </div>
        </template>
    </ContentArea>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthors } from '../composables/useAuthors'
import { usePageTitle } from '../composables/usePageTitle'
import AuthorCard from '../components/AuthorCard.vue'
import QuestionCard from '../components/QuestionCard.vue'
import { ContentArea } from '../components/ui'

export default {
    name: 'AuthorShow',
    components: {
        AuthorCard,
        QuestionCard,
        ContentArea,
    },
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
    },
    setup(props) {
        const route = useRoute()
        const router = useRouter()
        const { fetchAuthor, isLoading, errors } = useAuthors()
        const { setTitle } = usePageTitle()

        const author = ref(null)
        const error = ref(null)

        const loadAuthor = async () => {
            const { success, data, error: fetchError } = await fetchAuthor(props.id)
            if (success) {
                author.value = data
                // Update page title with author name
                if (author.value && author.value.name) {
                    setTitle(`پروفایل ${author.value.name}`)
                }
            } else {
                error.value = fetchError
            }
        }

        const navigateToQuestion = (question) => {
            router.push({ name: 'QuestionShow', params: { slug: question.slug } })
        }

        onMounted(loadAuthor)

        return {
            author,
            isLoading,
            error,
            navigateToQuestion,
        }
    },
}
</script>
