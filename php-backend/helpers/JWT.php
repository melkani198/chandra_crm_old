<?php
/**
 * Vista CRM - JWT Helper
 */

class JWT {

    private static $secret = 'vista-crm-secret-key-change-in-production';
    private static $algo   = 'sha256';
    private static $expiryHours = 8;

    public static function encode($payload) {

        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        $payload['iat'] = time();
        $payload['exp'] = time() + (self::$expiryHours * 3600);

        $base64Header  = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            self::$algo,
            $base64Header . "." . $base64Payload,
            self::$secret,
            true
        );

        $base64Signature = self::base64UrlEncode($signature);

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    public static function decode($token) {

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        $signature = self::base64UrlDecode($base64Signature);

        $expectedSignature = hash_hmac(
            self::$algo,
            $base64Header . "." . $base64Payload,
            self::$secret,
            true
        );

        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Invalid token signature');
        }

        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token expired');
        }

        return $payload;
    }

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
