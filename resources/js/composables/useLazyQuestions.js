import { ref, computed } from 'vue'
import axios from 'axios'

export function useLazyQuestions() {
  const questions = ref([])
  const isLoading = ref(false)
  const isLoadingMore = ref(false)
  const hasMore = ref(true)
  const errors = ref({})
  const currentPage = ref(0)
  const perPage = 10

  // Computed properties
  const totalQuestions = computed(() => questions.value.length)
  const isInitialLoading = computed(() => isLoading.value && currentPage.value === 0)

  // Load initial questions
  const loadInitialQuestions = async (params = {}) => {
    if (isLoading.value) return

    isLoading.value = true
    errors.value = {}
    currentPage.value = 0
    questions.value = []
    hasMore.value = true

    try {
      const response = await axios.get('/api/questions', {
        params: {
          page: 1,
          per_page: perPage,
          ...params
        }
      })

      const data = response.data
      questions.value = data.data
      currentPage.value = 1

      // Check if there are more pages
      hasMore.value = data.meta.current_page < data.meta.last_page

      return {
        success: true,
        data: data.data,
        meta: data.meta
      }
    } catch (error) {
      console.error('Error loading initial questions:', error)
      const errorMessage = 'خطا در بارگذاری سوالات'
      errors.value.fetch = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Load more questions (for infinite scroll)
  const loadMoreQuestions = async (params = {}) => {
    if (isLoadingMore.value || !hasMore.value || isLoading.value) return

    isLoadingMore.value = true
    errors.value = {}

    try {
      const nextPage = currentPage.value + 1

      const response = await axios.get('/api/questions', {
        params: {
          page: nextPage,
          per_page: perPage,
          ...params
        }
      })

      const data = response.data

      // Only append if we actually got new questions
      if (data.data && data.data.length > 0) {
        // Append new questions to existing ones
        questions.value = [...questions.value, ...data.data]
        currentPage.value = nextPage
      }

      // Check if there are more pages
      hasMore.value = data.meta.current_page < data.meta.last_page

      return {
        success: true,
        data: data.data,
        meta: data.meta,
        newQuestionsCount: data.data ? data.data.length : 0
      }
    } catch (error) {
      console.error('Error loading more questions:', error)

      let errorMessage = 'خطا در بارگذاری سوالات بیشتر'

      if (error.response) {
        if (error.response.status === 404) {
          errorMessage = 'صفحه‌ای یافت نشد'
          hasMore.value = false
        } else if (error.response.status >= 500) {
          errorMessage = 'خطای سرور. لطفاً دوباره تلاش کنید'
        } else if (error.response.status === 429) {
          errorMessage = 'درخواست‌های زیادی ارسال شده. لطفاً کمی صبر کنید'
        }
      } else if (error.request) {
        errorMessage = 'مشکل در اتصال به اینترنت'
      }

      errors.value.loadMore = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoadingMore.value = false
    }
  }

  // Refresh all questions (reload from beginning)
  const refreshQuestions = async (params = {}) => {
    return await loadInitialQuestions(params)
  }

  // Add a new question to the beginning of the list
  const prependQuestion = (question) => {
    questions.value.unshift(question)
  }

  // Remove a question from the list
  const removeQuestion = (questionId) => {
    const index = questions.value.findIndex(q => q.id === questionId)
    if (index > -1) {
      questions.value.splice(index, 1)
    }
  }

  // Update a question in the list
  const updateQuestion = (questionId, updatedQuestion) => {
    const index = questions.value.findIndex(q => q.id === questionId)
    if (index > -1) {
      questions.value[index] = { ...questions.value[index], ...updatedQuestion }
    }
  }

  // Clear all errors
  const clearErrors = () => {
    errors.value = {}
  }

  // Reset the state
  const reset = () => {
    questions.value = []
    currentPage.value = 0
    hasMore.value = true
    isLoading.value = false
    isLoadingMore.value = false
    errors.value = {}
  }

  return {
    // State
    questions,
    isLoading,
    isLoadingMore,
    hasMore,
    errors,
    currentPage,
    totalQuestions,
    isInitialLoading,

    // Methods
    loadInitialQuestions,
    loadMoreQuestions,
    refreshQuestions,
    prependQuestion,
    removeQuestion,
    updateQuestion,
    clearErrors,
    reset
  }
}
