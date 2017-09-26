<?php
/**
 * Client.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    /** @var GuzzleClient */
    public $httpClient;
    private $ip;
    private $port;

    public function __construct($ip, $port = 8545)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->httpClient = new GuzzleClient();
    }

    /**
     * @param $message
     * @return mixed
     * @throws \Exception
     */
    public function callMethod($message)
    {
        file_put_contents('wallet.json', $message . PHP_EOL, FILE_APPEND);
        $response = $this->getResponse($message);
        $response = json_decode($response, true);

        if (json_last_error() > 0) {
            throw new \Exception(json_last_error_msg());
        }

        return $response;
    }

    /**
     * @param $body
     *
     * @return string
     */
    private function getResponse($body)
    {
        $curl = curl_init($this->getUrl());
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_VERBOSE => true,
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * @return string
     */
    private function getUrl()
    {
        return 'http://' . $this->ip . ':' . $this->port . '/api/services/cryptocurrency/wallets/transaction';
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     *
     * @return Client
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     *
     * @return Client
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    private function getHeaders()
    {
        return ['Content-Type' => 'application/json'];
    }

}