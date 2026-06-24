<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TestRabbit — ImageMagick performance</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body>
<div class="relative flex justify-center min-h-screen bg-gray-100 sm:items-start py-4">
    <div class="w-2/3 mx-auto pt-4"
         x-data="perfApp({ count: {{ $count }} })">

        <div class="flex justify-between items-baseline mb-4">
            <h1 class="text-2xl font-bold">ImageMagick performance</h1>
            <a class="border-dotted hover:border-solid border-b border-gray-600" href="/">&larr; back to tests</a>
        </div>

        {{-- Controls --}}
        <div class="bg-white mb-4 p-4 rounded-lg">
            <div class="flex items-center flex-wrap gap-2">
                <span class="font-semibold mr-1">Renditions per source:</span>
                @foreach ([4, 8, 16, 32, 64] as $option)
                    <button type="button"
                            @click="count = {{ $option }}"
                            :disabled="running"
                            :class="count === {{ $option }} ? 'bg-gray-800 text-white' : 'bg-gray-100 hover:bg-gray-200'"
                            class="px-3 py-1 rounded disabled:opacity-50">{{ $option }}</button>
                @endforeach

                <button type="button"
                        @click="run()"
                        :disabled="running"
                        class="ml-auto px-5 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-semibold disabled:opacity-50">
                    <span x-show="!running">Run benchmark</span>
                    <span x-show="running" x-cloak>Running…</span>
                </button>
            </div>
            <div class="text-sm text-gray-500 mt-2">
                4 sources × <span x-text="count"></span> renditions
                = <span x-text="4 * count"></span> JPEGs (q82, downscale only)
            </div>
        </div>

        {{-- Live timer while running --}}
        <div x-show="running" x-cloak class="bg-white mb-4 p-6 rounded-lg flex items-center gap-4">
            <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path>
            </svg>
            <div>
                <div class="text-4xl font-bold tabular-nums" x-text="(elapsed / 1000).toFixed(1) + ' s'"></div>
                <div class="text-gray-500 text-sm">transforming <span x-text="4 * count"></span> renditions…</div>
            </div>
        </div>

        {{-- Error --}}
        <div x-show="error" x-cloak class="bg-red-50 border border-red-200 text-red-700 mb-4 p-4 rounded-lg" x-text="error"></div>

        {{-- Results --}}
        <template x-if="results">
            <div>
                {{-- Headline --}}
                <div class="bg-white mb-4 p-6 rounded-lg">
                    <div class="text-5xl font-bold tabular-nums">
                        <span x-text="fmt(results.totals.msPerRendition, 1)"></span>
                        <span class="text-2xl font-normal text-gray-500">ms / rendition</span>
                    </div>
                    <div class="mt-2 text-gray-600">
                        <span x-text="results.totals.renditions"></span> renditions
                        <span x-show="results.totals.failed > 0" class="text-red-600">
                            (<span x-text="results.totals.failed"></span> failed)</span>
                        in <span x-text="fmt(results.totals.totalMs, 1)"></span> ms total
                        · widths <span x-text="results.widths.join(', ')"></span> px
                    </div>
                </div>

                {{-- Per-source table --}}
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
                            <template x-for="s in results.sources" :key="s.file">
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 font-mono" x-text="s.file"></td>
                                    <td class="py-2 text-right" x-text="s.exists ? fmtBytes(s.bytes) : '—'"></td>
                                    <td class="py-2 text-right">
                                        <span x-text="s.renditions"></span>
                                        <span x-show="s.failed > 0" class="text-red-600"
                                              x-text="'/ ' + s.failed + ' failed'"></span>
                                    </td>
                                    <td class="py-2 text-right" x-text="fmt(s.totalMs, 1)"></td>
                                    <td class="py-2 text-right font-semibold" x-text="fmt(s.msPerRendition, 1)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Generated images, grouped per source --}}
                <template x-for="s in results.sources" :key="'imgs-' + s.file">
                    <div class="bg-white mb-4 p-4 rounded-lg" x-show="s.images.length">
                        <div class="flex justify-between items-baseline mb-2">
                            <h3 class="font-bold font-mono text-sm" x-text="s.file"></h3>
                            <span class="text-xs text-gray-500" x-text="s.images.length + ' renditions'"></span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="(img, i) in s.images" :key="i">
                                <img :src="'/' + img.url"
                                     :title="img.width + 'px · ' + fmtBytes(img.bytes) + ' · ' + fmt(img.ms, 1) + ' ms'"
                                     class="h-16 w-auto rounded object-cover bg-gray-100"
                                     loading="lazy">
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Environment --}}
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

<script>
    function perfApp(init) {
        return {
            count: init.count,
            running: false,
            elapsed: 0,
            results: null,
            error: null,
            _timer: null,

            run() {
                if (this.running) return;
                this.running = true;
                this.error = null;
                this.results = null;
                this.elapsed = 0;

                const started = performance.now();
                this._timer = setInterval(() => {
                    this.elapsed = performance.now() - started;
                }, 100);

                fetch('/imagick-perf/run?count=' + this.count)
                    .then(r => {
                        if (!r.ok) throw new Error('Server returned ' + r.status);
                        return r.json();
                    })
                    .then(data => { this.results = data; })
                    .catch(e => { this.error = e.message || String(e); })
                    .finally(() => {
                        clearInterval(this._timer);
                        this.running = false;
                    });
            },

            fmt(n, d) {
                return Number(n).toLocaleString(undefined, { minimumFractionDigits: d, maximumFractionDigits: d });
            },
            fmtBytes(bytes) {
                if (!bytes) return '0 B';
                const units = ['B', 'kB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
            },
        };
    }
</script>
</body>
</html>
