<template>
  <div class="mt-8">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
      پاسخ‌ها ({{ usePagination ? (answersPagination?.total || sortedAnswers.length) : sortedAnswers.length }})
    </h3>
    <!-- Filters Dropdown -->
    <div class="mb-6 relative" ref="filterWrapper">
      <button
        @click="toggleFilterDropdown"
        class="flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded text-sm text-gray-700 dark:text-gray-200"
      >
        <span>مرتب سازی بر اساس:</span>
        <span class="font-medium text-blue-600 dark:text-blue-400">{{ currentFilterLabel }}</span>
        <svg :class="['w-4 h-4 transition-transform', showFilters ? 'rotate-180' : '']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      <transition name="fade">
        <div
          v-if="showFilters"
          class="absolute z-30 mt-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded shadow-lg overflow-hidden"
        >
          <ul class="py-1 text-sm">
            <li
              v-for="option in filterOptions"
              :key="option.value"
            >
              <button
                @click="selectFilter(option.value)"
                :class="[
                  'w-full text-right px-4 py-2 flex items-center justify-between gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors',
                  selectedFilter === option.value ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-medium' : 'text-gray-700 dark:text-gray-200'
                ]"
              >
                <span>{{ option.label }}</span>
                <span v-if="selectedFilter === option.value" class="text-xs">✓</span>
              </button>
            </li>
            <li v-if="showClearFilter">
              <button
                @click="selectFilter(defaultFilter)"
                class="w-full text-right px-4 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs"
              >
                حذف فیلتر (بازنشانی)
              </button>
            </li>
          </ul>
        </div>
      </transition>
    </div>

    <!-- Add Answer Section -->
    <div class="mb-8">
      <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
        پاسخ خود را ثبت کنید
      </h4>
      <div v-if="isAuthenticated">
        <button
          @click="toggleAnswerForm"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          {{ showAnswerForm ? 'بستن فرم پاسخ' : ' پاسخ خود را بنویسید' }}
        </button>
        <transition name="fade">
          <div v-if="showAnswerForm" class="mt-4">
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
        </transition>
      </div>
      <div v-else class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 text-center">
        <p class="text-gray-600 dark:text-gray-400">
          برای ثبت پاسخ، لطفا وارد حساب کاربری خود شوید.
        </p>
      </div>
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
            <router-link
              v-if="answer.user"
              :to="`/authors/${answer.user.id}`"
              class="flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded group"
              :title="`نمایش پروفایل ${answer.user?.name || ''}`"
            >
              <BaseAvatar
                :src="answer.user?.image_url"
                :name="answer.user?.name"
                :score="answer.user?.score"
                size="md"
                class="transition-transform group-hover:scale-105"
              />
            </router-link>
            <div class="flex-1 min-w-0">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                  <router-link
                    v-if="answer.user"
                    :to="`/authors/${answer.user.id}`"
                    class="font-medium text-gray-900 dark:text-gray-100 truncate hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                  >{{ answer.user?.name }}</router-link>
                  <span v-else class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ answer.user?.name }}</span>
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
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
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
    const { isAuthenticated } = useAuth()
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

    // Filtering state
    const defaultFilter = 'newest'
    const selectedFilter = ref(defaultFilter)
    const filterOptions = [
      { value: 'newest', label: 'جدیدترین' },
      { value: 'oldest', label: 'قدیمی‌ترین' },
      { value: 'votes', label: 'بیشترین رای' },
      { value: 'comments', label: 'بیشترین نظر' },
      { value: 'correct', label: 'پاسخ‌های صحیح' }
    ]
    const showClearFilter = computed(() => selectedFilter.value !== defaultFilter)
    const showFilters = ref(false)
    const filterWrapper = ref(null)
    const currentFilterLabel = computed(() => filterOptions.find(o => o.value === selectedFilter.value)?.label || 'جدیدترین')

    // Add Answer Form state
    const showAnswerForm = ref(false)

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
      let list = [...displayAnswers.value]

      // Filter for correct answers only
      if (selectedFilter.value === 'correct') {
        list = list.filter(a => a.is_correct)
      }

      switch (selectedFilter.value) {
        case 'votes':
          list.sort((a, b) => {
            const aScore = (a.votes?.upvotes || 0) - (a.votes?.downvotes || 0)
            const bScore = (b.votes?.upvotes || 0) - (b.votes?.downvotes || 0)
            if (bScore !== aScore) return bScore - aScore
            return new Date(b.created_at) - new Date(a.created_at)
          })
          break
        case 'comments':
          list.sort((a, b) => {
            const aComments = (a.comments ? a.comments.length : (a.comments_count || 0))
            const bComments = (b.comments ? b.comments.length : (b.comments_count || 0))
            if (bComments !== aComments) return bComments - aComments
            return new Date(b.created_at) - new Date(a.created_at)
          })
          break
        case 'oldest':
          list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
          break
        case 'newest':
        default:
          list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
          break
      }

      return list
    })

    const canUpdate = (answer) => {
      return !!(answer?.can?.update)
    }

    const canDelete = (answer) => {
      return !!(answer?.can?.delete)
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
      const result = await fetchAnswersApi(props.questionId, page, selectedFilter.value)
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

    const changeFilter = async (filter) => {
      if (selectedFilter.value === filter) return
      selectedFilter.value = filter
      if (usePagination.value) {
        await fetchPaginatedAnswers(1, false)
      }
    }

    const selectFilter = async (value) => {
      await changeFilter(value)
      showFilters.value = false
    }

    const toggleFilterDropdown = () => {
      showFilters.value = !showFilters.value
    }

    const toggleAnswerForm = () => {
      showAnswerForm.value = !showAnswerForm.value
    }

    const handleClickOutside = (e) => {
      if (!filterWrapper.value) return
      if (!filterWrapper.value.contains(e.target)) {
        showFilters.value = false
      }
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })

  // Clean up listener
  onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    const submitAnswer = async () => {
      if (!newAnswer.value.trim()) return

      const result = await addAnswer(props.questionId, newAnswer.value)

      if (result.success) {
        newAnswer.value = ''
        emit('answer-added')

        // If using internal pagination, refresh the first page to include the new answer
        if (usePagination.value) {
          await fetchPaginatedAnswers(1, false)
        }

        // No success toast for adding answer
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

                // No success toast for editing answer

                emit('answer-added') // Trigger refresh to get fresh data from API

                // If using internal pagination, refresh current page to reflect the edit
                if (usePagination.value) {
                    await fetchPaginatedAnswers(currentAnswersPage.value, false)
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

        const deleteAnswerAction = async (answer) => {
      const Swal = window.Swal || window.$swal;

      const SwalLocal = window.Swal || window.$swal
      const confirmResult = await SwalLocal.fire({
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

      const result = await deleteAnswer(answer.id)

      if (result.success) {
        emit('answer-added') // Trigger refresh

        // If using internal pagination, refetch to remove the deleted answer from the list
        if (usePagination.value) {
          const targetPage = Math.max(1, currentAnswersPage.value)
          await fetchPaginatedAnswers(targetPage, false)
        }

        // No success toast for deleting answer
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
                const response = await window.$api?.post(`/answers/${answer.id}/publish`) || await fetch(`/api/answers/${answer.id}/publish`, { method: 'POST', headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })

                // If using fetch fallback
                if (!(response.data)) {
                  const ok = response.ok
                  const json = ok ? await response.json() : { success: false }
                  if (!ok) throw new Error(json.message || 'خطا')
                  response.data = json
                }

                if (response.data.success) {
                    // Show success message
                    const Swal = window.Swal || window.$swal;
                    // No success toast for publishing answer

                    emit('answer-added') // Trigger refresh to get fresh data from API

                    // If using internal pagination, refresh list to reflect publish state
                    if (usePagination.value) {
                      await fetchPaginatedAnswers(currentAnswersPage.value, false)
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

        const toggleAnswerCorrectness = async (answer, event) => {
            isTogglingCorrectness.value = answer.id

            try {
                const response = await window.$api?.post(`/answers/${answer.id}/toggle-correctness`) || await fetch(`/api/answers/${answer.id}/toggle-correctness`, { method: 'POST', headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` } })
                if (!(response.data)) {
                  const ok = response.ok
                  const json = ok ? await response.json() : { success: false }
                  if (!ok) throw new Error(json.message || 'خطا')
                  response.data = json
                }

        if (response.data.success) {
                    // Emit event to parent to update question solved status and answer locally
                    emit('answer-correctness-changed', {
                        answerId: answer.id,
                        isCorrect: response.data.is_correct,
                        message: response.data.message
                    })

          // Update local state immediately
          answer.is_correct = response.data.is_correct
                    if (response.data.data && response.data.data.can) {
                      // Refresh permission (may become false after marking correct)
                      if (!answer.can) answer.can = {}
                      answer.can.toggle_correctness = response.data.data.can.toggle_correctness
                    }

                    // Show success message
                    const Swal = window.Swal || window.$swal;
                    // No success toast for marking correctness

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
      selectedFilter,
      filterOptions,
      showClearFilter,
      defaultFilter,
      changeFilter,
      selectFilter,
      showFilters,
      toggleFilterDropdown,
      currentFilterLabel,
      filterWrapper,
      showAnswerForm,
      toggleAnswerForm,
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