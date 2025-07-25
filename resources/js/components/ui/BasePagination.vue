<template>
  <nav v-if="totalPages > 1" class="flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 sm:px-6">
    <div class="flex flex-1 justify-between sm:hidden">
      <!-- Mobile pagination -->
      <button
        @click="$emit('page-changed', currentPage - 1)"
        :disabled="currentPage <= 1"
        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        قبلی
      </button>
      <button
        @click="$emit('page-changed', currentPage + 1)"
        :disabled="currentPage >= totalPages"
        class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        بعدی
      </button>
    </div>

    <div class="hidden sm:flex sm:flex-col sm:items-center sm:gap-4">
      <!-- Pagination controls -->
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <!-- Previous button -->
          <button
            @click="$emit('page-changed', currentPage - 1)"
            :disabled="currentPage <= 1"
            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 dark:text-gray-500 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span class="sr-only">قبلی</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </button>

          <!-- Page numbers -->
          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page !== '...'"
              @click="$emit('page-changed', page)"
              :class="[
                page === currentPage
                  ? 'relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                  : 'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0'
              ]"
            >
              {{ page }}
            </button>
            <span
              v-else
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:outline-offset-0"
            >
              ...
            </span>
          </template>

          <!-- Next button -->
          <button
            @click="$emit('page-changed', currentPage + 1)"
            :disabled="currentPage >= totalPages"
            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 dark:text-gray-500 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span class="sr-only">بعدی</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </button>
        </nav>
      </div>
    </div>
  </nav>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'BasePagination',
  emits: ['page-changed'],
  props: {
    currentPage: {
      type: Number,
      required: true,
      default: 1
    },
    totalPages: {
      type: Number,
      required: true,
      default: 1
    },
    total: {
      type: Number,
      required: true,
      default: 0
    },
    perPage: {
      type: Number,
      default: 10
    },
    maxVisiblePages: {
      type: Number,
      default: 7
    }
  },
  setup(props) {
    const from = computed(() => {
      return props.total === 0 ? 0 : (props.currentPage - 1) * props.perPage + 1
    })

    const to = computed(() => {
      return Math.min(props.currentPage * props.perPage, props.total)
    })

    const visiblePages = computed(() => {
      const { currentPage, totalPages, maxVisiblePages } = props
      const pages = []

      if (totalPages <= maxVisiblePages) {
        // Show all pages if total is less than max
        for (let i = 1; i <= totalPages; i++) {
          pages.push(i)
        }
      } else {
        // Always show first page
        pages.push(1)

        let start = Math.max(2, currentPage - Math.floor(maxVisiblePages / 2))
        let end = Math.min(totalPages - 1, currentPage + Math.floor(maxVisiblePages / 2))

        // Adjust start and end to show exactly maxVisiblePages
        if (end - start + 1 < maxVisiblePages - 2) {
          if (start === 2) {
            end = Math.min(totalPages - 1, start + maxVisiblePages - 3)
          } else {
            start = Math.max(2, end - maxVisiblePages + 3)
          }
        }

        // Add ellipsis before start if needed
        if (start > 2) {
          pages.push('...')
        }

        // Add pages in range
        for (let i = start; i <= end; i++) {
          pages.push(i)
        }

        // Add ellipsis after end if needed
        if (end < totalPages - 1) {
          pages.push('...')
        }

        // Always show last page
        if (totalPages > 1) {
          pages.push(totalPages)
        }
      }

      return pages
    })

    return {
      from,
      to,
      visiblePages
    }
  }
}
</script>
