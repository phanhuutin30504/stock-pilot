import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../modules/dashboard/DashboardView.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: DashboardView,
    },
  ],
})
