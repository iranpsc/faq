<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-all duration-300
               cursor-pointer transform hover:-translate-y-1 border border-gray-200 dark:border-gray-700"
        @click="$emit('click', author)">
        <!-- Author Header -->
        <div class="p-6">
            <div class="flex flex-col items-center space-y-2">
                <!-- Avatar -->
                <div class="relative">
                    <img :src="authorImage" :alt="author.name" loading="lazy" decoding="async"
                        class="w-16 h-16 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600"
                        @error="handleImageError"
                        :style="{ 'content-visibility': 'auto' }">
                    <!-- Level Badge -->
                    <div class="absolute -bottom-1 -left-1 bg-blue-500 text-white text-xs px-2 py-1 rounded-full
                                font-semibold shadow-lg border-2 border-white dark:border-gray-800">
                        {{ author.level }}
                    </div>
                </div>

                <!-- Author Name -->
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate mb-1 text-center">
                    {{ author.name }}
                </h3>

                <!-- Score -->
                <div class="flex items-center">
                    امتیاز:
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ author.score }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="px-6 pb-4">
            <div class="grid grid-cols-3 gap-4 text-center">
                <!-- Questions Count -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ formatNumber(author.questions_count) }}
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        سوالات
                    </div>
                </div>

                <!-- Answers Count -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ formatNumber(author.answers_count) }}
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        پاسخ‌ها
                    </div>
                </div>

                <!-- Comments Count -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ formatNumber(author.comments_count) }}
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        نظرات
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Questions Preview -->
        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                آخرین سوالات:
            </h4>
            <div class="space-y-2" v-if="author.recent_questions && author.recent_questions.length">
                <div v-for="question in author.recent_questions.slice(0, 2)" :key="question.id" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400
                           transition-colors duration-200 cursor-pointer truncate"
                    @click.stop="handleQuestionClick(question)" :title="question.title">
                    {{ question.title }}
                </div>
                <div v-if="author.recent_questions.length > 2" class="text-xs text-gray-500 dark:text-gray-500">
                    و {{ author.recent_questions.length - 2 }} سوال دیگر...
                </div>
            </div>
            <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                این کاربر هنوز سوالی نپرسیده است.
            </div>
        </div>
    </div>
</template>

<script>
import { computed, ref } from 'vue'

export default {
    name: 'AuthorCard',
    props: {
        author: {
            type: Object,
            required: true
        }
    },
    emits: ['click'],
    setup(props, { emit }) {
        const imageError = ref(false)

        const authorImage = computed(() => {
            if (imageError.value || !props.author.image_url) {
                return `https://ui-avatars.com/api/?name=${encodeURIComponent(props.author.name)}&size=64&background=3b82f6&color=fff&bold=true`
            }
            return props.author.image_url
        })

        const roleClasses = computed(() => {
            const baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'

            switch (props.author.role) {
                case 'admin':
                    return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                case 'moderator':
                    return 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
                default:
                    return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
            }
        })

        const roleText = computed(() => {
            switch (props.author.role) {
                case 'admin':
                    return 'مدیر'
                case 'moderator':
                    return 'ناظر'
                default:
                    return 'کاربر'
            }
        })

        const handleImageError = () => {
            imageError.value = true
        }

        const handleQuestionClick = (question) => {
            // Navigate to question detail page
            // You can emit this event or handle routing here
            console.log('Question clicked:', question)
        }

        const formatNumber = (num) => {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M'
            } else if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K'
            }
            return num.toString()
        }

        const formatDate = (dateString) => {
            const date = new Date(dateString)
            const now = new Date()
            const diffTime = Math.abs(now - date)
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

            if (diffDays < 30) {
                return `${diffDays} روز پیش`
            } else if (diffDays < 365) {
                const months = Math.floor(diffDays / 30)
                return `${months} ماه پیش`
            } else {
                const years = Math.floor(diffDays / 365)
                return `${years} سال پیش`
            }
        }

        return {
            authorImage,
            roleClasses,
            roleText,
            handleImageError,
            handleQuestionClick,
            formatNumber,
            formatDate
        }
    }
}
</script>

<style scoped>
/* Card hover effects */
.cursor-pointer:hover {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.dark .cursor-pointer:hover {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Truncate text properly */
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
