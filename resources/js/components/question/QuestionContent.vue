<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 sm:p-6 mb-6 w-full min-w-0 overflow-hidden">
        <!-- Top Row: User Info & Category (right), Creation Date (left) -->
        <div class="flex items-center justify-between mb-4 gap-2 min-w-0">
            <!-- Right: User Info & Category -->
            <div class="flex items-center gap-3 min-w-0 flex-shrink-0">
                <!-- User Info -->
                <div class="text-right min-w-0">
                    <div class="font-medium text-gray-900 dark:text-gray-100 text-sm truncate">{{ question.user?.name }}
                    </div>
                    <div v-if="question.user?.score" class="text-xs text-blue-600 whitespace-nowrap">امتیاز: {{
                        formatNumber(question.user.score) }}</div>
                </div>
                <BaseAvatar :src="question.user?.image_url" :name="question.user?.name" size="md" />
                <!-- Category -->
                <span v-if="question.category?.name"
                    class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-full whitespace-nowrap">
                    {{ question.category.name }}
                </span>
            </div>
            <!-- Left: Creation Date -->
            <div class="text-sm text-gray-500 dark:text-gray-400 flex-shrink-0">
                {{ formatDate(question.created_at) }}
            </div>
        </div>

        <!-- Question Title -->
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 leading-relaxed break-words">
            {{ question.title }}
        </h1>

        <!-- Bottom Row: Voting and Actions -->
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-sm text-gray-600 dark:text-gray-400">
            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-2 sm:gap-4 flex-wrap justify-start sm:justify-end">
                <!-- Views -->
                <div v-if="question.views !== undefined" class="flex items-center gap-1 whitespace-nowrap">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">بازدید</span>
                    <span>{{ formatNumber(question.views) }}</span>
                </div>

                <!-- Answers Count -->
                <div v-if="question.answers_count !== undefined" class="flex items-center gap-1 whitespace-nowrap">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">پاسخ</span>
                    <span>{{ formatNumber(question.answers_count) }}</span>
                </div>

                <!-- Report -->
                <button class="flex items-center gap-1 hover:text-red-600 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">گزارش</span>
                </button>

                <!-- Edit -->
                <button v-if="canEdit"
                    class="flex items-center gap-1 hover:text-blue-600 transition-colors whitespace-nowrap"
                    @click="$emit('edit')">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">ویرایش</span>
                </button>

                <!-- Pin Toggle -->
                <button v-if="user" @click="togglePin" :disabled="pinLoading"
                    :class="[
                        'flex items-center gap-1 transition-colors whitespace-nowrap',
                        question.is_pinned_by_user
                            ? 'text-yellow-600 hover:text-gray-500'  // Changed hover color for unpin state
                            : 'text-gray-500 hover:text-yellow-600',
                        pinLoading ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                    :title="question.is_pinned_by_user ? 'برداشتن پین' : 'پین کردن سوال'">
                    <svg v-if="!pinLoading" class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <!-- Use different icon for pinned/unpinned states -->
                        <path v-if="question.is_pinned_by_user" d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"></path>
                        <path v-else d="M4 3a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H4zm5.293 6.707a1 1 0 0 1 1.414 0l3 3a1 1 0 0 1-1.414 1.414L10 11.828l-2.293 2.293a1 1 0 0 1-1.414-1.414l3-3z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="hidden sm:inline">
                        {{ pinLoading ? 'در حال پردازش...' : (question.is_pinned_by_user ? 'برداشتن پین' : 'پین کردن') }}
                    </span>
                </button>

                <!-- Delete -->
                <button v-if="canDelete"
                    class="flex items-center gap-1 hover:text-red-600 transition-colors whitespace-nowrap"
                    @click="$emit('delete')">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">حذف</span>
                </button>

                <!-- Publish Status and Button -->
                <div v-if="!question.published" class="flex items-center gap-2">
                    <span
                        class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 text-xs rounded-full">
                        منتشر نشده
                    </span>
                    <button v-if="question.can?.publish" @click="publishQuestion" :disabled="isPublishing"
                        class="flex items-center gap-1 hover:text-green-600 transition-colors whitespace-nowrap disabled:opacity-50">
                        <svg v-if="!isPublishing" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="hidden sm:inline">{{ isPublishing ? 'در حال انتشار...' : 'انتشار' }}</span>
                    </button>
                </div>

                <!-- Solved indicator -->
                <div v-if="question.is_solved" class="flex items-center gap-1 text-green-600 whitespace-nowrap">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="hidden sm:inline">حل شده</span>
                </div>
            </div>

            <!-- Left: Voting Buttons -->
            <div class="flex items-center gap-4 min-w-0">
                <VoteButtons resource-type="question" :resource-id="question.id" :question-id="question.id"
                    :initial-upvotes="Array.isArray(question.votes?.upvotes) ? question.votes.upvotes.length : (question.votes?.upvotes || 0)"
                    :initial-downvotes="Array.isArray(question.votes?.downvotes) ? question.votes.downvotes.length : (question.votes?.downvotes || 0)"
                    :initial-user-vote="question.votes?.user_vote" @vote-changed="handleVoteChanged" />
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 overflow-hidden">
            <div class="prose dark:prose-invert max-w-none break-words" v-html="question.content"></div>
        </div>
    </div>
</template>

<script>
import { computed, ref, getCurrentInstance } from 'vue'
import { useAuth } from '../../composables/useAuth'
import VoteButtons from '../ui/VoteButtons.vue'
import { BaseAvatar } from '../ui'

export default {
    name: 'QuestionContent',
    components: {
        VoteButtons,
        BaseAvatar
    },
    props: {
        question: {
            type: Object,
            required: true
        }
    },
    emits: ['edit', 'delete', 'vote', 'vote-changed', 'question-published', 'pin-changed'],
    setup(props, { emit }) {
        const { user, can } = useAuth()
        const isPublishing = ref(false)
        const pinLoading = ref(false)

        // Get the current instance to access global properties
        const instance = getCurrentInstance()
        const $axios = instance?.appContext.config.globalProperties.$axios
        const $swal = instance?.appContext.config.globalProperties.$swal

        const canEdit = computed(() => {
            // Use the permissions from the API response
            return props.question.can?.update || false
        })

        const canDelete = computed(() => {
            // Use the permissions from the API response
            return props.question.can?.delete || false
        })

        const formatNumber = (num) => {
            if (!num || num === 0) return 0
            if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'k'
            }
            return num
        }

        const formatDate = (dateString) => {
            const date = new Date(dateString)
            return date.toLocaleDateString('fa-IR')
        }

        const handleVote = (type) => {
            emit('vote', { type, questionId: props.question.id })
        }

        const handleVoteChanged = (voteData) => {
            emit('vote-changed', voteData)
        }

        const publishQuestion = async () => {
            if (isPublishing.value) return

            try {
                isPublishing.value = true

                const response = await $axios.post(`/api/questions/${props.question.id}/publish`)

                // Update the question object
                props.question.published = true

                // Emit event to parent
                emit('question-published', props.question)

                // Show success message
                if ($swal) {
                    $swal.fire({
                        title: 'موفق!',
                        text: 'سوال با موفقیت منتشر شد.',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    })
                }

            } catch (error) {
                console.error('Error publishing question:', error)
                if ($swal) {
                    $swal.fire({
                        title: 'خطا!',
                        text: error.response?.data?.message || 'خطا در انتشار سوال',
                        icon: 'error'
                    })
                }
            } finally {
                isPublishing.value = false
            }
        }

        const togglePin = async () => {
            if (pinLoading.value || !user.value) return

            pinLoading.value = true

            try {
                const url = `/api/questions/${props.question.id}/pin`
                let response

                if (props.question.is_pinned_by_user) {
                    // Unpin the question
                    response = await $axios.delete(url)
                } else {
                    // Pin the question
                    response = await $axios.post(url)
                }

                if (response.data.success) {
                    // Update the question object
                    props.question.is_pinned_by_user = response.data.is_pinned_by_user
                    props.question.pinned_at = response.data.pinned_at

                    // Emit event to parent
                    emit('pin-changed', {
                        questionId: props.question.id,
                        isPinned: props.question.is_pinned_by_user,
                        pinnedAt: props.question.pinned_at
                    })

                    // Show success message
                    if ($swal) {
                        $swal.fire({
                            title: 'موفق!',
                            text: response.data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        })
                    }
                } else {
                    throw new Error(response.data.message || 'خطا در تغییر وضعیت پین')
                }
            } catch (error) {
                console.error('Error toggling pin:', error)

                let errorMessage = 'خطا در تغییر وضعیت پین'
                if (error.response?.data?.message) {
                    errorMessage = error.response.data.message
                } else if (error.message) {
                    errorMessage = error.message
                }

                if ($swal) {
                    $swal.fire({
                        title: 'خطا!',
                        text: errorMessage,
                        icon: 'error'
                    })
                }
            } finally {
                pinLoading.value = false
            }
        }

        return {
            user,
            canEdit,
            canDelete,
            isPublishing,
            pinLoading,
            formatNumber,
            formatDate,
            handleVote,
            handleVoteChanged,
            publishQuestion,
            togglePin
        }
    }
}
</script>

<style scoped>
.prose {
    color: inherit;
    line-height: 1.7;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.prose p {
    margin-bottom: 1rem;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
    word-wrap: break-word;
}

.prose ul,
.prose ol {
    margin-bottom: 1rem;
    padding-right: 1.5rem;
}

.prose li {
    margin-bottom: 0.25rem;
}

.prose blockquote {
    border-right: 4px solid #e5e7eb;
    padding-right: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: #6b7280;
}

.dark .prose blockquote {
    border-right-color: #4b5563;
    color: #9ca3af;
}

.prose code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
    word-break: break-all;
    overflow-wrap: break-word;
}

.dark .prose code {
    background-color: #374151;
}

.prose pre {
    background-color: #f3f4f6;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1rem 0;
    word-wrap: normal;
    white-space: pre;
}

.prose pre code {
    word-break: normal;
    overflow-wrap: normal;
}

.dark .prose pre {
    background-color: #1f2937;
}

/* Handle images and other media */
.prose img,
.prose video,
.prose iframe {
    max-width: 100%;
    height: auto;
}

/* Handle tables */
.prose table {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    display: block;
    white-space: nowrap;
}

.prose table th,
.prose table td {
    overflow-wrap: break-word;
    word-wrap: break-word;
    word-break: break-word;
}

/* Force long URLs and text to break */
.prose a {
    word-break: break-all;
    overflow-wrap: break-word;
}
</style>
