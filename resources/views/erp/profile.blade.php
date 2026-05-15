@extends('layouts.erp')

@section('title', 'User profile')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            User profile
        </div>
        <div class="grid gap-3 p-4 md:grid-cols-2">
            <label class="erp-field">
                <span class="erp-label">Name</span>
                <div class="erp-input border border-slate-400 bg-[#f0f0f0] text-slate-800">{{ $user?->name ?? '—' }}</div>
            </label>
            <label class="erp-field">
                <span class="erp-label">Email</span>
                <div class="erp-input border border-slate-400 bg-[#f0f0f0] font-mono text-slate-800">{{ $user?->email ?? '—' }}</div>
            </label>
            <label class="erp-field md:col-span-2">
                <span class="erp-label">Roles</span>
                <div class="erp-input border border-slate-400 bg-[#f0f0f0] text-slate-800">
                    {{ $user?->roles->pluck('name')->join(', ') ?: '—' }}
                </div>
            </label>
        </div>
    </div>
@endsection
