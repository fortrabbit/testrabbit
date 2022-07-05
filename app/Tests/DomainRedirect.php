<?php

namespace App\Tests;

class DomainRedirect extends StatusCodeTest implements Test
{
    protected string $scheme;
    protected string $url;
    protected int $expectedStatus;
    protected string $expectedRedirectUrl;

    public function __construct()
    {
        $this->scheme = 'https';
        $this->url = '/htaccess/domain-redirect/';
        $this->expectedStatus = 301;
        $this->expectedRedirectUrl = 'https://www.your-domain.example/htaccess/domain-redirect/';
    }

    public function execute(): Result
    {
        try {
            $url = $this->scheme . '://' . $_SERVER['HTTP_HOST'] . $this->url;
            $httpResponse = $this->call($url, false);
            $message = '';
            $success = true;
            if ($httpResponse->getStatus() != $this->expectedStatus) {
                $success = false;
                $message = $this->buildFailedTestMessage('Status', $this->expectedStatus, $httpResponse->getStatus());
            }
            if ($httpResponse->getUrl() !== $this->expectedRedirectUrl) {
                $success = false;
                $message .= $this->buildFailedTestMessage('Redirect URL', $this->expectedRedirectUrl, $httpResponse->getUrl());
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
