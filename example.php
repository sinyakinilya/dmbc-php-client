<?php
/**
 * example.php
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
use SunTechSoft\Blockchain\MiningMessage;
use SunTechSoft\Blockchain\TradeMessage;
use SunTechSoft\Blockchain\TransferMessage;

include_once 'vendor/autoload.php';

$client = new Client('127.0.0.1', 8000);

/**
 * methods:
 *  1 - createWallet (create and setup 100 coins)
 *  3 - addAssets (assets)
 *  2 - transfer (couin and assets)
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
    ->addAsset('u1_asset1', 10)
    ->addAsset('u1_asset2', 1);

$message  = new AddAssetMessage($pk1, $assets1->toArray());
$msg      = $message->createMessage($sk1);
$response = $client->callMethod(json_encode($msg));
$bcUser1Assets = $response['transaction_info']['external_internal'];
echo "AddAsset for user1", PHP_EOL, print_r($response['tx_hash']), PHP_EOL;


$assets2  = (new Assets())
    ->addAsset('u2_asset1', 3);

$message  = new AddAssetMessage($pk2, $assets2->toArray());
$msg      = $message->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
$bcUser2Assets = $response['transaction_info']['external_internal'];
echo "AddAsset for user2", PHP_EOL, print_r($response['tx_hash']), PHP_EOL;

$bcAssets = $bcUser1Assets + $bcUser2Assets;

sleep(1);
$offerAssets = (new Assets)
    ->addAsset($bcAssets['u1_asset1'], 5)
    ->addAsset($bcAssets['u1_asset2'], 1);

$tradeOffer = new TradeOffer($pk1, $offerAssets, 20);
$tradeOffer->setSignature($sk1);

$txTrade = new TradeMessage($pk2, $tradeOffer);
$msg = $txTrade->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
echo "Sell assets", PHP_EOL, $response['tx_hash'], PHP_EOL;


sleep(1);
$sendAssets = (new Assets)
    ->addAsset($bcAssets['u2_asset1'], 3);

$message  = new TransferMessage($pk2, $pk1, 10, $sendAssets->toArray());
$msg      = $message->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
echo "Send 10 coins and 3 assets form user2 to user1", PHP_EOL;
echo $response['tx_hash'], PHP_EOL;



sleep(1);

$senderAssets = (new Assets)
    ->addAsset($bcAssets['u1_asset1'], 3)
    ->addAsset($bcAssets['u2_asset1'], 1);

$recipientAssets = (new Assets)
    ->addAsset($bcAssets['u1_asset2'], 1);

$exchangeOffer = new ExchangeOffer($pk1, $senderAssets, '37', $pk2, $recipientAssets, '0', 1);
$exchangeOffer->setSignature($sk1);
$txExchange = new ExchangeMessage($exchangeOffer);
$msg = $txExchange->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
echo "Exchange", PHP_EOL, $response['tx_hash'], PHP_EOL;



sleep(1);

$txMining = new MiningMessage($pk1);
$miningMessage = $txMining->createMessage($sk1);
$response = $client->callMethod(json_encode($miningMessage));

echo "Mining for user1", PHP_EOL, $response['tx_hash'], PHP_EOL;
