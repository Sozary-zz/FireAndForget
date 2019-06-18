<?php
namespace Sozary\FireAndForget;

use League\Uri;

final class FireAndForget
{
    private $connectionTimeout;

    function __construct($connectionTimeout = 3)
    {
        $this->connectionTimeout = $connectionTimeout;
    }

    public function get($url,  $params = [], $auth = null)
    {
        $this->send(strtoupper(__FUNCTION__), $url, $auth, $params);
    }

    public function post($url, $params = [], $auth = null)
    {
        $this->send(strtoupper(__FUNCTION__), $url, $auth,  $params);
    }

    private function getDefaultPort($scheme)
    {
        switch ($scheme) {
            case 'https':
                return 443;
            case 'http':
                return 80;
            default:
                return 80;
        }
    }

    private function getHeaders($method, $url, $auth,  $queryString)
    {
        $path = $method === 'GET' ? $url->getPath() . "?" . $queryString : $url->getPath();
        $headers = $method . " " . $path . " HTTP/1.1\r\n";
        $headers .= "Host: " . $url->getHost() . "\r\n";
        $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $headers .= "Content-Length: " . strlen($queryString) . "\r\n";
        if ($auth) {
            $headers .= "Authorization: Bearer " . $auth . "\r\n";
        }
        $headers .= "Connection: Close\r\n";
        return $headers;
    }


    private function getRequest($method, $url, $auth, $params)
    {
        $queryString = http_build_query($params);
        $headers     = $this->getHeaders($method, $url, $auth, $queryString);
        $body        = $method === 'GET' ? '' : $queryString;
        return $headers . "\r\n" . $body;
    }

    private function send($method, $url, $auth,  $params)
    {
        try {
            $url =  Uri\Http::createFromString($url);
        } catch (\RuntimeException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        $scheme = $url->getScheme() === "https" ? "ssl://" : "";
        $host   = $scheme . $url->getHost();
        $port   = $url->getPort() ?: $this->getDefaultPort($url->getScheme());
        $request = $this->getRequest($method, $url, $auth, $params);
        $socket  = @fsockopen($host, $port, $errno, $errstr, $this->connectionTimeout);
        if (!$socket) {
            throw new \SocketException($errstr, $errno);
        }
        fwrite($socket, $request);
        fclose($socket);
    }
}
