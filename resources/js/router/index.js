import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import QuestionShow from '../pages/QuestionShow.vue'
import Authors from '../pages/Authors.vue'
import AuthorShow from '../pages/AuthorShow.vue'
import Categories from '../pages/Categories.vue'
import Category from '../pages/Category.vue'
import Profile from '../pages/Profile.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/questions/:id',
    name: 'QuestionShow',
    component: QuestionShow,
    props: true
  },
  {
    path: '/authors',
    name: 'Authors',
    component: Authors
  },
  {
    path: '/authors/:id',
    name: 'AuthorShow',
    component: AuthorShow,
    props: true
  },
  {
    path: '/categories',
    name: 'Categories',
    component: Categories
  },
  {
    path: '/categories/:slug',
    name: 'Category',
    component: Category,
    props: true
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard for authenticated routes
router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    // Check if user is authenticated
    const token = localStorage.getItem('auth_token')
    if (!token) {
      // Redirect to home page if not authenticated
      next('/')
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
