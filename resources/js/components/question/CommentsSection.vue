<template>
  <div :class="parentType === 'question' ? 'bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6' : 'mt-4 border-t border-gray-200 dark:border-gray-600 pt-4'">
    <h3 :class="parentType === 'question' ? 'text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4' : 'text-sm font-medium text-gray-900 dark:text-gray-100 mb-3'">
      نظرات {{ parentType === 'question' ? 'کاربران' : '' }} ({{ comments.length }})
    </h3>

    <!-- Comments List -->
    <div :class="parentType === 'question' ? 'space-y-4 mb-6' : 'space-y-3 mb-4'">
      <div v-if="comments.length === 0" :class="parentType === 'question' ? 'text-center py-8 text-gray-500 dark:text-gray-400' : 'text-center py-4 text-gray-500 dark:text-gray-400 text-sm'">
        هنوز نظری ثبت نشده است{{ parentType === 'question' ? '. اولین نفری باشید که نظر می‌دهد!' : '.' }}
      </div>

      <div
        v-for="comment in comments"
        :key="comment.id"
        :class="parentType === 'question' ? 'bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4' : 'bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3'"
      >
        <div class="flex items-start gap-3">
          <div :class="parentType === 'question' ? 'w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0' : 'w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0'">
            <img
              v-if="comment.user?.avatar"
              :src="comment.user.avatar"
              :alt="comment.user.name"
              class="w-full h-full object-cover rounded-full"
            >
            <svg v-else :class="parentType === 'question' ? 'w-5 h-5 text-gray-600' : 'w-3 h-3 text-gray-600'" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
          </div>
          <div class="flex-1">
            <div :class="parentType === 'question' ? 'flex items-center gap-2 mb-2' : 'flex items-center gap-2 mb-1'">
              <span :class="parentType === 'question' ? 'font-medium text-gray-900 dark:text-gray-100' : 'font-medium text-gray-900 dark:text-gray-100 text-sm'">{{ comment.user?.name }}</span>
              <span class="text-xs text-gray-500">امتیاز: {{ formatNumber(comment.user?.score || 0) }}</span>
              <span class="text-xs text-gray-400">•</span>
              <span class="text-xs text-gray-500">{{ comment.created_at }}</span>
              <span v-if="comment.updated_at !== comment.created_at" class="text-xs text-gray-400">(ویرایش شده: {{ comment.updated_at }})</span>
            </div>

            <!-- Comment Content -->
            <div v-if="editingComment !== comment.id">
              <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                {{ comment.content }}
              </p>
            </div>

            <!-- Edit Form -->
            <div v-else :class="parentType === 'question' ? 'mb-3' : 'mb-2'">
              <textarea
                v-model="editContent"
                :rows="parentType === 'question' ? 3 : 2"
                :class="parentType === 'question' ? 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm resize-none' : 'w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm resize-none'"
              ></textarea>
              <div class="flex gap-2 mt-2">
                <button
                  @click="saveEdit(comment)"
                  :disabled="!editContent.trim() || isUpdating"
                  :class="parentType === 'question' ? 'px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 disabled:opacity-50' : 'px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 disabled:opacity-50'"
                >
                  {{ isUpdating ? 'در حال ذخیره...' : 'ذخیره' }}
                </button>
                <button
                  @click="cancelEdit"
                  :class="parentType === 'question' ? 'px-3 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600' : 'px-2 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600'"
                >
                  انصراف
                </button>
              </div>
            </div>

            <!-- Actions -->
            <div :class="parentType === 'question' ? 'flex items-center gap-4 mt-2' : 'flex items-center gap-3 mt-2'">
              <!-- Voting -->
              <VoteButtons
                resource-type="comment"
                :resource-id="comment.id"
                :initial-upvotes="comment.votes?.upvotes || 0"
                :initial-downvotes="comment.votes?.downvotes || 0"
                :initial-user-vote="comment.votes?.user_vote"
                size="small"
                @vote-changed="handleCommentVoteChanged(comment.id, $event)"
              />

              <!-- Edit/Delete Actions -->
              <div v-if="comment.can?.update" class="flex items-center gap-2">
                <button
                  v-if="editingComment !== comment.id"
                  @click="startEdit(comment)"
                  class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  ویرایش
                </button>
              </div>

              <div v-if="comment.can?.delete" class="flex items-center gap-2">
                <button
                  @click="deleteComment(comment)"
                  class="text-xs text-red-600 hover:text-red-800 flex items-center gap-1"
                  :disabled="isDeleting === comment.id"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                  {{ isDeleting === comment.id ? 'در حال حذف...' : 'حذف' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Comment Form -->
    <div v-if="isAuthenticated" :class="parentType === 'question' ? 'border-t border-gray-200 dark:border-gray-600 pt-4' : 'border-t border-gray-200 dark:border-gray-600 pt-3'">
      <h4 v-if="parentType === 'question'" class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">دیدگاه خود را وارد کنید...</h4>
      <form @submit.prevent="submitComment" :class="parentType === 'question' ? 'space-y-3' : 'space-y-2'">
        <textarea
          v-model="newComment"
          :rows="parentType === 'question' ? 3 : 2"
          placeholder="نظر خود را بنویسید..."
          :class="parentType === 'question' ? 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm resize-none' : 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm resize-none'"
        ></textarea>
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="!newComment.trim() || isSubmitting"
            :class="parentType === 'question' ? 'px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed' : 'px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed'"
          >
            {{ isSubmitting ? 'در حال ارسال...' : 'ارسال نظر' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Login prompt for non-authenticated users -->
    <div v-else :class="parentType === 'question' ? 'border-t border-gray-200 dark:border-gray-600 pt-4 text-center' : 'border-t border-gray-200 dark:border-gray-600 pt-3 text-center'">
      <p :class="parentType === 'question' ? 'text-gray-600 dark:text-gray-400 text-sm' : 'text-gray-600 dark:text-gray-400 text-xs'">
        برای ثبت نظر، لطفا وارد حساب کاربری خود شوید.
      </p>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { useComments } from '../../composables/useComments'
import VoteButtons from '../ui/VoteButtons.vue'

export default {
  name: 'CommentsSection',
  components: {
    VoteButtons
  },
  props: {
    questionId: {
      type: [String, Number],
      required: false
    },
    answerId: {
      type: [String, Number],
      required: false
    },
    parentType: {
      type: String,
      default: 'question',
      validator: (value) => ['question', 'answer'].includes(value)
    }
  },
  emits: ['comment-added'],
  setup(props, { emit }) {
    const { isAuthenticated } = useAuth()
    const {
      isLoading,
      isSubmitting,
      isUpdating,
      isDeleting,
      fetchComments: fetchCommentsApi,
      addComment,
      updateComment,
      deleteComment: deleteCommentApi
    } = useComments()

    const comments = ref([])
    const newComment = ref('')
    const editingComment = ref(null)
    const editContent = ref('')

    // Computed property to get the parent ID
    const parentId = computed(() => {
      return props.parentType === 'question' ? props.questionId : props.answerId
    })

    const formatNumber = (num) => {
      return new Intl.NumberFormat('fa-IR').format(num)
    }

    const fetchComments = async () => {
      const result = await fetchCommentsApi(parentId.value, props.parentType)
      if (result.success) {
        comments.value = result.data
      } else {
        console.error('Error fetching comments:', result.message)
      }
    }

    // Handle vote changed from VoteButtons component
    const handleCommentVoteChanged = (commentId, voteData) => {
      const commentIndex = comments.value.findIndex(c => c.id === commentId)
      if (commentIndex !== -1) {
        // Update the comment's vote data
        const existingComment = comments.value[commentIndex]
        const updatedComment = {
          ...existingComment,
          votes: {
            upvotes: voteData.upvotes,
            downvotes: voteData.downvotes,
            user_vote: voteData.userVote
          }
        }
        // Replace the comment to trigger reactivity
        comments.value[commentIndex] = updatedComment
      }
    }

    const submitComment = async () => {
      if (!newComment.value.trim()) return

      const result = await addComment(parentId.value, newComment.value, props.parentType)

      if (result.success) {
        // Add the new comment to the list
        comments.value.unshift(result.data)
        newComment.value = ''
        emit('comment-added')

        // Show success message
        const Swal = window.Swal || window.$swal;
        if (Swal) {
          Swal.fire({
            title: 'موفقیت!',
            text: result.message,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });
        }
      } else {
        // Show error message
        const Swal = window.Swal || window.$swal;
        if (Swal) {
          Swal.fire({
            title: 'خطا!',
            text: result.message,
            icon: 'error',
            confirmButtonText: 'باشه'
          });
        }
      }
    }

    const startEdit = (comment) => {
      editingComment.value = comment.id
      editContent.value = comment.content
    }

    const cancelEdit = () => {
      editingComment.value = null
      editContent.value = ''
    }

    const saveEdit = async (comment) => {
      if (!editContent.value.trim()) return

      const result = await updateComment(comment.id, editContent.value)

      if (result.success) {
        // Update the comment in the list while preserving other properties
        const commentIndex = comments.value.findIndex(c => c.id === comment.id)
        if (commentIndex !== -1) {
          // Create a new comment object to ensure reactivity
          const existingComment = comments.value[commentIndex]
          const updatedComment = {
            ...existingComment,
            content: editContent.value,
            updated_at: result.data.updated_at || existingComment.updated_at
          }

          // Replace the comment to trigger reactivity
          comments.value[commentIndex] = updatedComment
        }

        editingComment.value = null
        editContent.value = ''

        // Show success message
        const Swal = window.Swal || window.$swal;
        if (Swal) {
          Swal.fire({
            title: 'موفقیت!',
            text: result.message,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });
        }
      } else {
        // Show error message
        const Swal = window.Swal || window.$swal;
        if (Swal) {
          Swal.fire({
            title: 'خطا!',
            text: result.message,
            icon: 'error',
            confirmButtonText: 'باشه'
          });
        }
      }
    }

    const deleteComment = async (comment) => {
      const Swal = window.Swal || window.$swal;

      if (Swal) {
        const confirmResult = await Swal.fire({
          title: 'آیا مطمئن هستید؟',
          text: 'آیا مطمئن هستید که میخواهید این نظر را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'بله، حذف کن!',
          cancelButtonText: 'انصراف',
          reverseButtons: true
        });

        if (!confirmResult.isConfirmed) return;
      } else {
        if (!confirm('آیا از حذف این نظر اطمینان دارید؟')) return;
      }

      const result = await deleteCommentApi(comment.id)

      if (result.success) {
        // Remove the comment from the list
        comments.value = comments.value.filter(c => c.id !== comment.id)

        // Show success message
        if (Swal) {
          Swal.fire({
            title: 'حذف شد!',
            text: result.message,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });
        }
      } else {
        // Show error message
        if (Swal) {
          Swal.fire({
            title: 'خطا!',
            text: result.message,
            icon: 'error',
            confirmButtonText: 'باشه'
          });
        }
      }
    }

    // Fetch comments when component mounts
    onMounted(() => {
      fetchComments()
    })

    // Watch for changes in parentId and refetch comments
    watch(() => parentId.value, () => {
      if (parentId.value) {
        fetchComments()
      }
    })

    return {
      isAuthenticated,
      comments,
      newComment,
      isSubmitting,
      editingComment,
      editContent,
      isUpdating,
      isDeleting,
      submitComment,
      handleCommentVoteChanged,
      startEdit,
      cancelEdit,
      saveEdit,
      deleteComment,
      formatNumber
    }
  }
}
</script>

<style scoped>
/* No additional styles needed - using VoteButtons component styles */
</style>
