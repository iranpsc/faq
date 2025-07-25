<template>
  <aside class="hidden md:block w-full h-full overflow-y-auto sidebar-scrollbar">
    <div class="space-y-6">
      <!-- Statistics Card -->
      <StatsCard
        :stats="stats"
        :is-loading="isDashboardLoading"
      />

      <!-- Recommended Questions -->
      <QuestionListCard
        title="مطالب پیشنهادی"
        :questions="recommendedQuestions"
        :is-loading="isRecommendedLoading"
        @question-click="handleQuestionClick"
      />

      <!-- Popular Questions -->
      <QuestionListCard
        title="سوالات پربازدید هفته"
        :questions="popularQuestions"
        :is-loading="isPopularLoading"
        @question-click="handleQuestionClick"
      />
    </div>
  </aside>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useDashboard } from '../../composables'
import StatsCard from './StatsCard.vue'
import QuestionListCard from './QuestionListCard.vue'

export default {
  name: 'HomeSidebar',
  components: {
    StatsCard,
    QuestionListCard
  },
  setup() {
    const router = useRouter()
    const isRecommendedLoading = ref(false)
    const isPopularLoading = ref(false)

    const {
      stats,
      recommendedQuestions,
      popularQuestions,
      isLoading: isDashboardLoading,
      fetchStats,
      fetchRecommendedQuestions,
      fetchPopularQuestions
    } = useDashboard()

    const handleQuestionClick = (question) => {
      router.push(`/questions/${question.id}`)
    }

    const loadRecommendedQuestions = async () => {
      isRecommendedLoading.value = true
      try {
        await fetchRecommendedQuestions()
      } finally {
        isRecommendedLoading.value = false
      }
    }

    const loadPopularQuestions = async () => {
      isPopularLoading.value = true
      try {
        await fetchPopularQuestions()
      } finally {
        isPopularLoading.value = false
      }
    }

    onMounted(async () => {
      // Load all sidebar data
      await Promise.all([
        fetchStats(),
        loadRecommendedQuestions(),
        loadPopularQuestions()
      ])
    })

    return {
      stats,
      recommendedQuestions,
      popularQuestions,
      isDashboardLoading,
      isRecommendedLoading,
      isPopularLoading,
      handleQuestionClick
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for sidebar */
.sidebar-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.sidebar-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.sidebar-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 2px;
}

.sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.8);
}

.dark .sidebar-scrollbar {
  scrollbar-color: rgba(75, 85, 99, 0.5) transparent;
}

.dark .sidebar-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.8);
}
</style>
