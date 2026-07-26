<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>Cortex</title>
    <link rel="stylesheet" href="{{ asset('vendor/cortex/app.css') }}{{ $assetVersion ? '?v='.$assetVersion : '' }}">
</head>
<body>
    <div id="cortex"></div>
    <script>window.CortexConfig = @json($cortexConfig);</script>
    <script type="module" src="{{ asset('vendor/cortex/app.js') }}{{ $assetVersion ? '?v='.$assetVersion : '' }}"></script>
</body>
</html>
