import { ref } from 'vue'
import axios from 'axios'

export function useCategories() {
  const categories = ref([])
  const isLoading = ref(false)
  const errors = ref({})

  // Fetch categories with optional search query
  const fetchCategories = async (query = '') => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await axios.get('/api/categories', {
        params: { query }
      })
      categories.value = response.data.data || response.data
      return { success: true, data: categories.value }
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
      const response = await axios.get('/api/categories/popular', {
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
      const response = await axios.post('/api/categories', categoryData)
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
      const response = await axios.put(`/api/categories/${id}`, categoryData)
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
      await axios.delete(`/api/categories/${id}`)

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
