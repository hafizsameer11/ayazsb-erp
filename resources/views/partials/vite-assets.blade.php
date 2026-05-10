@php
    $manifestPath = public_path('build/manifest.json');
    $manifest = [];

    if (is_file($manifestPath)) {
        $decoded = json_decode(file_get_contents($manifestPath), true);
        if (is_array($decoded)) {
            $manifest = $decoded;
        }
    }

    $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
    $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
@endphp

{{-- Tom Select base styles first; app.css (below) adds ERP overrides --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.6.2/dist/css/tom-select.default.min.css" crossorigin="anonymous">

@if ($cssFile)
    <link rel="stylesheet" href="{{ (bool) env('FORCE_HTTPS', false) ? secure_asset('build/' . $cssFile) : asset('build/' . $cssFile) }}">
@endif

@if ($jsFile)
    <script type="module" src="{{ (bool) env('FORCE_HTTPS', false) ? secure_asset('build/' . $jsFile) : asset('build/' . $jsFile) }}"></script>
@endif
