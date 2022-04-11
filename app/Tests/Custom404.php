<?php

namespace App\Tests;

class Custom404 extends StatusCodeTest implements Test
{
    protected string $scheme;
    protected string $url;
    protected int $expectedStatus;
    protected string $pageContains;

    public function __construct()
    {
        $this->scheme = 'https';
        $this->url = '/htaccess/custom-404/not-found/';
        $this->expectedStatus = 404;
        $this->pageContains = 'This file should be used instead of the standard 404 page.';
    }

    public function execute(): Result
    {
        try {
            $url = $this->scheme . '://' . $_SERVER['HTTP_HOST'] . $this->url;
            $httpResponse = $this->call($url, true);
            $message = '';
            if ($httpResponse->getStatus() != $this->expectedStatus) {
                $message = $this->buildFailedTestMessage('Status', $this->expectedStatus, $httpResponse->getStatus());
            }
            if (! str_contains($httpResponse->getBody(), $this->pageContains)) {
                $success = false;
                $message .= $this->buildFailedTestMessage('Page content', $this->pageContains, $httpResponse->getBody());
            }
            if ($success) {
                $message = $httpResponse->getBody();
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
