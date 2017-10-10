<?php
/**
 * ExchangeMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

use SunTechSoft\Blockchain\Helper\ExchangeOffer;

class ExchangeMessage extends AbstractMessage
{
    const MESSAGE_ID = 6;

    /**
     * @var ExchangeOffer
     */
    private $offer;

    public function __construct(ExchangeOffer $offer)
    {
        parent::__construct(ExchangeMessage::MESSAGE_ID);
        $this->offer = $offer;
    }

    /**
     * Exonum structure for Exchange's message
     *
     * body:
     *   offer:              ExchangeOffer      [00 => 08]
     *   seed:               u64                [08 => 16]
     *   sender_signature    &Signature         [16 => 80]
     **
     */
    public function createMessageForSignature()
    {
        $startIndexForBody = 10; // length(networkId + protocolVersion + messageId + serviceId + payloadLength)
        $sizeBody = 80;
        $hashOffer = $this->offer->toHash();

        $this->payloadLength = $startIndexForBody + $sizeBody + strlen($hashOffer) + 64; // 64 - length(signature)

        $s = pack('ccvv', $this->networkId, $this->protocolVersion, $this->messageId, $this->serviceId)
             . pack('V', $this->payloadLength)
             . pack('VV', $startIndexForBody + $sizeBody, strlen($hashOffer))
             . pack('P', (int)$this->getSeed())
             . \Sodium\hex2bin($this->getOffer()->getSignature())
             . $hashOffer
        ;

        return $s;
    }

    public function getBody()
    {
        if (is_null($this->body)) {
            $this->body = [
                'offer'            => $this->getOffer()->toArray(),
                'seed'             => $this->getSeed(),
                'sender_signature' => $this->getOffer()->getSignature()
            ];
        }

        return $this->body;
    }

    /**
     * @return ExchangeOffer
     */
    public function getOffer(): ExchangeOffer
    {
        return $this->offer;
    }

    /**
     * @param ExchangeOffer $offer
     *
     * @return ExchangeMessage
     */
    public function setOffer(ExchangeOffer $offer): ExchangeMessage
    {
        $this->offer = $offer;

        return $this;
    }
}
