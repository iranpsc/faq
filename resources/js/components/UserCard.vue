<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center hover:shadow-md transition-all duration-300 cursor-pointer">
    <!-- User Avatar -->
    <div class="mb-4">
      <BaseAvatar
        :src="user.image_url"
        :name="user.name"
        size="xl"
        class="mx-auto"
      />
    </div>

    <!-- User Name -->
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
      {{ user.name }}
    </h3>

    <!-- User Score -->
    <div class="mb-4">
      <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
        {{ formatNumber(user.score) }}
      </span>
      <p class="text-sm text-gray-500 dark:text-gray-400">امتیاز</p>
    </div>

    <!-- Activity Stats -->
    <div class="grid grid-cols-3 gap-2 text-center border-t border-gray-200 dark:border-gray-700 pt-4">
      <div>
        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          {{ formatNumber(user.questions_count) }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">سوال</div>
      </div>
      <div>
        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          {{ formatNumber(user.answers_count) }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">پاسخ</div>
      </div>
      <div>
        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          {{ formatNumber(user.comments_count) }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">نظر</div>
      </div>
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
