const LBS_PER_BAG = 100;
const LBS_PER_KG = 2.20462;

export function calculateYarnTotals(input) {
    const bags = parseFloat(input.no_of_bags ?? input.quantity ?? 0) || 0;
    const cones = parseFloat(input.no_of_cones ?? 0) || 0;
    const packingSize = parseFloat(input.packing_size ?? 0) || 0;
    const rate = parseFloat(input.rate ?? 0) || 0;
    const commissionPercent = parseFloat(input.commission_percent ?? 0) || 0;
    const brokeryPercent = parseFloat(input.brokery_percent ?? 0) || 0;

    let totalLbs = bags * LBS_PER_BAG;
    if (cones > 0 && packingSize > 0) {
        totalLbs += cones * (LBS_PER_BAG / packingSize);
    }

    const totalKgs = totalLbs > 0 ? totalLbs / LBS_PER_KG : 0;
    const totalAmount = rate * totalLbs;
    const totalCommission = (totalAmount * commissionPercent) / 100;
    const totalBrokery = (totalAmount * brokeryPercent) / 100;
    const totalNet = totalAmount + totalBrokery + totalCommission;

    return {
        weight_lbs: totalLbs,
        total_kgs: totalKgs,
        total_amount: totalAmount,
        total_commission: totalCommission,
        total_brokery: totalBrokery,
        total_net_amount: totalNet,
    };
}

function setField(form, name, value, decimals = 4) {
    const el = form.querySelector(`[name="${name}"]`);
    if (!el) {
        return;
    }
    const num = Number(value) || 0;
    if (decimals === 2) {
        el.value = num.toFixed(2);
    } else {
        el.value = num.toFixed(4).replace(/\.?0+$/, '') || '0';
    }
}

function readFormInput(form) {
    const get = (name) => form.querySelector(`[name="${name}"]`)?.value ?? '';
    return {
        no_of_bags: get('quantity') || get('no_of_bags'),
        no_of_cones: get('no_of_cones'),
        packing_size: get('packing_size'),
        rate: get('rate'),
        commission_percent: get('commission_percent'),
        brokery_percent: get('brokery_percent'),
    };
}

function recalcForm(form) {
    const totals = calculateYarnTotals(readFormInput(form));
    setField(form, 'weight_lbs', totals.weight_lbs);
    setField(form, 'total_kgs', totals.total_kgs);
    setField(form, 'total_amount', totals.total_amount, 2);
    setField(form, 'total_commission', totals.total_commission, 2);
    setField(form, 'total_brokery', totals.total_brokery, 2);
    setField(form, 'total_net_amount', totals.total_net_amount, 2);
}

export function initYarnContractCalculations() {
    document.querySelectorAll('[data-yarn-contract-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.yarnCalcBound === '1') {
            return;
        }
        form.dataset.yarnCalcBound = '1';

        const trigger = () => recalcForm(form);
        form.querySelectorAll('[data-yarn-calc-trigger]').forEach((el) => {
            el.addEventListener('input', trigger);
            el.addEventListener('change', trigger);
        });

        const itemSelect = form.querySelector('[name="item_id"]');
        const packingInput = form.querySelector('[name="packing_size"]');
        const items = window.erpYarnItems ?? [];

        itemSelect?.addEventListener('change', () => {
            const item = items.find((row) => String(row.id) === String(itemSelect.value));
            if (item && packingInput) {
                packingInput.value = item.pack_size_cones ?? '';
                if (item.packing_weight && form.querySelector('[name="packing_weight"]')) {
                    form.querySelector('[name="packing_weight"]').value = item.packing_weight;
                }
            }
            const desc = form.querySelector('[data-yarn-item-description]');
            if (desc) {
                desc.value = item ? `${item.code} — ${item.name}` : '';
            }
            trigger();
        });

        const accountSelect = form.querySelector('[name="account_id"]');
        const contractSelect = form.querySelector('[name="yarn_contract_id"]');
        const contracts = window.erpYarnContracts ?? [];

        const applyContract = () => {
            if (!contractSelect) {
                return;
            }
            const contract = contracts.find((row) => String(row.id) === String(contractSelect.value));
            if (!contract) {
                return;
            }

            const set = (name, value) => {
                const el = form.querySelector(`[name="${name}"]`);
                if (el && value !== undefined && value !== null) {
                    el.value = value;
                }
            };

            if (accountSelect && contract.account_id) {
                accountSelect.value = String(contract.account_id);
                accountSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }

            set('item_id', contract.item_id);
            itemSelect?.dispatchEvent(new Event('change', { bubbles: true }));
            set('packing_size', contract.packing_size);
            set('packing_weight', contract.packing_weight);
            set('quantity', contract.quantity);
            set('no_of_cones', contract.no_of_cones);
            set('rate', contract.rate);
            set('commission_percent', contract.commission_percent);
            set('brokery_percent', contract.brokery_percent);
            set('yarn_type', contract.yarn_type);
            set('broker_account_id', contract.broker_account_id);
            const brokerHidden = form.querySelector('input[type="hidden"][name="broker_account_id"]');
            if (brokerHidden && contract.broker_account_id) {
                brokerHidden.value = String(contract.broker_account_id);
            }

            const brokerDesc = form.querySelector('[data-yarn-broker-description]');
            if (brokerDesc) {
                brokerDesc.value = contract.broker_name ?? '';
            }

            trigger();
        };

        contractSelect?.addEventListener('change', applyContract);

        const filterContractsByParty = () => {
            if (!contractSelect || !accountSelect) {
                return;
            }
            const accountId = accountSelect.value;
            Array.from(contractSelect.options).forEach((opt) => {
                if (!opt.value) {
                    return;
                }
                const contract = contracts.find((row) => String(row.id) === opt.value);
                opt.hidden = accountId !== '' && contract && String(contract.account_id) !== accountId;
            });
        };

        accountSelect?.addEventListener('change', filterContractsByParty);
        filterContractsByParty();

        form.addEventListener('submit', () => {
            trigger();
            const totals = calculateYarnTotals(readFormInput(form));
            const input = readFormInput(form);
            const sync = (name, value) => {
                const el = form.querySelector(`[name="${name}"]`);
                if (el) {
                    el.value = value;
                }
            };
            sync('lines[0][qty]', input.no_of_bags || input.quantity || '');
            sync('lines[0][weight_lbs]', totals.weight_lbs);
            sync('lines[0][rate]', form.querySelector('[name="rate"]')?.value ?? '');
            sync('lines[0][amount]', totals.total_net_amount.toFixed(2));
            sync('lines[0][item_id]', form.querySelector('[name="item_id"]')?.value ?? '');
        });

        trigger();
    });
}
