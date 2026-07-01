<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TestRabbit — PHP errors</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body>
@php
    $types = ['500', '503', '504', 'warning', 'info', 'random'];
    $initType = in_array($type, $types, true) ? $type : null;
    $initCount = (int) ($count ?? 0);
    $initCount = ($initCount >= 1 && $initCount <= 200) ? $initCount : 10;
    $initConcurrency = (int) ($concurrency ?? 0);
    $initConcurrency = $initConcurrency >= 1 ? min($initConcurrency, 200) : 5;
    $initSleep = (int) ($sleep ?? 0);
    $initSleep = ($initSleep >= 1 && $initSleep <= 600) ? $initSleep : 65;
    $initAbort = filter_var($abort ?? false, FILTER_VALIDATE_BOOLEAN);
@endphp
<div class="relative flex justify-center min-h-screen bg-gray-100 sm:items-start py-4">
    <div class="w-2/3 mx-auto pt-4"
         x-data="errorsApp({
             type: @json($initType),
             count: {{ $initCount }},
             concurrency: {{ $initConcurrency }},
             sleep: {{ $initSleep }},
             abort: {{ $initAbort ? 'true' : 'false' }},
         })">

        <div class="flex justify-between items-baseline mb-4">
            <h1 class="text-2xl font-bold">PHP errors</h1>
            <a class="border-dotted hover:border-solid border-b border-gray-600" href="/">&larr; back to tests</a>
        </div>

        {{-- Controls --}}
        <div class="bg-white mb-4 p-4 rounded-lg">
            <div class="flex items-center flex-wrap gap-2 mb-3">
                <span class="font-semibold mr-1">Generate:</span>
                @foreach ($types as $t)
                    <button type="button"
                            @click="start('{{ $t }}')"
                            :disabled="running"
                            :class="type === '{{ $t }}' ? 'bg-gray-800 text-white' : 'bg-gray-100 hover:bg-gray-200'"
                            class="px-3 py-1 rounded font-semibold disabled:opacity-50">{{ $t }}</button>
                @endforeach
                <button type="button"
                        x-show="running" x-cloak
                        @click="stop()"
                        class="ml-auto px-4 py-1 rounded bg-red-600 hover:bg-red-700 text-white font-semibold">
                    Stop
                </button>
            </div>

            <div class="flex items-center flex-wrap gap-4 text-sm">
                <label class="flex items-center gap-1">
                    <span class="text-gray-600">count</span>
                    <input type="number" min="1" max="200" x-model.number="count" :disabled="running"
                           class="w-20 px-2 py-1 border border-gray-300 rounded disabled:opacity-50">
                </label>
                <label class="flex items-center gap-1">
                    <span class="text-gray-600">concurrency</span>
                    <input type="number" min="1" max="200" x-model.number="concurrency" :disabled="running"
                           class="w-20 px-2 py-1 border border-gray-300 rounded disabled:opacity-50">
                </label>
                <label class="flex items-center gap-1">
                    <span class="text-gray-600">504 sleep (s)</span>
                    <input type="number" min="1" max="600" x-model.number="sleep" :disabled="running"
                           class="w-20 px-2 py-1 border border-gray-300 rounded disabled:opacity-50">
                </label>
                <label class="flex items-center gap-1">
                    <input type="checkbox" x-model="abort" :disabled="running">
                    <span class="text-gray-600">instant (abort)</span>
                </label>
            </div>
            <div class="text-xs text-gray-500 mt-2">
                Faithful mode (default): 500 throws a real exception, 504 sleeps past the gateway timeout, 503 is abort(503), warning/info write to the PHP log.
                Instant mode emits 500/503/504 immediately via <code>abort()</code>.
            </div>
        </div>

        {{-- Progress + summary --}}
        <div x-show="total > 0" x-cloak class="bg-white mb-4 p-4 rounded-lg">
            <div class="flex justify-between items-baseline mb-2 text-sm">
                <span class="font-semibold">
                    <span x-text="done"></span> / <span x-text="total"></span> done
                    <span x-show="running" class="text-gray-500">· <span x-text="inFlight"></span> in flight</span>
                </span>
                <span>
                    <span class="text-emerald-700" x-text="okCount + ' ✓'"></span>
                    <span class="text-red-600 ml-2" x-text="failCount + ' ✗'"></span>
                </span>
            </div>
            <div class="w-full bg-gray-100 rounded h-2 overflow-hidden">
                <div class="bg-emerald-600 h-2 transition-all" :style="'width: ' + pct + '%'"></div>
            </div>
            <div class="flex flex-wrap gap-2 mt-3 text-xs font-mono">
                <template x-for="[code, n] in statusEntries" :key="code">
                    <span class="px-2 py-0.5 rounded bg-gray-100"
                          x-text="(code === '0' ? 'net' : code) + ' × ' + n"></span>
                </template>
            </div>
        </div>

        {{-- Results list --}}
        <div x-show="rows.length > 0" x-cloak class="bg-white mb-4 p-4 rounded-lg">
            <div class="max-h-96 overflow-y-auto">
                <table class="w-full text-sm font-mono">
                    <tbody>
                        <template x-for="row in rows" :key="row.n">
                            <tr class="border-b border-gray-100" :class="rowClass(row)">
                                <td class="py-1 pr-3 text-right text-gray-400" x-text="'#' + row.n"></td>
                                <td class="py-1 pr-3" x-text="row.type"></td>
                                <td class="py-1 pr-3 text-right" x-text="row.status === null ? '' : (row.status === 0 ? 'net' : row.status)"></td>
                                <td class="py-1 pr-3" x-text="row.label"></td>
                                <td class="py-1 text-right text-gray-500" x-text="row.pending ? '…' : fmtMs(row.ms)"></td>
                                <td class="py-1 pl-3 text-right" x-text="row.pending ? '' : (row.ok ? '✓' : '✗')"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    function errorsApp(cfg) {
        return {
            type: cfg.type,
            count: cfg.count,
            concurrency: cfg.concurrency,
            sleep: cfg.sleep,
            abort: cfg.abort,

            running: false,
            stopped: false,
            rows: [],
            total: 0,
            done: 0,
            inFlight: 0,
            okCount: 0,
            failCount: 0,
            byStatus: {},
            nextIndex: 0,
            _abort: null,

            init() {
                // Auto-run when the page is opened with an ?errors= param.
                if (this.type !== null) this.start(this.type);
            },

            async start(type) {
                if (this.running) return;
                this.type = type;
                this.resetRun();
                this.running = true;
                this.stopped = false;
                this._abort = new AbortController();
                this.total = this.clamp(this.count, 1, 200);
                this.count = this.total;

                const pool = Math.max(1, Math.min(this.clamp(this.concurrency, 1, 200), this.total));
                const workers = [];
                for (let w = 0; w < pool; w++) workers.push(this.worker());
                await Promise.all(workers);

                this.running = false;
            },

            async worker() {
                while (!this.stopped) {
                    const i = this.nextIndex;
                    if (i >= this.total) return;
                    this.nextIndex++;
                    await this.emitOne(i);
                }
            },

            async emitOne(i) {
                const row = { n: i + 1, type: this.type, status: null, label: '…', ms: 0, ok: false, pending: true };
                this.rows.push(row);
                this.inFlight++;

                const qs = new URLSearchParams({
                    type: this.type,
                    abort: this.abort ? '1' : '0',
                    sleep: String(this.sleep),
                });
                const t0 = performance.now();

                try {
                    const r = await fetch('/php-errors/emit?' + qs.toString(), { signal: this._abort.signal });
                    row.ms = performance.now() - t0;
                    row.status = r.status;
                    if (r.ok) {
                        let body = {};
                        try { body = await r.json(); } catch (e) {}
                        row.type = body.type || this.type;
                        row.label = body.status || 'ok';
                        row.ok = true;
                        this.okCount++;
                    } else {
                        row.label = 'HTTP ' + r.status;
                        this.failCount++;
                    }
                    this.tally(r.status);
                } catch (e) {
                    row.ms = performance.now() - t0;
                    if (e.name === 'AbortError') {
                        // User hit Stop — cancelled, not a failure.
                        row.status = null;
                        row.label = 'aborted';
                    } else {
                        row.status = 0;
                        row.label = 'network';
                        this.failCount++;
                        this.tally(0);
                    }
                } finally {
                    row.pending = false;
                    this.inFlight--;
                    this.done++;
                }
            },

            stop() {
                this.stopped = true;
                // Cancel in-flight requests too, so a long faithful-504 run
                // stops immediately instead of waiting out each sleep.
                if (this._abort) this._abort.abort();
            },

            resetRun() {
                this.rows = [];
                this.total = 0;
                this.done = 0;
                this.inFlight = 0;
                this.okCount = 0;
                this.failCount = 0;
                this.byStatus = {};
                this.nextIndex = 0;
            },

            tally(status) {
                const key = String(status);
                this.byStatus = { ...this.byStatus, [key]: (this.byStatus[key] || 0) + 1 };
            },

            clamp(v, lo, hi) {
                v = parseInt(v) || lo;
                return Math.max(lo, Math.min(hi, v));
            },

            get statusEntries() {
                return Object.entries(this.byStatus).sort((a, b) => b[1] - a[1]);
            },

            get pct() {
                return this.total ? Math.round((this.done / this.total) * 100) : 0;
            },

            rowClass(row) {
                if (row.pending) return 'text-gray-400';
                if (row.ok) return 'text-emerald-700';
                if (row.status >= 500) return 'text-red-600';
                return 'text-gray-500';
            },

            fmtMs(ms) {
                return (ms / 1000).toFixed(2) + ' s';
            },
        };
    }
</script>
</body>
</html>
