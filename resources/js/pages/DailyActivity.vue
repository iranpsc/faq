<template>
    <ContentArea layout="centered" :show-sidebar="false">
        <!-- Main Content -->
        <template #main>
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">فعالیت ها</h1>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            </div>

            <!-- Activities List -->
            <div v-else-if="activities.length > 0" class="space-y-4">
                <TransitionGroup name="list" tag="div" class="space-y-4">
                    <div
                        v-for="activity in activities"
                        :key="activity.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="flex items-start gap-4">
                            <!-- User Avatar -->
                            <BaseAvatar
                                :src="activity.user_image"
                                :name="activity.user_name"
                                size="md"
                            />

                            <!-- Activity Content -->
                            <div class="flex-1 min-w-0">
                                <!-- Activity Description -->
                                <p class="text-gray-900 dark:text-gray-100 mb-2">
                                    {{ activity.description }}
                                </p>

                                <!-- Activity Meta -->
                                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <!-- Activity Type Badge -->
                                    <BaseBadge
                                        :variant="getActivityBadgeVariant(activity.type)"
                                        size="xs"
                                    >
                                        {{ getActivityTypeLabel(activity.type) }}
                                    </BaseBadge>

                                    <!-- Correct Answer Badge -->
                                    <BaseBadge
                                        v-if="activity.type === 'answer' && activity.is_correct"
                                        variant="success"
                                        size="xs"
                                    >
                                        پاسخ صحیح
                                    </BaseBadge>

                                    <!-- Category -->
                                    <span v-if="activity.category_name" class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        {{ activity.category_name }}
                                    </span>

                                    <!-- Time -->
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ formatTime(activity.created_at) }}
                                    </span>
                                </div>

                                <!-- View Link -->
                                <div v-if="activity.url" class="mt-3">
                                    <router-link
                                        :to="activity.url"
                                        class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium transition-colors"
                                    >
                                        مشاهده
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <svg class="w-24 h-24 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">فعالیتی یافت نشد</h2>
                </div>
            </div>

            <!-- Load More Button -->
            <div v-if="activities.length > 0 && activities.length >= limit" class="text-center mt-8">
                <BaseButton
                    @click="loadMore"
                    variant="ghost"
                    size="lg"
                    :loading="loadingMore"
                >
                    بارگذاری بیشتر
                </BaseButton>
            </div>

            <!-- Error Message -->
            <BaseAlert
                v-if="error"
                variant="danger"
                class="mt-4"
                @dismiss="error = null"
            >
                {{ error }}
            </BaseAlert>
        </template>
    </ContentArea>
</template>

<script>
import { ref, onMounted, computed, defineAsyncComponent } from 'vue'
import api from '../services/api.js'
const BaseAvatar = defineAsyncComponent(() => import('../components/ui/BaseAvatar.vue'))
const BaseBadge = defineAsyncComponent(() => import('../components/ui/BaseBadge.vue'))
const BaseButton = defineAsyncComponent(() => import('../components/ui/BaseButton.vue'))
const BaseAlert = defineAsyncComponent(() => import('../components/ui/BaseAlert.vue'))
const ContentArea = defineAsyncComponent(() => import('../components/ContentArea.vue'))

export default {
    name: 'DailyActivity',
    components: {
        BaseAvatar,
        BaseBadge,
        BaseButton,
        BaseAlert,
        ContentArea
    },
    setup() {
        const activities = ref([])
        const loading = ref(false)
        const loadingMore = ref(false)
        const error = ref(null)
        const selectedDate = ref(new Date().toISOString().split('T')[0])
        const limit = ref(20)

        const fetchActivities = async (append = false) => {
            try {
                if (append) {
                    loadingMore.value = true
                } else {
                    loading.value = true
                    activities.value = []
                }

                error.value = null

                const response = await api.get('/dashboard/activity', {
                    params: {
                        date: selectedDate.value,
                        limit: limit.value
                    }
                })

                if (response.data.success) {
                    if (append) {
                        activities.value.push(...response.data.data)
                    } else {
                        activities.value = response.data.data
                    }
                } else {
                    throw new Error(response.data.message || 'خطا در دریافت فعالیت‌ها')
                }
            } catch (err) {
                console.error('Error fetching activities:', err)
                error.value = err.response?.data?.message || err.message || 'خطا در دریافت فعالیت‌ها'
            } finally {
                loading.value = false
                loadingMore.value = false
            }
        }

        const loadMore = () => {
            limit.value += 20
            fetchActivities(true)
        }

        const loadToday = () => {
            selectedDate.value = new Date().toISOString().split('T')[0]
            limit.value = 20
            fetchActivities()
        }

        const getActivityTypeLabel = (type) => {
            const labels = {
                question: 'سوال',
                answer: 'پاسخ',
                comment: 'نظر'
            }
            return labels[type] || type
        }

        const getActivityBadgeVariant = (type) => {
            const variants = {
                question: 'primary',
                answer: 'success',
                comment: 'warning'
            }
            return variants[type] || 'secondary'
        }

        const formatTime = (timestamp) => {
            const date = new Date(timestamp)
            const now = new Date()
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

            if (diffInHours < 1) {
                const diffInMinutes = Math.floor((now - date) / (1000 * 60))
                return diffInMinutes < 1 ? 'همین الان' : `${diffInMinutes} دقیقه پیش`
            } else if (diffInHours < 24) {
                return `${diffInHours} ساعت پیش`
            } else {
                return date.toLocaleDateString('fa-IR', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            }
        }

        onMounted(() => {
            fetchActivities()
        })

        return {
            activities,
            loading,
            loadingMore,
            error,
            selectedDate,
            limit,
            fetchActivities,
            loadMore,
            loadToday,
            getActivityTypeLabel,
            getActivityBadgeVariant,
            formatTime
        }
    }
}
</script>

<style scoped>
/* Transition animations */
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.list-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

/* Custom input styling */
input[type="date"] {
    color-scheme: light;
}

.dark input[type="date"] {
    color-scheme: dark;
}

/* RTL support */
.container {
    direction: rtl;
    font-family: 'Vazirmatn', 'Tahoma', sans-serif;
}

/* Hover effects */
.bg-white:hover {
    transform: translateY(-1px);
}

.dark .bg-gray-800:hover {
    transform: translateY(-1px);
}
</style>
