import { ref } from 'vue'
import api from '../services/api.js'

export function useCategories() {
  const categories = ref([])
  const isLoading = ref(false)
  const errors = ref({})

  // Fetch categories with optional search query and pagination
  const fetchCategories = async (params = {}) => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await api.get('/categories', {
        params: {
          query: params.query || '',
          page: params.page || 1,
          per_page: params.per_page || 10,
          ...params
        }
      })

      const data = response.data.data || response.data

      // For Select2 component, we don't manage state here - just return the data
      // The component will handle its own state management
      return { success: true, data: data }
    } catch (error) {
      console.error('Error fetching categories:', error)
      const errorMessage = 'خطا در بارگذاری دسته‌بندی‌ها'
      errors.value.fetch = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Fetch popular categories
  const fetchPopularCategories = async (limit = 15) => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await api.get('/categories/popular', {
        params: { limit }
      })
      const popularCategories = response.data.data || response.data
      return { success: true, data: popularCategories }
    } catch (error) {
      console.error('Error fetching popular categories:', error)
      const errorMessage = 'خطا در بارگذاری دسته‌بندی‌های محبوب'
      errors.value.fetchPopular = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Create a new category
  const createCategory = async (categoryData) => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await api.post('/categories', categoryData)
      const newCategory = response.data.data || response.data
      categories.value.push(newCategory)
      return { success: true, data: newCategory }
    } catch (error) {
      if (error.response && error.response.status === 422) {
        // Handle validation errors
        errors.value = Object.entries(error.response.data.errors).reduce((acc, [key, value]) => {
          acc[key] = Array.isArray(value) ? value[0] : value
          return acc
        }, {})
        return { success: false, errors: errors.value }
      } else if (error.response && error.response.status === 401) {
        return { success: false, error: 'authentication', message: 'برای ایجاد دسته‌بندی باید وارد شوید.' }
      } else {
        console.error('Error creating category:', error)
        const errorMessage = 'خطایی در ایجاد دسته‌بندی رخ داد. لطفا دوباره تلاش کنید.'
        errors.value.create = errorMessage
        return { success: false, error: 'general', message: errorMessage }
      }
    } finally {
      isLoading.value = false
    }
  }

  // Update an existing category
  const updateCategory = async (id, categoryData) => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await api.put(`/categories/${id}`, categoryData)
      const updatedCategory = response.data.data || response.data

      // Update the category in the local array
      const index = categories.value.findIndex(cat => cat.id === id)
      if (index !== -1) {
        categories.value[index] = updatedCategory
      }

      return { success: true, data: updatedCategory }
    } catch (error) {
      if (error.response && error.response.status === 422) {
        errors.value = Object.entries(error.response.data.errors).reduce((acc, [key, value]) => {
          acc[key] = Array.isArray(value) ? value[0] : value
          return acc
        }, {})
        return { success: false, errors: errors.value }
      } else if (error.response && error.response.status === 401) {
        return { success: false, error: 'authentication', message: 'برای ویرایش دسته‌بندی باید وارد شوید.' }
      } else if (error.response && error.response.status === 403) {
        return { success: false, error: 'authorization', message: 'شما مجاز به ویرایش این دسته‌بندی نیستید.' }
      } else {
        console.error('Error updating category:', error)
        const errorMessage = 'خطایی در ویرایش دسته‌بندی رخ داد. لطفا دوباره تلاش کنید.'
        errors.value.update = errorMessage
        return { success: false, error: 'general', message: errorMessage }
      }
    } finally {
      isLoading.value = false
    }
  }

  // Delete a category
  const deleteCategory = async (id) => {
    isLoading.value = true
    errors.value = {}

    try {
      await api.delete(`/categories/${id}`)

      // Remove the category from the local array
      categories.value = categories.value.filter(cat => cat.id !== id)

      return { success: true }
    } catch (error) {
      if (error.response && error.response.status === 401) {
        return { success: false, error: 'authentication', message: 'برای حذف دسته‌بندی باید وارد شوید.' }
      } else if (error.response && error.response.status === 403) {
        return { success: false, error: 'authorization', message: 'شما مجاز به حذف این دسته‌بندی نیستید.' }
      } else {
        console.error('Error deleting category:', error)
        const errorMessage = 'خطایی در حذف دسته‌بندی رخ داد. لطفا دوباره تلاش کنید.'
        errors.value.delete = errorMessage
        return { success: false, error: 'general', message: errorMessage }
      }
    } finally {
      isLoading.value = false
    }
  }

  // Get a specific category by ID
  const getCategoryById = (id) => {
    return categories.value.find(cat => cat.id === id)
  }

  // Clear errors
  const clearErrors = () => {
    errors.value = {}
  }

  return {
    // State
    categories,
    isLoading,
    errors,

    // Methods
    fetchCategories,
    fetchPopularCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    getCategoryById,
    clearErrors
  }
}
