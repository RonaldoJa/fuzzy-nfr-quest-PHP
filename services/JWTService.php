<?php
class JWTPayload
{
    public $id;
    public $email;
    public $name;
    public $last_name;
    public $exp;
}

class JWTService
{
    private static $secretKey;
    private static $ttl;

    public static function initialize()
    {
        if (empty(self::$secretKey)) {
            self::$secretKey = getenv('JWT_SECRET');
        }

        if (empty(self::$ttl)) {
            self::$ttl = intval(getenv('JWT_TTL') ?: 60);
        }
    }

    public static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function generateJWT($id, $email, $name, $last_name)
    {
        self::initialize();

        $payload = new JWTPayload();
        $payload->id = $id;
        $payload->email = $email;
        $payload->name = $name;
        $payload->last_name = $last_name;

        $payload->exp = time() + (60 * self::$ttl);

        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verifyJWT($jwt)
    {
        self::initialize();

        list($header, $payload, $signature) = explode('.', $jwt);
        $validSignature = self::base64UrlEncode(hash_hmac('sha256', $header . "." . $payload, self::$secretKey, true));

        if (!hash_equals($signature, $validSignature)) {
            return false;
        }

        $payloadArray = json_decode(base64_decode($payload), true);

        if (isset($payloadArray['exp']) && $payloadArray['exp'] < time()) {
            return false;
        }

        return $payloadArray;
    }
}
