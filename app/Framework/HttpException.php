<?php

namespace App\Framework;

/**
 * Thrown by abort(); carries the HTTP status the front controller must send.
 */
class HttpException extends \RuntimeException
{
    private int $status;

    public function __construct(int $status, string $message = '')
    {
        parent::__construct($message);
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
