<?php

namespace App\Tests;

class HttpsRedirect extends StatusCodeTest
{
    public function __construct()
    {
        $this->scheme = 'http';
        $this->url = '/htaccess/https-redirect/index.html';
        $this->expectedStatus = 301;
    }
}
