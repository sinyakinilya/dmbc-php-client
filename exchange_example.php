<?php
/**
 * exchange_example.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

use SunTechSoft\Blockchain\AddAssetMessage;
use SunTechSoft\Blockchain\CreateWalletMessage;
use SunTechSoft\Blockchain\Client;
use SunTechSoft\Blockchain\ExchangeMessage;
use SunTechSoft\Blockchain\Helper\Assets;
use SunTechSoft\Blockchain\Helper\Cryptography;
use SunTechSoft\Blockchain\Helper\ExchangeOffer;
use SunTechSoft\Blockchain\Helper\TradeOffer;

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
$pk1 = $pk2 = $sk1 = $sk2 = '';
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

$assets = (new Assets())
    ->addAsset('a8d5c97d-9978-4b0b-9947-7a95dcb31d0f', 45)
    ->addAsset('a8d5c97d-9978-4111-9947-7a95dcb31d0f', 17);

$message  = new AddAssetMessage($pk1, $assets->toArray());
$msg      = $message->createMessage($sk1);
$response = $client->callMethod(json_encode($msg));
echo "AddAsset for user1", PHP_EOL, $response['tx_hash'], PHP_EOL;

$assets  = (new Assets())
    ->addAsset('a8d5c97d-9978-cccc-9947-7a95dcb31d0f', 5);

$message  = new AddAssetMessage($pk2, $assets->toArray());
$msg      = $message->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
echo "AddAsset for user2", PHP_EOL, $response['tx_hash'], PHP_EOL;


sleep(1);

$senderAssets = (new Assets)
    ->addAsset('a8d5c97d-9978-4b0b-9947-7a95dcb31d0f', 5)
    ->addAsset('a8d5c97d-9978-4111-9947-7a95dcb31d0f', 7);

$recipientAssets = (new Assets)
    ->addAsset('a8d5c97d-9978-cccc-9947-7a95dcb31d0f', 1);

$exchangeOffer = new ExchangeOffer($pk1, $senderAssets, '37', $pk2, $recipientAssets, '0', 1);
$exchangeOffer->setSignature($sk1);

$txExchange = new ExchangeMessage($exchangeOffer);
//echo join(',', unpack('C*', $txExchange->createMessageForSignature())) . PHP_EOL;

$msg = $txExchange->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
echo "Exchange", PHP_EOL, $response['tx_hash'], PHP_EOL;


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

