<?php
/**
 * Client.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

class Client
{
    private $ip;
    private $port;
    private $ssl;

    public function __construct($ip, $port = 8545, $useSSl = false)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->ssl = $useSSl;
    }

    /**
     * @param $message
     * @return mixed
     * @throws \Exception
     */
    public function callMethod($message)
    {
        $responseData = $this->getResponse($message);
        $response = json_decode($responseData, true);

        if (json_last_error() > 0) {
            print_r([json_last_error_msg(),$responseData]);
            throw new \Exception();
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
            CURLOPT_HTTPHEADER => $this->getHeaders(),
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
//        return 'https://dmarket.com/api/services/cryptocurrency/v1/wallets/transaction';
        return $this->getProtocol() . '://' . $this->getIp() . ':' . $this->getPort()
               . '/api/services/cryptocurrency/v1/wallets/transaction';
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

    private function getProtocol()
    {
        return $this->ssl ? 'https' : 'http';
    }
}