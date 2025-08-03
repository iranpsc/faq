import './bootstrap';
import { createApp } from 'vue'
import App from './App.vue'
import UIComponents from './plugins/ui-components.js'
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import Editor from '@tinymce/tinymce-vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

import router from './router'

NProgress.configure({ showSpinner: false });

router.beforeEach((to, from, next) => {
    NProgress.start();
    next();
});

router.afterEach(() => {
    NProgress.done();
});

// Make SweetAlert available globally
window.Swal = Swal;

const app = createApp(App)

app.use(router)

// Register UI components globally
app.use(UIComponents)

// Use SweetAlert2
app.use(VueSweetalert2);

app.component('Editor', Editor);

app.config.globalProperties.$axios = axios;

app.mount('#app')
