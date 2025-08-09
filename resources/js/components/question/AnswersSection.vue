<template>
  <div class="mt-8">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
      پاسخ‌ها ({{ usePagination ? (answersPagination?.total || sortedAnswers.length) : sortedAnswers.length }})
    </h3>

    <!-- Add Answer Form -->
    <div v-if="isAuthenticated" class="mb-8">
      <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
        پاسخ خود را ثبت کنید
      </h4>
      <form @submit.prevent="submitAnswer">
        <BaseEditor
          v-model="newAnswer"
          mode="simple"
          :height="300"
          placeholder="پاسخ خود را بنویسید..."
          :image-upload="true"
        />
        <div class="mt-4 flex justify-end">
          <button
            type="submit"
            :disabled="!newAnswer.trim() || isSubmittingAnswer"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isSubmittingAnswer ? 'در حال ارسال...' : 'ارسال پاسخ' }}
          </button>
        </div>
      </form>
    </div>
    <div v-else class="mb-8 border border-gray-200 dark:border-gray-600 rounded-lg p-4 text-center">
      <p class="text-gray-600 dark:text-gray-400">
        برای ثبت پاسخ، لطفا وارد حساب کاربری خود شوید.
      </p>
    </div>

    <!-- Answers List -->
    <div class="space-y-8">
      <div v-if="sortedAnswers.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
        هنوز پاسخی ثبت نشده است. اولین نفری باشید که پاسخ می‌دهد!
      </div>

      <div
        v-for="answer in sortedAnswers"
        :key="answer.id"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm w-full min-w-0 overflow-hidden"
      >
        <div
          :class="[
            'p-4 sm:p-8',
            answer.is_correct
              ? 'bg-green-50 dark:bg-green-900/20'
              : ''
          ]"
        >
          <div class="flex items-start gap-3 sm:gap-6 min-w-0">
            <BaseAvatar
              :src="answer.user?.image_url"
              :name="answer.user?.name"
              :score="answer.user?.score"
              size="md"
              class="flex-shrink-0"
            />
            <div class="flex-1 min-w-0">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                  <span class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ answer.user?.name }}</span>
                  <span class="text-xs text-blue-600 whitespace-nowrap">امتیاز: {{ formatNumber(answer.user?.score || 0) }}</span>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ formatDate(answer.created_at) }}</span>
              </div>

              <!-- Answer Content -->
              <div v-if="editingAnswer !== answer.id" class="mt-4 sm:mt-6 overflow-hidden">
                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 break-words" v-html="answer.content"></div>
              </div>

              <!-- Edit Form -->
              <div v-else class="mt-6">
                <BaseEditor
                  v-model="editContent"
                  mode="simple"
                  :height="200"
                  placeholder="پاسخ خود را ویرایش کنید..."
                  :image-upload="true"
                />
                <div class="flex gap-2 mt-3">
                  <button
                    @click="saveEdit(answer)"
                    :disabled="!editContent.trim() || isUpdatingAnswer"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50"
                  >
                    {{ isUpdatingAnswer ? 'در حال ذخیره...' : 'ذخیره' }}
                  </button>
                  <button
                    @click="cancelEdit"
                    class="px-4 py-2 bg-gray-500 text-white text-sm rounded hover:bg-gray-600"
                  >
                    انصراف
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 sm:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-2 sm:gap-6 flex-wrap">
            <span v-if="answer.is_correct" class="text-sm font-medium text-green-600 dark:text-green-400 whitespace-nowrap">تایید شده</span>
            <span v-if="!answer.published" class="text-sm font-medium text-yellow-600 dark:text-yellow-400 whitespace-nowrap">منتشر نشده</span>
            <button
              v-if="answer.can?.publish"
              @click="publishAnswer(answer)"
              :disabled="isPublishingAnswer === answer.id"
              class="text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 disabled:opacity-50 whitespace-nowrap"
            >
              {{ isPublishingAnswer === answer.id ? 'در حال انتشار...' : 'انتشار' }}
            </button>
            <button
              v-if="canUpdate(answer)"
              @click="startEdit(answer)"
              class="text-sm text-blue-600 hover:text-blue-800 whitespace-nowrap"
            >
              ویرایش
            </button>
            <button
              v-if="canDelete(answer)"
              @click="deleteAnswerAction(answer)"
              :disabled="isDeletingAnswer === answer.id"
              class="text-sm text-red-600 hover:text-red-800 whitespace-nowrap"
            >
              {{ isDeletingAnswer === answer.id ? 'در حال حذف...' : 'حذف' }}
            </button>
            <div
              v-if="answer.can?.toggle_correctness"
              class="flex items-center gap-2 whitespace-nowrap"
            >
              <input
                type="checkbox"
                :id="`correctness-${answer.id}`"
                :checked="answer.is_correct"
                @change="toggleAnswerCorrectness(answer, $event)"
                :disabled="isTogglingCorrectness === answer.id"
                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded transition-colors disabled:opacity-50"
              />
              <label
                :for="`correctness-${answer.id}`"
                :class="[
                  'text-sm cursor-pointer transition-colors select-none',
                  answer.is_correct
                    ? 'text-green-600 dark:text-green-400 font-medium'
                    : 'text-gray-600 dark:text-gray-400',
                  isTogglingCorrectness === answer.id && 'opacity-50'
                ]"
              >
                {{ isTogglingCorrectness === answer.id
                  ? 'در حال تغییر...'
                  : 'پاسخ صحیح'
                }}
              </label>
            </div>
          </div>

          <!-- Voting Section -->
          <div class="flex justify-start sm:justify-end">
            <VoteButtons
              resource-type="answer"
              :resource-id="answer.id"
              :initial-upvotes="answer.votes?.upvotes || 0"
              :initial-downvotes="answer.votes?.downvotes || 0"
              :initial-user-vote="answer.votes?.user_vote"
              @vote-changed="handleAnswerVoteChanged(answer.id, $event)"
            />
          </div>
        </div>

        <!-- Answer Comments Section -->
        <div class="px-2 sm:px-4 pb-4">
          <CommentsSection
            :answer-id="answer.id"
            parent-type="answer"
            @comment-added="handleAnswerCommentAdded"
            :key="`answer-comments-${answer.id}`"
          />
        </div>
      </div>
    </div>

    <!-- Show More Answers Button -->
    <div v-if="hasMoreAnswers" class="text-center mt-6">
      <button
        @click="loadMoreAnswers"
        :disabled="isLoadingMoreAnswers"
        class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ isLoadingMoreAnswers ? 'در حال بارگذاری...' : 'نمایش پاسخ‌های بیشتر' }}
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { useAnswers } from '../../composables/useAnswers'
import BaseEditor from '../ui/BaseEditor.vue'
import VoteButtons from '../ui/VoteButtons.vue'
import CommentsSection from './CommentsSection.vue'
import { BaseAvatar } from '../ui'

export default {
  name: 'AnswersSection',
  components: {
    BaseEditor,
    VoteButtons,
    CommentsSection,
    BaseAvatar
  },
  props: {
    questionId: {
      type: [String, Number],
      required: true
    },
    answers: {
      type: Array,
      default: () => []
    }
  },
  emits: ['answer-added', 'vote-changed', 'answer-correctness-changed', 'comment-added'],
  setup(props, { emit }) {
    const { isAuthenticated, user } = useAuth()
    const {
      isSubmitting: isSubmittingAnswer,
      isUpdating: isUpdatingAnswer,
      isDeleting: isDeletingAnswer,
      addAnswer,
      updateAnswer,
      deleteAnswer,
      fetchAnswers: fetchAnswersApi
    } = useAnswers()

    const newAnswer = ref('')
    const editingAnswer = ref(null)
    const editContent = ref('')
    const componentKey = ref(0)
    const isPublishingAnswer = ref(null)
    const isTogglingCorrectness = ref(null)

    // Pagination state
    const paginatedAnswers = ref([])
    const answersPagination = ref(null)
    const currentAnswersPage = ref(1)
    const isLoadingMoreAnswers = ref(false)
    const usePagination = ref(false)

    // Computed properties
    const hasMoreAnswers = computed(() => {
      return usePagination.value &&
             answersPagination.value &&
             answersPagination.value.current_page < answersPagination.value.last_page
    })

    // Use either paginated answers or props answers based on mode
    const displayAnswers = computed(() => {
      return usePagination.value ? paginatedAnswers.value : props.answers
    })

    // Sort answers by vote score and best answer
    const sortedAnswers = computed(() => {
      return [...displayAnswers.value].sort((a, b) => {
        // Best answers first
        if (a.is_best && !b.is_best) return -1
        if (!a.is_best && b.is_best) return 1

        // Correct answers next (if not best)
        if (a.is_correct && !b.is_correct) return -1
        if (!a.is_correct && b.is_correct) return 1

        // Then by vote score
        const aScore = (a.votes?.upvotes || 0) - (a.votes?.downvotes || 0)
        const bScore = (b.votes?.upvotes || 0) - (b.votes?.downvotes || 0)
        if (aScore !== bScore) return bScore - aScore

        // Finally by creation date (newer first)
        return new Date(b.created_at) - new Date(a.created_at)
      })
    })

    const canUpdate = (answer) => {
      return user.value && (user.value.id === answer.user_id || user.value.is_admin)
    }

    const canDelete = (answer) => {
      return user.value && (user.value.id === answer.user_id || user.value.is_admin)
    }

    const formatNumber = (num) => {
      return new Intl.NumberFormat('fa-IR').format(num)
    }

    const formatDate = (dateString) => {
        return new Intl.DateTimeFormat('fa-IR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(dateString))
    }

    // Initialize pagination if needed
    const initializePagination = async () => {
      if (props.answers.length === 0) {
        usePagination.value = true
        await fetchPaginatedAnswers()
      }
    }

    const fetchPaginatedAnswers = async (page = 1, append = false) => {
      const result = await fetchAnswersApi(props.questionId, page)
      if (result.success) {
        if (append) {
          paginatedAnswers.value = [...paginatedAnswers.value, ...result.data]
        } else {
          paginatedAnswers.value = result.data
        }
        answersPagination.value = result.meta
        currentAnswersPage.value = page
      }
    }

    const loadMoreAnswers = async () => {
      if (!hasMoreAnswers.value || isLoadingMoreAnswers.value) return

      isLoadingMoreAnswers.value = true
      try {
        await fetchPaginatedAnswers(currentAnswersPage.value + 1, true)
      } finally {
        isLoadingMoreAnswers.value = false
      }
    }

    const submitAnswer = async () => {
      if (!newAnswer.value.trim()) return

      const result = await addAnswer(props.questionId, newAnswer.value)

      if (result.success) {
        newAnswer.value = ''
        emit('answer-added')

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

    const startEdit = (answer) => {
      editingAnswer.value = answer.id
      editContent.value = answer.content
    }

    const cancelEdit = () => {
      editingAnswer.value = null
      editContent.value = ''
    }

        const saveEdit = async (answer) => {
            if (!editContent.value.trim()) return

            const result = await updateAnswer(answer.id, editContent.value)

            if (result.success) {
                editingAnswer.value = null
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

                emit('answer-added') // Trigger refresh to get fresh data from API
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

        const deleteAnswerAction = async (answer) => {
      const Swal = window.Swal || window.$swal;

      if (Swal) {
        const confirmResult = await Swal.fire({
          title: 'آیا مطمئن هستید؟',
          text: 'آیا مطمئن هستید که میخواهید این پاسخ را حذف کنید؟',
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
        if (!confirm('آیا از حذف این پاسخ اطمینان دارید؟')) return;
      }

      const result = await deleteAnswer(answer.id)

      if (result.success) {
        emit('answer-added') // Trigger refresh

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

    const handleAnswerVoteChanged = (answerId, voteData) => {
      // Emit an event to parent component to update the answer's vote data
      emit('vote-changed', {
        type: 'answer',
        id: answerId,
        votes: {
          upvotes: voteData.upvotes,
          downvotes: voteData.downvotes,
          user_vote: voteData.userVote
        }
      })

      // Update local answer data immediately for better UX
      const targetAnswers = usePagination.value ? paginatedAnswers.value : props.answers
      const answerIndex = targetAnswers.findIndex(answer => answer.id === answerId)
      if (answerIndex !== -1) {
        const updatedAnswer = { ...targetAnswers[answerIndex] }
        updatedAnswer.votes = {
          upvotes: voteData.upvotes,
          downvotes: voteData.downvotes,
          user_vote: voteData.userVote
        }
        if (usePagination.value) {
          paginatedAnswers.value[answerIndex] = updatedAnswer
        }
        // Since we can't mutate props directly, we rely on parent component to update
        // But we've already emitted the event above for parent to handle
      }
    }

    const handleAnswerCommentAdded = (commentData) => {
      // The CommentsSection component handles its own state updates locally
      // We don't need to refresh answers here as it would interfere with the comment state
      // Just emit to parent if needed for any other updates (like notification counts)
      emit('comment-added', commentData)
    }

        const publishAnswer = async (answer) => {
            isPublishingAnswer.value = answer.id

            try {
                const response = await window.axios.post(`/api/answers/${answer.id}/publish`)

                if (response.data.success) {
                    // Show success message
                    const Swal = window.Swal || window.$swal;
                    if (Swal) {
                        Swal.fire({
                            title: 'موفقیت!',
                            text: response.data.message || 'پاسخ با موفقیت منتشر شد',
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }

                    emit('answer-added') // Trigger refresh to get fresh data from API
                }
            } catch (error) {
                console.error('Error publishing answer:', error)

                // Show error message
                const Swal = window.Swal || window.$swal;
                if (Swal) {
                    Swal.fire({
                        title: 'خطا!',
                        text: 'خطا در انتشار پاسخ',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            } finally {
                isPublishingAnswer.value = null
            }
        }

        const toggleAnswerCorrectness = async (answer, event) => {
            isTogglingCorrectness.value = answer.id

            try {
                const response = await window.axios.post(`/api/answers/${answer.id}/toggle-correctness`)

                if (response.data.success) {
                    // Emit event to parent to update question solved status and answer locally
                    emit('answer-correctness-changed', {
                        answerId: answer.id,
                        isCorrect: response.data.is_correct,
                        message: response.data.message
                    })

                    // Show success message
                    const Swal = window.Swal || window.$swal;
                    if (Swal) {
                        Swal.fire({
                            title: 'موفقیت!',
                            text: response.data.message,
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }

                    // Don't emit 'answer-added' to avoid full refresh
                }
            } catch (error) {
                console.error('Error toggling answer correctness:', error)

                // Revert checkbox state on error
                if (event && event.target) {
                    event.target.checked = !event.target.checked;
                }

                const errorMessage = error.response?.data?.message || 'خطا در علامت‌گذاری پاسخ'

                // Show error message
                const Swal = window.Swal || window.$swal;
                if (Swal) {
                    Swal.fire({
                        title: 'خطا!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            } finally {
                isTogglingCorrectness.value = null
            }
        }

    // Initialize pagination on mount
    onMounted(() => {
      initializePagination()
    })

    // Watch for changes in answers and update component key
    // Only update component key for significant changes, not vote updates
    watch(() => props.answers, (newAnswers, oldAnswers) => {
      // If pagination mode is active, don't react to props changes
      if (usePagination.value) return

      // Don't trigger re-render for vote-only changes or if arrays have same structure
      if (newAnswers && oldAnswers && newAnswers.length === oldAnswers.length) {
        const hasNonVoteChanges = newAnswers.some((newAnswer, index) => {
          const oldAnswer = oldAnswers[index]
          if (!oldAnswer || newAnswer.id !== oldAnswer.id) return true

          // Check for changes other than votes (and comments since those are handled separately)
          const { votes: newVotes, comments: newComments, ...newRest } = newAnswer
          const { votes: oldVotes, comments: oldComments, ...oldRest } = oldAnswer

          return JSON.stringify(newRest) !== JSON.stringify(oldRest)
        })

        if (!hasNonVoteChanges) return // Don't update component key for vote-only changes
      }

      // Only update for significant changes like new answers, deleted answers, content changes
      componentKey.value = Date.now()
    }, { deep: true })

    return {
      isAuthenticated,
      newAnswer,
      isSubmittingAnswer,
      isUpdatingAnswer,
      isDeletingAnswer,
      isPublishingAnswer,
      isTogglingCorrectness,
      editingAnswer,
      editContent,
      componentKey,
      sortedAnswers,
      hasMoreAnswers,
      isLoadingMoreAnswers,
      usePagination,
      answersPagination,
      submitAnswer,
      startEdit,
      cancelEdit,
      saveEdit,
      deleteAnswerAction,
      publishAnswer,
      toggleAnswerCorrectness,
      loadMoreAnswers,
      handleAnswerVoteChanged,
      handleAnswerCommentAdded,
      canUpdate,
      canDelete,
      formatNumber,
      formatDate
    }
  }
}
</script>

<style scoped>
/* Ensure prose content wraps properly */
.prose {
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.prose img,
.prose video,
.prose iframe {
  max-width: 100%;
  height: auto;
}

.prose table {
  width: 100%;
  max-width: 100%;
  overflow-x: auto;
  display: block;
  white-space: nowrap;
}

.prose a {
  word-break: break-all;
  overflow-wrap: break-word;
}

.prose pre {
  overflow-x: auto;
  word-wrap: normal;
  white-space: pre;
}

.prose code {
  word-break: break-all;
  overflow-wrap: break-word;
}
</style>
