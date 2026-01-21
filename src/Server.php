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
            $requestRaw = fread($client, 2048);

            $req = new Request($requestRaw);
            // favicon ignore
            if ($req->path() === '/favicon.ico') {
                fwrite($client, "HTTP/1.1 204 No Content\r\n\r\n");
                fclose($client);
                continue;
            }

            // Middleware handle karo aur response bhejo
            $res = Middleware::handle($req, function (Request $req) {
                return Router::dispatch($req);
            });

            fwrite($client, $res->send());
            fclose($client);
        }
    }
}
