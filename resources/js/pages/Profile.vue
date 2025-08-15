<template>
    <ContentArea layout="centered" :show-sidebar="false">
        <!-- Main Content -->
        <template #main>
            <!-- Profile Header -->
            <BaseCard class="mb-8">
                <template #header>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">پروفایل کاربر</h1>
                </template>

                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Avatar Section -->
                        <div class="flex flex-col items-center space-y-4">
                            <BaseAvatar :src="profileData.image_url" :name="profileData.name" size="2xl"
                                :status="profileData.online ? 'online' : 'offline'"
                                class="ring-4 ring-white dark:ring-gray-800 shadow-lg" />

                            <!-- Image Upload -->
                            <div class="text-center">
                                <input ref="imageInput" type="file" accept="image/*" @change="handleImageUpload"
                                    class="hidden" />
                                <BaseButton @click="$refs.imageInput.click()" variant="outline" size="sm"
                                    :loading="uploadingImage">
                                    <template #icon>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </template>
                                    تغییر عکس پروفایل
                                </BaseButton>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    فرمت های مجاز: JPG، PNG، GIF (حداکثر 2MB)
                                </p>
                            </div>
                        </div>

                        <!-- User Info -->
                        <div class="flex-1 space-y-6">
                            <!-- Basic Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        نام کاربری
                                    </label>
                                    <BaseInput :modelValue="profileData.name" disabled
                                        class="bg-gray-50 dark:bg-gray-700 dark:text-gray-700" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        نام کاربری قابل تغییر نیست
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        ایمیل
                                    </label>
                                    <BaseInput :modelValue="profileData.email" disabled
                                        class="bg-gray-50 dark:bg-gray-700 dark:text-gray-700" type="email" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        ایمیل قابل تغییر نیست
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        شماره موبایل
                                    </label>
                                    <BaseInput :modelValue="profileData.mobile" disabled
                                        class="bg-gray-50 dark:bg-gray-700 dark:text-gray-700" type="tel" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        شماره موبایل قابل تغییر نیست
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        امتیاز
                                    </label>
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <BaseBadge variant="primary" size="lg">
                                            {{ profileData.score || 0 }}
                                        </BaseBadge>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">امتیاز</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Info -->
                            <div class="pt-6 border-t border-gray-200 dark:border-gray-600">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">اطلاعات تکمیلی</h2>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                            {{ userStats.questionsCount }}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300">سوال ارسالی</div>
                                    </div>
                                    <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                            {{ userStats.answersCount }}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300">پاسخ ارسالی</div>
                                    </div>
                                    <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                            {{ userStats.commentsCount }}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300">دیدگاه ارسالی</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </BaseCard>

            <!-- Recent Activity -->
            <BaseCard>
                <template #header>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">فعالیت های اخیر</h2>
                </template>

                <div class="p-6">
                    <div v-if="recentActivity.length > 0" class="space-y-4">
                        <div v-for="activity in recentActivity" :key="activity.id"
                            @click="handleActivityClick(activity)"
                            :title="activity.question_slug ? 'کلیک کنید تا به سوال مربوطه بروید' : ''"
                            :class="[
                                'flex items-start space-x-3 space-x-reverse p-4 bg-gray-50 dark:bg-gray-700 rounded-lg transition-all duration-200',
                                activity.question_slug ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 hover:shadow-md' : 'cursor-default'
                            ]">
                            <div class="flex-shrink-0">
                                <BaseBadge :variant="getActivityBadgeVariant(activity.type)" size="sm">
                                    {{ getActivityTypeText(activity.type) }}
                                </BaseBadge>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ activity.description }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ formatDate(activity.created_at) }}
                                </p>
                            </div>
                            <div v-if="activity.question_slug" class="flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">هیچ فعالیتی یافت نشد</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            فعالیت های شما در اینجا نمایش داده می شود
                        </p>
                    </div>
                </div>
            </BaseCard>
        </template>
    </ContentArea>

    <!-- Success/Error Messages -->
    <BaseAlert v-if="alert.show" :type="alert.type" :message="alert.message" @close="alert.show = false"
        class="fixed bottom-4 right-4 z-50 max-w-sm" />
</template>

<script>
import { ref, reactive, onMounted, defineAsyncComponent } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth.js'
import { usePageTitle } from '../composables/usePageTitle'
const BaseCard = defineAsyncComponent(() => import('../components/ui/BaseCard.vue'))
const BaseAvatar = defineAsyncComponent(() => import('../components/ui/BaseAvatar.vue'))
const BaseButton = defineAsyncComponent(() => import('../components/ui/BaseButton.vue'))
const BaseInput = defineAsyncComponent(() => import('../components/ui/BaseInput.vue'))
const BaseBadge = defineAsyncComponent(() => import('../components/ui/BaseBadge.vue'))
const BaseAlert = defineAsyncComponent(() => import('../components/ui/BaseAlert.vue'))
const ContentArea = defineAsyncComponent(() => import('../components/ContentArea.vue'))

export default {
    name: 'Profile',
    components: {
        BaseCard,
        BaseAvatar,
        BaseButton,
        BaseInput,
        BaseBadge,
        BaseAlert,
        ContentArea
    },
    setup() {
        const { user, updateUser } = useAuth()
        const { setTitle } = usePageTitle()
        const router = useRouter()

        // Set page title
        setTitle('پروفایل کاربری')

        const profileData = ref({
            name: '',
            email: '',
            mobile: '',
            image_url: '',
            score: 0,
            online: false
        })

        const userStats = ref({
            questionsCount: 0,
            answersCount: 0,
            commentsCount: 0
        })

        const recentActivity = ref([])
        const uploadingImage = ref(false)

        const alert = reactive({
            show: false,
            type: 'success',
            message: ''
        })

        const showAlert = (type, message) => {
            alert.type = type
            alert.message = message
            alert.show = true
            setTimeout(() => {
                alert.show = false
            }, 5000)
        }

        const loadUserData = () => {
            if (user.value) {
                profileData.value = {
                    name: user.value.name || '',
                    email: user.value.email || '',
                    mobile: user.value.mobile || '',
                    image_url: user.value.image_url || '',
                    score: user.value.score || 0,
                    online: user.value.online || false
                }
            }
        }

        const fetchUserProfile = async () => {
            try {
                const response = await window.$api.get('/user/profile')

                if (response?.data) {
                    const data = response.data
                    profileData.value = {
                        name: data.name || '',
                        email: data.email || '',
                        mobile: data.mobile || '',
                        image_url: data.image || '',
                        score: data.score || 0,
                        online: data.online || false
                    }
                }
            } catch (error) {
                console.error('Error fetching user profile:', error)
            }
        }

        const fetchUserStats = async () => {
            try {
                const response = await window.$api.get('/user/stats')

                if (response?.data) {
                    userStats.value = response.data
                }
            } catch (error) {
                console.error('Error fetching user stats:', error)
            }
        }

        const fetchRecentActivity = async () => {
            try {
                const response = await window.$api.get('/user/activity')

                if (response?.data) {
                    recentActivity.value = response.data
                }
            } catch (error) {
                console.error('Error fetching user activity:', error)
            }
        }

        const handleImageUpload = async (event) => {
            const file = event.target.files[0]
            if (!file) return

            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                showAlert('error', 'حجم فایل باید کمتر از 2 مگابایت باشد')
                return
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif']
            if (!allowedTypes.includes(file.type)) {
                showAlert('error', 'فرمت فایل مجاز نیست. لطفا فایل JPG، PNG یا GIF انتخاب کنید')
                return
            }

            uploadingImage.value = true

            try {
                const formData = new FormData()
                formData.append('image', file)

                const response = await window.$api.post('/user/update-image', formData, { headers: { 'Content-Type': 'multipart/form-data' } })

                if (response?.data) {
                    const data = response.data
                    profileData.value.image_url = data.image_url
                    // Also update the user in the auth store
                    updateUser({ image_url: data.image_url })
                    showAlert('success', 'عکس پروفایل با موفقیت بروزرسانی شد')
                }
            } catch (error) {
                console.error('Error uploading image:', error)
                showAlert('error', 'خطا در بروزرسانی عکس پروفایل')
            } finally {
                uploadingImage.value = false
            }
        }

        const getActivityBadgeVariant = (type) => {
            const variants = {
                question: 'primary',
                answer: 'success',
                comment: 'warning',
                vote: 'info'
            }
            return variants[type] || 'secondary'
        }

        const getActivityTypeText = (type) => {
            const types = {
                question: 'سوال',
                answer: 'پاسخ',
                comment: 'دیدگاه',
                vote: 'رای'
            }
            return types[type] || 'فعالیت'
        }

        const formatDate = (dateString) => {
            const date = new Date(dateString)
            const now = new Date()
            const diffMs = date - now
            const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24))

            // If absolute days is less than 30, show days
            if (Math.abs(diffDays) < 30) {
                return new Intl.RelativeTimeFormat('fa', { numeric: 'auto' }).format(diffDays, 'day')
            }

            // If absolute months is less than 12, show months
            const diffMonths = Math.round(diffDays / 30)
            if (Math.abs(diffMonths) < 12) {
                return new Intl.RelativeTimeFormat('fa', { numeric: 'auto' }).format(diffMonths, 'month')
            }

            // Otherwise, show years
            const diffYears = Math.round(diffMonths / 12)
            return new Intl.RelativeTimeFormat('fa', { numeric: 'auto' }).format(diffYears, 'year')
        }

        const handleActivityClick = (activity) => {
            // Only navigate if the activity has a question_slug
            if (activity.question_slug) {
                router.push({
                    name: 'QuestionShow',
                    params: { slug: activity.question_slug }
                })
            }
        }

        onMounted(() => {
            loadUserData()
            fetchUserProfile()
            fetchUserStats()
            fetchRecentActivity()
        })

        return {
            profileData,
            userStats,
            recentActivity,
            uploadingImage,
            alert,
            handleImageUpload,
            getActivityBadgeVariant,
            getActivityTypeText,
            formatDate,
            handleActivityClick,
            fetchUserProfile
        }
    }
}
</script>

<style scoped>
.min-h-screen {
    direction: rtl;
    font-family: 'Vazirmatn', 'Tahoma', sans-serif;
}
</style>
