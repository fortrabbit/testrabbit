<?php

namespace App\DTO;

class HttpResponse
{
    private int $status;
    private string $body;
    private string $url;

    public function __construct(int $status, string $body, string $url)
    {
        $this->status = $status;
        $this->body = $body;
        $this->url = $url;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
