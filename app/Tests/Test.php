<?php

namespace App\Tests;

interface Test
{
    const APP_UNI = 'uni';
    const APP_PRO = 'pro';

    public function execute(): Result;

    public function appType(): string;
}
