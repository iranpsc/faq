import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const pageTitle = ref('انجمن پرسش و پاسخ')
const defaultTitle = 'انجمن پرسش و پاسخ'

export function usePageTitle() {
  const route = useRoute()

  const setTitle = (title) => {
    if (title && title.trim()) {
      pageTitle.value = `${title} - ${defaultTitle}`
      document.title = pageTitle.value
    } else {
      pageTitle.value = defaultTitle
      document.title = defaultTitle
    }
  }

  const setDefaultTitle = () => {
    pageTitle.value = defaultTitle
    document.title = defaultTitle
  }

  // Watch for route changes to reset title if needed
  watch(
    () => route.name,
    () => {
      // Set default title when route changes, individual pages will override this
      setDefaultTitle()
    },
    { immediate: true }
  )

  return {
    pageTitle,
    setTitle,
    setDefaultTitle
  }
}
