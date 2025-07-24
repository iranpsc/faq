<template>
  <main class="flex-grow p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900/50 overflow-y-auto main-content-container">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4 sm:mb-0">
          آخرین پرسش و پاسخ ها
        </h1>
        <div class="flex items-center gap-2">
          <BaseButton variant="outline" size="sm">
            <template #icon>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9M3 12h9m-9 4h13m-5-4v8m0 0l-4-4m4 4l4-4"></path></svg>
            </template>
            جدیدترین
          </BaseButton>
          <BaseButton variant="ghost" size="sm">محبوب ترین</BaseButton>
          <BaseButton variant="ghost" size="sm">بدون پاسخ</BaseButton>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-1 gap-4">
        <div v-for="n in 5" :key="n" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-pulse">
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-4"></div>
          <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-6"></div>
          <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-full mb-2"></div>
          <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-5/6 mb-4"></div>
          <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/6"></div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="errors.fetch">
        <BaseAlert variant="error" :message="errors.fetch" />
      </div>

      <!-- Question List -->
      <div v-else-if="questions.length > 0">
        <QuestionCard
          v-for="question in questions"
          :key="question.id"
          :question="question"
          @click="handleQuestionClick(question)"
          @edit="handleEditQuestion"
          @delete="handleDeleteQuestion"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">سوالی یافت نشد</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">با پرسیدن اولین سوال شروع کنید.</p>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.meta && pagination.meta.last_page > 1" class="mt-8 flex justify-center items-center gap-2">
        <BaseButton
          @click="changePage(pagination.meta.current_page - 1)"
          :disabled="!pagination.links.prev"
          variant="outline"
          size="sm"
        >
          قبلی
        </BaseButton>
        <span class="text-sm text-gray-700 dark:text-gray-300">
          صفحه {{ pagination.meta.current_page }} از {{ pagination.meta.last_page }}
        </span>
        <BaseButton
          @click="changePage(pagination.meta.current_page + 1)"
          :disabled="!pagination.links.next"
          variant="outline"
          size="sm"
        >
          بعدی
        </BaseButton>
      </div>
    </div>
  </main>
</template>

<script>
import { onMounted, ref, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
import { useQuestions } from '../composables'
import QuestionCard from '../components/QuestionCard.vue'
import { BaseButton, BaseAlert } from '../components/ui'

export default {
  name: 'Home',
  components: {
    QuestionCard,
    BaseButton,
    BaseAlert
  },
  emits: ['edit-question'],
  setup(props, { emit }) {
    const router = useRouter()
    const instance = getCurrentInstance()
    const {
      questions,
      pagination,
      isLoading,
      errors,
      fetchQuestions,
      deleteQuestion,
      changePage
    } = useQuestions()

    const handleQuestionClick = (question) => {
      router.push(`/questions/${question.id}`)
    }

    const handleEditQuestion = (question) => {
      emit('edit-question', question)
    }

    const handleDeleteQuestion = async (question) => {
      // Show confirmation dialog
      const { value: confirmed } = await instance.appContext.config.globalProperties.$swal({
        title: 'حذف سوال',
        text: `آیا از حذف سوال "${question.title}" اطمینان دارید؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'انصراف',
        reverseButtons: true
      })

      if (confirmed) {
        const result = await deleteQuestion(question.id)

        if (result.success) {
          // Show success message
          instance.appContext.config.globalProperties.$swal({
            title: 'موفق',
            text: result.message || 'سوال با موفقیت حذف شد.',
            icon: 'success',
            confirmButtonText: 'باشه'
          })

          // Refresh the questions list
          await fetchQuestions()
        } else {
          // Show error message
          instance.appContext.config.globalProperties.$swal({
            title: 'خطا',
            text: result.message || 'خطا در حذف سوال',
            icon: 'error',
            confirmButtonText: 'باشه'
          })
        }
      }
    }

    const refreshQuestions = () => {
      fetchQuestions()
    }

    onMounted(() => {
      fetchQuestions()
    })

    return {
      questions,
      pagination,
      isLoading,
      errors,
      changePage,
      handleQuestionClick,
      handleEditQuestion,
      handleDeleteQuestion,
      refreshQuestions
    }
  }
}
</script>
