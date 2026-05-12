<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name', 'ERP') }}</title>
    @include('partials.vite-assets')
</head>
<body class="erp-body flex min-h-screen items-center justify-center p-4">
    <div class="w-full max-w-md border border-slate-600 bg-[#d8d8d8] p-4 shadow-lg">
        <div class="mb-3 border border-slate-500 bg-gradient-to-b from-slate-700 to-slate-800 px-3 py-2 text-sm font-semibold text-white">
            {{ config('app.name', 'ERP') }} — Login
        </div>
        <form method="post" action="{{ route('login.store') }}" class="space-y-3">
            @csrf
            <label class="erp-field">
                <span class="erp-label">Email or username</span>
                <input type="text" name="login" value="{{ old('login') }}" class="erp-input" required autofocus autocomplete="username">
            </label>
            <label class="erp-field">
                <span class="erp-label">Password</span>
                <input type="password" name="password" class="erp-input" required autocomplete="current-password">
            </label>
            <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4">
                Remember me
            </label>
            @if ($errors->any())
                <div class="border border-red-300 bg-red-50 px-2 py-1 text-xs text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
            <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-sm font-semibold text-slate-900 shadow-sm hover:bg-white">
                Sign in
            </button>
        </form>
    </div>
</body>
</html>

