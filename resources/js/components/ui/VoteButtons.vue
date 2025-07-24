<template>
  <div class="vote-buttons flex items-center space-x-4 space-x-reverse">
    <!-- Upvote Button -->
    <button
      @click="handleVote('up')"
      :disabled="isVoting"
      :class="[
        'vote-btn upvote-btn',
        'flex items-center space-x-1 space-x-reverse',
        size === 'small' ? 'px-2 py-1' : 'px-3 py-1.5',
        'rounded-lg transition-all duration-200',
        'hover:scale-105 active:scale-95',
        {
          'bg-green-500 text-white shadow-lg': userVote === 'up',
          'text-gray-600 hover:text-green-600 hover:bg-green-50': userVote !== 'up' && !isVoting,
          'opacity-50 cursor-not-allowed': isVoting,
          'cursor-not-allowed opacity-60': !isAuthenticated
        }
      ]"
      :title="userVote === 'up' ? 'حذف رای مثبت' : 'رای مثبت'"
    >
      <!-- Upvote Icon (Thumbs Up) -->
      <svg :class="size === 'small' ? 'w-3 h-3' : 'w-4 h-4'" fill="currentColor" viewBox="0 0 20 20">
        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
      </svg>
      <span :class="size === 'small' ? 'text-xs font-medium' : 'text-sm font-medium'">{{ upvotes }}</span>
    </button>

    <!-- Downvote Button -->
    <button
      @click="handleVote('down')"
      :disabled="isVoting"
      :class="[
        'vote-btn downvote-btn',
        'flex items-center space-x-1 space-x-reverse',
        size === 'small' ? 'px-2 py-1' : 'px-3 py-1.5',
        'rounded-lg transition-all duration-200',
        'hover:scale-105 active:scale-95',
        {
          'bg-red-500 text-white shadow-lg': userVote === 'down',
          'text-gray-600 hover:text-red-600 hover:bg-red-50': userVote !== 'down' && !isVoting,
          'opacity-50 cursor-not-allowed': isVoting,
          'cursor-not-allowed opacity-60': !isAuthenticated
        }
      ]"
      :title="userVote === 'down' ? 'حذف رای منفی' : 'رای منفی'"
    >
      <!-- Downvote Icon (Thumbs Down) -->
      <svg :class="size === 'small' ? 'w-3 h-3' : 'w-4 h-4'" fill="currentColor" viewBox="0 0 20 20">
        <path d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.2 6A2 2 0 004.44 12H8v4a2 2 0 002 2 1 1 0 001-1v-.667a4 4 0 01.8-2.4l1.4-1.866a4 4 0 00.8-2.4z" />
      </svg>
      <span :class="size === 'small' ? 'text-xs font-medium' : 'text-sm font-medium'">{{ downvotes }}</span>
    </button>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { useQuestions } from '../../composables/useQuestions'
import { useComments } from '../../composables/useComments'
import { useAnswers } from '../../composables/useAnswers'
import { useAuth } from '../../composables/useAuth'

export default {
  name: 'VoteButtons',
  props: {
    // The type of resource being voted on
    resourceType: {
      type: String,
      required: true,
      validator: (value) => ['question', 'comment', 'answer'].includes(value)
    },
    // The ID of the resource
    resourceId: {
      type: [Number, String],
      required: true
    },
    // For legacy support - questionId prop
    questionId: {
      type: Number,
      default: null
    },
    // Initial vote counts and user vote
    initialUpvotes: {
      type: [Number, Array],
      default: 0
    },
    initialDownvotes: {
      type: [Number, Array],
      default: 0
    },
    initialUserVote: {
      type: String,
      default: null,
      validator: (value) => value === null || ['up', 'down'].includes(value)
    },
    // Button size
    size: {
      type: String,
      default: 'medium',
      validator: (value) => ['small', 'medium'].includes(value)
    }
  },
  emits: ['vote-changed'],
  setup(props, { emit }) {
    const { voteQuestion } = useQuestions()
    const { voteComment } = useComments()
    const { voteAnswer } = useAnswers()
    const { isAuthenticated } = useAuth()

    const isVoting = ref(false)

    // Handle different data types from API (count or array)
    const getVoteCount = (votes) => {
      if (Array.isArray(votes)) {
        return votes.length
      }
      return typeof votes === 'number' ? votes : 0
    }

    const upvotes = ref(getVoteCount(props.initialUpvotes))
    const downvotes = ref(getVoteCount(props.initialDownvotes))
    const userVote = ref(props.initialUserVote)

    // Watch for prop changes and update local state
    watch(() => props.initialUpvotes, (newValue) => {
      upvotes.value = getVoteCount(newValue)
    })

    watch(() => props.initialDownvotes, (newValue) => {
      downvotes.value = getVoteCount(newValue)
    })

    watch(() => props.initialUserVote, (newValue) => {
      userVote.value = newValue
    })

    // Get the actual resource ID to use
    const getResourceId = () => {
      // For legacy support, use questionId if provided and resourceType is question
      if (props.questionId && props.resourceType === 'question') {
        return props.questionId
      }
      return props.resourceId
    }

    const showLoginAlert = () => {
      const Swal = window.Swal || window.$swal;

      if (Swal) {
        Swal.fire({
          title: 'ورود به حساب کاربری',
          text: 'برای رای دادن باید وارد حساب کاربری خود شوید.',
          icon: 'info',
          showCancelButton: true,
          confirmButtonText: 'ورود',
          cancelButtonText: 'انصراف',
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
          },
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = '/auth/redirect'
          }
        })
      } else {
        const shouldLogin = confirm('برای رای دادن باید وارد حساب کاربری خود شوید.\n\nآیا می‌خواهید به صفحه ورود بروید؟')
        if (shouldLogin) {
          window.location.href = '/auth/redirect'
        }
      }
    }

    const showSuccessAlert = (message) => {
      const Swal = window.Swal || window.$swal;

      if (Swal) {
        Swal.fire({
          title: 'موفق!',
          text: message,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end'
        })
      }
    }

    const showErrorAlert = (message) => {
      const Swal = window.Swal || window.$swal;

      if (Swal) {
        Swal.fire({
          title: 'خطا!',
          text: message,
          icon: 'error',
          confirmButtonText: 'باشه',
          confirmButtonColor: '#d33'
        })
      } else {
        alert(message)
      }
    }

    // Get the appropriate voting function based on resource type
    const getVotingFunction = () => {
      switch (props.resourceType) {
        case 'question':
          return voteQuestion
        case 'comment':
          return voteComment
        case 'answer':
          return voteAnswer
        default:
          return null
      }
    }

    const handleVote = async (voteType) => {
      if (isVoting.value) return

      // Check if user is authenticated
      if (!isAuthenticated.value) {
        showLoginAlert()
        return
      }

      const votingFunction = getVotingFunction()
      if (!votingFunction) {
        showErrorAlert('عملکرد رای‌دهی برای این نوع منبع پیاده‌سازی نشده است')
        return
      }

      isVoting.value = true

      try {
        const resourceId = getResourceId()
        const result = await votingFunction(resourceId, voteType)

        if (result.success) {
          // Update state from server response
          if (result.data) {
            // Handle different response structures
            const voteData = result.data.votes || result.data
            upvotes.value = voteData.upvotes || 0
            downvotes.value = voteData.downvotes || 0
            userVote.value = voteData.user_vote
          } else {
            // Fallback to local calculation (for backward compatibility)
            const previousVote = userVote.value

            if (previousVote === voteType) {
              // User is removing their vote
              userVote.value = null
              if (voteType === 'up') {
                upvotes.value = Math.max(0, upvotes.value - 1)
              } else {
                downvotes.value = Math.max(0, downvotes.value - 1)
              }
            } else if (previousVote === null) {
              // User is adding a new vote
              userVote.value = voteType
              if (voteType === 'up') {
                upvotes.value += 1
              } else {
                downvotes.value += 1
              }
            } else {
              // User is changing their vote
              userVote.value = voteType
              if (voteType === 'up') {
                upvotes.value += 1
                downvotes.value = Math.max(0, downvotes.value - 1)
              } else {
                downvotes.value += 1
                upvotes.value = Math.max(0, upvotes.value - 1)
              }
            }
          }

          // Emit event to parent component
          emit('vote-changed', {
            resourceType: props.resourceType,
            resourceId: getResourceId(),
            upvotes: upvotes.value,
            downvotes: downvotes.value,
            userVote: userVote.value,
            message: result.message || 'رای شما ثبت شد'
          })

          // Show success message
          const successMessage = userVote.value
            ? (userVote.value === 'up' ? 'رای مثبت شما ثبت شد' : 'رای منفی شما ثبت شد')
            : 'رای شما حذف شد'

          showSuccessAlert(successMessage)
        } else {
          // Handle error - including authentication errors from API
          if (result.error === 'authentication') {
            showLoginAlert()
          } else {
            console.error('Voting error:', result.error)
            showErrorAlert(result.message || 'خطا در ثبت رای. لطفا دوباره تلاش کنید.')
          }
        }
      } catch (error) {
        console.error('Vote error:', error)

        // Check if it's a 401 authentication error
        if (error.response && error.response.status === 401) {
          showLoginAlert()
        } else {
          showErrorAlert('خطا در ثبت رای. لطفا دوباره تلاش کنید.')
        }
      } finally {
        isVoting.value = false
      }
    }

    return {
      isVoting,
      upvotes,
      downvotes,
      userVote,
      isAuthenticated,
      handleVote
    }
  }
}
</script>

<style scoped>
.vote-buttons {
  user-select: none;
}

.vote-btn {
  transition: all 0.2s ease-in-out;
  position: relative;
}

.vote-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.vote-btn:active:not(:disabled) {
  transform: translateY(0);
}

.vote-btn:disabled {
  transform: none;
  box-shadow: none;
}

/* Active vote styles */
.vote-btn.bg-green-500 {
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.vote-btn.bg-red-500 {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Loading spinner for voting state */
.vote-btn:disabled::after {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  opacity: 0.6;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Hide the original icon when loading */
.vote-btn:disabled svg {
  opacity: 0;
}

/* Score animation */
.vote-score span {
  transition: all 0.3s ease;
}

/* Pulse animation for score changes */
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.vote-score.updated span {
  animation: pulse 0.3s ease;
}
</style>
