<?php
/**
 * nats_example.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */

use Nats\ConnectionOptions;
use Nats\EncodedConnection;
use Nats\Encoders\JSONEncoder;

include_once 'vendor/autoload.php';

$encoder = new JSONEncoder();
$options = new ConnectionOptions([
     'host' => '35.197.200.238',
     'user' => 'vRJY4vOIoNSsG72F',
     'pass' => 'UCF0IsKwMlYON4g7',
]);
$client = new EncodedConnection($options, $encoder);

do {
    $client->connect();
    printf('Connected to NATS at %s'.PHP_EOL, $client->connectedServerId());

    // Responding to requests.
    $sid = $client->subscribe(
        'transaction.commit',
        function ($message) {
            $transactionResponse = $message->getBody();
            print_r($transactionResponse);
        }
    );

    $client->wait(1000);

} while (true);
