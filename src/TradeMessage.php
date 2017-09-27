<?php
/**
 * TransferMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

use SunTechSoft\Blockchain\Helper\Offer;

class TradeMessage extends AbstractMessage
{
    /**
     * @var Offer
     */
    private $offer;
    /**
     * @var string
     */
    private $buyerPublicKey;

    public function __construct(string $buyerPublicKey, Offer $offer)
    {
        parent::__construct(5);
        $this->setBuyerPublicKey($buyerPublicKey)
            ->setOffer($offer);
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
         *   buyer:      &PublicKey  [00 => 32]
         *   offer:      Offer       [32 => 40]
         *   seed:       u64         [40 => 48]
         **
         */
        $startIndexForBody = 10; // length(networkId + protocolVersion + messageId + serviceId + payloadLength)
        $sizeBody = 48;
        $hashOffer = $this->offer->toHash(0, true);

        $this->payloadLength = $startIndexForBody + $sizeBody + strlen($hashOffer) + 64; // 64 - length(signature)

        $s = pack('ccvv', $this->networkId, $this->protocolVersion, $this->messageId, $this->serviceId)
             . pack('V', $this->payloadLength)
             . \Sodium\hex2bin($this->getBuyerPublicKey())
             . pack('VV', $startIndexForBody + $sizeBody, strlen($hashOffer))
             . pack('P', (int)$this->getSeed())
             . $hashOffer
        ;

        return $s;
    }

    public function getBody()
    {
        if (is_null($this->body)) {
            $this->body = [
                    'buyer'  => $this->getBuyerPublicKey(),
                    'offer' => $this->getOffer()->toArray(),
                    'seed'   => $this->getSeed(),
                ];
        }

        return $this->body;
    }

    /**
     * @param mixed $buyerPublicKey
     *
     * @return TradeMessage
     * @throws \Exception
     */
    public function setBuyerPublicKey($buyerPublicKey)
    {
        if (strlen($buyerPublicKey) != 64) {
            throw new \Exception('fromPublicKey\' length != 64');
        }

        $this->buyerPublicKey = $buyerPublicKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBuyerPublicKey()
    {
        return $this->buyerPublicKey;
    }

    /**
     * @param Offer $offer
     *
     * @return TradeMessage
     */
    public function setOffer(Offer $offer): TradeMessage
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * @return Offer
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

}