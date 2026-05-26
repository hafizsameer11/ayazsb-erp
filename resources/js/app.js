import './bootstrap';
import { initErpDetailLines } from './erp-detail-lines';
import { initErpForms } from './erp-forms';
import { initErpAjaxSave } from './erp-ajax-save';
import { initErpRecordDelete } from './erp-record-delete';
import { initYarnContractCalculations } from './yarn-contract-calculations';
import './grey-transaction-calculations';
import { initYarnMovementCalculations } from './yarn-movement-calculations';

document.addEventListener('DOMContentLoaded', () => {
    initErpDetailLines();
    initErpForms();
    initErpAjaxSave();
    initErpRecordDelete();
    initYarnContractCalculations();
    initYarnMovementCalculations();
});
