import { ref, reactive } from 'vue'
import api from '../services/api.js'

export function useAuthors() {
  const isLoading = ref(false)
  const authors = ref([])
  const pagination = ref({})
  const errors = reactive({})

  // Fetch authors with pagination and filters
  const fetchAuthors = async (params = {}) => {
    isLoading.value = true
    clearErrors()

    try {
      const response = await api.get('/authors', { params })

      if (response.data.success) {
        authors.value = response.data.data
        pagination.value = response.data.pagination
        return { success: true, data: response.data.data }
      } else {
        throw new Error(response.data.message || 'خطا در دریافت لیست نویسندگان')
      }
    } catch (error) {
      console.error('Error fetching authors:', error)
      const errorMessage = error.response?.data?.message || 'خطا در بارگذاری لیست نویسندگان'
      errors.fetch = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Fetch single author details
  const fetchAuthor = async (authorId) => {
    isLoading.value = true
    clearErrors()

    try {
      const response = await api.get(`/authors/${authorId}`)

      if (response.data.success) {
        return { success: true, data: response.data.data }
      } else {
        throw new Error(response.data.message || 'خطا در دریافت اطلاعات نویسنده')
      }
    } catch (error) {
      console.error('Error fetching author:', error)
      const errorMessage = error.response?.data?.message || 'خطا در بارگذاری اطلاعات نویسنده'
      errors.fetch = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Search authors
  const searchAuthors = async (query, params = {}) => {
    isLoading.value = true
    clearErrors()

    try {
      const searchParams = {
        search: query,
        ...params
      }

      const response = await api.get('/authors', { params: searchParams })

      if (response.data.success) {
        authors.value = response.data.data
        pagination.value = response.data.pagination
        return { success: true, data: response.data.data }
      } else {
        throw new Error(response.data.message || 'خطا در جستجوی نویسندگان')
      }
    } catch (error) {
      console.error('Error searching authors:', error)
      const errorMessage = error.response?.data?.message || 'خطا در جستجوی نویسندگان'
      errors.search = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Clear all errors
  const clearErrors = () => {
    Object.keys(errors).forEach(key => {
      delete errors[key]
    })
  }

  // Reset authors data
  const resetAuthors = () => {
    authors.value = []
    pagination.value = {}
    clearErrors()
  }

  // Helper function to get author statistics
  const getAuthorStats = (author) => {
    return {
      totalActivity: author.questions_count + author.answers_count + author.comments_count,
      isActive: author.questions_count > 0 || author.answers_count > 0,
      isExperienced: author.score > 100,
      isAdmin: author.role === 'admin',
      isModerator: author.role === 'moderator'
    }
  }

  // Helper function to format author data
  const formatAuthorData = (author) => {
    return {
      ...author,
      stats: getAuthorStats(author),
      formattedScore: formatScore(author.score),
      formattedJoinDate: formatJoinDate(author.created_at)
    }
  }

  // Format score for display
  const formatScore = (score) => {
    if (score >= 1000000) {
      return (score / 1000000).toFixed(1) + 'M'
    } else if (score >= 1000) {
      return (score / 1000).toFixed(1) + 'K'
    }
    return score.toString()
  }

  // Format join date
  const formatJoinDate = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffTime = Math.abs(now - date)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

    if (diffDays < 30) {
      return `${diffDays} روز پیش`
    } else if (diffDays < 365) {
      const months = Math.floor(diffDays / 30)
      return `${months} ماه پیش`
    } else {
      const years = Math.floor(diffDays / 365)
      return `${years} سال پیش`
    }
  }

  return {
    // State
    isLoading,
    authors,
    pagination,
    errors,

    // Methods
    fetchAuthors,
    fetchAuthor,
    searchAuthors,
    clearErrors,
    resetAuthors,
    getAuthorStats,
    formatAuthorData,
    formatScore,
    formatJoinDate
  }
}
