import '../node_modules/flowbite-vue/dist/index.css'
import './assets/style.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ApiService from '@/composables/ApiService'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)
ApiService.init(app)

app.mount('#app')
