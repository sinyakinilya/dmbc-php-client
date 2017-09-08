<?php
/**
 * Cryptography.php
 *
 * @author   Ilya Sinyakin <sinyakin.ilya@gmail.com>
 */
declare(strict_types=1);

namespace SunTechSoft\Blockchain\Helper;

final class Cryptography
{
    public static function generateKeys(&$publicKey, &$privateKey)
    {
        $alice_sign_kp = \Sodium\crypto_sign_keypair();
        // Split the key for the crypto_sign API for ease of use
        $alice_sign_publickey = \Sodium\crypto_sign_publickey($alice_sign_kp);
        $alice_sign_secretkey = \Sodium\crypto_sign_secretkey($alice_sign_kp);

        $publicKey = \Sodium\bin2hex($alice_sign_publickey);
        $privateKey = \Sodium\bin2hex($alice_sign_secretkey);
    }

}
