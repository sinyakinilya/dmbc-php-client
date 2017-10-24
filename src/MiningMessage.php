<?php
/**
 * CreateWalletMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

final class MiningMessage extends AbstractMessage
{
    const MINING_MESSAGE_ID = 7;
    private $publicKey;

    public function __construct($publicKey)
    {
        parent::__construct(self::MINING_MESSAGE_ID);
        $this->publicKey = $publicKey;
    }

    public function createMessageForSignature()
    {
        $this->payloadLength = 114;
        $body = $this->getBody();
        $msg = '';
        $msg .= pack('ccvv', $this->networkId, $this->protocolVersion, $this->messageId, $this->serviceId);
        $msg .= pack('V', $this->payloadLength);
        $msg .= \Sodium\hex2bin($body['pub_key']);
        $msg .= pack('P', (int)$body['seed']);

        return $msg;
    }

    public function getBody()
    {
        if (is_null($this->body)) {
            $this->body = [
                'pub_key' => $this->publicKey,
                'seed' => $this->getSeed()
            ];
        }

        return $this->body;
    }

}
