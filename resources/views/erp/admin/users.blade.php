@extends('layouts.erp')

@section('title', 'Users & Roles')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            Users & Roles Assignment
        </div>
        <div class="p-3">
            <div class="mb-3 flex justify-end">
                @allowed('admin.roles.view')
                    <a href="{{ route('erp.admin.roles') }}" class="rounded border border-slate-500 bg-slate-200 px-3 py-1 text-xs font-semibold hover:bg-white">
                        Manage roles
                    </a>
                @endallowed
            </div>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full min-w-[760px] border-collapse text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#d8d8d8]">
                            <th class="border border-slate-400 px-2 py-1">User</th>
                            <th class="border border-slate-400 px-2 py-1">Username</th>
                            <th class="border border-slate-400 px-2 py-1">Email</th>
                            <th class="border border-slate-400 px-2 py-1">Roles</th>
                            <th class="w-24 border border-slate-400 px-2 py-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $rowUser)
                            <tr>
                                <td class="border border-slate-300 px-2 py-1">{{ $rowUser->name }}</td>
                                <td class="border border-slate-300 px-2 py-1 font-mono">{{ $rowUser->username ?? '-' }}</td>
                                <td class="border border-slate-300 px-2 py-1 font-mono">{{ $rowUser->email }}</td>
                                <td class="border border-slate-300 px-2 py-1">
                                    @allowed('admin.users.edit')
                                        <form method="post" action="{{ route('erp.admin.users.update-roles', $rowUser) }}" class="flex flex-wrap gap-2">
                                            @csrf
                                            @foreach ($roles as $role)
                                                <label class="inline-flex items-center gap-1 rounded border border-slate-300 bg-white px-2 py-1">
                                                    <input
                                                        type="checkbox"
                                                        name="role_ids[]"
                                                        value="{{ $role->id }}"
                                                        @checked($rowUser->roles->contains('id', $role->id))
                                                    >
                                                    <span class="text-xs">{{ $role->name }}</span>
                                                </label>
                                            @endforeach
                                            <button type="submit" class="rounded border border-slate-500 bg-slate-200 px-2 py-1 text-xs font-semibold hover:bg-white">
                                                Update
                                            </button>
                                        </form>
                                    @else
                                        <div class="text-xs text-slate-600">{{ $rowUser->roles->pluck('name')->implode(', ') ?: 'No roles assigned' }}</div>
                                    @endallowed
                                </td>
                                <td class="border border-slate-300 px-2 py-1 text-xs text-slate-500">
                                    {{ $rowUser->roles->count() }} role(s)
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

