<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
    <div v-if="isLoading" class="space-y-4">
      <div v-for="n in 4" :key="n" class="animate-pulse">
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
      </div>
    </div>

    <div v-else class="space-y-4">
      <!-- Total Questions -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-blue-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-sm text-gray-600 dark:text-gray-400">کل سوالات</span>
        </div>
        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatNumber(stats.totalQuestions) }}</span>
      </div>

      <!-- Total Answers -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-green-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
          <span class="text-sm text-gray-600 dark:text-gray-400">کل پاسخ‌ها</span>
        </div>
        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatNumber(stats.totalAnswers) }}</span>
      </div>

      <!-- Total Users -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-purple-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
          </svg>
          <span class="text-sm text-gray-600 dark:text-gray-400">کاربران</span>
        </div>
        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatNumber(stats.totalUsers) }}</span>
      </div>

      <!-- Solved Questions -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-emerald-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-sm text-gray-600 dark:text-gray-400">سوالات حل شده</span>
        </div>
        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatNumber(stats.solvedQuestions) }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatsCard',
  props: {
    stats: {
      type: Object,
      required: true,
      default: () => ({
        totalQuestions: 0,
        totalAnswers: 0,
        totalUsers: 0,
        solvedQuestions: 0
      })
    },
    isLoading: {
      type: Boolean,
      default: false
    }
  },
  setup() {
    const formatNumber = (number) => {
      return new Intl.NumberFormat('fa-IR').format(number || 0)
    }

    return {
      formatNumber
    }
  }
}
</script>
