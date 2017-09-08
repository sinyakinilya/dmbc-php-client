<?php
/**
 * AddAssetMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

class DelAssetMessage extends AssetMessage
{
    public function __construct($publicKey, $assetId, $amount)
    {
        parent::__construct($publicKey, $assetId, $amount, 4);
    }
}