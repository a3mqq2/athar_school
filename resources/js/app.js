import './bootstrap';
import { createApp } from 'vue';

import StudentPromotion from './components/StudentPromotion.vue'; // مثال على مكون
import PayrollManager from './components/PayrollManager.vue'; // مثال على مكون

const app = createApp({});
app.component('student-promotion', StudentPromotion);
app.component('payroll-manager', PayrollManager);
app.mount('#app');
