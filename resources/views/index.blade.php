<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TestRabbit</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body>
    <div class="relative flex justify-center min-h-screen bg-gray-100 sm:items-center py-4 sm:pt-0">
        <div class="w-2/3 mx-auto pt-4">
            @foreach ($tests as $name => $test)
                <div
                    x-cloak
                    x-data="{success: false, pass: false, message: '', isLoading: true}"
                    x-init="fetch('/tests/{{ $test }}')
                    .then(response => response.json())
                    .then(response => { isLoading = false; pass = response.pass; success = response.success; message = response.message; })"
                    class="bg-white mb-4 p-4 rounded-lg"
                >
                    <div class="flex justify-between mb-2">
                        <h3 class="text-lg font-bold">{{ $name }}</h3>
                        <div x-show="!isLoading">
                            <span x-show="success"><x-check></x-check></span>
                            <span x-show="!success && !pass"><x-cross></x-cross></span>
                            <span x-show="!success && pass"><x-hand></x-hand></span>
                        </div>
                    </div>
                    <div x-show="isLoading">
                        <x-spinner></x-spinner>
                    </div>
                    <div x-show="!isLoading" x-html="message" class="max-h-72 overflow-y-auto"></div>
                </div>
            @endforeach
            <h2 class="text-lg font-bold">Additional test tools</h2>
            <ul>
                <li class="mt-2 flex">
                    <a class="border-dotted hover:border-solid border-b border-gray-600 mr-2" href="info.php" target="_blank">PHP info</a>
                    <x-link></x-link>
                </li>
            </ul>
            <h3 class="text-lg font-bold mt-4 mb-2">Test Workers</h3>
            <ol class="list-decimal ml-5">
                <li>Open one of the Testrabbit Pro apps, or create a new one</li>
                <li>
                    Create one nonstop job with SIGTERM - 10 sec<br>
                    <code>artisan queue:work --sleep=5</code>
                </li>
                <li>
                    Create one cronjob<br>
                    <code>artisan schedule:run</code>
                </li>
                <li>
                    Open a tail for the worker logs to watch for output<br>
                    <code>ssh testrabbit-us1@log.us1.frbit.com tail source:worker</code>
                </li>
                <li>
                    Run these ssh commands to create worker jobs<br>
                    <code>ssh testrabbit-us1@deploy.us1.frbit.com "php artisan test:job sleep -C 5"</code><br>
                    <code>ssh testrabbit-us1@deploy.us1.frbit.com "php artisan test:job error -C 10"</code>
                </li>
            </ol>

            <h3 class="text-lg font-bold mt-4 mb-2">Test cli commands</h3>
            <pre>
# Creates two dummy job with StdOut
php artisan test:job sleep -C 2

# Creates 10 jobs random with StdErr + Exceptions
php artisan test:job error -C 10

# Creates a jobs some memory consumption (64,512MB)
php artisan test:job memory --args=64
php artisan test:job memory --args=512

# Mongo connection test (deploy)
php artisan test:mongo

# Mongo connection test (Worker)
php artisan test:mongo --queued

# Handle queued jobs
php artisan queue:work
php artisan queue:cleanup

# Run migrations (if any)
php artisan migrate --force
            </pre>
        </div>
    </div>
</body>
</html>
