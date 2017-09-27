<?php
/**
 * Assets.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

namespace SunTechSoft\Blockchain\Helper;

/**
 * Class Assets
 *
 *
 * Exonum struct:
 *    size = 12
 *
 *    field hash_id:    &str      [00 => 08]
 *    field amount:      u32      [08 => 12]
 *
 *
 * @package SunTechSoft\Blockchain\Helper
 */
final class Assets
{
    /** @var array */
    private $assets;

    public function __construct(array $assets = null)
    {
        if (is_array($assets)) {
            $this->setAssets($assets);
        }
    }

    /**
     * @param array $assets
     *
     * @return Assets
     * @throws \Exception
     */
    public function setAssets(array $assets): Assets
    {
        $newAssets = $badAssets = [];
        foreach ($assets as $asset) {
            if ($asset['hash_id'] == '' || $asset['amount'] <= 0) {
                $badAssets[] = $asset;
            } else {
                $newAssets[$asset['hash_id']]= $asset['amount'];
            }
        }

        if (count($badAssets)) {
            throw new \Exception('Bad Assets : ' . json_encode($badAssets));
        } else {
            $this->assets = $newAssets;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * @param string $hashId
     * @param int $amount
     *
     * @return $this
     */
    public function addAsset(string $hashId, int $amount): Assets
    {
        $this->assets[$hashId] = (empty($this->assets[$hashId]) ? 0 : $this->assets[$hashId]) + $amount;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_map(
            function ($k, $v) {
                return ['hash_id' => $k,'amount' => $v];
            },
            array_keys($this->assets),
            $this->assets
        );
    }

    public function toHash($startIndex = 0): string
    {
        $exonumSize = 12;
        $assetsBytes = '';
        $vectorBytes = '';
        $nextElementIndex = $startIndex + 8 * count($this->assets);
        foreach ($this->assets as $hashId => $amount) {
            $lenHashId = strlen($hashId);

            $vectorBytes .= pack('VV', $nextElementIndex, 12 + $lenHashId);
            $assetsBytes .= pack('VVV', $exonumSize, $lenHashId, $amount) . $hashId;

            $nextElementIndex += 12 + $lenHashId;
        }

        return $vectorBytes . $assetsBytes;
    }

    public function count():int
    {
        return count($this->assets);
    }
}