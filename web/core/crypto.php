<?php
require_once('config.php');

// java -jar Crypto.jar sign <content> <privKey>
// java -jar Crypto.jar verify <content> <signature> <pubKey>
// java -jar Crypto.jar genKey
define('EXEC_CRYPTO', 'java -jar ../lib/Crypto.jar ');

class Crypto {
    private static $pubKey = SERVER_PUBLIC_KEY;
    private static $privKey = SERVER_PRIVATE_KEY;

    public static function sign($content) {
        return self::execCrypto(
            array(
                'sign',
                $content,
                self::$privKey
            )
        )[0];
    }

    public static function verify($content, $signature) {
        return self::execCrypto(
            array(
                'verify',
                $content,
                $signature,
                self::$pubKey
            )
        )[0];
    }

    private static function execCrypto($params) {
        array_walk($params, 'self::sanitize');
        exec(EXEC_CRYPTO. implode(' ', $params), $output);
        return $output;
    }

    private static function sanitize(&$item, $key) {
        $item = '"'. $item. '"';
    }
}

// $data = 'sign on me and verify!!';
// $sig = Crypto::sign($data);
// echo 'Verify: '. Crypto::verify($data, $sig);
?>