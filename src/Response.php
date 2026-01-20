<?php

class Response
{
    private int $status = 200;
    private string $body = '';
    private array $headers = [
        'Content-Type' => 'text/html'
    ];

    public static function html(string $html): self
    {
        $res = new self();
        $res->body = $html;
        return $res;
    }

    public static function text(string $text): self
    {
        $res = new self();
        $res->headers['Content-Type'] = 'text/plain';
        $res->body = $text;
        return $res;
    }

    public function status(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    public function send(): string
    {
        $response =
            "HTTP/1.1 {$this->status} OK\r\n";

        foreach ($this->headers as $key => $value) {
            $response .= "$key: $value\r\n";
        }

        $response .= "Content-Length: " . strlen($this->body) . "\r\n\r\n";
        $response .= $this->body;

        return $response;
    }
}
