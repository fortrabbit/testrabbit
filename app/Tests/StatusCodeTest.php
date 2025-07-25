<?php

namespace App\Tests;

use App\DTO\HttpResponse;

class StatusCodeTest
{
    protected string $scheme;
    protected string $url;
    protected string $pageContains;
    protected int $expectedStatus;

    public function execute(): Result
    {
        try {
            $url = $this->scheme . '://' . $_SERVER['HTTP_HOST'] . $this->url;
            $message = $this->call($url);
            $success = $message == $this->expectedStatus;
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return new Result($success, $message);
    }

    protected function call(string $url, bool $followLocation = true): HttpResponse
    {
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_FOLLOWLOCATION, $followLocation);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handler);
        $status = curl_getinfo($handler, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($handler, CURLINFO_REDIRECT_URL);
        curl_close($handler);

        return new HttpResponse($status, $response, $finalUrl);
    }

    protected function buildFailedTestMessage(string $type, $expected, $actual): string
    {
        return 'Test failed on ' . $type . ': expected ' . $expected . ', got ' . $actual . '<br>';
    }
}
