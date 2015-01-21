<?php
require_once('config.php');
require_once('crypto.php');

class Security {
    /**
     * [issueCertificateFor description]
     * @param  string $id        User id
     * @param  string $type      User type
     * @param  string $publickey User publickey
     * @return string            Certificate
     */
    public static function issueCertificateFor($id, $type, $publickey) {
        $certContent = implode(',', array($id, $type, $publickey));
        $serverSignature = Crypto::sign($certContent);
        return $certContent. "|". $serverSignature;
    }

}
?>