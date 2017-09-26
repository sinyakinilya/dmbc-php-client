<?php
/**
 * AddAssetMessage.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain;

class AddAssetMessage extends AssetMessage
{
    public function __construct($publicKey, $assets)
    {
        parent::__construct($publicKey, $assets, 3);
    }
}