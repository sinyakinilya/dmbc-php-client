<?php
/**
 * example.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

use SunTechSoft\Blockchain\CreateWalletMessage;
use SunTechSoft\Blockchain\AddAssetMessage;
use SunTechSoft\Blockchain\DelAssetMessage;
use SunTechSoft\Blockchain\Client;
use SunTechSoft\Blockchain\Helper\Cryptography;

include_once 'vendor/autoload.php';

$client = new Client('127.0.0.1', 8000);

/**
 * methods:
 *  1 - createWallet
 *  2 - sendCoin
 */

/** CreateWallet */

Cryptography::generateKeys($pk, $sk);
file_put_contents('wallet.json', $sk . PHP_EOL, FILE_APPEND);

$message = new CreateWalletMessage($pk);

$msg = $message->createMessage($sk);
$response = $client->callMethod(json_encode($msg));
print_r($response);


sleep(1);

$message = new AddAssetMessage($pk, 'a8d5c97d-9978-4b0b-9947-7a95dcb31d0f', 7);
$msg = $message->createMessage($sk);
$response = $client->callMethod(json_encode($msg));
print_r($response);

sleep(1);

$message = new DelAssetMessage($pk, 'a8d5c97d-9978-4b0b-9947-7a95dcb31d0f', 2);
$msg = $message->createMessage($sk);
$response = $client->callMethod(json_encode($msg));
print_r($response);
