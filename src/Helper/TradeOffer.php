<?php
/**
 * Offer.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain\Helper;

/**
 * Class Offer
 * @package SunTechSoft\Blockchain\Helper
 *
 * Exonum struct Offer:
 *   size = 112
 *
 *   seller:     &PublicKey  [00 => 32]
 *   assets:     Vec<Asset>  [32 => 40]
 *   price:      u64         [40 => 48]
 *   signature:  &Signature  [48 => 112]
 */
final class TradeOffer implements Offer
{
    /** @var string */
    private $sellerPublicKey;
    /** @var Assets */
    private $assets;
    /** @var string */
    private $price;
    /** @var string */
    private $signature = null;

    public function __construct(string $sellerPublicKey, Assets $assets, int $price)
    {
        $this->setSellerPublicKey($sellerPublicKey);
        $this->setAssets($assets);
        $this->setPrice($price);
    }

    public function toArray(): array
    {
        return [
            'seller'    => $this->getSellerPublicKey(),
            'assets'    => $this->getAssets()->toArray(),
            'price'     => $this->getPrice(),
        ];
    }

    /**
     * @param string $sellerPublicKey
     *
     * @return TradeOffer
     */
    public function setSellerPublicKey(string $sellerPublicKey): TradeOffer
    {
        $this->sellerPublicKey = $sellerPublicKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getSellerPublicKey(): string
    {
        return $this->sellerPublicKey;
    }

    /**
     * @param Assets $assets
     *
     * @return TradeOffer
     */
    public function setAssets(Assets $assets): TradeOffer
    {
        $this->assets = $assets;

        return $this;
    }

    /**
     * @return Assets
     */
    public function getAssets(): Assets
    {
        return $this->assets;
    }

    /**
     * @return array
     */
    public function getArrayAssets(): array
    {
        return $this->assets->toArray();
    }

    /**
     * @param int $price
     *
     * @return TradeOffer
     */
    public function setPrice(int $price): TradeOffer
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param string $sellerSecretKey
     *
     * @return string
     */
    public function createSignature(string $sellerSecretKey): string
    {
        return \Sodium\bin2hex(\Sodium\crypto_sign_detached(
            $this->toHash(),
            \Sodium\hex2bin($sellerSecretKey)
        ));
    }

    /**
     * @param string $sellerSecretKey
     *
     * @return TradeOffer
     */
    public function setSignature(string $sellerSecretKey): TradeOffer
    {

        $this->signature = $this->createSignature($sellerSecretKey);

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     *
     * Exonum struct TradeOffer:
     *   size = 48
     *
     *   seller:     &PublicKey  [00 => 32]
     *   assets:     Vec<Asset>  [32 => 40]
     *   price:      u64         [40 => 48]
     *
     * @param int $startIndex
     *
     * @return string
     */
    public function toHash($startIndex = 0): string
    {
        $bytes = \Sodium\hex2bin($this->getSellerPublicKey());
        $index = 48 + $startIndex;
        $bytes .= pack('VV', $index, $this->assets->count());
        $bytes .= pack('P', $this->getPrice());
        $bytes .= $this->assets->toHash($index);

        return $bytes;
    }
}
