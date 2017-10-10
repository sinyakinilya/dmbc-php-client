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
    private $assets;

    public function __construct($publicKey, array $assets, $messageId)
    {
        parent::__construct($messageId);
        $this->publicKey = $publicKey;
        $this->setAssets($assets);
    }

    public function createMessageForSignature()
    {
        /**
         * Exonum structure for addAsset's message
         *
         * body:
         *   pub_key    00 -> 32
         *   assets     32 -> 40
         *   seed       40 -> 48
         *
         * Asset:
         *   hash_id    00 -> 08
         *   amount     08 -> 12
         *
         */
        $startIndexForBody = 10; // length(networkId + protocolVersion + messageId + serviceId + payloadLength)
        $sizeBody = 48;
        $sizeAsset = 12;
        $body = $this->getBody();
        $this->payloadLength = $startIndexForBody;
        $assets = [];
        $this->payloadLength = $startIndexForBody + $sizeBody + 64; // 64 - length(signature)

        foreach ($body['assets'] as $i => $asset) {
            $lenAsset = $sizeAsset + strlen($asset['hash_id']);
            $assets[$i] = [
                'start' => 0,
                'size' =>  $lenAsset,
                'bytes' => pack('VVV', $sizeAsset, strlen($asset['hash_id']), $asset['amount']).$asset['hash_id']
            ];
            $this->payloadLength += (8 + $lenAsset);
        }

        $s = pack('ccvv', $this->networkId, $this->protocolVersion, $this->messageId, $this->serviceId)
             . pack('V', $this->payloadLength)
             . \Sodium\hex2bin($this->publicKey)
             . pack('VVP', ($startIndexForBody + $sizeBody), count($assets), (int)$body['seed'])
        ;

        foreach ($assets as $i => $asset) {
            if ($i>0) {
                $assets[$i]['start'] = $assets[$i-1]['start'] + $assets[$i-1]['size'];
            } else {
                $assets[$i]['start'] = ($startIndexForBody + $sizeBody) + 8 * count($assets);
            }
            $s .= pack('VV', $assets[$i]['start'], $assets[$i]['size']);
        }
        foreach ($assets as $asset) {
            $s .= $asset['bytes'];
        }
        return $s;
    }

    public function getBody()
    {
        if (is_null($this->body)) {
            $this->body = [
                'pub_key' => $this->publicKey,
                'assets'  => $this->getAssets(),
                'seed'    => $this->getSeed(),
            ];
        }

        return $this->body;
    }

    public function setAssets($assets)
    {
        $badAssets = [];
        foreach ($assets as $asset) {
            if ($asset['hash_id'] == '' || $asset['amount'] <= 0) {
                $badAssets[] = $asset;
            }
        }

        if (count($badAssets)) {
            throw new \Exception('Bad Assets : ' . json_encode($badAssets));
        } else {
            $this->assets = $assets;
        }
    }

    public function getAssets()
    {
        return $this->assets;
    }

}
