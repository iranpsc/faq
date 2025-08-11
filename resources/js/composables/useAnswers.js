import { ref } from 'vue'
import api from '../services/api.js'

export function useAnswers() {
  const isLoading = ref(false)
  const isSubmitting = ref(false)
  const isUpdating = ref(false)
  const isDeleting = ref(null)
  const isVoting = ref(null)

  // Fetch answers for a question
  const fetchAnswers = async (questionId, page = 1, sort = null) => {
    isLoading.value = true
    try {
      const response = await api.get(`/questions/${questionId}/answers`, {
        params: { page, sort }
      })
      return {
        success: true,
        data: response.data.data,
        meta: response.data.meta,
        links: response.data.links,
        message: 'Answers fetched successfully'
      }
    } catch (error) {
      console.error('Error fetching answers:', error)
      return {
        success: false,
        data: [],
        meta: null,
        links: null,
        message: error.response?.data?.message || 'خطایی در بارگذاری پاسخ‌ها رخ داد'
      }
    } finally {
      isLoading.value = false
    }
  }

  // Add a new answer
  const addAnswer = async (questionId, content) => {
    if (!content.trim()) {
      return {
        success: false,
        message: 'محتوای پاسخ نمی‌تواند خالی باشد'
      }
    }

    isSubmitting.value = true
    try {
      const response = await api.post(`/questions/${questionId}/answers`, {
        content: content.trim()
      })
      return {
        success: true,
        data: response.data,
        message: 'پاسخ با موفقیت اضافه شد'
      }
    } catch (error) {
      console.error('Error adding answer:', error)
      return {
        success: false,
        message: error.response?.data?.message || 'خطایی در ارسال پاسخ رخ داد'
      }
    } finally {
      isSubmitting.value = false
    }
  }

  // Update an answer
  const updateAnswer = async (answerId, content) => {
    if (!content.trim()) {
      return {
        success: false,
        message: 'محتوای پاسخ نمی‌تواند خالی باشد'
      }
    }

    isUpdating.value = true
    try {
      const response = await api.put(`/answers/${answerId}`, {
        content: content.trim()
      })
      return {
        success: true,
        data: response.data,
        message: 'پاسخ با موفقیت ویرایش شد'
      }
    } catch (error) {
      console.error('Error updating answer:', error)
      return {
        success: false,
        message: error.response?.data?.message || 'خطایی در ویرایش پاسخ رخ داد'
      }
    } finally {
      isUpdating.value = false
    }
  }

  // Delete an answer
  const deleteAnswer = async (answerId) => {
    isDeleting.value = answerId
    try {
      await api.delete(`/answers/${answerId}`)
      return {
        success: true,
        message: 'پاسخ با موفقیت حذف شد'
      }
    } catch (error) {
      console.error('Error deleting answer:', error)
      return {
        success: false,
        message: error.response?.data?.message || 'خطایی در حذف پاسخ رخ داد'
      }
    } finally {
      isDeleting.value = null
    }
  }

  // Vote on an answer
  const voteAnswer = async (answerId, voteType) => {
    if (!['up', 'down'].includes(voteType)) {
      return {
        success: false,
        message: 'نوع رای نامعتبر است'
      }
    }

    isVoting.value = answerId
    try {
      const response = await api.post(`/answers/${answerId}/vote`, {
        type: voteType
      })
      return {
        success: true,
        data: response.data,
        message: 'رای شما ثبت شد'
      }
    } catch (error) {
      console.error('Error voting on answer:', error)
      return {
        success: false,
        message: error.response?.data?.message || 'خطایی در ثبت رای رخ داد'
      }
    } finally {
      isVoting.value = null
    }
  }

  return {
    // State
    isLoading,
    isSubmitting,
    isUpdating,
    isDeleting,
    isVoting,

    // Methods
    fetchAnswers,
    addAnswer,
    updateAnswer,
    deleteAnswer,
    voteAnswer
  }
}
