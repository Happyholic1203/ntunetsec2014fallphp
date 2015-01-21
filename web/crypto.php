<?php
// java -jar Crypto.jar sign <content> <privKey>
// java -jar Crypto.jar verify <content> <signature> <pubKey>
// java -jar Crypto.jar genKey
define('EXEC_CRYPTO', 'java -jar lib/Crypto.jar ');

class Crypto {
    private static $pubKey = '3059301306072A8648CE3D020106082A8648CE3D03010703420004C4E59419DDEDA6260603922F47A7F34EC0F325CEB0E86964CC1727C7CE31755E94589205C1CEF6D02457E3ED7153DC0E7482E0F8EA01B9497D14A4176B851A66';
    private static $privKey = '308193020100301306072A8648CE3D020106082A8648CE3D030107047930770201010420852AFA779733F2870A0AD39362E93FA9B088123AF00B826FE535F5BADA1E950BA00A06082A8648CE3D030107A14403420004C4E59419DDEDA6260603922F47A7F34EC0F325CEB0E86964CC1727C7CE31755E94589205C1CEF6D02457E3ED7153DC0E7482E0F8EA01B9497D14A4176B851A66';

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