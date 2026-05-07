<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Accounts & Finance') — {{ config('app.name', 'ERP') }}</title>
    @include('partials.vite-assets')
</head>
<body class="erp-body min-h-screen text-[13px] text-slate-900 antialiased">
    <header class="erp-titlebar border-b border-slate-600 bg-gradient-to-b from-slate-700 to-slate-800 px-3 py-1.5 text-white shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div class="font-semibold tracking-tight">{{ config('app.name', 'ERP') }}</div>
            <div class="flex items-center gap-2 text-[11px] text-slate-300">
                <span class="hidden sm:block">Enterprise Resource Planning</span>
                <span class="hidden rounded border border-slate-500 bg-slate-800 px-1.5 py-0.5 sm:inline-block">
                    {{ auth()->user()?->name ?? 'Guest' }}
                </span>
                @auth
                    <form action="{{ route('logout') }}" method="post" class="inline">
                        @csrf
                        <button type="submit" class="rounded border border-slate-500 bg-slate-800 px-1.5 py-0.5 text-[11px] text-slate-200 hover:bg-slate-700">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <div class="erp-statusbar border-b border-sky-800 bg-sky-700 px-3 py-1 text-[11px] text-white">
        <span class="opacity-90">DEVELOPED BY IT</span>
        <span class="mx-2 text-sky-200">/</span>
        <span>DB User: <span class="font-mono text-white">{{ auth()->user()?->email ?? 'guest' }}</span></span>
        <span class="mx-2 text-sky-200">/</span>
        <span>{{ now()->format('d-m-Y h:i A') }}</span>
    </div>

    <div class="flex min-h-[calc(100vh-4.5rem)]">
        @include('erp.partials.module-sidebar', ['activeModule' => $activeModule ?? 'accounts'])

        <main class="flex-1 overflow-auto bg-[#c8c8c8] p-3">
            <div class="mb-2 border border-slate-500 bg-[#dfdfdf] px-2 py-1 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-1 text-[11px] text-slate-700">
                        @foreach (($breadcrumbs ?? []) as $crumb)
                            @if (!empty($crumb['route']))
                                <a href="{{ route($crumb['route']) }}" class="rounded border border-slate-400 bg-white px-1.5 py-0.5 hover:bg-sky-50">
                                    {{ $crumb['label'] }}
                                </a>
                            @else
                                <span class="rounded border border-slate-400 bg-[#f7f7f7] px-1.5 py-0.5">{{ $crumb['label'] }}</span>
                            @endif
                            @if (! $loop->last)
                                <span class="text-slate-500">&raquo;</span>
                            @endif
                        @endforeach
                    </div>
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                        {{ $pageTitle ?? 'ERP' }}
                    </div>
                </div>
            </div>
            @if (session('status'))
                <div class="mb-2 border border-green-300 bg-green-50 px-2 py-1 text-xs text-green-700">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-2 border border-red-300 bg-red-50 px-2 py-1 text-xs text-red-700">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-2 border border-red-300 bg-red-50 px-2 py-1 text-xs text-red-700">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </main>

        <aside class="hidden w-52 shrink-0 border-l border-slate-400 bg-[#d4d4d4] p-2 lg:block">
            <div class="mb-2 text-[11px] font-semibold uppercase text-slate-600">Bookmark</div>
            <ul class="space-y-1 text-[12px] text-slate-700">
                <li class="rounded border border-slate-400 bg-white px-2 py-1">Grey Sale</li>
                <li class="rounded border border-slate-400 bg-white px-2 py-1">Yarn (Gain / Shortage)</li>
            </ul>
            <div class="mt-4 border border-slate-400 bg-white p-2 text-center text-[11px] text-slate-600">
                <div class="mx-auto mb-1 flex h-12 w-12 items-center justify-center rounded-full bg-slate-300 text-slate-600">User</div>
                <div class="font-medium text-slate-800">Demo User</div>
            </div>
        </aside>
    </div>

    <footer class="border-t border-slate-500 bg-[#b8b8b8] px-3 py-2">
        <a href="{{ route('erp.accounts.dashboard') }}" class="inline-flex items-center rounded border border-slate-500 bg-slate-200 px-3 py-1 text-[12px] font-medium text-slate-800 shadow-sm hover:bg-slate-100">
            Main menu
        </a>
    </footer>
    @stack('scripts')
</body>
</html>
