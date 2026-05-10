@extends('layouts.erp')

@section('title', 'Chart of accounts')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            ACCNTS_0003 — Chart of accounts (COA)
        </div>
        @include('erp.partials.erp-form-toolbar')

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

        <form class="grid gap-2 border-b border-slate-300 p-3 md:grid-cols-2 lg:grid-cols-4" action="{{ route('erp.accounts.coa.store') }}" method="post">
            @csrf
            <input type="hidden" name="level" id="coa-level" value="{{ old('level', 'head') }}">

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
                <input class="erp-input" type="text" name="name" value="{{ old('name') }}" required autocomplete="off">
            </label>

            <div class="flex items-end md:col-span-2 lg:col-span-2">
                <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-3 py-1 text-xs font-semibold hover:bg-white">Save</button>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $tabKey === 'head' ? 2 : 3 }}" class="border border-slate-300 px-2 py-2 text-slate-500">
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

            document.addEventListener('DOMContentLoaded', () => {
                const initialLevel = @json(old('level', 'head'));
                const initialParent = @json(old('parent_id'));
                const btn = document.querySelector(`.coa-tab[data-coa-tab="${initialLevel}"]`);
                (btn ?? document.querySelector('.coa-tab[data-coa-tab="head"]')).click();
                if (initialParent && initialLevel !== 'head' && parentSelect) {
                    parentSelect.value = String(initialParent);
                    parentSelect.dispatchEvent(new Event('change'));
                }
            });
        </script>
    @endpush
@endsection
