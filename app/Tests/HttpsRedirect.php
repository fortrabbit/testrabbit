<?php

namespace App\Tests;

class HttpsRedirect extends StatusCodeTest implements Test
{
    protected string $scheme;
    protected string $url;
    protected int $expectedStatus;

    public function __construct()
    {
        $this->scheme = 'http';
        $this->url = '/htaccess/https-redirect/index.html';
        $this->expectedStatus = 301;
    }

    public function execute(): Result
    {
        try {
            $url = $this->scheme . '://' . $_SERVER['HTTP_HOST'] . $this->url;
            $httpResponse = $this->call($url, false);
            $message = $httpResponse->getBody();
            $success = true;
            if ($httpResponse->getStatus() != $this->expectedStatus) {
                $success = false;
                $message = $this->buildFailedTestMessage('Status', $this->expectedStatus, $httpResponse->getStatus());
            }
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
}
