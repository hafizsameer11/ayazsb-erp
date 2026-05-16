@extends('layouts.erp')

@section('title', 'Accounts opening')

@section('content')
    @php
        $editingOpening = $editingOpening ?? null;
        $cancelUrl = route('erp.accounts.opening', \App\Support\RecordHistory::historyQuery(request()));
    @endphp
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            ACCNTS_0004 — Accounts opening
        </div>
        <div class="p-3">
            <form
                class="mb-3 grid gap-2 border border-slate-400 bg-[#f7f7f7] p-2 md:grid-cols-6"
                method="post"
                data-erp-ajax-save
                @if($editingOpening) data-erp-editing="1" @endif
                action="{{ $editingOpening ? route('erp.accounts.opening.update', $editingOpening) : route('erp.accounts.opening.store') }}"
            >
                @csrf
                <div data-erp-form-feedback class="hidden" aria-live="polite"></div>
                @if ($editingOpening)
                    @method('PATCH')
                @endif
                @if ($editingOpening)
                    @include('erp.partials.erp-editing-banner', [
                        'editingLabel' => 'Opening #' . $editingOpening->id,
                        'cancelUrl' => $cancelUrl,
                    ])
                @endif
                <label class="erp-field md:col-span-6"><span class="erp-label">Voucher date</span><x-erp-date-input name="voucher_date" :value="old('voucher_date', $editingOpening?->voucher_date)" :required="true" /></label>
                <label class="erp-field md:col-span-3"><span class="erp-label">Financial year</span>
                    <select class="erp-input" name="financial_year_id">
                        @foreach(($financialYears ?? []) as $fy)
                            <option value="{{ $fy->id }}" @selected((string) old('financial_year_id', $editingOpening?->financial_year_id) === (string) $fy->id)>{{ $fy->year_code }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field"><span class="erp-label">Debit</span><input class="erp-input" type="number" step="0.01" name="debit" value="{{ old('debit', $editingOpening?->debit ?? 0) }}"></label>
                <label class="erp-field"><span class="erp-label">Credit</span><input class="erp-input" type="number" step="0.01" name="credit" value="{{ old('credit', $editingOpening?->credit ?? 0) }}"></label>
                <div class="flex items-end md:col-span-6">
                    <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-3 py-1 text-xs font-semibold hover:bg-white">
                        {{ $editingOpening ? 'Update opening' : 'Add opening' }}
                    </button>
                </div>
                <label class="erp-field md:col-span-6">
                    <span class="erp-label">Account</span>
                    <select class="js-account-search" name="account_id" autocomplete="off" required>
                        @include('erp.accounts.partials.account-select-options', ['selectedAccountId' => old('account_id', $editingOpening?->account_id)])
                    </select>
                </label>
                <label class="erp-field md:col-span-6"><span class="erp-label">Narration</span><input class="erp-input" type="text" name="narration" value="{{ old('narration', $editingOpening?->narration ?? 'Opening balance') }}"></label>
            </form>
            @include('erp.partials.records-history', [
                'historyType' => 'opening',
                'historyTitle' => 'Saved opening entries',
                'historyEmpty' => 'No opening entries yet. Add an opening above; entries will list here grouped by voucher date.',
                'recordsForDay' => $recordsForDay ?? collect(),
                'historyDate' => $historyDate ?? null,
                'historyNav' => $historyNav ?? [],
            ])
            <div class="mt-3 grid gap-2 border border-slate-400 bg-[#f0f0f0] p-2 md:grid-cols-2">
                <label class="flex flex-col gap-0.5 text-[11px] font-medium text-slate-700">
                    Account name
                    <input class="erp-input" type="text" readonly placeholder="From account code">
                </label>
                <label class="flex flex-col gap-0.5 text-[11px] font-medium text-slate-700">
                    Amount difference
                    <input class="erp-input text-right font-mono" type="text" readonly value="0.00">
                </label>
            </div>
            <div class="mt-2 flex justify-end gap-4 border-t border-slate-300 pt-2 text-[12px] font-mono">
                <span>Debit total: <input class="erp-input ml-1 w-28 text-right" type="text" readonly value="0.00"></span>
                <span>Credit total: <input class="erp-input ml-1 w-28 text-right" type="text" readonly value="0.00"></span>
            </div>
        </div>
    </div>
@endsection
