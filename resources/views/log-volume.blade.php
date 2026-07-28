<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TestRabbit — Log volume</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Nunito', sans-serif; } [x-cloak] { display: none !important; }</style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body>
<div class="relative flex justify-center min-h-screen bg-gray-100 sm:items-start py-4">
    <div class="w-2/3 mx-auto pt-4" x-data="logVolumeApp()" x-init="init()">
        <div class="flex justify-between items-baseline mb-4">
            <h1 class="text-2xl font-bold">Log volume generator</h1>
            <a class="border-dotted hover:border-solid border-b border-gray-600" href="/">&larr; back to tests</a>
        </div>

        <div class="bg-white mb-4 p-4 rounded-lg">
            <p class="text-sm text-gray-600 mb-4">The web process only queues and controls the run. Log data is emitted by the existing Laravel queue worker.</p>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <label><span class="block text-gray-600 mb-1">Target size</span><input x-model="form.target_size" :disabled="active" class="w-full px-2 py-1 border rounded" placeholder="2gb"></label>
                <label><span class="block text-gray-600 mb-1">Bytes per second</span><input type="number" min="1" max="100000000" x-model.number="form.bytes_per_second" :disabled="active" class="w-full px-2 py-1 border rounded"></label>
                <label><span class="block text-gray-600 mb-1">Payload bytes</span><input type="number" min="48" max="65536" x-model.number="form.payload_bytes" :disabled="active" class="w-full px-2 py-1 border rounded"></label>
                <label class="col-span-2"><span class="block text-gray-600 mb-1">Run ID (optional)</span><input x-model="form.run_id" :disabled="active" class="w-full px-2 py-1 border rounded" placeholder="generated automatically"></label>
            </div>
            <div class="flex gap-2 mt-4">
                <button @click="start" :disabled="active || busy" class="px-4 py-2 rounded bg-gray-800 hover:bg-gray-900 text-white font-semibold disabled:opacity-50">Start worker run</button>
                <button x-show="active" x-cloak @click="stop" :disabled="busy" class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white font-semibold disabled:opacity-50">Stop</button>
            </div>
            <div x-show="error" x-cloak class="mt-3 text-sm text-red-700" x-text="error"></div>
        </div>

        <div x-show="run" x-cloak class="bg-white mb-4 p-4 rounded-lg">
            <div class="flex justify-between mb-2">
                <span class="font-semibold" x-text="run?.run_id"></span>
                <span class="px-2 py-0.5 rounded bg-gray-100 font-semibold" x-text="run?.status"></span>
            </div>
            <div class="w-full bg-gray-100 rounded h-3 overflow-hidden">
                <div class="bg-emerald-600 h-3 transition-all" :style="'width: ' + percent + '%' "></div>
            </div>
            <div class="flex justify-between mt-2 text-sm text-gray-600">
                <span><span x-text="formatBytes(run?.written_bytes || 0)"></span> / <span x-text="formatBytes(run?.target_bytes || 0)"></span></span>
                <span><span x-text="run?.lines || 0"></span> lines · <span x-text="percent.toFixed(1)"></span>%</span>
            </div>
            <div x-show="run?.error" class="mt-3 text-sm text-red-700" x-text="run?.error"></div>
        </div>

        <div class="bg-white p-4 rounded-lg text-sm text-gray-600">
            <strong>Worker requirement:</strong> the existing <code>php artisan queue:work --sleep=5</code> process must be running. Output is capped at 12 lines per second to stay below the platform suppression threshold.
        </div>
    </div>
</div>
<script>
function logVolumeApp() {
    return {
        form: { target_size: '2gb', bytes_per_second: 2000000, payload_bytes: 49152, run_id: '' },
        run: null, busy: false, error: '', timer: null,
        get active() { return this.run && ['queued', 'running', 'cancelling'].includes(this.run.status); },
        get percent() { return this.run?.target_bytes ? Math.min(100, this.run.written_bytes / this.run.target_bytes * 100) : 0; },
        async init() { await this.refresh(); this.timer = setInterval(() => this.refresh(), 2000); },
        async refresh() {
            try { const response = await fetch('/log-volume/status'); const body = await response.json(); this.run = body.run; } catch (e) { this.error = e.message; }
        },
        async start() { await this.send('/log-volume/start', this.form); },
        async stop() { if (this.run) await this.send('/log-volume/' + this.run.id + '/stop', {}); },
        async send(url, body) {
            this.busy = true; this.error = '';
            try {
                const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify(body) });
                const text = await response.text();
                let data = {};
                try { data = text ? JSON.parse(text) : {}; } catch (e) {
                    throw new Error('Server returned HTTP ' + response.status + ' instead of JSON. Check the application log.');
                }
                if (!response.ok) {
                    if (data.run) this.run = data.run;
                    throw new Error(data.message || Object.values(data.errors || {}).flat().join(' '));
                }
                this.run = data.run;
            } catch (e) { this.error = e.message; } finally { this.busy = false; }
        },
        formatBytes(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB']; let i = 0;
            while (bytes >= 1000 && i < units.length - 1) { bytes /= 1000; i++; }
            return bytes.toFixed(i ? 1 : 0) + ' ' + units[i];
        },
    };
}
</script>
</body>
</html>
