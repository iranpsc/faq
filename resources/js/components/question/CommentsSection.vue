<template>
  <div
    :class="parentType === 'question'
      ? 'bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 sm:p-6 mb-6 w-full min-w-0 overflow-hidden'
      : 'mt-4 border-t border-gray-200 dark:border-gray-600 pt-4 w-full min-w-0'"
  >
    <h3
      :class="parentType === 'question'
        ? 'text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4'
        : 'text-sm font-medium text-gray-900 dark:text-gray-100 mb-3'"
    >
      نظرات {{ parentType === 'question' ? 'کاربران' : '' }} ({{ commentsPagination?.total || comments.length }})
    </h3>

    <!-- Comments List -->
    <div :class="parentType === 'question' ? 'space-y-4 mb-6' : 'space-y-3 mb-4'">
      <div
        v-if="comments.length === 0"
        :class="parentType === 'question'
          ? 'text-center py-8 text-gray-500 dark:text-gray-400'
          : 'text-center py-4 text-gray-500 dark:text-gray-400 text-sm'"
      >
        هنوز نظری ثبت نشده است{{ parentType === 'question' ? '. اولین نفری باشید که نظر می‌دهد!' : '.' }}
      </div>

      <div
        v-for="comment in comments"
        :key="comment.id"
        :class="parentType === 'question'
          ? 'bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4'
          : 'bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3'"
      >
        <div class="flex items-start gap-2 sm:gap-3 min-w-0">
          <router-link
            v-if="comment.user"
            :to="`/authors/${comment.user.id}`"
            class="flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded group"
            :title="`نمایش پروفایل ${comment.user?.name || ''}`"
          >
            <BaseAvatar
              :src="comment.user?.image_url"
              :name="comment.user?.name"
              :size="parentType === 'question' ? 'sm' : 'xs'"
              class="transition-transform group-hover:scale-105"
            />
          </router-link>
          <BaseAvatar
            v-else
            :src="comment.user?.image_url"
            :name="comment.user?.name"
            :size="parentType === 'question' ? 'sm' : 'xs'"
            class="flex-shrink-0"
          />
          <div class="flex-1 min-w-0">
            <div
              :class="parentType === 'question'
                ? 'flex flex-wrap items-center gap-2 mb-2'
                : 'flex flex-wrap items-center gap-2 mb-1'"
            >
              <router-link
                v-if="comment.user"
                :to="`/authors/${comment.user.id}`"
                :class="parentType === 'question'
                  ? 'font-medium text-gray-900 dark:text-gray-100 truncate hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 rounded'
                  : 'font-medium text-gray-900 dark:text-gray-100 text-sm truncate hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 rounded'"
                >{{ comment.user?.name }}</router-link
              >
              <span
                v-else
                :class="parentType === 'question'
                  ? 'font-medium text-gray-900 dark:text-gray-100 truncate'
                  : 'font-medium text-gray-900 dark:text-gray-100 text-sm truncate'"
                >{{ comment.user?.name }}</span
              >
              <span class="text-xs text-gray-500 whitespace-nowrap"
                >امتیاز: {{ formatNumber(comment.user?.score || 0) }}</span
              >
              <span class="text-xs text-gray-400">•</span>
              <span class="text-xs text-gray-500 whitespace-nowrap">{{ comment.created_at }}</span>
              <span
                v-if="comment.updated_at !== comment.created_at"
                class="text-xs text-gray-400 whitespace-nowrap"
                >(ویرایش شده: {{ comment.updated_at }})</span
              >
            </div>

            <!-- Comment Content -->
            <div v-if="editingComment !== comment.id">
              <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed break-words overflow-wrap-anywhere">
                {{ comment.content }}
              </p>
            </div>

            <!-- Edit Form -->
            <div v-else :class="parentType === 'question' ? 'mb-3' : 'mb-2'">
              <textarea
                v-model="editContent"
                :rows="parentType === 'question' ? 3 : 2"
                :class="parentType === 'question'
                  ? 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm resize-none'
                  : 'w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm resize-none'"
              ></textarea>
              <div class="flex gap-2 mt-2">
                <button
                  @click="saveEdit(comment)"
                  :disabled="!editContent.trim() || isUpdating"
                  :class="parentType === 'question'
                    ? 'px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 disabled:opacity-50'
                    : 'px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 disabled:opacity-50'"
                >
                  {{ isUpdating ? 'در حال ذخیره...' : 'ذخیره' }}
                </button>
                <button
                  @click="cancelEdit"
                  :class="parentType === 'question'
                    ? 'px-3 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600'
                    : 'px-2 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600'"
                >
                  انصراف
                </button>
              </div>
            </div>

            <!-- Actions -->
            <div :class="parentType === 'question' ? 'flex items-center gap-4 mt-2' : 'flex items-center gap-3 mt-2'">
              <!-- Publish -->
              <div class="flex items-center gap-2">
                <span
                  v-if="!comment.published"
                  class="text-xs font-medium text-yellow-600 dark:text-yellow-400 whitespace-nowrap"
                  >منتشر نشده</span
                >
                <button
                  v-if="comment.can?.publish"
                  @click="publishComment(comment)"
                  :disabled="isPublishingComment === comment.id"
                  class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700 disabled:opacity-50 whitespace-nowrap"
                >
                  {{ isPublishingComment === comment.id ? 'در حال انتشار...' : 'انتشار' }}
                </button>
              </div>

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

              <!-- Edit/Delete -->
              <div v-if="comment.can?.update" class="flex items-center gap-2">
                <button
                  v-if="editingComment !== comment.id"
                  @click="startEdit(comment)"
                  class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1"
                >
                  ✏️ ویرایش
                </button>
              </div>

              <div v-if="comment.can?.delete" class="flex items-center gap-2">
                <button
                  @click="deleteComment(comment)"
                  class="text-xs text-red-600 hover:text-red-800 flex items-center gap-1"
                  :disabled="isDeleting === comment.id"
                >
                  🗑️ {{ isDeleting === comment.id ? 'در حال حذف...' : 'حذف' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Show More Comments -->
    <div v-if="hasMoreComments" class="text-center mb-4">
      <button
        @click="loadMoreComments"
        :disabled="isLoadingMore"
        :class="parentType === 'question'
          ? 'px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed'
          : 'px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed'"
      >
        {{ isLoadingMore ? 'در حال بارگذاری...' : 'نمایش نظرات بیشتر' }}
      </button>
    </div>

    <!-- Add Comment -->
    <div v-if="isAuthenticated">
      <!-- دکمه باز کردن فرم -->
      <div class="text-center mt-4" v-if="!showCommentBox">
        <button
          @click="showCommentBox = true"
          class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700"
        >
          ثبت نظر جدید
        </button>
      </div>

      <!-- فرم نظر دادن -->
      <div
        v-else
        :class="parentType === 'question'
          ? 'border-t border-gray-200 dark:border-gray-600 pt-4'
          : 'border-t border-gray-200 dark:border-gray-600 pt-3'"
      >
        <h4 v-if="parentType === 'question'" class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">
          دیدگاه خود را وارد کنید...
        </h4>
        <form @submit.prevent="submitComment" :class="parentType === 'question' ? 'space-y-3' : 'space-y-2'">
          <textarea
            v-model="newComment"
            :rows="parentType === 'question' ? 3 : 2"
            placeholder="نظر خود را بنویسید..."
            :class="parentType === 'question'
              ? 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm resize-none'
              : 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm resize-none'"
          ></textarea>
          <div class="flex justify-end gap-2">
            <button
              type="button"
              @click="showCommentBox = false"
              class="px-3 py-2 bg-gray-500 text-white text-sm rounded-lg hover:bg-gray-600"
            >
              انصراف
            </button>
            <button
              type="submit"
              :disabled="!newComment.trim() || isSubmitting"
              class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isSubmitting ? 'در حال ارسال...' : 'ارسال نظر' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Login prompt -->
    <div
      v-else
      :class="parentType === 'question'
        ? 'border-t border-gray-200 dark:border-gray-600 pt-4 text-center'
        : 'border-t border-gray-200 dark:border-gray-600 pt-3 text-center'"
    >
      <p
        :class="parentType === 'question'
          ? 'text-gray-600 dark:text-gray-400 text-sm'
          : 'text-gray-600 dark:text-gray-400 text-xs'"
      >
        برای ثبت نظر، لطفا وارد حساب کاربری خود شوید.
      </p>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { useComments } from '../../composables/useComments'
import VoteButtons from '../ui/VoteButtons.vue'
import { BaseAvatar } from '../ui'

export default {
  name: 'CommentsSection',
  components: { VoteButtons, BaseAvatar },
  props: {
    questionId: { type: [String, Number], required: false },
    answerId: { type: [String, Number], required: false },
    parentType: {
      type: String,
      default: 'question',
      validator: (v) => ['question', 'answer'].includes(v)
    }
  },
  emits: ['comment-added'],
  setup(props, { emit }) {
    const { isAuthenticated } = useAuth()
    const { isSubmitting, isUpdating, isDeleting, fetchComments: fetchCommentsApi, addComment, updateComment, deleteComment: deleteCommentApi } = useComments()

    const comments = ref([])
    const newComment = ref('')
    const editingComment = ref(null)
    const editContent = ref('')
    const isPublishingComment = ref(null)
    const commentsPagination = ref(null)
    const currentCommentsPage = ref(1)
    const isLoadingMore = ref(false)
    const showCommentBox = ref(false)

    const hasMoreComments = computed(() => {
      return commentsPagination.value && commentsPagination.value.current_page < commentsPagination.value.last_page
    })

    const parentId = computed(() => props.parentType === 'question' ? props.questionId : props.answerId)

    const formatNumber = (num) => new Intl.NumberFormat('fa-IR').format(num)

    const fetchComments = async (page = 1, append = false) => {
      const result = await fetchCommentsApi(parentId.value, props.parentType, page)
      if (result.success) {
        comments.value = append ? [...comments.value, ...result.data] : result.data
        commentsPagination.value = result.meta
        currentCommentsPage.value = page
      }
    }

    const loadMoreComments = async () => {
      if (!hasMoreComments.value || isLoadingMore.value) return
      isLoadingMore.value = true
      try {
        await fetchComments(currentCommentsPage.value + 1, true)
      } finally {
        isLoadingMore.value = false
      }
    }

    const handleCommentVoteChanged = (commentId, voteData) => {
      const idx = comments.value.findIndex(c => c.id === commentId)
      if (idx !== -1) {
        const existing = comments.value[idx]
        comments.value[idx] = { ...existing, votes: { upvotes: voteData.upvotes, downvotes: voteData.downvotes, user_vote: voteData.userVote } }
      }
    }

    const submitComment = async () => {
      if (!newComment.value.trim()) return
      const result = await addComment(parentId.value, newComment.value, props.parentType)
      if (result.success) {
        comments.value.unshift(result.data)
        if (commentsPagination.value) commentsPagination.value.total += 1
        newComment.value = ''
        showCommentBox.value = false
        emit('comment-added', result.data)
      }
    }

    const startEdit = (c) => { editingComment.value = c.id; editContent.value = c.content }
    const cancelEdit = () => { editingComment.value = null; editContent.value = '' }
    const saveEdit = async (c) => {
      if (!editContent.value.trim()) return
      const result = await updateComment(c.id, editContent.value)
      if (result.success) {
        const idx = comments.value.findIndex(cc => cc.id === c.id)
        if (idx !== -1) comments.value[idx] = { ...comments.value[idx], content: editContent.value, updated_at: result.data.updated_at || comments.value[idx].updated_at }
        cancelEdit()
      }
    }
    const deleteComment = async (c) => {
      const result = await deleteCommentApi(c.id)
      if (result.success) comments.value = comments.value.filter(cc => cc.id !== c.id)
    }
    const publishComment = async (c) => {
      isPublishingComment.value = c.id
      try {
        const res = await fetch(`/api/comments/${c.id}/publish`, { method: 'POST' })
        const json = await res.json()
        if (json.success) {
          const idx = comments.value.findIndex(cc => cc.id === c.id)
          if (idx !== -1) comments.value[idx].published = true
        }
      } finally {
        isPublishingComment.value = null
      }
    }

    onMounted(() => { fetchComments() })
    watch(() => parentId.value, (n, o) => { if (n && n !== o) fetchComments() })

    return {
      isAuthenticated, comments, commentsPagination, newComment,
      isSubmitting, editingComment, editContent, isUpdating, isDeleting,
      isPublishingComment, hasMoreComments, isLoadingMore,
      submitComment, loadMoreComments, handleCommentVoteChanged,
      startEdit, cancelEdit, saveEdit, deleteComment, publishComment,
      formatNumber, showCommentBox
    }
  }
}
</script>

<style scoped>
.overflow-wrap-anywhere {
  overflow-wrap: anywhere;
  word-break: break-word;
  hyphens: auto;
}
textarea {
  min-width: 0;
  width: 100%;
}
</style>
