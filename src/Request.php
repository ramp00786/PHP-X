<?php
/**
 * Request is IMMUTABLE by design.
 * Do not add setters. Create a new Request if needed.
 */

class Request
{
    private string $method;
    private string $path;
    private string $body;
    private array $data = [];
    private array $headers = [];
    private array $params = [];

    public function __construct(string $rawRequest)
    {
        $lines = explode("\n", $rawRequest);

        // Step 1: Parse first line (METHOD PATH HTTP/1.1)
        [$this->method, $this->path] = explode(' ', trim($lines[0]));

        // Step 2: Parse headers (skip first line)
        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (strpos($line, ':') !== false) {
                [$k, $v] = explode(':', $line, 2);
                $this->headers[strtolower(trim($k))] = trim($v);
            }
        }

        // Step 3: Extract body (after \r\n\r\n)
        $parts = explode("\r\n\r\n", $rawRequest, 2);
        $this->body = $parts[1] ?? '';

        // Step 4: Parse body based on Content-Type
        $this->parseBody();
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
    final public function __set($name, $value)
    {
        throw new \LogicException("Request is immutable");
    }

    private function parseBody(): void
    {
        $type = $this->headers['content-type'] ?? '';

        if (str_contains($type, 'application/json')) {
            $this->data = json_decode($this->body, true) ?? [];
        } elseif (str_contains($type, 'application/x-www-form-urlencoded')) {
            parse_str($this->body, $this->data);
        }
    }

    public function input(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->data;
    }


    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }



}
