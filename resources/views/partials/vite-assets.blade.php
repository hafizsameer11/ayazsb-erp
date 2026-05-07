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

@if ($cssFile)
    <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
@endif

@if ($jsFile)
    <script type="module" src="{{ asset('build/' . $jsFile) }}"></script>
@endif
