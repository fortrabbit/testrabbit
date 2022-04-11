<?php

namespace App\Tests;

class HttpsRedirect extends StatusCodeTest implements Test
{
    public function execute(): Result
    {
        $success = true;
        try {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/htaccess/https-redirect/index.html';
            $message = $this->getStatusCode($url);
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
