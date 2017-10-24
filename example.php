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

for ($i = 0; $i<1; $i++) {
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
//    $response = $client->callMethod(json_encode($msg));
//    echo $response['tx_hash'], PHP_EOL;

    sleep(1);

    $assets1 = [
        [
            'hash_id' => 'a8d5c97d-9978-4b0b-9947-7a95dcb31d0f',
            'amount'  => 45,
        ],
        [
            'hash_id' => 'a8d5c97d-9978-4111-9947-7a95dcb31d0f',
            'amount'  => 45,

        ]
    ];

    $message  = new AddAssetMessage($pk1, $assets1);
    $msg      = $message->createMessage($sk1);
    $response = $client->callMethod(json_encode($msg));
    echo "AddAsset for user1", PHP_EOL;
//    print_r($msg);
    echo $response['tx_hash'], PHP_EOL;

//
//sleep(1);
//

//    $assets2  = [
//        [
//            'hash_id' => 'a8d5c97d-9978-4b0b-9947-7a95dcb31d0f',
//            'amount'  => 5,
//        ]
//    ];
//    $message  = new AddAssetMessage($pk2, $assets2);
//    $msg      = $message->createMessage($sk2);
//    $response = $client->callMethod(json_encode($msg));
//    echo "AddAsset for user2", PHP_EOL;
//    print_r($msg);
//    echo $response['tx_hash'], PHP_EOL;


sleep(1);


    $message  = new TransferMessage($pk1, $pk2, 10, []);
    $msg      = $message->createMessage($sk1);
    $response = $client->callMethod(json_encode($msg));
    echo "Send 10 coins form user1 to user2", PHP_EOL;
//    print_r($msg);
    echo $response['tx_hash'], PHP_EOL;

//
//sleep(1);
//

    $message  = new TransferMessage($pk1, $pk2, 3, [
        [
            'hash_id' => 'a8d5c97d-9978-4b0b-9947-7a95dcb31d0f',
            'amount'  => 3,

        ]
    ]);
    $msg      = $message->createMessage($sk1);
    echo join(",", unpack("C*", $message->createMessageForSignature())), PHP_EOL;
    $response = $client->callMethod(json_encode($msg));
    echo "Send 0 coins form user1 to user2 and asset", PHP_EOL;
//    print_r($msg);
    echo $response['tx_hash'], PHP_EOL;

//
//sleep(1);
//

    $message  = new TransferMessage($pk1, $pk2, 0, [
        [
            'hash_id' => 'a8d5c97d-9978-4111-9947-7a95dcb31d0f',
            'amount'  => 30,

        ]
    ]);
    $msg      = $message->createMessage($sk1);
    $response = $client->callMethod(json_encode($msg));
    echo "Send 0 coins form user1 to user2 and asset", PHP_EOL;
//    print_r($msg);
    echo $response['tx_hash'], PHP_EOL;

    sleep(3);
    $t = file_get_contents("http://127.0.0.1:8000/api/services/cryptocurrency/v1/wallet/$pk2");
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
    if (!$have) {
        echo "BAD", PHP_EOL;
    }

}
