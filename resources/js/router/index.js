import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import QuestionShow from '../pages/QuestionShow.vue'
import Authors from '../pages/Authors.vue'
import AuthorShow from '../pages/AuthorShow.vue'
import Categories from '../pages/Categories.vue'
import Category from '../pages/Category.vue'

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
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
