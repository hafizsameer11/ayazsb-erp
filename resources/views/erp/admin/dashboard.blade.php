@extends('layouts.erp')

@section('title', 'Access Management')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            Access Management Dashboard
        </div>
        <div class="grid gap-3 p-3 md:grid-cols-2">
            @allowed('admin.users.view')
                <a href="{{ route('erp.admin.users') }}" class="rounded border border-slate-400 bg-[#f5f5f5] p-3 hover:bg-sky-50">
                    <div class="text-sm font-semibold text-slate-800">Users & Roles</div>
                    <div class="text-xs text-slate-600">Assign and manage role memberships for users.</div>
                </a>
            @endallowed
            @allowed('admin.roles.view')
                <a href="{{ route('erp.admin.roles') }}" class="rounded border border-slate-400 bg-[#f5f5f5] p-3 hover:bg-sky-50">
                    <div class="text-sm font-semibold text-slate-800">Roles & Permissions</div>
                    <div class="text-xs text-slate-600">Manage roles and action-level permission matrix.</div>
                </a>
            @endallowed
        </div>
    </div>
@endsection

