<template>
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center hover:shadow-md transition-all duration-300 cursor-pointer flex flex-col">
        <!-- User Avatar -->
        <div class="mb-3">
            <BaseAvatar :src="user.image_url" :name="user.name" size="xl" class="mx-auto" />
        </div>

        <!-- User Name -->
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ user.name }}
        </h3>

        <!-- User Score Badge -->
        <div class="flex justify-center mb-4">
            <span
                class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-bold border border-gray-300 dark:border-gray-500">
                <span class="ml-2">{{ formatNumber(user.score) }}</span>
                <span class="ml-2 text-xs font-normal">امتیاز</span>
            </span>
        </div>

        <!-- Answers Count -->
        <div class="mb-2">
            <svg class="inline w-4 h-4 text-blue-500 dark:text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M2 5a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H7l-4 3v-3a2 2 0 01-1-1.732V5zm2-1a1 1 0 00-1 1v8c0 .265.105.52.293.707L5 16.414V15a1 1 0 011-1h9a1 1 0 001-1V5a1 1 0 00-1-1H4z" />
            </svg>
            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">پاسخ داده شده:</span>
            <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ formatNumber(user.answers_count) }}
            </span>
        </div>

        <!-- Comments Count -->
        <!-- Comments Count -->
        <div class="mb-4">
            <svg class="inline w-4 h-4 text-green-500 dark:text-yellow-400 mr-1" fill="currentColor"
                viewBox="0 0 20 20">
                <path
                    d="M18 10c0 3.866-3.582 7-8 7a8.96 8.96 0 01-3.468-.664l-3.17.634a1 1 0 01-1.18-1.18l.634-3.17A8.96 8.96 0 012 10c0-3.866 3.582-7 8-7s8 3.134 8 7zm-8-5C6.134 5 3 7.239 3 10c0 1.13.47 2.19 1.32 3.07a1 1 0 01.26.95l-.37 1.85 1.85-.37a1 1 0 01.95.26A6.96 6.96 0 0010 15c3.866 0 7-2.239 7-5s-3.134-5-7-5z" />
            </svg>
            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">نظر داده شده:</span>
            <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ formatNumber(user.comments_count) }}
            </span>
        </div>

        <!-- Divider -->
        <hr class="border-t border-gray-200 dark:border-gray-700 my-4">

        <!-- Footer Chat Button -->
        <div class="mt-auto">
            <button
                class="flex w-full justify-between items-center px-4 py-3 rounded-lg bg-blue-200 dark:bg-gray-900 text-yellow-400 font-bold transition-colors focus:outline-none gap-2">
                <div>
                    <span class="mr-2 text-blue-600 dark:text-yellow-400">گفتگو</span>
                </div>
                <div>
                    <svg class="w-5 h-5 text-blue-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M18 10c0 3.866-3.582 7-8 7a8.96 8.96 0 01-3.468-.664l-3.17.634a1 1 0 01-1.18-1.18l.634-3.17A8.96 8.96 0 012 10c0-3.866 3.582-7 8-7s8 3.134 8 7zm-8-5C6.134 5 3 7.239 3 10c0 1.13.47 2.19 1.32 3.07a1 1 0 01.26.95l-.37 1.85 1.85-.37a1 1 0 01.95.26A6.96 6.96 0 0010 15c3.866 0 7-2.239 7-5s-3.134-5-7-5z" />
                    </svg>
                </div>
            </button>
        </div>
    </div>
</template>

<script>
import { BaseAvatar } from './ui'

export default {
    name: 'UserCard',
    components: {
        BaseAvatar
    },
    props: {
        user: {
            type: Object,
            required: true,
            validator: (user) => {
                return user && typeof user === 'object' && user.id && user.name
            }
        }
    },
    methods: {
        formatNumber(number) {
            if (!number && number !== 0) return '0'

            const num = parseInt(number)
            if (num >= 1000000) {
                return Math.floor(num / 1000000) + 'M'
            } else if (num >= 1000) {
                return Math.floor(num / 1000) + 'K'
            }
            return num.toString()
        }
    }
}
</script>

<style scoped>
/* Additional styles for the card hover effect */
.bg-white:hover {
    transform: translateY(-2px);
}

.dark .bg-gray-800:hover {
    transform: translateY(-2px);
}
</style>
