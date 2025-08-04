<template>
    <main class="flex-grow bg-gray-50 dark:bg-gray-900/50 overflow-hidden">
        <div class="flex h-full">
            <!-- Right Content Area - 75% on medium screens and larger, full width on smaller screens -->
            <div class="w-full md:w-3/4 lg:w-3/4 xl:w-3/4 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                <div class="max-w-4xl mx-auto w-full min-w-0">
                    <!-- Loading State -->
                    <div v-if="isLoading" class="animate-pulse">
                        <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-4"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-6"></div>
                        <div class="h-32 bg-gray-200 dark:bg-gray-700 rounded mb-6"></div>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="error" class="text-center py-16">
                        <div class="text-red-500 text-lg">{{ error }}</div>
                    </div>

                    <!-- Question Content -->
                    <div v-else-if="question && question.id">
                        <!-- Updating Indicator -->
                        <div v-if="isUpdating"
                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4 flex items-center gap-3">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                            <span class="text-blue-700 dark:text-blue-300">در حال بروزرسانی سوال...</span>
                        </div>

                        <!-- Question Content -->
                        <QuestionContent :question="question" @edit="handleEdit" @delete="handleDelete"
                            @vote="handleVote" @vote-changed="handleVoteChanged"
                            :key="`question-${question.id}-${componentKey}`" />

                        <!-- Comments Section -->
                        <CommentsSection :questionId="question.id" parent-type="question"
                            @comment-added="refreshQuestionData" :key="`comments-${question.id}-${componentKey}`" />

                        <!-- Answers Section -->
                        <AnswersSection :questionId="question.id" :answers="answers" @answer-added="refreshQuestionData"
                            @vote-changed="handleAnswerVoteChanged" @answer-correctness-changed="handleAnswerCorrectnessChanged"
                            :key="`answers-${question.id}-${componentKey}`" />
                    </div>
                </div>
            </div>

            <!-- Left Sidebar - 25% on medium screens and larger -->
            <div
                class="hidden md:block md:w-1/4 lg:w-1/4 xl:w-1/4 dark:border-gray-700 dark:bg-gray-800">
                <div class="p-4 h-full overflow-y-auto">
                    <HomeSidebar />
                </div>
            </div>
        </div>

        <!-- Question Modal -->
        <QuestionModal v-if="showEditModal && question" :question-to-edit="question" @close="handleCloseModal"
            @question-updated="handleQuestionUpdated" />
    </main>
</template>

<script>
import { ref, onMounted, computed, onBeforeUnmount, nextTick, watch, triggerRef } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import { useQuestions } from '../composables/useQuestions'
import { usePageTitle } from '../composables/usePageTitle'
import QuestionContent from '../components/question/QuestionContent.vue'
import CommentsSection from '../components/question/CommentsSection.vue'
import AnswersSection from '../components/question/AnswersSection.vue'
import QuestionModal from '../components/QuestionModal.vue'
import HomeSidebar from '../components/sidebar/HomeSidebar.vue'
import questionService from '../services/questionService.js'
import axios from 'axios'

export default {
    name: 'QuestionShow',
    components: {
        QuestionContent,
        CommentsSection,
        AnswersSection,
        QuestionModal,
        HomeSidebar
    },
    setup() {
        const route = useRoute()
        const router = useRouter()
        const { isAuthenticated } = useAuth()
        const { deleteQuestion } = useQuestions()
        const { setTitle } = usePageTitle()

        const question = ref(null)
        const answers = ref([])
        const isLoading = ref(false)
        const error = ref(null)
        const showEditModal = ref(false)
        const isUpdating = ref(false)
        const componentKey = ref(0)
        const questionServiceCleanup = ref([])

        const questionSlug = computed(() => route.params.slug)

        const fetchQuestion = async (skipLoadingState = false) => {
            if (!skipLoadingState) {
                isLoading.value = true
            }
            error.value = null

            try {
                const response = await axios.get(`/api/questions/${questionSlug.value}`)
                question.value = response.data.data

                // Extract answers from the response
                answers.value = response.data.data.answers || []

                // Update page title with question title
                if (question.value && question.value.title) {
                    setTitle(question.value.title)
                }
            } catch (err) {
                error.value = 'خطا در بارگذاری سوال'
                console.error('Error fetching question:', err)
            } finally {
                if (!skipLoadingState) {
                    isLoading.value = false
                }
            }
        }

        const refreshComments = async () => {
            // Comments are now managed by CommentsSection component
            // This function is kept for compatibility but does nothing
        }

        const refreshAnswers = async () => {
            try {
                const response = await axios.get(`/api/questions/${questionSlug.value}`)
                answers.value = response.data.data.answers || []
            } catch (err) {
                console.error('Error refreshing answers:', err)
            }
        }

        const refreshQuestionData = async () => {
            try {
                // Add cache busting parameter to ensure fresh data
                const timestamp = Date.now()
                const response = await axios.get(`/api/questions/${questionSlug.value}?_t=${timestamp}`)
                const data = response.data.data

                // Clear existing data first
                question.value = null
                answers.value = []

                // Wait for UI update
                await nextTick()

                // Force complete reactivity by creating entirely new objects
                question.value = JSON.parse(JSON.stringify(data))
                answers.value = JSON.parse(JSON.stringify(data.answers || []))

                // Update page title with question title
                if (question.value && question.value.title) {
                    setTitle(question.value.title)
                }

                // Manually trigger reactivity
                triggerRef(question)
                triggerRef(answers)

                // Force component re-render with timestamp
                componentKey.value = Date.now()

                // Additional force update by triggering nextTick
                await nextTick()

                // Force DOM update
                await new Promise(resolve => setTimeout(resolve, 100))
            } catch (err) {
                console.error('Error refreshing question data:', err)
                throw err
            }
        }

        const handleEdit = () => {
            // Open the edit modal
            showEditModal.value = true
        }

        const handleCloseModal = () => {
            showEditModal.value = false
        }

        const handleKeyDown = (event) => {
            // Close modal on Escape key
            if (event.key === 'Escape' && showEditModal.value) {
                handleCloseModal()
            }
        }

        const handleQuestionUpdated = async (updatedQuestion) => {
            // Close the modal
            showEditModal.value = false

            // Set updating state for user feedback
            isUpdating.value = true

            try {
                // Wait a moment for the backend to process
                await new Promise(resolve => setTimeout(resolve, 500))

                // Clear current data to force a fresh state
                question.value = null
                answers.value = []

                // Wait a moment to ensure the UI updates
                await nextTick()

                // Refresh all question data to get the latest information
                await refreshQuestionData()

                // Show success message
                const Swal = window.Swal || window.$swal;
                if (Swal) {
                    Swal.fire({
                        title: 'موفقیت!',
                        text: 'سوال با موفقیت ویرایش شد و صفحه بروزرسانی شد.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000
                    });
                }
            } catch (err) {
                console.error('Error refreshing question after update:', err)
                // Show error message
                const Swal = window.Swal || window.$swal;
                if (Swal) {
                    Swal.fire({
                        title: 'خطا!',
                        text: 'سوال ویرایش شد اما خطایی در بارگذاری مجدد رخ داد. صفحه را تازه‌سازی کنید.',
                        icon: 'warning',
                        confirmButtonText: 'باشه'
                    });
                }
            } finally {
                isUpdating.value = false
            }
        }

        const handleExternalQuestionUpdate = async (updatedQuestion) => {
            // Only update if this is the same question we're currently viewing
            if (question.value && updatedQuestion.id === question.value.id) {
                // Set updating state for user feedback
                isUpdating.value = true

                try {
                    // Refresh to get the complete updated data structure
                    await refreshQuestionData()

                    // Show a subtle notification that the question was updated
                    const Swal = window.Swal || window.$swal;
                    if (Swal) {
                        Swal.fire({
                            title: 'بروزرسانی!',
                            text: 'این سوال به‌روزرسانی شده است.',
                            icon: 'info',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                } catch (err) {
                    console.error('Error refreshing question after external update:', err)
                } finally {
                    isUpdating.value = false
                }
            }
        }

        const handleDelete = async () => {
            const Swal = window.Swal || window.$swal;

            if (Swal) {
                const result = await Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    text: `آیا مطمئن هستید که میخواهید این سوال را حذف کنید؟ این عمل قابل بازگشت نیست.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'بله، حذف کن!',
                    cancelButtonText: 'انصراف',
                    reverseButtons: true
                });

                if (result.isConfirmed) {
                    const deleteResult = await deleteQuestion(questionSlug.value);

                    if (deleteResult.success) {
                        // Show success message
                        await Swal.fire({
                            title: 'حذف شد!',
                            text: deleteResult.message || 'سوال با موفقیت حذف شد.',
                            icon: 'success',
                            confirmButtonText: 'باشه'
                        });

                        router.push('/')
                    } else {
                        // Show error message based on the error type
                        let errorMessage = deleteResult.message || 'خطایی در حذف سوال رخ داد.';

                        await Swal.fire({
                            title: 'خطا!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'باشه'
                        });
                    }
                }
            } else {
                // Fallback to browser confirm if SweetAlert is not available
                if (confirm('آیا از حذف این سوال اطمینان دارید؟')) {
                    const deleteResult = await deleteQuestion(questionSlug.value);

                    if (deleteResult.success) {
                        router.push('/')
                    } else {
                        alert(deleteResult.message || 'خطایی در حذف سوال رخ داد.')
                    }
                }
            }
        }

        const handleVote = async (voteData) => {
            try {
                // Handle voting logic here
                // You would typically send this to an API endpoint
                // await axios.post(`/api/questions/${questionId.value}/vote`, voteData)
            } catch (err) {
                console.error('Error voting:', err)
            }
        }

        const handleVoteChanged = (voteData) => {
            // Update the question vote data in real-time
            if (question.value && question.value.votes) {
                question.value.votes.upvotes = voteData.upvotes
                question.value.votes.downvotes = voteData.downvotes
                question.value.votes.score = voteData.upvotes - voteData.downvotes
                question.value.votes.user_vote = voteData.userVote
            }
        }

        const handleAnswerVoteChanged = (voteData) => {
            console.log('QuestionShow - Answer vote changed:', voteData)
            // Update the answer vote data in real-time
            if (voteData.type === 'answer') {
                const answerIndex = answers.value.findIndex(a => a.id === voteData.id)
                if (answerIndex !== -1) {
                    console.log('Updating answer at index:', answerIndex, 'with votes:', voteData.votes)
                    // Create a new answer object to ensure reactivity
                    const existingAnswer = answers.value[answerIndex]
                    const updatedAnswer = {
                        ...existingAnswer,
                        votes: voteData.votes
                    }
                    // Create a new array to ensure reactivity
                    const newAnswers = [...answers.value]
                    newAnswers[answerIndex] = updatedAnswer
                    answers.value = newAnswers
                }
            }
        }

        const handleAnswerCorrectnessChanged = (data) => {
            console.log('Answer correctness changed:', data)
            // Refresh question data to update solved status
            refreshQuestionData()
        }

        onMounted(() => {
            fetchQuestion()
            // Add keyboard event listener
            window.addEventListener('keydown', handleKeyDown)

            // Subscribe to question service events
            const unsubscribeQuestionUpdated = questionService.subscribe('question-updated', handleExternalQuestionUpdate)

            // Store unsubscribe function to clean up later
            questionServiceCleanup.value = [unsubscribeQuestionUpdated]
        })

        onBeforeUnmount(() => {
            // Clean up event listener
            window.removeEventListener('keydown', handleKeyDown)

            // Clean up question service subscriptions
            if (questionServiceCleanup.value) {
                questionServiceCleanup.value.forEach(unsubscribe => unsubscribe())
            }
        })

        // Watch for route parameter changes to handle navigation between different questions
        watch(() => route.params.slug, (newSlug, oldSlug) => {
            if (newSlug && newSlug !== oldSlug) {
                // Reset state
                question.value = null
                answers.value = []
                error.value = null
                showEditModal.value = false
                isUpdating.value = false

                // Fetch new question data
                fetchQuestion()
            }
        }, { immediate: false })

        return {
            question,
            answers,
            isLoading,
            isUpdating,
            error,
            showEditModal,
            componentKey,
            refreshComments,
            refreshAnswers,
            refreshQuestionData,
            handleEdit,
            handleCloseModal,
            handleQuestionUpdated,
            handleDelete,
            handleVote,
            handleVoteChanged,
            handleAnswerVoteChanged,
            handleAnswerCorrectnessChanged
        }
    }
}
</script>
