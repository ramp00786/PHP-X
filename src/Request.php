<?php

class Request
{
    private string $method;
    private string $path;
    private string $body;

    public function __construct(string $rawRequest)
    {
        $lines = explode("\n", $rawRequest);

        // First line: METHOD PATH HTTP/1.1
        [$this->method, $this->path] = explode(' ', trim($lines[0]));

        // Body (last part after headers)
        $this->body = trim(end($lines));
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function body(): string
    {
        return $this->body;
    }
}
