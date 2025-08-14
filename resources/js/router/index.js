import { createRouter, createWebHistory } from 'vue-router'
import NProgress from 'nprogress'
import { useAuth } from '../composables/useAuth'

// Lazy-loaded route components with prefetching for better performance
const Home = () => import(/* webpackChunkName: "page-home", webpackPrefetch: true */ '../pages/Home.vue')
const QuestionShow = () => import(/* webpackChunkName: "page-question" */ '../pages/QuestionShow.vue')
const Authors = () => import(/* webpackChunkName: "page-authors" */ '../pages/Authors.vue')
const AuthorShow = () => import(/* webpackChunkName: "page-author-show" */ '../pages/AuthorShow.vue')
const Categories = () => import(/* webpackChunkName: "page-categories" */ '../pages/Categories.vue')
const Category = () => import(/* webpackChunkName: "page-category" */ '../pages/Category.vue')
const Tags = () => import(/* webpackChunkName: "page-tags" */ '../pages/Tags.vue')
const Tag = () => import(/* webpackChunkName: "page-tag" */ '../pages/Tag.vue')
const Profile = () => import(/* webpackChunkName: "page-profile" */ '../pages/Profile.vue')
const DailyActivity = () => import(/* webpackChunkName: "page-activity" */ '../pages/DailyActivity.vue')

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { title: 'صفحه اصلی' }
  },
  {
    path: '/questions/:slug',
    name: 'QuestionShow',
    component: QuestionShow,
    props: true,
    meta: { title: 'جزئیات سوال', dynamic: true }
  },
  {
    path: '/authors',
    name: 'Authors',
    component: Authors,
    meta: { title: 'نویسندگان' }
  },
  {
    path: '/authors/:id',
    name: 'AuthorShow',
    component: AuthorShow,
    props: true,
    meta: { title: 'پروفایل نویسنده', dynamic: true }
  },
  {
    path: '/categories',
    name: 'Categories',
    component: Categories,
    meta: { title: 'دسته‌بندی‌ها' }
  },
  {
    path: '/categories/:slug',
    name: 'Category',
    component: Category,
    props: true,
    meta: { title: 'دسته‌بندی', dynamic: true }
  },
  {
    path: '/tags',
    name: 'Tags',
    component: Tags,
    meta: { title: 'برچسب‌ها' }
  },
  {
    path: '/tags/:slug',
    name: 'Tag',
    component: Tag,
    props: true,
    meta: { title: 'برچسب', dynamic: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true, title: 'پروفایل کاربری' }
  },
  {
    path: '/activities',
    name: 'DailyActivity',
    component: DailyActivity,
    meta: { title: 'فعالیت ها' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

NProgress.configure({ showSpinner: false })

// Global guards: start progress + auth check
router.beforeEach(async (to, from, next) => {
  NProgress.start()

  if (to.matched.some(record => record.meta.requiresAuth)) {
    const token = localStorage.getItem('auth_token')
    if (!token) {
      try {
        // Start OAuth login flow with intended URL
        const intendedUrl = `${window.location.origin}${to.fullPath}`
        const response = await fetch('/api/auth/redirect', {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ intended_url: intendedUrl })
        })
        if (response.ok) {
          const data = await response.json()
          window.location.href = data.redirect_url
        } else {
          next('/')
        }
      } catch (_) {
        next('/')
      }
      return
    }

    // If token exists but user not loaded yet, ensure user is fetched before allowing entry
    try {
      const { isAuthenticated, fetchUser } = useAuth()
      if (!isAuthenticated.value) {
        await fetchUser()
      }
      if (!isAuthenticated.value) {
        // Token invalid → trigger login flow
        const intendedUrl = `${window.location.origin}${to.fullPath}`
        const response = await fetch('/api/auth/redirect', {
          method: 'POST',
          headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ intended_url: intendedUrl })
        })
        if (response.ok) {
          const data = await response.json()
          window.location.href = data.redirect_url
        } else {
          next('/')
        }
        return
      }
    } catch (_) {
      next('/')
      return
    }
  }
  next()
})

// After each navigation: set title (non-dynamic) and stop progress
router.afterEach((to) => {
  if (to.meta?.title && !to.meta?.dynamic) {
    const defaultTitle = 'انجمن پرسش و پاسخ'
    document.title = `${to.meta.title} - ${defaultTitle}`
  }
  NProgress.done()
})

export default router
