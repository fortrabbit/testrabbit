<?php

namespace App\Tests;

class Result
{
    private bool $success;
    private string $message;

    public function __construct(bool $success, string $message)
    {
        $this->success = $success;
        $this->message = $message;
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
