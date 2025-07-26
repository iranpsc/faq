<template>
  <BaseCard variant="bordered" class="mb-4 hover:shadow-md transition-all duration-300">
    <div class="p-6">
      <div class="flex gap-4">
        <!-- Question Content -->
        <div class="flex-1 cursor-pointer" @click="$emit('click', question)">
          <!-- Question Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 leading-relaxed">
                {{ question.title }}
              </h3>
              <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-2">
                  <BaseAvatar
                    :src="question.user?.image"
                    :name="question.user?.name"
                    size="xs"
                  />
                  <span>{{ question.user?.name }}</span>
                </div>
                <span>{{ formatDate(question.created_at) }}</span>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <BaseBadge
                v-if="question.category"
                :variant="getCategoryVariant(question.category.id)"
                size="sm"
              >
                {{ question.category.name }}
              </BaseBadge>
            </div>
          </div>

          <!-- Question Content Preview -->
          <div class="text-gray-600 dark:text-gray-400 mb-4 text-sm leading-relaxed" v-html="getContentPreview(question.content)"></div>

          <!-- Tags -->
          <div v-if="question.tags && question.tags.length > 0" class="flex flex-wrap gap-2 mb-4">
            <BaseBadge
              v-for="tag in question.tags.slice(0, 5)"
              :key="tag.id"
              variant="secondary"
              size="xs"
              class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
            >
              {{ tag.name }}
            </BaseBadge>
            <BaseBadge
              v-if="question.tags.length > 5"
              variant="secondary"
              size="xs"
              class="bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400"
            >
              +{{ question.tags.length - 5 }}
            </BaseBadge>
          </div>

          <!-- Question Stats -->
          <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex items-center gap-4">
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                {{ question?.answers_count || 0 }} پاسخ
              </span>
              <span v-if="question.comments" class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v10a2 2 0 002 2h8a2 2 0 002-2V8M9 12h6"></path>
                </svg>
                {{ question.comments.length }} نظر
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                {{ totalVotes }} رای
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                {{ question.views }} بازدید
              </span>
            </div>
            <div class="text-xs">
              آخرین بروزرسانی: {{ formatTimeAgo(question.updated_at || question.created_at) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </BaseCard>
</template>

<script>
import { BaseCard, BaseBadge, BaseAvatar } from './ui'

export default {
  name: 'QuestionCard',
  components: {
    BaseCard,
    BaseBadge,
    BaseAvatar
  },
  props: {
    question: {
      type: Object,
      required: true
    }
  },
  emits: ['click'],
  computed: {
    totalVotes() {
      if (!this.question.votes) return 0
      const upvotes = Array.isArray(this.question.votes.upvotes)
        ? this.question.votes.upvotes.length
        : (this.question.votes.upvotes || 0)
      const downvotes = Array.isArray(this.question.votes.downvotes)
        ? this.question.votes.downvotes.length
        : (this.question.votes.downvotes || 0)
      return upvotes - downvotes
    }
  },
  methods: {
    formatDate(dateString) {
      const date = new Date(dateString)
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date)
    },
    formatTimeAgo(dateString) {
      const date = new Date(dateString)
      const now = new Date()
      const diffInMinutes = Math.floor((now - date) / 60000)

      if (diffInMinutes < 1) return 'همین الان'
      if (diffInMinutes < 60) return `${diffInMinutes} دقیقه پیش`

      const diffInHours = Math.floor(diffInMinutes / 60)
      if (diffInHours < 24) return `${diffInHours} ساعت پیش`

      const diffInDays = Math.floor(diffInHours / 24)
      if (diffInDays < 30) return `${diffInDays} روز پیش`

      return this.formatDate(dateString)
    },
    getContentPreview(content) {
      if (!content) return ''
      // Remove HTML tags and get first 200 characters
      const text = content.replace(/<[^>]*>/g, '')
      return text.length > 200 ? text.substring(0, 200) + '...' : text
    },
    getCategoryVariant(categoryId) {
      // Rotate through different variants based on category ID
      const variants = ['primary', 'success', 'warning', 'info', 'secondary']
      return variants[categoryId % variants.length]
    }
  }
}
</script>

<style scoped>
.question-card {
  transition: all 0.2s ease-in-out;
}

.question-card:hover {
  transform: translateY(-1px);
}

/* Ensure content preview looks good */
:deep(.content-preview) {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
