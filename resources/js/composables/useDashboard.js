import { ref } from 'vue'
import api from '../services/api.js'

export function useDashboard() {
  const isLoading = ref(false)
  const stats = ref({
    totalQuestions: 0,
    totalAnswers: 0,
    totalUsers: 0,
    solvedQuestions: 0
  })
  const recommendedQuestions = ref([])
  const popularQuestions = ref([])
  const errors = ref({})

  // Fetch dashboard statistics
  const fetchStats = async () => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await api.get('/dashboard/stats')
      stats.value = response.data.data
      return { success: true, data: response.data.data }
    } catch (error) {
      console.error('Error fetching stats:', error)
      const errorMessage = 'خطا در بارگذاری آمار'
      errors.value.stats = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Fetch recommended questions
  const fetchRecommendedQuestions = async () => {
    try {
      const response = await api.get('/questions/recommended', { params: { limit: 15 } })
      recommendedQuestions.value = response.data.data
      return { success: true, data: response.data.data }
    } catch (error) {
      console.error('Error fetching recommended questions:', error)
      const errorMessage = 'خطا در بارگذاری سوالات پیشنهادی'
      errors.value.recommended = errorMessage
      return { success: false, error: errorMessage }
    }
  }

  // Fetch most visited questions in recent week
  const fetchPopularQuestions = async () => {
    try {
      const response = await api.get('/questions/popular', { params: { period: 'week', limit: 15 } })
      popularQuestions.value = response.data.data
      return { success: true, data: response.data.data }
    } catch (error) {
      console.error('Error fetching popular questions:', error)
      const errorMessage = 'خطا در بارگذاری سوالات محبوب'
      errors.value.popular = errorMessage
      return { success: false, error: errorMessage }
    }
  }

  // Clear all errors
  const clearErrors = () => {
    errors.value = {}
  }

  return {
    // State
    isLoading,
    stats,
    recommendedQuestions,
    popularQuestions,
    errors,

    // Methods
    fetchStats,
    fetchRecommendedQuestions,
    fetchPopularQuestions,
    clearErrors
  }
}
