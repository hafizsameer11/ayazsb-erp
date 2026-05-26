import { calculateYarnTotals } from './yarn-contract-calculations';

function recalcLineRow(row) {
    const bags = row.querySelector('[data-yarn-bags]')?.value ?? 0;
    const cones = row.querySelector('[data-yarn-cones]')?.value ?? 0;
    const packingSize = row.querySelector('[data-yarn-packing-size]')?.value ?? 0;
    const rate = row.querySelector('[data-yarn-rate]')?.value ?? 0;

    const totals = calculateYarnTotals({
        no_of_bags: bags,
        no_of_cones: cones,
        packing_size: packingSize,
        rate,
        commission_percent: 0,
        brokery_percent: 0,
    });

    const set = (sel, val, dec = 4) => {
        const el = row.querySelector(sel);
        if (el) {
            el.value = dec === 2 ? Number(val).toFixed(2) : String(Number(val).toFixed(4)).replace(/\.?0+$/, '') || '0';
        }
    };

    set('[data-yarn-weight-lbs]', totals.weight_lbs);
    set('[data-yarn-total-kgs]', totals.total_kgs);
    set('[data-yarn-amount]', totals.total_amount, 2);
}

function applyItemToRow(row, item, options = {}) {
    if (!item) {
        return;
    }
    const packing = row.querySelector('[data-yarn-packing-size]');
    if (packing) {
        packing.value = item.packing_size ?? item.pack_size_cones ?? '';
    }
    const rate = row.querySelector('[data-yarn-rate]');
    if (rate && (options.forceRate || !rate.value)) {
        rate.value = item.purchase_rate ?? '';
    }
    const bags = row.querySelector('[data-yarn-bags]');
    if (bags && options.autoFillBags && item.available_bags) {
        bags.value = item.available_bags;
    }
    const cones = row.querySelector('[data-yarn-cones]');
    if (cones && options.autoFillCones && item.available_cones) {
        cones.value = item.available_cones;
    }
    if (bags && item.available_bags) {
        bags.max = item.available_bags;
        bags.title = `Available bags: ${item.available_bags}`;
    }
    if (cones && item.available_cones) {
        cones.max = item.available_cones;
        cones.title = `Available cones: ${item.available_cones}`;
    }
    recalcLineRow(row);
}

function parsePayload(option) {
    try {
        return JSON.parse(option?.dataset?.payload || '{}');
    } catch {
        return {};
    }
}

function issuableLinesKey(accountId, greyContractId) {
    return `${accountId}:${greyContractId}`;
}

export function initYarnMovementCalculations() {
    const globalItems = window.erpYarnItems ?? [];
    const greyContracts = window.erpGreyConversionContracts ?? [];
    const issuances = window.erpYarnIssuances ?? [];
    const issuableByKey = window.erpIssuableLinesByPartyContract ?? {};
    const fromContractsByAccount = window.erpFromGreyContractsByAccount ?? {};
    const contractRemarks = window.erpYarnContractRemarksByAccount ?? {};

    document.querySelectorAll('[data-yarn-opening-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.yarnOpeningBound === '1') {
            return;
        }
        form.dataset.yarnOpeningBound = '1';

        const accountSelect = form.querySelector('[name="account_id"]');
        const contractRefSelect = form.querySelector('[data-yarn-contract-ref]');
        const remarksInput = form.querySelector('[name="remarks"]');

        const rebuildContractRefs = () => {
            if (!contractRefSelect) {
                return;
            }
            const accountId = accountSelect?.value ?? '';
            const refs = contractRemarks[accountId] ?? [];
            const current = contractRefSelect.value;
            contractRefSelect.innerHTML = '<option value=""></option>';
            refs.forEach((ref) => {
                const opt = document.createElement('option');
                opt.value = ref.key;
                opt.textContent = ref.label;
                opt.dataset.remarks = ref.remarks ?? '';
                contractRefSelect.appendChild(opt);
            });
            if (current) {
                contractRefSelect.value = current;
            }
        };

        accountSelect?.addEventListener('change', rebuildContractRefs);
        contractRefSelect?.addEventListener('change', () => {
            const selected = contractRefSelect.selectedOptions[0];
            if (remarksInput && selected) {
                remarksInput.value = selected.dataset.remarks ?? '';
            }
        });

        rebuildContractRefs();
    });

    document.querySelectorAll('[data-yarn-line-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.yarnLineBound === '1') {
            return;
        }
        form.dataset.yarnLineBound = '1';

        form.querySelectorAll('[data-yarn-line-row]').forEach((row) => {
            row.addEventListener('input', () => recalcLineRow(row));
            row.addEventListener('change', () => recalcLineRow(row));
            const select = row.querySelector('[data-yarn-item-select]');
            select?.addEventListener('change', () => {
                const item = globalItems.find((r) => String(r.id) === String(select.value));
                applyItemToRow(row, item, { autoFillBags: false, autoFillCones: false });
            });
            if (select?.value) {
                applyItemToRow(row, globalItems.find((r) => String(r.id) === String(select.value)));
            }
        });
    });

    document.querySelectorAll('[data-yarn-issuance-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.yarnIssuanceBound === '1') {
            return;
        }
        form.dataset.yarnIssuanceBound = '1';

        const isTransfer = form.dataset.yarnFormVariant === 'issuance-transfer';
        const isReturn = form.dataset.yarnFormVariant === 'issuance-return';
        const isIssuance = form.dataset.yarnFormVariant === 'issuance';

        const accountSelect = form.querySelector('[name="account_id"]');
        const greyContractSelect = form.querySelector('[name="grey_conversion_contract_id"]');
        const issuanceSelect = form.querySelector('[name="source_transaction_id"]');
        const fromAccountSelect = form.querySelector('[name="from_account_id"]');
        const toAccountSelect = form.querySelector('[name="to_account_id"]');
        const fromGreySelect = form.querySelector('[name="from_grey_conversion_contract_id"]');
        const toGreySelect = form.querySelector('[name="to_grey_conversion_contract_id"]');
        const contractDisplay = form.querySelector('[name="meta[contract_display]"]');
        const contractSummary = form.querySelector('[data-yarn-contract-summary]');

        const filterOptions = (select, filterFn) => {
            if (!select) {
                return;
            }
            Array.from(select.options).forEach((opt) => {
                if (!opt.value) {
                    return;
                }
                const data = parsePayload(opt);
                opt.hidden = !filterFn(data);
            });
        };

        const updateContractSummary = (contract) => {
            if (!contractSummary) {
                return;
            }
            if (!contract) {
                contractSummary.textContent = '';
                contractSummary.classList.add('hidden');
                return;
            }
            contractSummary.textContent = `Required: ${contract.required_bags ?? 0} | Issued: ${contract.issued_bags ?? 0} | Remaining: ${contract.remaining_bags ?? 0}`;
            contractSummary.classList.remove('hidden');
        };

        const lineItemsForContext = () => {
            if (isIssuance) {
                return globalItems;
            }
            const accountId = isTransfer ? fromAccountSelect?.value : accountSelect?.value;
            const greyId = isTransfer
                ? fromGreySelect?.value
                : (greyContractSelect?.value || form.querySelector('[name="grey_conversion_contract_id"]')?.value);
            if (!accountId || !greyId) {
                return [];
            }
            return issuableByKey[issuableLinesKey(accountId, greyId)] ?? [];
        };

        const rebuildYarnSelects = (autoFill = false) => {
            const items = lineItemsForContext();
            form.querySelectorAll('[data-yarn-item-select]').forEach((select) => {
                const current = select.value;
                const row = select.closest('[data-yarn-line-row]');
                if (!isReturn && !isTransfer && items.length === 0) {
                    return;
                }
                if (items.length > 0) {
                    select.innerHTML = '<option value=""></option>';
                    items.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = String(item.item_id);
                        opt.textContent = item.lov_label ?? `${item.item_code} | Bags: ${item.available_bags}`;
                        opt.dataset.payload = JSON.stringify(item);
                        select.appendChild(opt);
                    });
                }
                if (current) {
                    select.value = current;
                }
                if (autoFill && select.value && row) {
                    const item = items.find((r) => String(r.item_id) === String(select.value))
                        ?? globalItems.find((r) => String(r.id) === String(select.value));
                    applyItemToRow(row, item, { autoFillBags: true, autoFillCones: false, forceRate: true });
                }
            });
        };

        const populateFromGreyContract = (contractId) => {
            const accountId = fromAccountSelect?.value ?? '';
            const contracts = fromContractsByAccount[accountId] ?? fromContractsByAccount[String(accountId)] ?? [];
            const contract = contracts.find((c) => String(c.id) === String(contractId))
                ?? greyContracts.find((c) => String(c.id) === String(contractId));
            updateContractSummary(contract);
            rebuildYarnSelects(true);
        };

        accountSelect?.addEventListener('change', () => {
            const accountId = accountSelect.value;
            filterOptions(greyContractSelect, (c) => String(c.account_id) === accountId);
            filterOptions(issuanceSelect, (i) => String(i.account_id) === accountId);
            rebuildYarnSelects();
        });

        fromAccountSelect?.addEventListener('change', () => {
            const accountId = fromAccountSelect.value;
            if (fromGreySelect) {
                const contracts = fromContractsByAccount[accountId] ?? fromContractsByAccount[String(accountId)] ?? [];
                const current = fromGreySelect.value;
                fromGreySelect.innerHTML = '<option value=""></option>';
                contracts.forEach((c) => {
                    const opt = document.createElement('option');
                    opt.value = String(c.id);
                    opt.textContent = c.lov_label ?? c.contract_code ?? c.contract_no;
                    opt.dataset.payload = JSON.stringify(c);
                    fromGreySelect.appendChild(opt);
                });
                if (current) {
                    fromGreySelect.value = current;
                }
            }
            filterOptions(issuanceSelect, (i) => String(i.account_id) === accountId);
            rebuildYarnSelects();
        });

        toAccountSelect?.addEventListener('change', () => {
            const accountId = toAccountSelect.value;
            filterOptions(toGreySelect, (c) => String(c.account_id) === accountId);
        });

        greyContractSelect?.addEventListener('change', () => {
            const contract = greyContracts.find((c) => String(c.id) === String(greyContractSelect.value));
            if (contract && accountSelect && !accountSelect.value) {
                accountSelect.value = String(contract.account_id);
            }
            updateContractSummary(contract);
            if (isIssuance) {
                rebuildYarnSelects();
            }
        });

        fromGreySelect?.addEventListener('change', () => {
            populateFromGreyContract(fromGreySelect.value);
        });

        issuanceSelect?.addEventListener('change', () => {
            const issuance = issuances.find((i) => String(i.id) === String(issuanceSelect.value));
            if (!issuance) {
                return;
            }
            const set = (name, val) => {
                const el = form.querySelector(`[name="${name}"]`);
                if (el && val !== undefined && val !== null) {
                    el.value = val;
                }
            };
            set('grey_conversion_contract_id', issuance.grey_conversion_contract_id);
            set('account_id', issuance.account_id);
            if (fromGreySelect && issuance.grey_conversion_contract_id) {
                fromGreySelect.value = String(issuance.grey_conversion_contract_id);
                populateFromGreyContract(fromGreySelect.value);
            }
            const contract = greyContracts.find((c) => String(c.id) === String(issuance.grey_conversion_contract_id));
            if (contractDisplay && contract) {
                contractDisplay.value = contract.lov_label ?? contract.contract_code ?? '';
            }
            updateContractSummary(contract);

            const tbody = form.querySelector('[data-yarn-line-rows]');
            if (!tbody || !issuance.lines?.length) {
                rebuildYarnSelects(true);
                return;
            }
            tbody.querySelectorAll('[data-yarn-line-row]').forEach((row, idx) => {
                const line = issuance.lines[idx];
                if (!line) {
                    return;
                }
                const itemSelect = row.querySelector('[data-yarn-item-select]');
                if (itemSelect) {
                    itemSelect.value = String(line.item_id ?? '');
                }
                const bags = row.querySelector('[data-yarn-bags]');
                if (bags) {
                    bags.value = line.qty ?? '';
                }
                const cones = row.querySelector('[data-yarn-cones]');
                if (cones) {
                    cones.value = line.cones ?? '';
                }
                const rate = row.querySelector('[data-yarn-rate]');
                if (rate) {
                    rate.value = line.rate ?? '';
                    rate.readOnly = true;
                }
                recalcLineRow(row);
            });
        });

        form.querySelectorAll('[data-yarn-item-select]').forEach((select) => {
            select.addEventListener('change', () => {
                const row = select.closest('[data-yarn-line-row]');
                if (!row) {
                    return;
                }
                const payload = parsePayload(select.selectedOptions[0]);
                const item = payload.item_id
                    ? payload
                    : globalItems.find((r) => String(r.id) === String(select.value));
                applyItemToRow(row, item, {
                    autoFillBags: isIssuance,
                    autoFillCones: false,
                    forceRate: isIssuance || isReturn || isTransfer,
                });
            });
        });

        if (greyContractSelect?.value) {
            greyContractSelect.dispatchEvent(new Event('change'));
        }
        if (fromGreySelect?.dataset.initialValue) {
            fromGreySelect.value = fromGreySelect.dataset.initialValue;
        }
        if (fromGreySelect?.value) {
            fromGreySelect.dispatchEvent(new Event('change'));
        }
        accountSelect?.dispatchEvent(new Event('change'));
        fromAccountSelect?.dispatchEvent(new Event('change'));
        toAccountSelect?.dispatchEvent(new Event('change'));
    });
}
