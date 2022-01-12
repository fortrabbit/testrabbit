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
        <div class="w-2/3 mx-auto">
            @foreach ($tests as $name => $test)
                <div
                    x-cloak
                    x-data="{success: false, message: '', isLoading: true}"
                    x-init="fetch('/tests/{{ $test }}')
                    .then(response => response.json())
                    .then(response => { isLoading = false; success = response.success; message = response.message; })"
                    class="bg-white mb-4 p-4 rounded-lg"
                >
                    <div class="flex justify-between mb-2">
                        <h3 class="text-lg font-bold">{{ $name }}</h3>
                        <div x-show="!isLoading">
                            <span x-show="success"><x-check></x-check></span>
                            <span x-show="!success"><x-cross></x-cross></span>
                        </div>
                    </div>
                    <div x-show="isLoading">
                        <x-spinner></x-spinner>
                    </div>
                    <div x-show="!isLoading" x-html="message" class="max-h-72 overflow-y-auto"></div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
