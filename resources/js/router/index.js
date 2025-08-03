import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import QuestionShow from '../pages/QuestionShow.vue'
import Authors from '../pages/Authors.vue'
import AuthorShow from '../pages/AuthorShow.vue'
import Categories from '../pages/Categories.vue'
import Category from '../pages/Category.vue'
import Profile from '../pages/Profile.vue'
import DailyActivity from '../pages/DailyActivity.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { title: 'صفحه اصلی' }
  },
  {
    path: '/questions/:id',
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

// Navigation guard for authenticated routes and title updates
router.beforeEach((to, from, next) => {
  // Handle authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    // Check if user is authenticated
    const token = localStorage.getItem('auth_token')
    if (!token) {
      // Redirect to home page if not authenticated
      next('/')
      return
    }
  }

  // Set page title if not dynamic
  if (to.meta.title && !to.meta.dynamic) {
    const defaultTitle = 'انجمن پرسش و پاسخ'
    document.title = `${to.meta.title} - ${defaultTitle}`
  }

  next()
})

export default router
