import './bootstrap';
import { createApp } from 'vue'
import App from './App.vue'
import UIComponents from './plugins/ui-components.js'
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

import Editor from 'primevue/editor';
import PrimeVue from 'primevue/config';

const app = createApp(App)

// Register UI components globally
app.use(UIComponents)

// Use SweetAlert2
app.use(VueSweetalert2);

app.use(PrimeVue);
app.component('Editor', Editor);

app.mount('#app')
