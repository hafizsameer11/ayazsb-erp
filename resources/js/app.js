import './bootstrap';
import { initErpDetailLines } from './erp-detail-lines';
import { initErpForms } from './erp-forms';

document.addEventListener('DOMContentLoaded', () => {
    initErpDetailLines();
    initErpForms();
});
