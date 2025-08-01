import { ref } from 'vue'
import axios from 'axios'

export function useTags() {
  const tags = ref([])
  const isLoading = ref(false)
  const errors = ref({})

  // Fetch tags with optional search query
  const fetchTags = async (query = '') => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await axios.get('/api/tags', {
        params: { query }
      })
      tags.value = response.data.data || response.data
      return { success: true, data: tags.value }
    } catch (error) {
      console.error('Error fetching tags:', error)
      const errorMessage = 'خطا در بارگذاری برچسب‌ها'
      errors.value.fetch = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Create a new tag
  const createTag = async (tagData) => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await axios.post('/api/tags', tagData)
      const newTag = response.data.data || response.data
      tags.value.push(newTag)
      return { success: true, data: newTag }
    } catch (error) {
      if (error.response && error.response.status === 422) {
        // Handle validation errors
        errors.value = Object.entries(error.response.data.errors).reduce((acc, [key, value]) => {
          acc[key] = Array.isArray(value) ? value[0] : value
          return acc
        }, {})
        return { success: false, errors: errors.value }
      } else if (error.response && error.response.status === 401) {
        return { success: false, error: 'authentication', message: 'برای ایجاد برچسب باید وارد شوید.' }
      } else {
        console.error('Error creating tag:', error)
        const errorMessage = 'خطایی در ایجاد برچسب رخ داد. لطفا دوباره تلاش کنید.'
        errors.value.create = errorMessage
        return { success: false, error: 'general', message: errorMessage }
      }
    } finally {
      isLoading.value = false
    }
  }

  // Update an existing tag
  const updateTag = async (id, tagData) => {
    isLoading.value = true
    errors.value = {}

    try {
      const response = await axios.put(`/api/tags/${id}`, tagData)
      const updatedTag = response.data.data || response.data

      // Update the tag in the local array
      const index = tags.value.findIndex(tag => tag.id === id)
      if (index !== -1) {
        tags.value[index] = updatedTag
      }

      return { success: true, data: updatedTag }
    } catch (error) {
      if (error.response && error.response.status === 422) {
        errors.value = Object.entries(error.response.data.errors).reduce((acc, [key, value]) => {
          acc[key] = Array.isArray(value) ? value[0] : value
          return acc
        }, {})
        return { success: false, errors: errors.value }
      } else if (error.response && error.response.status === 401) {
        return { success: false, error: 'authentication', message: 'برای ویرایش برچسب باید وارد شوید.' }
      } else if (error.response && error.response.status === 403) {
        return { success: false, error: 'authorization', message: 'شما مجاز به ویرایش این برچسب نیستید.' }
      } else {
        console.error('Error updating tag:', error)
        const errorMessage = 'خطایی در ویرایش برچسب رخ داد. لطفا دوباره تلاش کنید.'
        errors.value.update = errorMessage
        return { success: false, error: 'general', message: errorMessage }
      }
    } finally {
      isLoading.value = false
    }
  }

  // Delete a tag
  const deleteTag = async (id) => {
    isLoading.value = true
    errors.value = {}

    try {
      await axios.delete(`/api/tags/${id}`)

      // Remove the tag from the local array
      tags.value = tags.value.filter(tag => tag.id !== id)

      return { success: true }
    } catch (error) {
      if (error.response && error.response.status === 401) {
        return { success: false, error: 'authentication', message: 'برای حذف برچسب باید وارد شوید.' }
      } else if (error.response && error.response.status === 403) {
        return { success: false, error: 'authorization', message: 'شما مجاز به حذف این برچسب نیستید.' }
      } else {
        console.error('Error deleting tag:', error)
        const errorMessage = 'خطایی در حذف برچسب رخ داد. لطفا دوباره تلاش کنید.'
        errors.value.delete = errorMessage
        return { success: false, error: 'general', message: errorMessage }
      }
    } finally {
      isLoading.value = false
    }
  }

  // Add a new tag to the options (for multiselect)
  const addTag = (newTagName) => {
    // Check if tag already exists to avoid duplicates
    const existingTag = tags.value.find(tag =>
      tag.name.toLowerCase() === newTagName.toLowerCase()
    )

    if (existingTag) {
      return existingTag
    }

    const tag = {
      name: newTagName,
      id: newTagName // For new tags, we use the name as a temporary ID
    }

    tags.value.push(tag)
    return tag
  }

  // Get a specific tag by ID
  const getTagById = (id) => {
    return tags.value.find(tag => tag.id === id)
  }

  // Get a specific tag by name
  const getTagByName = (name) => {
    return tags.value.find(tag => tag.name === name)
  }

  // Clear errors
  const clearErrors = () => {
    errors.value = {}
  }

  return {
    // State
    tags,
    isLoading,
    errors,

    // Methods
    fetchTags,
    createTag,
    updateTag,
    deleteTag,
    addTag,
    getTagById,
    getTagByName,
    clearErrors
  }
}
