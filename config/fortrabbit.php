<?php

const PLATFORM_UBUNTU18='ubuntu18';
const PLATFORM_UBUNTU20='ubuntu20';
const PLATFORM_UBUNTU22='ubuntu22';
const PLATFORM_UBUNTU24='ubuntu24';
const PLATFORM_K8S='new';

return [
    'platform' => env('FRBIT_PLATFORM', PLATFORM_UBUNTU18)
];
