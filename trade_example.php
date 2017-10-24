<?php
/**
 * example.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

use SunTechSoft\Blockchain\AddAssetMessage;
use SunTechSoft\Blockchain\CreateWalletMessage;
use SunTechSoft\Blockchain\Client;
use SunTechSoft\Blockchain\Helper\Assets;
use SunTechSoft\Blockchain\Helper\Cryptography;
use SunTechSoft\Blockchain\Helper\TradeOffer;
use SunTechSoft\Blockchain\MiningMessage;
use SunTechSoft\Blockchain\TradeMessage;

include_once 'vendor/autoload.php';

$client = new Client('127.0.0.1', 8000);

/**
 * methods:
 *  1 - createWallet (create and setup 100 coins)
 *  2 - transfer (couin and assets)
 *  3 - addAssets (assets)
 *  4 - delAssets (assets)
 */

/** CreateWallet */

Cryptography::generateKeys($pk1, $sk1);
Cryptography::generateKeys($pk2, $sk2);
file_put_contents('wallet.json', $sk1 . PHP_EOL . $sk2 . PHP_EOL, FILE_APPEND);

echo PHP_EOL, PHP_EOL, "  ---- start ---- ", PHP_EOL;
echo "Create Wallets for user1 and user2";
$message  = new CreateWalletMessage($pk1);
$msg      = $message->createMessage($sk1);
$response = $client->callMethod(json_encode($msg));
echo PHP_EOL, $response['tx_hash'], PHP_EOL;
$message  = new CreateWalletMessage($pk2);
$msg      = $message->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
echo $response['tx_hash'], PHP_EOL;

sleep(1);

$assets1 = (new Assets())
    ->addAsset('a8d5c97d-9978-4b0b-9947-7a95dcb31d0f', 45)
    ->addAsset('a8d5c97d-9978-4111-9947-7a95dcb31d0f', 17);

$message  = new AddAssetMessage($pk1, $assets1->toArray());
$msg      = $message->createMessage($sk1);
$response = $client->callMethod(json_encode($msg));
$bcUser1Assets = $response['transaction_info']['external_internal'];
echo "AddAsset for user1", PHP_EOL, print_r($response['tx_hash']), PHP_EOL;

$assets2  = (new Assets())
    ->addAsset('a8d5c97d-9978-cccc-9947-7a95dcb31d0f', 5);

$message  = new AddAssetMessage($pk2, $assets2->toArray());
$msg      = $message->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
$bcUser2Assets = $response['transaction_info']['external_internal'];
echo "AddAsset for user2", PHP_EOL, print_r($response['tx_hash']), PHP_EOL;


sleep(1);

$offerAssets = (new Assets)
    ->addAsset($bcUser1Assets['a8d5c97d-9978-4b0b-9947-7a95dcb31d0f'], 5)
    ->addAsset($bcUser1Assets['a8d5c97d-9978-4111-9947-7a95dcb31d0f'], 7);

$tradeOffer = new TradeOffer($pk1, $offerAssets, 37);
$tradeOffer->setSignature($sk1);

$txTrade = new TradeMessage($pk2, $tradeOffer);
$msg = $txTrade->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
echo "Trade for user2", PHP_EOL, $response['tx_hash'], PHP_EOL;


$pk = '36a05e418393fb4b23819753f6e6dd51550ce030d53842c43dd1349857a96a61';
$sk = 'd6935ba259dd54b18a2a40fd1c5f8f0544ae8472db48d7726a02aab582d35a6336a05e418393fb4b23819753f6e6dd51550ce030d53842c43dd1349857a96a61';
$txMining = new MiningMessage($pk);
$miningMessage = $txMining->createMessage($sk);
$response = $client->callMethod(json_encode($miningMessage));

echo "Mining for user1", PHP_EOL, $response['tx_hash'], PHP_EOL;


die();















sleep(3);
$t = file_get_contents("http://127.0.0.1:8000/api/services/cryptocurrency/wallet/$pk2");
//    echo $t, PHP_EOL;
$balance = json_decode($t, true);

assert($balance['balance'] == 109);
$have = false;
foreach ($balance['assets'] as $asset) {
    if ((($asset['hash_id'] == 'a8d5c97d-9978-4111-9947-7a95dcb31d0f') && ($asset['amount'] == 30)) ||
        (($asset['hash_id'] == 'a8d5c97d-9978-4b0b-9947-7a95dcb31d0f') && ($asset['amount'] == 8))
    ) {
        echo "COOL", PHP_EOL;
        $have = true;
    }
}
if ( ! $have) {
    echo "BAD", PHP_EOL;
}

