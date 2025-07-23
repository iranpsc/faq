<template>
  <BaseCard variant="bordered" class="mb-4 hover:shadow-md transition-all duration-300 cursor-pointer" @click="$emit('click', question)">
    <div class="p-6">
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
            <span v-if="question.votes_count">{{ question.votes_count }} رای</span>
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
          <div v-if="question.can" class="flex items-center gap-1">
            <button v-if="question.can.update" @click.stop="$emit('edit', question)" class="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" title="ویرایش">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"></path></svg>
            </button>
            <button v-if="question.can.delete" @click.stop="$emit('delete', question)" class="p-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" title="حذف">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
            <button v-if="question.can.publish" @click.stop="$emit('publish', question)" class="p-1 text-green-500 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300" title="انتشار">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            </button>
            <button v-if="question.can.pin" @click.stop="$emit('pin', question)" class="p-1 text-yellow-500 hover:text-yellow-700 dark:text-yellow-400 dark:hover:text-yellow-300" title="پین کردن">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v12l-5-3-5 3V4z"></path></svg>
            </button>
          </div>
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
            {{ question.answers?.length || 0 }} پاسخ
          </span>
          <span v-if="question.comments" class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v10a2 2 0 002 2h8a2 2 0 002-2V8M9 12h6"></path>
            </svg>
            {{ question.comments.length }} نظر
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
  emits: ['click', 'edit', 'delete', 'publish', 'pin'],
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
