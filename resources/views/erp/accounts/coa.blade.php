@extends('layouts.erp')

@section('title', 'Chart of accounts')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            ACCNTS_0003 — Chart of accounts (COA)
        </div>
        <div class="border-b border-slate-300 px-2">
            <nav class="flex gap-1 text-[11px]" aria-label="COA levels">
                @foreach (['head' => 'Head', 'control' => 'Control', 'ledger' => 'Ledger', 'sub_ledger' => 'Sub ledger'] as $key => $label)
                    <button
                        type="button"
                        class="coa-tab -mb-px border border-b-0 border-slate-400 px-3 py-1.5 font-semibold {{ $key === 'head' ? 'bg-white text-slate-900' : 'bg-[#d8d8d8] text-slate-600' }}"
                        data-coa-tab="{{ $key }}"
                        data-coa-label="{{ $label }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        <form
            id="coa-form"
            class="grid gap-2 border-b border-slate-300 p-3 md:grid-cols-2 lg:grid-cols-4"
            action="{{ route('erp.accounts.coa.store') }}"
            method="post"
        >
            @csrf
            <input type="hidden" name="level" id="coa-level" value="{{ old('level', 'head') }}">
            <input type="hidden" name="account_id" id="coa-account-id" value="{{ old('account_id') }}">

            <label id="coa-code-wrap" class="erp-field hidden">
                <span class="erp-label">Code</span>
                <input class="erp-input border border-slate-400 bg-[#f0f0f0]" type="text" id="coa-code-display" readonly>
            </label>

            <label class="erp-field">
                <span class="erp-label">Level</span>
                <div id="coa-level-static" class="erp-input border border-slate-400 bg-[#f0f0f0] text-slate-700">Head</div>
            </label>

            <div id="coa-parent-wrap" class="erp-field hidden">
                <label class="flex w-full flex-col gap-0">
                    <span class="erp-label">Parent</span>
                    <select class="erp-input" name="parent_id" id="coa-parent"></select>
                </label>
            </div>

            <label class="erp-field md:col-span-2 lg:col-span-2">
                <span class="erp-label">Name</span>
                <input class="erp-input" type="text" name="name" id="coa-name" value="{{ old('name') }}" required autocomplete="off">
            </label>

            <label id="coa-active-wrap" class="erp-field hidden items-end">
                <span class="erp-label">Active</span>
                <label class="inline-flex items-center gap-1 text-[11px]">
                    <input type="checkbox" name="is_active" id="coa-is-active" value="1" checked class="h-3.5 w-3.5">
                    Yes
                </label>
            </label>

            <div class="flex flex-wrap items-end gap-2 md:col-span-2 lg:col-span-4">
                <button type="submit" id="coa-save-btn" class="rounded border border-slate-600 bg-slate-200 px-3 py-1 text-xs font-semibold hover:bg-white">Save</button>
                <button type="button" id="coa-clear-btn" class="rounded border border-slate-500 bg-white px-3 py-1 text-xs font-semibold hover:bg-slate-50">Clear</button>
            </div>
        </form>

        <div class="border-t border-slate-300 p-3">
            <div class="mb-2 text-[11px] font-semibold uppercase text-slate-600">Saved accounts</div>
            @foreach (['head' => 'Head', 'control' => 'Control', 'ledger' => 'Ledger', 'sub_ledger' => 'Sub ledger'] as $tabKey => $tabLabel)
                @php($savedForTab = ($accounts ?? collect())->where('level', $tabKey)->values())
                <div
                    class="coa-saved-panel overflow-x-auto border border-slate-400 {{ $tabKey === 'head' ? '' : 'hidden' }}"
                    data-coa-saved="{{ $tabKey }}"
                >
                    <table class="w-full border-collapse text-[12px]">
                        <thead>
                            <tr class="bg-[#d8d8d8]">
                                <th class="border border-slate-400 px-1 py-1">Code</th>
                                @if ($tabKey !== 'head')
                                    <th class="border border-slate-400 px-1 py-1">Parent code</th>
                                @endif
                                <th class="border border-slate-400 px-1 py-1">Name</th>
                                <th class="w-24 border border-slate-400 px-1 py-1 text-center">Active</th>
                                @allowed('accounts.coa.edit')
                                    <th class="w-16 border border-slate-400 px-1 py-1"></th>
                                @endallowed
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($savedForTab as $acc)
                                <tr>
                                    <td class="border border-slate-300 px-1 py-1">{{ $acc->code }}</td>
                                    @if ($tabKey !== 'head')
                                        <td class="border border-slate-300 px-1 py-1">{{ $acc->parent?->code ?? '—' }}</td>
                                    @endif
                                    <td class="border border-slate-300 px-1 py-1">{{ $acc->name }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-center">{{ $acc->is_active ? 'Yes' : 'No' }}</td>
                                    @allowed('accounts.coa.edit')
                                        <td class="border border-slate-300 px-1 py-1 text-center">
                                            <button
                                                type="button"
                                                class="coa-edit-btn rounded border border-slate-500 bg-white px-2 py-0.5 text-[11px] hover:bg-sky-50"
                                                data-id="{{ $acc->id }}"
                                                data-code="{{ $acc->code }}"
                                                data-name="{{ $acc->name }}"
                                                data-level="{{ $acc->level }}"
                                                data-parent-id="{{ $acc->parent_id ?? '' }}"
                                                data-active="{{ $acc->is_active ? '1' : '0' }}"
                                            >Edit</button>
                                        </td>
                                    @endallowed
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($tabKey === 'head' ? 3 : 4) + (auth()->user()?->hasPermission('accounts.coa.edit') ? 1 : 0) }}" class="border border-slate-300 px-2 py-2 text-slate-500">
                                        No {{ strtolower($tabLabel) }} accounts yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
        <script>
            const coaParents = @json($coaParentsJson ?? ['control' => [], 'ledger' => [], 'sub_ledger' => []]);

            const levelInput = document.getElementById('coa-level');
            const levelStatic = document.getElementById('coa-level-static');
            const parentWrap = document.getElementById('coa-parent-wrap');
            const parentSelect = document.getElementById('coa-parent');

            function syncParentOptions(level) {
                const list = coaParents[level] || [];
                parentSelect.innerHTML = '';
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = list.length ? 'Select parent' : 'No accounts';
                parentSelect.appendChild(placeholder);
                list.forEach((p) => {
                    const opt = document.createElement('option');
                    opt.value = String(p.id);
                    opt.textContent = `${p.code} — ${p.name}`;
                    parentSelect.appendChild(opt);
                });
                parentSelect.value = '';
            }

            function applyLevel(level, label) {
                levelInput.value = level;
                levelStatic.textContent = label;
                const needsParent = level !== 'head';
                parentWrap.classList.toggle('hidden', !needsParent);
                if (needsParent) {
                    syncParentOptions(level);
                    const list = coaParents[level] || [];
                    parentSelect.disabled = list.length === 0;
                    parentSelect.required = list.length > 0;
                } else {
                    parentSelect.innerHTML = '';
                    parentSelect.disabled = true;
                    parentSelect.required = false;
                }
            }

            function showSavedForLevel(level) {
                document.querySelectorAll('.coa-saved-panel').forEach((p) => {
                    p.classList.toggle('hidden', p.dataset.coaSaved !== level);
                });
            }

            document.querySelectorAll('.coa-tab').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const key = btn.dataset.coaTab;
                    const label = btn.dataset.coaLabel || key;
                    document.querySelectorAll('.coa-tab').forEach((b) => {
                        const on = b.dataset.coaTab === key;
                        b.classList.toggle('bg-white', on);
                        b.classList.toggle('text-slate-900', on);
                        b.classList.toggle('bg-[#d8d8d8]', !on);
                        b.classList.toggle('text-slate-600', !on);
                    });
                    applyLevel(key, label);
                    showSavedForLevel(key);
                });
            });

            const coaForm = document.getElementById('coa-form');
            const coaAccountId = document.getElementById('coa-account-id');
            const coaName = document.getElementById('coa-name');
            const coaSaveBtn = document.getElementById('coa-save-btn');
            const coaClearBtn = document.getElementById('coa-clear-btn');
            const coaCodeWrap = document.getElementById('coa-code-wrap');
            const coaCodeDisplay = document.getElementById('coa-code-display');
            const coaActiveWrap = document.getElementById('coa-active-wrap');
            const coaIsActive = document.getElementById('coa-is-active');
            const coaStoreUrl = @json(route('erp.accounts.coa.store'));
            const coaUpdateBase = @json(url('/erp/accounts/coa'));
            const coaUpdateUrl = (id) => `${coaUpdateBase}/${id}`;
            let coaMethodInput = null;

            function ensureCoaMethodInput() {
                if (!coaMethodInput) {
                    coaMethodInput = document.createElement('input');
                    coaMethodInput.type = 'hidden';
                    coaMethodInput.name = '_method';
                    coaMethodInput.value = 'PATCH';
                    coaForm.appendChild(coaMethodInput);
                }
                return coaMethodInput;
            }

            function setCoaCreateMode() {
                coaForm.action = coaStoreUrl;
                coaMethodInput?.remove();
                coaMethodInput = null;
                coaAccountId.value = '';
                coaName.value = '';
                coaCodeWrap.classList.add('hidden');
                coaActiveWrap.classList.add('hidden');
                coaSaveBtn.textContent = 'Save';
                document.querySelectorAll('.coa-tab').forEach((b) => { b.disabled = false; });
                parentWrap.classList.remove('hidden');
            }

            function activateCoaTab(level) {
                const btn = document.querySelector(`.coa-tab[data-coa-tab="${level}"]`);
                if (btn) {
                    btn.click();
                }
            }

            function setCoaEditMode(data) {
                activateCoaTab(data.level);
                coaForm.action = coaUpdateUrl(data.id);
                ensureCoaMethodInput();
                coaAccountId.value = String(data.id);
                coaName.value = data.name;
                coaCodeDisplay.value = data.code;
                coaCodeWrap.classList.remove('hidden');
                coaActiveWrap.classList.remove('hidden');
                coaIsActive.checked = data.active === '1';
                coaSaveBtn.textContent = 'Update';
                document.querySelectorAll('.coa-tab').forEach((b) => { b.disabled = true; });

                if (data.level === 'head') {
                    parentWrap.classList.add('hidden');
                } else {
                    parentWrap.classList.remove('hidden');
                    syncParentOptions(data.level);
                    if (data.parentId) {
                        parentSelect.value = String(data.parentId);
                    }
                    parentSelect.disabled = false;
                    parentSelect.required = true;
                }

                coaForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            coaClearBtn?.addEventListener('click', () => setCoaCreateMode());

            document.querySelectorAll('.coa-edit-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    setCoaEditMode({
                        id: btn.dataset.id,
                        code: btn.dataset.code,
                        name: btn.dataset.name,
                        level: btn.dataset.level,
                        parentId: btn.dataset.parentId || '',
                        active: btn.dataset.active,
                    });
                });
            });

            document.addEventListener('DOMContentLoaded', () => {
                const initialLevel = @json(old('level', 'head'));
                const initialParent = @json(old('parent_id'));
                const btn = document.querySelector(`.coa-tab[data-coa-tab="${initialLevel}"]`);
                (btn ?? document.querySelector('.coa-tab[data-coa-tab="head"]')).click();
                if (initialParent && initialLevel !== 'head' && parentSelect) {
                    parentSelect.value = String(initialParent);
                    parentSelect.dispatchEvent(new Event('change'));
                }

                @if (old('account_id'))
                    setCoaEditMode({
                        id: @json(old('account_id')),
                        code: @json(old('code', '')),
                        name: @json(old('name', '')),
                        level: @json(old('level', 'head')),
                        active: @json(old('is_active', true) ? '1' : '0'),
                    });
                @endif
            });
        </script>
    @endpush
@endsection
