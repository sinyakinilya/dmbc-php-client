<?php
/**
 * CreateWalletMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

final class CreateWalletMessage extends AbstractMessage
{
    private $publicKey;

    public function __construct($publicKey)
    {
        parent::__construct(1);
        $this->publicKey = $publicKey;
    }

    public function createMessageForSignature()
    {
        $this->payloadLength = 106;

        $msg = '';
        $msg .= pack('ccvv', $this->networkId, $this->protocolVersion, $this->messageId, $this->serviceId);
        $msg .= pack('V', $this->payloadLength);
        $msg .= \Sodium\hex2bin($this->publicKey);

        return $msg;
    }

    public function getBody()
    {
        return [
            'pub_key' => $this->publicKey,
        ];
    }

}
