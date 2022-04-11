<?php

namespace App\Tests;

class StatusCodeTest
{
    protected function getStatusCode(string $url)
    {
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_exec($handler);
        $status = curl_getinfo($handler, CURLINFO_HTTP_CODE);
        curl_close($handler);

        return $status;
    }
}
