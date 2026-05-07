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
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        <form class="grid gap-2 border-b border-slate-300 p-3 md:grid-cols-4" action="{{ route('erp.accounts.coa.store') }}" method="post">
            @csrf
            <label class="erp-field">
                <span class="erp-label">Level</span>
                <select class="erp-input" name="level">
                    <option value="head">Head</option>
                    <option value="control">Control</option>
                    <option value="ledger">Ledger</option>
                    <option value="sub_ledger">Sub Ledger</option>
                </select>
            </label>
            <label class="erp-field"><span class="erp-label">Code</span><input class="erp-input" type="text" name="code" required></label>
            <label class="erp-field"><span class="erp-label">Name</span><input class="erp-input" type="text" name="name" required></label>
            <div class="flex items-end">
                <button class="rounded border border-slate-600 bg-slate-200 px-3 py-1 text-xs font-semibold hover:bg-white">Save Account</button>
            </div>
        </form>

        <form class="p-3" action="#" method="get" onsubmit="return false;">
            @foreach (['head', 'control', 'ledger', 'sub_ledger'] as $tab)
                <div class="coa-panel {{ $tab === 'head' ? '' : 'hidden' }}" data-coa-panel="{{ $tab }}">
                    <div class="overflow-x-auto border border-slate-400">
                        <table class="w-full min-w-[480px] border-collapse text-left text-[12px]">
                            <thead>
                                <tr class="bg-[#d8d8d8]">
                                    <th class="border border-slate-400 px-1 py-1 font-semibold">{{ str_replace('_', ' ', ucfirst($tab)) }} code</th>
                                    <th class="border border-slate-400 px-1 py-1 font-semibold">Description</th>
                                    <th class="w-20 border border-slate-400 px-1 py-1 font-semibold"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 8; $i++)
                                    <tr>
                                        <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="code[]" autocomplete="off"></td>
                                        <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="desc[]" autocomplete="off"></td>
                                        <td class="border border-slate-300 p-0 text-center"><button type="button" class="m-0.5 rounded border border-slate-500 bg-slate-200 px-2 py-0.5 text-[11px]">Save</button></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </form>
        <div class="border-t border-slate-300 p-3">
            <div class="mb-2 text-[11px] font-semibold uppercase text-slate-600">Saved accounts</div>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full border-collapse text-[12px]">
                    <thead><tr class="bg-[#d8d8d8]"><th class="border border-slate-400 px-1 py-1">Level</th><th class="border border-slate-400 px-1 py-1">Code</th><th class="border border-slate-400 px-1 py-1">Name</th></tr></thead>
                    <tbody>
                        @forelse(($accounts ?? []) as $acc)
                            <tr><td class="border border-slate-300 px-1 py-1">{{ strtoupper($acc->level) }}</td><td class="border border-slate-300 px-1 py-1">{{ $acc->code }}</td><td class="border border-slate-300 px-1 py-1">{{ $acc->name }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="border border-slate-300 px-2 py-2 text-slate-500">No accounts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.coa-tab').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const key = btn.dataset.coaTab;
                    document.querySelectorAll('.coa-tab').forEach((b) => {
                        const on = b.dataset.coaTab === key;
                        b.classList.toggle('bg-white', on);
                        b.classList.toggle('text-slate-900', on);
                        b.classList.toggle('bg-[#d8d8d8]', !on);
                        b.classList.toggle('text-slate-600', !on);
                    });
                    document.querySelectorAll('.coa-panel').forEach((p) => {
                        p.classList.toggle('hidden', p.dataset.coaPanel !== key);
                    });
                });
            });
        </script>
    @endpush
@endsection
