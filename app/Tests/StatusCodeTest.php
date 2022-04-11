<?php

namespace App\Tests;

class StatusCodeTest implements Test
{
    protected string $scheme;
    protected string $url;
    protected int $expectedStatus;

    public function execute(): Result
    {
        try {
            $url = $this->scheme . $_SERVER['HTTP_HOST'] . $this->url;
            $message = $this->getStatusCode($url);
            $success = $message == $this->expectedStatus;
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return new Result($success, $message);
    }

    public function appType(): string
    {
        return self::APP_UNI;
    }

    private function getStatusCode(string $url)
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
