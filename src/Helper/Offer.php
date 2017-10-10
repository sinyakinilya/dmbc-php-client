<?php
/**
 * Offer.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain\Helper;

interface Offer
{
    /**
     * @param string $secretKey
     *
     * @return string
     */
    public function createSignature(string $secretKey): string;

    /**
     * @param string $secretKey
     *
     * @return $this
     */
    public function setSignature(string $secretKey);

    /**
     * @return string
     */
    public function getSignature(): string;

    /**
     * @param int $startIndex = 0
     *
     * @return string
     */
    public function toHash($startIndex = 0): string;

    /**
     * @return array
     */
    public function toArray(): array;
}