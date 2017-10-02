<?php
/**
 * AbstractMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

abstract class AbstractMessage
{
    protected $networkId = 0;
    protected $protocolVersion = 0;
    protected $serviceId;
    protected $messageId;

    protected $payloadLength;
    protected $signature = null;

    protected $body = null;
    protected $seed = null;

    public function __construct($messageId, $serviceId = 2)
    {
        $this->messageId = $messageId;
        $this->serviceId = $serviceId;
    }

    public function createMessage($secretKey)
    {
        return [
            'body'             => $this->getBody(),
            'network_id'       => $this->networkId,
            'protocol_version' => $this->protocolVersion,
            'service_id'       => $this->serviceId,
            'message_id'       => $this->messageId,
            'signature'        => $this->createSignature($secretKey)
        ];
    }

    public function createSignature($secretKey)
    {
        return \Sodium\bin2hex(\Sodium\crypto_sign_detached(
            $this->createMessageForSignature(),
            \Sodium\hex2bin($secretKey)
        ));
    }

    /**
     * @return int
     */
    public function getNetworkId()
    {
        return $this->networkId;
    }

    /**
     * @return int
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @return int
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    protected function setSeed(int $seed = null)
    {
        //todo: нужно реализовать получение колиичство транзакции для кошелько подписывающего транзакцию.
        $this->seed = !is_int($seed) ? rand(0, 255) : $seed;

        return $this;
    }

    /**
     * @return string
     */
    protected function getSeed()
    {
        if (is_null($this->seed)) {
            $this->setSeed();
        }

        return (string)$this->seed;
    }

    abstract public function createMessageForSignature();
    abstract public function getBody();

}