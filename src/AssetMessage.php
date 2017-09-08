<?php
/**
 * AssetMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

abstract class AssetMessage extends AbstractMessage
{
    private $publicKey;
    private $assetId;
    private $amount;

    private $body = null;

    public function __construct($publicKey, $assetId, $amount, $messageId)
    {
        parent::__construct($messageId);
        $this->publicKey = $publicKey;
        $this->assetId = $assetId;
        $this->amount = $amount;
    }

    public function createMessageForSignature()
    {
        /**
         * Exonum structure for addAsset's message
         *
         * body:
         *   pub_key    00 -> 32
         *   asset      32 -> 40
         *   seed       40 -> 48
         *
         *Asset:
         *   hash_id    00 -> 08
         *   amount     08 -> 12
         *
         */
        $startIndexForBody = 10; // length(networkId + protocolVersion + messageId + serviceId + payloadLength)
        $sizeBody = 48;
        $sizeAsset = 12;
        $body = $this->getBody();

        $lenAsset = $sizeAsset + strlen($body['asset']['hash_id']);
        $this->payloadLength = $startIndexForBody + $sizeBody + $lenAsset + 64; // 64 - length(signature)

        $sAsset = pack("VV", $sizeAsset, strlen($body['asset']['hash_id']))
            . pack("V", $body['asset']['amount'])
            . $body['asset']['hash_id'];

        $s = pack('ccvv', $this->networkId, $this->protocolVersion, $this->messageId, $this->serviceId)
             . pack('V', $this->payloadLength)
             . \Sodium\hex2bin($this->publicKey)
             . pack('VVP', ($startIndexForBody + $sizeBody), $lenAsset, (int)$body['seed'])
             . $sAsset;

        return $s;
    }

    public function getBody()
    {
        if (is_null($this->body)) {
            $this->body = [
                'pub_key' => $this->publicKey,
                'asset' => [
                    'hash_id' => $this->assetId,
                    'amount' => $this->amount,
                ],
                'seed' => (string)rand(1, 1000000),
            ];
        }

        return $this->body;
    }

}
