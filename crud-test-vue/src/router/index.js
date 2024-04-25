import { createRouter, createWebHistory } from 'vue-router'
// import HomeView from '../views/HomeView.vue'
import HomeView from '../views/User/index.vue'
import CreateUser from '../views/User/create.vue'
import EditUser from '../views/User/edit.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/user/create',
      name: 'createUser',
      component: CreateUser
    },
    {
      path: '/user/edit/:id',
      name: 'editUser',
      component: EditUser
    }
  ]
})

export default router
