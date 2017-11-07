# DMarket blockchain PHP client

This PHP client is for composing and signing transactions for DMarket Blockchain.

Messages:
 * CreateWallets (create and setup 100 coins)
 * AddAssets (assets)
 * Trade assets
 * Transfer (send coins and/or assets)
 * Exchange (assets)
 * Mining (coins)


### Generate keys

```php
Cryptography::generateKeys($pk1, $sk1);
Cryptography::generateKeys($pk2, $sk2);
```

### Create wallet
```php
/** first wallet */
$message  = new CreateWalletMessage($pk1);
$msg      = $message->createMessage($sk1);

$response = $client->callMethod(json_encode($msg));

/** second wallet */
$message  = new CreateWalletMessage($pk2);
$msg      = $message->createMessage($sk2);

$response = $client->callMethod(json_encode($msg));
```

### Add assets
```php
/** first wallet */
$assets1 = (new Assets())
    ->addAsset('u1_asset1', 10)
    ->addAsset('u1_asset2', 1);

$message  = new AddAssetMessage($pk1, $assets1->toArray());
$msg      = $message->createMessage($sk1);
$response = $client->callMethod(json_encode($msg));

$bcUser1Assets = $response['transaction_info']['external_internal'];

/** second wallet */
$assets2  = (new Assets())
    ->addAsset('u2_asset1', 3);

$message  = new AddAssetMessage($pk2, $assets2->toArray());
$msg      = $message->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
$bcUser2Assets = $response['transaction_info']['external_internal'];
```

### Trade assets
```php
$offerAssets = (new Assets)
    ->addAsset($bcAssets['u1_asset1'], 5)
    ->addAsset($bcAssets['u1_asset2'], 1);

$tradeOffer = new TradeOffer($pk1, $offerAssets, 20);
$tradeOffer->setSignature($sk1);

$txTrade = new TradeMessage($pk2, $tradeOffer);
$msg = $txTrade->createMessage($sk2);
$response = $client->callMethod(json_encode($msg));
```

### Send coins and assets
```php
$sendAssets = (new Assets)
    ->addAsset($bcAssets['u2_asset1'], 3);

$message  = new TransferMessage($pk2, $pk1, 10, $sendAssets->toArray());
$msg      = $message->createMessage($sk2);

$response = $client->callMethod(json_encode($msg));
```

### Exchange assets 
```php
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
```

## Mining 1000000 coins
```php
$txMining = new MiningMessage($pk1);
$miningMessage = $txMining->createMessage($sk1);
$response = $client->callMethod(json_encode($miningMessage));
```
