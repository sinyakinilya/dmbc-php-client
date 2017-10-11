<?php
/**
 * ExchangeOffer.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain\Helper;


/**
 * Class ExchangeOffer
 * @package SunTechSoft\Blockchain\Helper
 *
 * Exonum struct ExchangeOffer:
 *   const SIZE = 97;
 *
 *   field sender:                 &PublicKey   [00 => 32]
 *   field sender_assets:          Vec<Asset>   [32 => 40]
 *   field sender_value:           u64          [40 => 48]
 *
 *   field recipient:              &PublicKey   [48 => 80]
 *   field recipient_assets:       Vec<Asset>   [80 => 88]
 *   field recipient_value:        u64          [88 => 96]
 *
 *   field fee_strategy:           u8           [96 => 97]
 */
final class ExchangeOffer implements Offer
{
    /** @var string */
    private $sender;
    /** @var Assets */
    private $senderAssets;
    /** @var string */
    private $senderValue;
    /** @var string */
    private $recipient;
    /** @var Assets */
    private $recipientAssets;
    /** @var string */
    private $recipientValue;
    /** @var int */
    private $feeStrategy;
    /** @var string */
    private $senderSignature;

    public function __construct(
        string $sender,
        Assets $senderAssets,
        string $senderValue,
        string $recipient,
        Assets $recipientAssets,
        string $recipientValue,
        int $feeStrategy
    ) {
        $this->setSender($sender);
        $this->setSenderAssets($senderAssets);
        $this->setSenderValue($senderValue);

        $this->setRecipient($recipient);
        $this->setRecipientAssets($recipientAssets);
        $this->setRecipientValue($recipientValue);

        $this->setFeeStrategy($feeStrategy);
    }

    /**
     * @return Assets
     */
    public function getRecipientAssets(): Assets
    {
        return $this->recipientAssets;
    }

    /**
     * @param Assets $recipientAssets
     *
     * @return ExchangeOffer
     */
    public function setRecipientAssets(Assets $recipientAssets): ExchangeOffer
    {
        $this->recipientAssets = $recipientAssets;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientValue(): string
    {
        return $this->recipientValue;
    }

    /**
     * @param string $recipientValue
     *
     * @return ExchangeOffer
     */
    public function setRecipientValue(string $recipientValue): ExchangeOffer
    {
        $this->recipientValue = $recipientValue;

        return $this;
    }

    /**
     * @return int
     */
    public function getFeeStrategy(): int
    {
        return $this->feeStrategy;
    }

    /**
     * @param int $feeStrategy
     *
     * @return ExchangeOffer
     */
    public function setFeeStrategy(int $feeStrategy): ExchangeOffer
    {
        $this->feeStrategy = $feeStrategy;

        return $this;
    }

    /**
     * @param string $sender
     *
     * @return ExchangeOffer
     */
    public function setSender(string $sender): ExchangeOffer
    {
        $this->sender = $sender;

        return $this;
}

    /**
     * @return string
     */
    public function getSender(): string
    {
        return $this->sender;
    }

    /**
     * @param Assets $senderAssets
     *
     * @return ExchangeOffer
     */
    public function setSenderAssets(Assets $senderAssets): ExchangeOffer
    {
        $this->senderAssets = $senderAssets;

        return $this;
    }

    /**
     * @return Assets
     */
    public function getSenderAssets(): Assets
    {
        return $this->senderAssets;
    }

    /**
     * @param string $senderValue
     *
     * @return ExchangeOffer
     */
    public function setSenderValue(string $senderValue): ExchangeOffer
    {
        $this->senderValue = $senderValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getSenderValue(): string
    {
        return $this->senderValue;
    }

    /**
     * @param string $recipient
     *
     * @return ExchangeOffer
     */
    public function setRecipient(string $recipient): ExchangeOffer
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecipient(): string
    {
        return $this->recipient;
    }

    /**
     * @param string $senderSecretKey
     *
     * @return string
     */
    public function createSignature(string $senderSecretKey): string
    {
        return \Sodium\bin2hex(\Sodium\crypto_sign_detached(
            $this->toHash(),
            \Sodium\hex2bin($senderSecretKey)
        ));
    }

    /**
     * @param string $senderSecretKey
     *
     * @return ExchangeOffer
     */
    public function setSignature(string $senderSecretKey): ExchangeOffer
    {
        $this->senderSignature = $this->createSignature($senderSecretKey);

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->senderSignature;
    }

    /**
     * Exonum struct ExchangeOffer:
     *    size = 97
     *
     *    field sender:                 &PublicKey   [00 => 32]
     *    field sender_assets:          Vec<Asset>   [32 => 40]
     *    field sender_value:           u64          [40 => 48]
     *
     *    field recipient:              &PublicKey   [48 => 80]
     *    field recipient_assets:       Vec<Asset>   [80 => 88]
     *    field recipient_value:        u64          [88 => 96]
     *
     *    field fee_strategy:           u8           [96 => 97]
     *
     * @param int $startIndex = 0
     *
     * @return string
     */
    public function toHash($startIndex = 0): string
    {
        $idxSenderAssets = 97 + $startIndex;
        $hashSenderAssets = $this->getSenderAssets()->toHash($idxSenderAssets);
        $idxRecipientAssets = $idxSenderAssets + strlen($hashSenderAssets);
        $hashRecipientAssets = $this->getRecipientAssets()->toHash($idxRecipientAssets);

        $bytes = \Sodium\hex2bin($this->getSender());
        $bytes .= pack('VV', $idxSenderAssets, $this->getSenderAssets()->count());
        $bytes .= pack('P', $this->getSenderValue());

        $bytes .= \Sodium\hex2bin($this->getRecipient());
        $bytes .= pack('VV', $idxRecipientAssets, $this->getRecipientAssets()->count());
        $bytes .= pack('P', $this->getRecipientValue());
        $bytes .= pack('c', $this->getFeeStrategy());

        $bytes .= $hashSenderAssets;
        $bytes .= $hashRecipientAssets;

        return $bytes;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'sender' => $this->getSender(),
            'sender_assets' => $this->getSenderAssets()->toArray(),
            'sender_value' => $this->getSenderValue(),

            'recipient' => $this->getRecipient(),
            'recipient_assets' => $this->getRecipientAssets()->toArray(),
            'recipient_value' => $this->getRecipientValue(),

            'fee_strategy' => $this->getFeeStrategy()
        ];
    }
}
