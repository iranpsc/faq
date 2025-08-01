<template>
  <div class="mt-8">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
      پاسخ‌ها ({{ sortedAnswers.length }})
    </h3>

    <!-- Add Answer Form -->
    <div v-if="isAuthenticated" class="mb-8">
      <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
        پاسخ خود را ثبت کنید
      </h4>
      <form @submit.prevent="submitAnswer">
        <Editor
          api-key="2sfprbtijd268hiw733k56v9bp9bpy8jgsqet6q8z4vvirow"
          v-model="newAnswer"
          :init="{
            height: 300,
            menubar: false,
            plugins: 'lists link code help wordcount',
            toolbar:
              'undo redo | formatselect | bold italic | \
              alignleft aligncenter alignright alignjustify | \
              bullist numlist outdent indent | removeformat | help',
            directionality: 'rtl'
          }"
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
        <div class="p-4 sm:p-8">
          <div class="flex items-start gap-3 sm:gap-6 min-w-0">
            <BaseAvatar
              :src="answer.user?.image_url"
              :name="answer.user?.name"
              size="md"
              class="flex-shrink-0"
            />
            <div class="flex-1 min-w-0">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                  <span class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ answer.user?.name }}</span>
                  <span class="text-xs text-blue-600 whitespace-nowrap">امتیاز: {{ formatNumber(answer.user?.points || 0) }}</span>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ formatDate(answer.created_at) }}</span>
              </div>

              <!-- Answer Content -->
              <div v-if="editingAnswer !== answer.id" class="mt-4 sm:mt-6 overflow-hidden">
                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 break-words" v-html="answer.content"></div>
              </div>

              <!-- Edit Form -->
              <div v-else class="mt-6">
                <Editor
                  api-key="2sfprbtijd268hiw733k56v9bp9bpy8jgsqet6q8z4vvirow"
                  v-model="editContent"
                  :init="{
                    height: 200,
                    menubar: false,
                    plugins: 'lists link code help wordcount',
                    toolbar:
                      'undo redo | formatselect | bold italic | \
                      alignleft aligncenter alignright alignjustify | \
                      bullist numlist outdent indent | removeformat | help'
                  }"
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
            <span v-if="answer.is_solution || answer.is_best" class="text-sm font-medium text-green-600 dark:text-green-400 whitespace-nowrap">تایید شده</span>
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
            :key="`answer-comments-${answer.id}-${componentKey}`"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, nextTick } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { useAnswers } from '../../composables/useAnswers'
import Editor from '@tinymce/tinymce-vue'
import VoteButtons from '../ui/VoteButtons.vue'
import CommentsSection from './CommentsSection.vue'
import { BaseAvatar } from '../ui'

export default {
  name: 'AnswersSection',
  components: {
    Editor,
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
  emits: ['answer-added', 'vote-changed'],
  setup(props, { emit }) {
    const { isAuthenticated, user } = useAuth()
    const {
      isSubmitting: isSubmittingAnswer,
      isUpdating: isUpdatingAnswer,
      isDeleting: isDeletingAnswer,
      addAnswer,
      updateAnswer,
      deleteAnswer
    } = useAnswers()

    const newAnswer = ref('')
    const editingAnswer = ref(null)
    const editContent = ref('')
    const componentKey = ref(0)
    const isPublishingAnswer = ref(null)

    // Sort answers by vote score and best answer
    const sortedAnswers = computed(() => {
      return [...props.answers].sort((a, b) => {
        // Best answers first
        if (a.is_best && !b.is_best) return -1
        if (!a.is_best && b.is_best) return 1

        // Then by vote score
        const aScore = (a.votes?.upvotes || 0) - (a.votes?.downvotes || 0)
        const bScore = (b.votes?.upvotes || 0) - (b.votes?.downvotes || 0)
        return bScore - aScore
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
        // Find and update the answer in the answers array
        const answerIndex = props.answers.findIndex(a => a.id === answer.id)
        if (answerIndex !== -1) {
          props.answers[answerIndex].content = editContent.value
          props.answers[answerIndex].updated_at = result.data?.updated_at || props.answers[answerIndex].updated_at
        }

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

        emit('answer-added') // Trigger refresh
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
      console.log('Answer vote changed:', { answerId, voteData })
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
    }

    const handleAnswerCommentAdded = () => {
      // Force component re-render to show new comments
      componentKey.value = Date.now()
    }

    const publishAnswer = async (answer) => {
      isPublishingAnswer.value = answer.id

      try {
        const response = await window.axios.post(`/api/answers/${answer.id}/publish`)

        if (response.data.success) {
          // Update the answer object
          const answerIndex = props.answers.findIndex(a => a.id === answer.id)
          if (answerIndex !== -1) {
            props.answers[answerIndex].published = true
            props.answers[answerIndex].published_at = new Date().toISOString()
            // Remove publish permission
            if (props.answers[answerIndex].can) {
              props.answers[answerIndex].can.publish = false
            }
          }

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

    // Watch for changes in answers and update component key
    watch(() => props.answers, () => {
      componentKey.value = Date.now()
    }, { deep: true })

    return {
      isAuthenticated,
      newAnswer,
      isSubmittingAnswer,
      isUpdatingAnswer,
      isDeletingAnswer,
      isPublishingAnswer,
      editingAnswer,
      editContent,
      componentKey,
      sortedAnswers,
      submitAnswer,
      startEdit,
      cancelEdit,
      saveEdit,
      deleteAnswerAction,
      publishAnswer,
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
