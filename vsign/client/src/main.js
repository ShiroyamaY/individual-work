import { createApp } from 'vue';
import { router } from '@/router/index.js';
import { createPinia } from 'pinia';
import './assets/main.css';
import App from '@/App.vue';
const app = createApp(App)
    .use(createPinia)
    .use(router)
    .mount('#app');
