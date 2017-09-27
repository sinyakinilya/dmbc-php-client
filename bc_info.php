<?php
/**
 * bc_info.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

function call($url)
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_VERBOSE        => true,
    ]);
    $result = json_decode(curl_exec($curl), true);
    curl_close($curl);

    return $result;
}
$explorer = 'http://172.104.146.242:8000/api/explorer/v1/blocks';
$blocksUrl = $explorer . '?count=100';
$blockInfoUrl = $explorer . '/';
$blocks = call($blocksUrl);

foreach ($blocks as $block) {
    if ($block['tx_count'] > 0) {
        print_r($block);
        print_r(call($blockInfoUrl . $block['height']));
    }
    if ($block['height'] == 1) {
        break;
    }
}
