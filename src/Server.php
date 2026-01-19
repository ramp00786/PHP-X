<?php

class Server
{
    public static function start(int $port = 8080)
    {
        // TCP server create kar rahe hain
        $server = stream_socket_server("tcp://127.0.0.1:$port", $errno, $errstr);

        if (!$server) {
            echo "Server error: $errstr ($errno)\n";
            exit(1);
        }

        echo "[Server] Running at http://127.0.0.1:$port\n";

        while (true) {
            // client request accept karo
            $client = stream_socket_accept($server);

            if (!$client) continue;

            $request = fread($client, 1024);

            // HTTP method & path nikaalo
            $lines = explode("\n", $request);
            $firstLine = $lines[0]; // e.g. GET / HTTP/1.1

            [$method, $path] = explode(' ', $firstLine);

            // favicon special case
            if ($path === '/favicon.ico') {
                $response = "HTTP/1.1 204 No Content\r\n\r\n";
                fwrite($client, $response);
                fclose($client);
                continue;
            }

            // Router dispatch
            $body = Router::dispatch($method, $path);

            // HTTP response
            $response =
                "HTTP/1.1 200 OK\r\n" .
                "Content-Type: text/html\r\n" .
                "Content-Length: " . strlen($body) . "\r\n\r\n" .
                $body;

            fwrite($client, $response);
            fclose($client);

           

            // // agar button submit hua
            // if (str_contains($request, "POST /click")) {
            //     echo "-> Button clicked from browser\n";

            //     DOM::setText("#title", "Hello from PHP-X !!");

            //     $responseBody = DOM::$html;
            // } else {
            //      // favicon request ignore karo
            //     if (str_contains($request, "GET /favicon.ico")) {
            //         $response =
            //             "HTTP/1.1 204 No Content\r\n" .
            //             "Connection: close\r\n\r\n";

            //         fwrite($client, $response);
            //         fclose($client);
            //         continue;
            //     }
            //     echo "REQUEST RECEIVED\n";
            //     DOM::load(__DIR__ . '/../examples/index.html');
            //     $responseBody = DOM::$html;
            // }

            // $response =
            //     "HTTP/1.1 200 OK\r\n" .
            //     "Content-Type: text/html\r\n" .
            //     "Content-Length: " . strlen($responseBody) . "\r\n\r\n" .
            //     $responseBody;

            // fwrite($client, $response);
            // fclose($client);
        }
    }
}
