import './bootstrap';
import { createApp, defineAsyncComponent } from 'vue'
import App from './App.vue'
import UIComponents from './plugins/ui-components.js'
import 'nprogress/nprogress.css';
import api from './services/api.js'

import router from './router'

const app = createApp(App)

app.use(router)

// Register UI components globally
app.use(UIComponents)

// Lazy SweetAlert2: load on first use, not at startup
const ensureSwal = async () => {
  const [{ default: Swal }] = await Promise.all([
    import('sweetalert2'),
    import('sweetalert2/dist/sweetalert2.min.css')
  ])
  return Swal
}

const $swal = (...args) => ensureSwal().then(Swal => Swal.fire(...args))
$swal.fire = (...args) => ensureSwal().then(Swal => Swal.fire(...args))
app.config.globalProperties.$swal = $swal

// Lazy-load the rich text editor to keep initial bundle small
app.component('Editor', defineAsyncComponent(() => import('@tinymce/tinymce-vue')));
// Provide the centralized API client
app.config.globalProperties.$api = api;
if (typeof window !== 'undefined') {
  window.$api = api
  // Provide a minimal window.Swal proxy
  window.Swal = { fire: (...args) => $swal.fire(...args) }
}

app.mount('#app')
