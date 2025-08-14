import { ref, onMounted, onUnmounted } from 'vue'
import api from '../services/api.js'

export function useQuestions() {
  const isSubmitting = ref(false)
  const isLoading = ref(false)
  const questions = ref([])
  const pagination = ref({})
  const errors = ref({})

  // Fetch questions with optional parameters
  const fetchQuestions = async (params = {}) => {
    isLoading.value = true
    errors.value = {}
    const startTime = performance.now()

    try {
      const response = await api.get('/questions', {
        params,
        cacheTimeout: 180000 // 3 minutes cache for questions
      })
      questions.value = response.data.data
      pagination.value = {
        meta: response.data.meta,
        links: response.data.links
      }

      // Performance monitoring
      const loadTime = performance.now() - startTime
      if (loadTime > 1000) {
        console.warn(`Slow questions fetch: ${loadTime.toFixed(2)}ms`)
      }

      return { success: true, data: response.data.data, pagination: pagination.value }
    } catch (error) {
      console.error('Error fetching questions:', error)
      const errorMessage = 'خطا در بارگذاری سوالات'
      errors.value.fetch = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  const refreshQuestions = () => {
    fetchQuestions();
  }

  onMounted(() => {
    window.addEventListener('auth:login', refreshQuestions)
    window.addEventListener('auth:logout', refreshQuestions)
  })

  onUnmounted(() => {
    window.removeEventListener('auth:login', refreshQuestions)
    window.removeEventListener('auth:logout', refreshQuestions)
  })

  // Fetch a single question by ID
  const fetchQuestion = async (id) => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await api.get(`/questions/${id}`)
      return { success: true, data: response.data.data }
    } catch (error) {
      console.error('Error fetching question:', error)
      const errorMessage = error.response?.status === 404 ? 'سوال یافت نشد' : 'خطا در بارگذاری سوال'
      errors.value.fetch = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Prepare data for submission
  const prepareSubmitData = (formData) => ({
    category_id: formData.category ? formData.category.id : null,
    title: formData.title,
    content: formData.content,
    tags: formData.tags.map(tag => {
      if (tag.id && typeof tag.id === 'number') {
        return { id: tag.id };
      }
      return { name: tag.name || tag.id };
    }),
  });

  // Submit a new question
  const submitQuestion = async (formData) => {
    isSubmitting.value = true
    errors.value = {}

    try {
      const authToken = localStorage.getItem('auth_token');
      if (!authToken) {
        return { success: false, error: 'authentication', message: 'برای ثبت سوال باید وارد شوید.' }
      }

      const submissionData = prepareSubmitData(formData);
      const response = await api.post('/questions', submissionData)

      // Refresh questions list after successful creation
      const refreshed = await fetchQuestions()

      return { success: true, data: response.data.data, questions: questions.value, pagination: pagination.value, refreshed }
    } catch (error) {
      return handleError(error);
    } finally {
      isSubmitting.value = false
    }
  }

  // Update an existing question
  const updateQuestion = async (formData) => {
    isSubmitting.value = true;
    errors.value = {};

    try {
      const authToken = localStorage.getItem('auth_token');
      if (!authToken) {
        return { success: false, error: 'authentication', message: 'برای ویرایش سوال باید وارد شوید.' };
      }

      const submissionData = prepareSubmitData(formData);
      const response = await api.put(`/questions/${formData.id}`, submissionData);
      return { success: true, data: response.data.data };
    } catch (error) {
      return handleError(error);
    } finally {
      isSubmitting.value = false;
    }
  };

  // Handle API errors
  const handleError = (error) => {
    if (error.response) {
      if (error.response.status === 422) {
        errors.value = Object.entries(error.response.data.errors).reduce((acc, [key, value]) => {
          acc[key] = Array.isArray(value) ? value[0] : value;
          return acc;
        }, {});
        return { success: false, errors: errors.value };
      }
      if (error.response.status === 401) {
        return { success: false, error: 'authentication', message: 'شما اجازه این کار را ندارید.' };
      }
      if (error.response.status === 403) {
        return { success: false, error: 'authorization', message: 'شما اجازه حذف این سوال را ندارید.' };
      }
      if (error.response.status === 429) {
        return { success: false, error: 'rate_limit', message: error.response.data?.message || 'شما خیلی سریع رای می‌دهید. لطفا کمی صبر کنید.' };
      }
    }
    console.error('API Error:', error);
    return { success: false, error: 'general', message: 'خطایی در ارتباط با سرور رخ داد.' };
  };

  // Delete a question
  const deleteQuestion = async (questionId) => {
    isSubmitting.value = true
    errors.value = {}

    try {
      const authToken = localStorage.getItem('auth_token');
      if (!authToken) {
        return { success: false, error: 'authentication', message: 'برای حذف سوال باید وارد شوید.' };
      }

      await api.delete(`/questions/${questionId}`)
      return { success: true, message: 'سوال با موفقیت حذف شد.' }
    } catch (error) {
      return handleError(error);
    } finally {
      isSubmitting.value = false
    }
  }

  // Reset errors
  const clearErrors = () => {
    errors.value = {}
  }

    // Vote on a question (upvote or downvote)
  const voteQuestion = async (questionId, voteType) => {
    errors.value = {}

    try {
      const authToken = localStorage.getItem('auth_token');
      if (!authToken) {
        return { success: false, error: 'authentication', message: 'برای رای دادن باید وارد شوید.' };
      }

      const response = await api.post(`/questions/${questionId}/vote`, {
        type: voteType
      });

      // Return the updated vote data from the API response
      return {
        success: true,
        message: 'رای شما ثبت شد',
        data: response.data
      };
    } catch (error) {
      if (error.response) {
        if (error.response.status === 409) {
          return { success: false, status: 409, message: error.response.data?.message, data: error.response.data };
        }
      }
      return handleError(error);
    }
  }

  // Change page for pagination
  const changePage = async (page) => {
    if (page < 1) return
    await fetchQuestions({ page })
  }

  // Search questions
  const searchQuestions = async (query, limit = 10) => {
    if (!query || query.trim().length === 0) {
      return { success: true, data: [] }
    }

    try {
      const response = await api.get('/questions/search', {
        params: {
          q: query.trim(),
          limit: limit
        }
      })
      return { success: true, data: response.data.data }
    } catch (error) {
      console.error('Error searching questions:', error)
      return { success: false, error: 'خطا در جستجو', data: [] }
    }
  }

  return {
    // State
    isSubmitting,
    isLoading,
    questions,
    pagination,
    errors,

    // Methods
    fetchQuestions,
    fetchQuestion,
    submitQuestion,
    updateQuestion,
    deleteQuestion,
    clearErrors,
    voteQuestion,
    changePage,
    searchQuestions
  }
}
