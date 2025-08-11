import './bootstrap';
import { createApp } from 'vue'
import App from './App.vue'
import UIComponents from './plugins/ui-components.js'
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import Editor from '@tinymce/tinymce-vue';
import 'nprogress/nprogress.css';
import api from './services/api.js'
import Swal from 'sweetalert2';

import router from './router'

const app = createApp(App)

app.use(router)

// Register UI components globally
app.use(UIComponents)

// Use SweetAlert2
app.use(VueSweetalert2);

app.component('Editor', Editor);
// Provide the centralized API client
app.config.globalProperties.$api = api;
if (typeof window !== 'undefined') {
  window.$api = api
  // Expose SweetAlert for non-Options API code paths that reference window.Swal
  window.Swal = Swal
}

app.mount('#app')
