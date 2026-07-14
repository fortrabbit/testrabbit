<!DOCTYPE html>
<html lang="<?= $this->e(config('app.locale', 'en')) ?>">
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
            <?php foreach ($tests as $name => $test): ?>
                <div
                    x-cloak
                    x-data="{success: false, pass: false, message: '', isLoading: true}"
                    x-init="fetch('/tests/<?= $this->e($test) ?>')
                    .then(response => response.json())
                    .then(response => { isLoading = false; pass = response.pass; success = response.success; message = response.message; })"
                    class="bg-white mb-4 p-4 rounded-lg"
                >
                    <div class="flex justify-between mb-2">
                        <h3 class="text-lg font-bold"><?= $this->e($name) ?></h3>
                        <div x-show="!isLoading">
                            <span x-show="success"><?php $this->partial('check'); ?></span>
                            <span x-show="!success && !pass"><?php $this->partial('cross'); ?></span>
                            <span x-show="!success && pass"><?php $this->partial('hand'); ?></span>
                        </div>
                    </div>
                    <div x-show="isLoading">
                        <?php $this->partial('spinner'); ?>
                    </div>
                    <div x-show="!isLoading" x-html="message" class="max-h-72 overflow-y-auto"></div>
                </div>
            <?php endforeach; ?>

            <div class="bg-white mb-4 p-4 rounded-lg">
                <h2 class="text-lg font-bold mb-2">Incoming HTTP Headers</h2>
                <div class="bg-gray-50 p-3 rounded max-h-96 overflow-y-auto">
                    <table class="w-full text-sm font-mono">
                        <tbody>
                            <?php foreach ($headers as $header => $values): ?>
                                <tr class="border-b border-gray-200">
                                    <td class="py-2 pr-4 font-semibold text-gray-700 align-top"><?= $this->e($header) ?></td>
                                    <td class="py-2 text-gray-600">
                                        <?php foreach ($values as $value): ?>
                                            <div><?= $this->e($value) ?></div>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <h2 class="text-lg font-bold">Additional test tools</h2>
            <ul>
                <li class="mt-2 flex">
                    <a class="border-dotted hover:border-solid border-b border-gray-600 mr-2" href="info.php" target="_blank">PHP info</a>
                    <?php $this->partial('link'); ?>
                </li>
                <li class="mt-2 flex">
                    <a class="border-dotted hover:border-solid border-b border-gray-600 mr-2" href="/php-errors">PHP error tests</a>
                    <?php $this->partial('link'); ?>
                </li>
            </ul>
            <h3 class="text-lg font-bold mt-4 mb-2">Test Workers</h3>
            <ol class="list-decimal ml-5">
                <li>Open one of the Testrabbit Pro apps, or create a new one</li>
                <li>
                    Create one nonstop job with SIGTERM - 10 sec<br>
                    <code>php bin/job-sleep.php</code>
                </li>
                <li>
                    Open a tail for the worker logs to watch for output<br>
                    <code>ssh testrabbit-us1@log.us1.frbit.com tail source:worker</code>
                </li>
                <li>
                    Run these ssh commands to create worker jobs<br>
                    <code>ssh testrabbit-us1@deploy.us1.frbit.com "php bin/job-sleep.php"</code><br>
                    <code>ssh testrabbit-us1@deploy.us1.frbit.com "php bin/job-random-error.php"</code>
                </li>
            </ol>

            <h3 class="text-lg font-bold mt-4 mb-2">Test cli commands</h3>
            <pre>
# One long-running job with stdout (sleeps 3s × 10)
php bin/job-sleep.php

# One random job: prints to stderr and throws ~1 in 5
php bin/job-random-error.php

# Consume memory up to N MB (e.g. 64, 512)
php bin/job-eat-memory.php 64
php bin/job-eat-memory.php 512
            </pre>
        </div>
    </div>
</body>
</html>
