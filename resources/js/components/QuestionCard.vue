<template>
    <BaseCard variant="bordered" class="mb-4 hover:shadow-md transition-all duration-300">
        <div class="p-6">
            <!-- Section 1: Category (right), Creation Date (left) -->
            <div class="flex items-center justify-between mb-2">
                <div>
                    <BaseBadge v-if="question.category"
                        size="lg"
                        class="cursor-pointer hover:-translate-y-0.5 transition-all duration-200 px-8 py-1 border-2 border-gray-400 dark:border-gray-200">
                        {{ question.category.name }}
                    </BaseBadge>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ formatDate(question.created_at) }}
                </div>
            </div>

            <!-- Section 2: Title and Content Preview -->
            <div class="mb-4 cursor-pointer" @click="$emit('click', question)">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 leading-relaxed">
                    {{ question.title }}
                </h3>
                <div class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed content-preview"
                    v-html="getContentPreview(question.content)"></div>
            </div>

            <!-- Section 3: User Info (right), Stats (right), Publish/Unpublished (left) -->
            <div
                class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4 text-sm text-gray-500 dark:text-gray-400">
                <!-- User Info and Stats (right) -->
                <div class="flex items-center gap-6">
                    <!-- User Info -->
                    <div class="flex items-center gap-2">
                        <BaseAvatar :src="question.user?.image_url" :name="question.user?.name" size="xs" />
                        <span>{{ question.user?.name }}</span>
                    </div>
                    <!-- Stats -->
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                        {{ question?.answers_count || 0 }} پاسخ
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                        {{ question?.votes_count || 0 }} رای
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        {{ question.views }} بازدید
                    </span>
                </div>
                <!-- Publish/Unpublished (left) -->
                <div class="flex items-center">
                    <BaseBadge v-if="!question.published" variant="warning" size="sm" class="ml-2">
                        منتشر نشده
                    </BaseBadge>
                    <button v-if="question.can?.publish" @click.stop="publishQuestion"
                        class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition-colors ml-2">
                        انتشار
                    </button>
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
    emits: ['click', 'published'],
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
        },
        async publishQuestion() {
            try {
                const response = await this.$axios.post(`/api/questions/${this.question.id}/publish`)
                if (response.data.success) {
                    // Update the question object to reflect published status
                    this.question.published = true
                    this.question.published_at = new Date().toISOString()
                    // Remove the publish permission since item is now published
                    if (this.question.can) {
                        this.question.can.publish = false
                    }
                    this.$emit('published', this.question)
                    // Show success message
                    this.$swal({
                        title: 'موفق',
                        text: response.data.message || 'سوال با موفقیت منتشر شد',
                        icon: 'success',
                        confirmButtonText: 'باشه'
                    })
                }
            } catch (error) {
                console.error('Error publishing question:', error)
                this.$swal({
                    title: 'خطا',
                    text: 'خطا در انتشار سوال',
                    icon: 'error',
                    confirmButtonText: 'باشه'
                })
            }
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
