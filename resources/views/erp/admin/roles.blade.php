@extends('layouts.erp')

@section('title', 'Roles & Permissions')

@section('content')
    <div class="space-y-3">
        <div class="erp-panel border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
                Create Role
            </div>
            @allowed('admin.roles.create')
                <form method="post" action="{{ route('erp.admin.roles.store') }}" class="grid gap-2 p-3 md:grid-cols-4">
                    @csrf
                    <label class="erp-field"><span class="erp-label">Name</span><input class="erp-input" type="text" name="name" required></label>
                    <label class="erp-field"><span class="erp-label">Slug</span><input class="erp-input" type="text" name="slug" required></label>
                    <label class="erp-field md:col-span-2"><span class="erp-label">Description</span><input class="erp-input" type="text" name="description"></label>
                    <div class="md:col-span-4">
                        <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-3 py-1 text-xs font-semibold hover:bg-white">Create role</button>
                    </div>
                </form>
            @else
                <div class="p-3 text-sm text-slate-600">You do not have permission to create roles.</div>
            @endallowed
        </div>

        <div class="erp-panel border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
                Roles Matrix
            </div>
            <div class="p-3 space-y-4">
                @foreach ($roles as $role)
                    <section class="rounded border border-slate-400 bg-[#f8f8f8] p-3">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $role->name }}</div>
                                <div class="text-xs text-slate-600">{{ $role->slug }} — {{ $role->description }}</div>
                            </div>
                            <div class="flex gap-2">
                                @allowed('admin.roles.edit')
                                    <form method="post" action="{{ route('erp.admin.roles.update', $role) }}" class="flex flex-wrap gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" class="erp-input w-36" name="name" value="{{ $role->name }}" required>
                                        <input type="text" class="erp-input w-52" name="description" value="{{ $role->description }}">
                                        <button type="submit" class="rounded border border-slate-500 bg-slate-200 px-2 py-1 text-xs font-semibold hover:bg-white">Rename</button>
                                    </form>
                                @endallowed
                                @allowed('admin.roles.delete')
                                    @if ($role->slug !== 'super-admin')
                                        <form method="post" action="{{ route('erp.admin.roles.delete', $role) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded border border-red-300 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button>
                                        </form>
                                    @endif
                                @endallowed
                            </div>
                        </div>
                        @allowed('admin.roles.edit')
                            <form method="post" action="{{ route('erp.admin.roles.permissions', $role) }}">
                                @csrf
                                <div class="grid gap-2 md:grid-cols-3 lg:grid-cols-4">
                                    @foreach ($permissions as $permission)
                                        <label class="inline-flex items-center gap-1 rounded border border-slate-300 bg-white px-2 py-1 text-xs">
                                            <input
                                                type="checkbox"
                                                name="permission_ids[]"
                                                value="{{ $permission->id }}"
                                                @checked($role->permissions->contains('id', $permission->id))
                                            >
                                            <span class="font-mono">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="mt-2">
                                    <button type="submit" class="rounded border border-slate-500 bg-slate-200 px-3 py-1 text-xs font-semibold hover:bg-white">
                                        Save permissions
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-xs text-slate-600">You do not have permission to edit role permissions.</div>
                        @endallowed
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection

