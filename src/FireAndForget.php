<?php
namespace Sozary\FireAndForget;

use League\Uri;
use Sozary\FireAndForget\SocketException;
use Sozary\FireAndForget\MethodException;

final class FireAndForget
{
    private $connectionTimeout;
    private $accepted_method = ["GET", "POST", "PATCH", "OPTION", "DELETE"];

    function __construct($connectionTimeout = 3)
    {
        $this->connectionTimeout = $connectionTimeout;
    }

    private function getPortIfNotDefined($scheme)
    {
        switch ($scheme) {
            case 'https':
                return 443;
            case 'http':
            default:
                return 80;
        }
    }

    private function getHeaders($method, $url, $auth,  $queryString)
    {
        $headers = $method . " " . ($method === 'GET' ? $url->getPath() . "?" . $queryString : $url->getPath()) . " HTTP/1.1\r\n";
        $headers .= "Host: " . $url->getHost() . "\r\n";
        $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $headers .= "Content-Length: " . strlen($queryString) . "\r\n";
        if ($auth) {
            $headers .= "Authorization: " . $auth . "\r\n";
        }
        $headers .= "Connection: Close\r\n";
        return $headers;
    }


    private function getRequest($method, $url, $auth, $params)
    {
        $queryString = http_build_query($params);
        return  $this->getHeaders($method, $url, $auth, $queryString) . "\r\n" . ($method === 'GET' ? '' : $queryString);
    }

    public function send($method, $url, $auth,  $params)
    {
        $method = strtoupper($method);
        if (!in_array($method, $this->accepted_method))
            throw new MethodException("Invalid method");

        try {
            $url =  Uri\Http::createFromString($url);
        } catch (\RuntimeException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
        $socket  = @fsockopen(($url->getScheme() === "https" ? "ssl://" : "") . $url->getHost(), $url->getPort() ?: $this->getPortIfNotDefined($url->getScheme()), $errno, $errstr, $this->connectionTimeout);
        if (!$socket) {
            throw new SocketException($errstr, $errno);
        }
        fwrite($socket,  $this->getRequest($method, $url, $auth, $params));
        fclose($socket);
    }
}
