import { ref } from 'vue'
import api from '../services/api.js'

export function useUsers() {
  const isLoading = ref(false)
  const activeUsers = ref([])
  const errors = ref({})

  // Fetch most active users
  const fetchActiveUsers = async (limit = 5) => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await api.get('/dashboard/active-users', {
        params: { limit }
      })
      activeUsers.value = response.data.data
      return { success: true, data: response.data.data }
    } catch (error) {
      console.error('Error fetching active users:', error)
      const errorMessage = 'خطا در بارگذاری کاربران فعال'
      errors.value.activeUsers = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Clear all errors
  const clearErrors = () => {
    errors.value = {}
  }

  return {
    // State
    isLoading,
    activeUsers,
    errors,

    // Methods
    fetchActiveUsers,
    clearErrors
  }
}
