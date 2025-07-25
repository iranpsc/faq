<template>
  <main class="flex-grow p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900/50 overflow-y-auto main-content-container">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4 sm:mb-0">
          آخرین پرسش و پاسخ ها
        </h1>
        <div class="flex items-center gap-2">
          <BaseButton
            :variant="currentFilter === 'newest' ? 'outline' : 'ghost'"
            size="sm"
            @click="handleFilterChange('newest')"
          >
            <template #icon>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9M3 12h9m-9 4h13m-5-4v8m0 0l-4-4m4 4l4-4"></path></svg>
            </template>
            جدیدترین
          </BaseButton>
          <BaseButton
            :variant="currentFilter === 'popular' ? 'outline' : 'ghost'"
            size="sm"
            @click="handleFilterChange('popular')"
          >
            محبوب ترین
          </BaseButton>
          <BaseButton
            :variant="currentFilter === 'unanswered' ? 'outline' : 'ghost'"
            size="sm"
            @click="handleFilterChange('unanswered')"
          >
            بدون پاسخ
          </BaseButton>
        </div>
      </div>

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
            <QuestionCard
              v-for="question in questions"
              :key="question.id"
              :question="question"
              @click="handleQuestionClick(question)"
            />
          </TransitionGroup>
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
      <div v-else class="text-center py-16">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">سوالی یافت نشد</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">با پرسیدن اولین سوال شروع کنید.</p>
      </div>

      <!-- Scroll to Top Button -->
      <Transition name="fade">
        <button
          v-if="showScrollToTop"
          @click="scrollToTop"
          class="fixed bottom-6 left-6 z-50 bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg transition-all duration-300 hover:shadow-xl"
          aria-label="برو به بالا"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
          </svg>
        </button>
      </Transition>
    </div>
  </main>
</template>

<script>
import { onMounted, ref, getCurrentInstance, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useQuestions } from '../composables'
import QuestionCard from '../components/QuestionCard.vue'
import { BaseButton, BaseAlert, BasePagination } from '../components/ui'

export default {
  name: 'Home',
  components: {
    QuestionCard,
    BaseButton,
    BaseAlert,
    BasePagination
  },
  emits: [],
  setup(props) {
    const router = useRouter()
    const instance = getCurrentInstance()

    // Scroll to top functionality
    const showScrollToTop = ref(false)
    const currentFilter = ref('newest')

    // Use questions composable for pagination
    const {
      questions,
      isLoading,
      pagination,
      errors,
      fetchQuestions,
      clearErrors,
      changePage
    } = useQuestions()

    // Handle filter change
    const handleFilterChange = async (filter, forceRefresh = false) => {
      if (filter === currentFilter.value && !forceRefresh) return

      currentFilter.value = filter
      clearErrors()

      let params = { page: 1 }

      switch (filter) {
        case 'newest':
          params.sort = 'created_at'
          params.order = 'desc'
          break
        case 'popular':
          params.sort = 'votes'
          params.order = 'desc'
          break
        case 'unanswered':
          params.filter = 'unanswered'
          break
      }

      await fetchQuestions(params)
      scrollToTop()
    }

    // Handle page change
    const handlePageChange = async (page) => {
      if (page === pagination.value.meta?.current_page) return

      clearErrors()

      let params = { page }

      // Include current filter parameters
      switch (currentFilter.value) {
        case 'newest':
          params.sort = 'created_at'
          params.order = 'desc'
          break
        case 'popular':
          params.sort = 'votes'
          params.order = 'desc'
          break
        case 'unanswered':
          params.filter = 'unanswered'
          break
      }

      await fetchQuestions(params)
      scrollToTop()
    }

    const handleQuestionClick = (question) => {
      router.push(`/questions/${question.id}`)
    }

    const refreshQuestions = async () => {
      clearErrors()
      await handleFilterChange(currentFilter.value, true)
    }

    // Add a new question to the beginning of the list (for external use)
    const prependQuestion = (question) => {
      questions.value.unshift(question)
    }

    // Update a question in the list (for external use)
    const updateQuestion = (questionId, updatedQuestion) => {
      const index = questions.value.findIndex(q => q.id === questionId)
      if (index > -1) {
        questions.value[index] = { ...questions.value[index], ...updatedQuestion }
      }
    }

    // Scroll to top functionality
    const scrollToTop = () => {
      const container = document.querySelector('.main-content-container')
      if (container) {
        container.scrollTo({
          top: 0,
          behavior: 'smooth'
        })
      }
    }

    // Handle scroll events to show/hide scroll to top button
    const handleScroll = () => {
      const container = document.querySelector('.main-content-container')
      if (container) {
        showScrollToTop.value = container.scrollTop > 500
      }
    }

    // Handle keyboard shortcuts
    const handleKeydown = (event) => {
      // Home key to scroll to top
      if (event.key === 'Home' && event.ctrlKey) {
        event.preventDefault()
        scrollToTop()
      }
      // End key to scroll to bottom
      if (event.key === 'End' && event.ctrlKey) {
        event.preventDefault()
        const container = document.querySelector('.main-content-container')
        if (container) {
          container.scrollTo({
            top: container.scrollHeight,
            behavior: 'smooth'
          })
        }
      }
    }

    onMounted(async () => {
      // Load initial questions when component mounts with newest filter
      await handleFilterChange('newest', true)

      // Add scroll event listener
      const container = document.querySelector('.main-content-container')
      if (container) {
        container.addEventListener('scroll', handleScroll)
      }

      // Add keyboard event listener
      window.addEventListener('keydown', handleKeydown)
    })

    onBeforeUnmount(() => {
      // Clean up scroll event listener
      const container = document.querySelector('.main-content-container')
      if (container) {
        container.removeEventListener('scroll', handleScroll)
      }

      // Clean up keyboard event listener
      window.removeEventListener('keydown', handleKeydown)
    })

    return {
      questions,
      isLoading,
      pagination,
      errors,
      currentFilter,
      showScrollToTop,
      handleQuestionClick,
      handleFilterChange,
      handlePageChange,
      refreshQuestions,
      prependQuestion,
      updateQuestion,
      scrollToTop
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

/* Fade transition for scroll to top button */
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.8);
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

/* Smooth scrolling for the main container */
.main-content-container {
  scroll-behavior: smooth;
}

/* Custom scrollbar styling */
.main-content-container::-webkit-scrollbar {
  width: 6px;
}

.main-content-container::-webkit-scrollbar-track {
  background: transparent;
}

.main-content-container::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

.main-content-container::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.8);
}

.dark .main-content-container::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark .main-content-container::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.8);
}
</style>
