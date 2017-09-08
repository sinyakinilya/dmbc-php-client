<?php
/**
 * AbstractMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

abstract class AbstractMessage
{
    public $networkId = 0;
    public $protocolVersion = 0;
    public $serviceId = 1;
    public $messageId;
    public $payloadLength;
    public $signature = null;

    private $body;

    public function __construct($messageId, $serviceId = 1)
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

    abstract public function createMessageForSignature();
    abstract public function getBody();

}