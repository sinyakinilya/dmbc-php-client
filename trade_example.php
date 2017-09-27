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
use SunTechSoft\Blockchain\Helper\Offer;
use SunTechSoft\Blockchain\TradeMessage;
use SunTechSoft\Blockchain\TransferMessage;

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
echo "AddAsset for user1", PHP_EOL, $response['tx_hash'], PHP_EOL;

$assets2  = (new Assets())
    ->addAsset('a8d5c97d-9978-4b0b-9947-7a95dcb31d0f', 5);

$message  = new AddAssetMessage($pk2, $assets2->toArray());
$msg      = $message->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
echo "AddAsset for user2", PHP_EOL, $response['tx_hash'], PHP_EOL;


sleep(1);

$offerAssets = (new Assets)
    ->addAsset('a8d5c97d-9978-4b0b-9947-7a95dcb31d0f', 5)
    ->addAsset('a8d5c97d-9978-4111-9947-7a95dcb31d0f', 7);

$offer = new Offer($pk1, $offerAssets, 37);
$offer->setSignature($sk1);

$txTrade = new TradeMessage($pk2, $offer);
echo join(',', unpack('C*', $txTrade->createMessageForSignature())) . PHP_EOL;

/*
$t =
[0,0,5,0,1,0,90,1,0,0, //meta
210,160,50,101,47,197,24,234,182,90,240,164,214,24,190,104,169,142,101,50,13,230,243,87,178,117,74,74,129,32,216,106, //buyer
58,0,0,0,224,0,0,0, //offer
110,0,0,0,0,0,0,0, // seed
153,133,145,152,89,66,193,44,187,162,50,238,247,85,139,87,27,254,250,66,80,148,252,2,55,0,239,110,137,251,57,209, // seller
112,0,0,0,2,0,0,0, // vec<Asset>
37,0,0,0,0,0,0,0,  // price
232,98,160,169,2,163,131,202,190,60,199,24,204,208,157,30,68,204,123,9,120,212,165,34,103,216,163,194,40,255,64,45,212,241,60,136,61,242,110,17,182,1,159,137,115,155,53,22,176,41,95,76,144,155,249,140,248,136,160,233,255,211,81,2, //signature
128,0,0,0,48,0,0,0,176,0,0,0,48,0,0,0, //position assets
12,0,0,0,36,0,0,0,5,0,0,0,97,56,100,53,99,57,55,100,45,57,57,55,56,45,52,98,48,98,45,57,57,52,55,45,55,97,57,53,100,99,98,51,49,100,48,102,  // asset 1
12,0,0,0,36,0,0,0,7,0,0,0,97,56,100,53,99,57,55,100,45,57,57,55,56,45,52,49,49,49,45,57,57,52,55,45,55,97,57,53,100,99,98,51,49,100,48,102]; // asset 2

$tt =
[0,0,5,0,1,0,122,1,0,0, //meta info
210,160,50,101,47,197,24,234,182,90,240,164,214,24,190,104,169,142,101,50,13,230,243,87,178,117,74,74,129,32,216,106, //buyer
90,0,0,0,224,0,0,0, // offer(start, length)
110,0,0,0,0,0,0,0,  // seed
0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, //   that's this??????
153,133,145,152,89,66,193,44,187,162,50,238,247,85,139,87,27,254,250,66,80,148,252,2,55,0,239,110,137,251,57,209, //seller
112,0,0,0,2,0,0,0,   //vec<Asset> info
37,0,0,0,0,0,0,0,    // price
232,98,160,169,2,163,131,202,190,60,199,24,204,208,157,30,68,204,123,9,120,212,165,34,103,216,163,194,40,255,64,45,212,241,60,136,61,242,110,17,182,1,159,137,115,155,53,22,176,41,95,76,144,155,249,140,248,136,160,233,255,211,81,2, //offer signature
128,0,0,0,48,0,0,0,176,0,0,0,48,0,0,0, // assets position
12,0,0,0,36,0,0,0,5,0,0,0,97,56,100,53,99,57,55,100,45,57,57,55,56,45,52,98,48,98,45,57,57,52,55,45,55,97,57,53,100,99,98,51,49,100,48,102, //asset 1
12,0,0,0,36,0,0,0,7,0,0,0,97,56,100,53,99,57,55,100,45,57,57,55,56,45,52,49,49,49,45,57,57,52,55,45,55,97,57,53,100,99,98,51,49,100,48,102, //asset 2
103,13,78,15,185,248,14,134,232,147,245,69,2,240,10,144,92,171,140,135,242,9,161,251,211,84,119,117,54,61,231,197,70,211,125,213,190,111,22,2,92,244,161,214,106,187,118,129,216,186,218,247,96,7,64,14,209,90,78,8,227,223,220,14] // transaction signature
*/




$msg = $txTrade->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
echo "Trade for user2", PHP_EOL, $response['tx_hash'], PHP_EOL;




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

