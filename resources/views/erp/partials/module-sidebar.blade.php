@php
    $modules = [
        ['key' => 'profile', 'label' => 'User profile', 'route' => null],
        ['key' => 'accounts', 'label' => 'Accounts & finance', 'route' => 'erp.accounts.dashboard', 'permission' => 'accounts.dashboard.view'],
        ['key' => 'yarn', 'label' => 'Yarn management', 'route' => 'erp.yarn.dashboard', 'permission' => 'yarn.dashboard.view'],
        ['key' => 'grey', 'label' => 'Grey management', 'route' => 'erp.grey.dashboard', 'permission' => 'grey.dashboard.view'],
        ['key' => 'reports', 'label' => 'Reports', 'route' => 'erp.reports.dashboard', 'permission' => 'reports.dashboard.view'],
        ['key' => 'docs', 'label' => 'ERP docs', 'route' => 'erp.docs', 'permission' => 'accounts.dashboard.view'],
        ['key' => 'admin', 'label' => 'Access management', 'route' => 'erp.admin.dashboard', 'permission' => 'admin.dashboard.view'],
    ];
@endphp
<nav class="flex w-48 shrink-0 flex-col gap-1 border-r border-slate-400 bg-[#d0d0d0] p-2" aria-label="Modules">
    @foreach ($modules as $mod)
        @if ($mod['route'] && isset($mod['permission']))
            @allowed($mod['permission'])
            <a
                href="{{ route($mod['route']) }}"
                class="flex items-center gap-1 rounded border px-2 py-2 text-left text-[11px] font-semibold uppercase tracking-wide shadow-sm {{ ($activeModule ?? '') === $mod['key'] ? 'border-amber-600 bg-amber-100 text-slate-900' : 'border-slate-500 bg-gradient-to-b from-[#e8e8e8] to-[#c8c8c8] text-slate-800 hover:from-white hover:to-[#dedede]' }}"
            >
                <span class="text-slate-500">&raquo;</span> {{ $mod['label'] }}
            </a>
            @endallowed
        @else
            <span class="flex cursor-not-allowed items-center gap-1 rounded border border-slate-400 bg-[#c0c0c0] px-2 py-2 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 opacity-80">
                <span class="text-slate-400">&raquo;</span> {{ $mod['label'] }}
            </span>
        @endif
    @endforeach
</nav>
