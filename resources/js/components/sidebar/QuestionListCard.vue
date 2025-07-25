<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
      {{ title }}
    </h3>

    <div v-if="isLoading" class="space-y-3">
      <div v-for="n in 5" :key="n" class="animate-pulse">
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full mb-2"></div>
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
      </div>
    </div>

    <div v-else-if="questions.length > 0" class="space-y-3">
      <!-- Scrollable container for questions -->
      <div class="max-h-80 overflow-y-auto question-list-scroll space-y-3">
        <div
          v-for="question in questions"
          :key="question.id"
          @click="$emit('question-click', question)"
          class="group cursor-pointer p-3 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
        >
          <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 line-clamp-2 mb-1">
            {{ question.title }}
          </h4>
          <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>{{ formatDate(question.created_at) }}</span>
            <div class="flex items-center gap-2">
              <!-- Answers count -->
              <span v-if="question.answers_count" class="flex items-center">
                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                {{ question.answers_count }}
              </span>
              <!-- Views count -->
              <span v-if="question.views_count" class="flex items-center">
                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                {{ question.views_count }}
              </span>
              <!-- Votes count -->
              <span v-if="question.votes_count" class="flex items-center">
                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                {{ question.votes_count }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
      </svg>
      <p class="text-sm text-gray-500 dark:text-gray-400">موردی یافت نشد</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QuestionListCard',
  emits: ['question-click'],
  props: {
    title: {
      type: String,
      required: true
    },
    questions: {
      type: Array,
      default: () => []
    },
    isLoading: {
      type: Boolean,
      default: false
    }
  },
  setup() {
    const formatDate = (dateString) => {
      const date = new Date(dateString)
      const now = new Date()
      const diffTime = Math.abs(now - date)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

      if (diffDays === 1) {
        return 'امروز'
      } else if (diffDays === 2) {
        return 'دیروز'
      } else if (diffDays <= 7) {
        return `${diffDays} روز پیش`
      } else {
        return new Intl.DateTimeFormat('fa-IR', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        }).format(date)
      }
    }

    return {
      formatDate
    }
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom scrollbar for question list */
.question-list-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.question-list-scroll::-webkit-scrollbar {
  width: 4px;
}

.question-list-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.question-list-scroll::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 2px;
}

.question-list-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.8);
}

.dark .question-list-scroll {
  scrollbar-color: rgba(75, 85, 99, 0.5) transparent;
}

.dark .question-list-scroll::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark .question-list-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.8);
}
</style>
