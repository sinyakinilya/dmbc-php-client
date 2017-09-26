<?php
/**
 * TransferMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

class TransferMessage extends AbstractMessage
{
    /**
     * @var int
     */
    private $amount;
    /**
     * @var array
     */
    private $assets;
    /**
     * @var string
     */
    private $fromPublicKey;
    /**
     * @var string
     */
    private $toPublicKey;

    public function __construct(string $from, string $to, int $amount, array $assets)
    {
        parent::__construct(2);

        $this->setFromPublicKey($from)
             ->setToPublicKey($to)
             ->setAmount($amount)
             ->setAssets($assets)
        ;
    }

    /**
     * @return string
     */
    public function createMessageForSignature()
    {
        /**
         * Exonum structure for Transfer's message
         *
         * body:
         *   from:        &PublicKey  [00 => 32]
         *   to:          &PublicKey  [32 => 64]
         *   amount:      u64         [64 => 72]
         *   assets:      Vec<Asset>  [72 => 80]
         *   seed:        u64         [80 => 88]
         *
         * Asset:
         *   hash_id    00 -> 08
         *   amount     08 -> 12
         *
         */
        $startIndexForBody = 10; // length(networkId + protocolVersion + messageId + serviceId + payloadLength)
        $sizeBody = 88;
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
             . \Sodium\hex2bin($body['from'])
             . \Sodium\hex2bin($body['to'])
             . pack('PVVP', $body['amount'], ($startIndexForBody + $sizeBody), count($assets), (int)$body['seed'])
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
                    'from'   => $this->getFromPublicKey(),
                    'to'     => $this->getToPublicKey(),
                    'amount' => $this->getAmount(),
                    'assets' => $this->getAssets(),
                    'seed'   => $this->getSeed(),
                ];
        }

        return $this->body;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return (string)$this->amount;
    }

    /**
     * @param int $amount
     *
     * @return TransferMessage
     */
    public function setAmount(int $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param mixed $fromPublicKey
     *
     * @return TransferMessage
     * @throws \Exception
     */
    public function setFromPublicKey($fromPublicKey)
    {
        if (strlen($fromPublicKey) != 64) {
            throw new \Exception('fromPublicKey\' length != 64');
        }

        $this->fromPublicKey = $fromPublicKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromPublicKey()
    {
        return $this->fromPublicKey;
    }

    /**
     * @param string $toPublicKey
     *
     * @return $this
     * @throws \Exception
     */
    public function setToPublicKey(string $toPublicKey)
    {
        if (strlen($toPublicKey) != 64) {
            throw new \Exception('toPublicKey\' length != 64');
        }
        $this->toPublicKey = $toPublicKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getToPublicKey()
    {
        return $this->toPublicKey;
    }

    /**
     * @param array $assets
     *
     * @return $this
     * @throws \Exception
     */
    public function setAssets(array $assets)
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

        return $this;
    }


    /**
     * @return array
     */
    public function getAssets()
    {
        return $this->assets;
    }


}