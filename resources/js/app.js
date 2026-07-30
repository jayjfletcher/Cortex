import { createApp } from 'vue';
import App from './App.vue';
import { bootAuth } from './auth';
import router from './router';
import '../css/app.css';

bootAuth()
    .catch(() => {})
    .finally(() => createApp(App).use(router).mount('#cortex'));
