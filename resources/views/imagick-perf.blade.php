@php
    if (! function_exists('tr_filesize')) {
        function tr_filesize($bytes, int $decimals = 2): string {
            if ($bytes <= 0) return '0 B';
            $size = ['B','kB','MB','GB','TB'];
            $factor = (int) floor((strlen((string) $bytes) - 1) / 3);
            return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . ($size[$factor] ?? '');
        }
    }
    $totals = $results['totals'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TestRabbit — ImageMagick performance</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Nunito', sans-serif; }</style>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="relative flex justify-center min-h-screen bg-gray-100 sm:items-start py-4">
    <div class="w-2/3 mx-auto pt-4">

        <div class="flex justify-between items-baseline mb-4">
            <h1 class="text-2xl font-bold">ImageMagick performance</h1>
            <a class="border-dotted hover:border-solid border-b border-gray-600" href="/">&larr; back to tests</a>
        </div>

        {{-- Headline --}}
        <div class="bg-white mb-4 p-6 rounded-lg">
            <div class="text-5xl font-bold">{{ number_format($totals['msPerRendition'], 1) }} <span class="text-2xl font-normal text-gray-500">ms / rendition</span></div>
            <div class="mt-2 text-gray-600">
                {{ $totals['renditions'] }} renditions
                @if ($totals['failed'] > 0)
                    <span class="text-red-600">({{ $totals['failed'] }} failed)</span>
                @endif
                in {{ number_format($totals['totalMs'], 1) }} ms total
                · {{ count(\App\Support\ImagickPerfRunner::SOURCES) }} sources × {{ $count }} widths
            </div>
        </div>

        {{-- Controls --}}
        <div class="bg-white mb-4 p-4 rounded-lg">
            <span class="font-semibold mr-2">Renditions per source:</span>
            @foreach ([4, 8, 16, 32, 64] as $option)
                <a href="/imagick-perf?count={{ $option }}"
                   class="inline-block px-3 py-1 mr-1 rounded {{ $count === $option ? 'bg-gray-800 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">{{ $option }}</a>
            @endforeach
            <div class="text-sm text-gray-500 mt-2">Widths: {{ implode(', ', $results['widths']) }} px (JPEG q{{ \App\Support\ImagickPerfRunner::QUALITY }}, downscale only)</div>
        </div>

        {{-- Per-source results --}}
        <div class="bg-white mb-4 p-4 rounded-lg">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-300 text-left">
                        <th class="py-2">Source</th>
                        <th class="py-2 text-right">Size</th>
                        <th class="py-2 text-right">Renditions</th>
                        <th class="py-2 text-right">Total ms</th>
                        <th class="py-2 text-right">ms / rendition</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results['sources'] as $source)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-mono">{{ $source['file'] }}</td>
                            <td class="py-2 text-right">{{ $source['exists'] ? tr_filesize($source['bytes']) : '—' }}</td>
                            <td class="py-2 text-right">
                                {{ $source['renditions'] }}
                                @if ($source['failed'] > 0)<span class="text-red-600">/ {{ $source['failed'] }} failed</span>@endif
                            </td>
                            <td class="py-2 text-right">{{ number_format($source['totalMs'], 1) }}</td>
                            <td class="py-2 text-right font-semibold">{{ number_format($source['msPerRendition'], 1) }}</td>
                        </tr>
                        @if ($source['error'])
                            <tr><td colspan="5" class="pb-2 text-xs text-red-600">{{ $source['error'] }}</td></tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Context --}}
        <div class="bg-white mb-4 p-4 rounded-lg text-sm">
            <h2 class="text-lg font-bold mb-2">Environment</h2>
            <table class="w-full font-mono">
                <tbody>
                    <tr class="border-b border-gray-100"><td class="py-1 pr-4 text-gray-700">Platform</td><td>{{ $platform }}</td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1 pr-4 text-gray-700">Imagick</td><td>{{ $imagickVersion }}</td></tr>
                    @foreach ($limits as $name => $value)
                        <tr class="border-b border-gray-100"><td class="py-1 pr-4 text-gray-700">{{ $name }}</td><td>{{ $value }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>
