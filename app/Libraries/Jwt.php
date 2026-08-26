<?php

namespace App\Libraries;

/**
 * Encoder/decoder JWT minimal (HS256 only), tanpa dependency eksternal.
 *
 * Cukup untuk kebutuhan internal API authentication. Jika composer punya akses
 * jaringan di kemudian hari, pertimbangkan pindah ke firebase/php-jwt.
 */
class Jwt
{
    public static function encode(array $payload, string $secret, int $ttlSeconds = 3600): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $payload['iat'] ??= time();
        $payload['exp'] ??= time() + $ttlSeconds;

        $segments = [
            self::base64UrlEncode(json_encode($header)),
            self::base64UrlEncode(json_encode($payload)),
        ];

        $signature  = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * Mengembalikan payload jika token valid & belum kedaluwarsa, null jika tidak.
     */
    public static function decode(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $expectedSignature = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $secret, true);
        $givenSignature     = self::base64UrlDecode($signatureB64);

        if ($givenSignature === false || ! hash_equals($expectedSignature, $givenSignature)) {
            return null;
        }

        $payloadJson = self::base64UrlDecode($payloadB64);
        $payload     = $payloadJson === false ? null : json_decode($payloadJson, true);

        if (! is_array($payload)) {
            return null;
        }

        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            return null;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}
