<?php

defined('PLATFORM_UBUNTU18') || define('PLATFORM_UBUNTU18', 'ubuntu18');
defined('PLATFORM_UBUNTU20') || define('PLATFORM_UBUNTU20', 'ubuntu20');
defined('PLATFORM_UBUNTU22') || define('PLATFORM_UBUNTU22', 'ubuntu22');
defined('PLATFORM_UBUNTU24') || define('PLATFORM_UBUNTU24', 'ubuntu24');
defined('PLATFORM_K8S') || define('PLATFORM_K8S', 'new');

return [
    'platform' => env('FRBIT_PLATFORM', PLATFORM_UBUNTU18)
];
