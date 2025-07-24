import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import QuestionShow from '../pages/QuestionShow.vue'

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
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
