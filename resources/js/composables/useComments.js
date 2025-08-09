import { ref } from 'vue'
import axios from 'axios'

export function useComments() {
  const isLoading = ref(false)
  const isSubmitting = ref(false)
  const isUpdating = ref(false)
  const isDeleting = ref(null)
  const isVoting = ref(null)

  // Fetch comments for a question or answer
  const fetchComments = async (parentId, parentType = 'question', page = 1) => {
    isLoading.value = true
    try {
      const endpoint = parentType === 'question'
        ? `/api/questions/${parentId}/comments`
        : `/api/answers/${parentId}/comments`
      const response = await axios.get(endpoint, {
        params: { page }
      })
      return {
        success: true,
        data: response.data.data,
        meta: response.data.meta,
        links: response.data.links,
        message: 'Comments fetched successfully'
      }
    } catch (error) {
      console.error('Error fetching comments:', error)
      return {
        success: false,
        data: [],
        meta: null,
        links: null,
        message: error.response?.data?.message || 'خطایی در بارگذاری نظرات رخ داد'
      }
    } finally {
      isLoading.value = false
    }
  }

  // Add a new comment
  const addComment = async (parentId, content, parentType = 'question') => {
    if (!content.trim()) {
      return {
        success: false,
        message: 'محتوای نظر نمی‌تواند خالی باشد'
      }
    }

    isSubmitting.value = true
    try {
      const endpoint = parentType === 'question'
        ? `/api/questions/${parentId}/comments`
        : `/api/answers/${parentId}/comments`
      const response = await axios.post(endpoint, {
        content: content.trim()
      })
      return {
        success: true,
        data: response.data.data,
        message: response.data.message || 'نظر با موفقیت اضافه شد'
      }
    } catch (error) {
      console.error('Error adding comment:', error)
      return {
        success: false,
        message: error.response?.data?.message || 'خطایی در ارسال نظر رخ داد'
      }
    } finally {
      isSubmitting.value = false
    }
  }

  // Update a comment
  const updateComment = async (commentId, content) => {
    if (!content.trim()) {
      return {
        success: false,
        message: 'محتوای نظر نمی‌تواند خالی باشد'
      }
    }

    isUpdating.value = true
    try {
      const response = await axios.put(`/api/comments/${commentId}`, {
        content: content.trim()
      })
      return {
        success: true,
        data: response.data,
        message: 'نظر با موفقیت ویرایش شد'
      }
    } catch (error) {
      console.error('Error updating comment:', error)
      return {
        success: false,
        message: error.response?.data?.message || 'خطایی در ویرایش نظر رخ داد'
      }
    } finally {
      isUpdating.value = false
    }
  }

  // Delete a comment
  const deleteComment = async (commentId) => {
    isDeleting.value = commentId
    try {
      await axios.delete(`/api/comments/${commentId}`)
      return {
        success: true,
        message: 'نظر با موفقیت حذف شد'
      }
    } catch (error) {
      console.error('Error deleting comment:', error)
      return {
        success: false,
        message: error.response?.data?.message || 'خطایی در حذف نظر رخ داد'
      }
    } finally {
      isDeleting.value = null
    }
  }

  // Vote on a comment
  const voteComment = async (commentId, voteType) => {
    if (!['up', 'down'].includes(voteType)) {
      return {
        success: false,
        message: 'نوع رای نامعتبر است'
      }
    }

    isVoting.value = commentId
    try {
      const response = await axios.post(`/api/comments/${commentId}/vote`, {
        type: voteType
      })
      return {
        success: true,
        data: response.data,
        message: 'رای شما ثبت شد'
      }
    } catch (error) {
      console.error('Error voting on comment:', error)
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
    fetchComments,
    addComment,
    updateComment,
    deleteComment,
    voteComment
  }
}
