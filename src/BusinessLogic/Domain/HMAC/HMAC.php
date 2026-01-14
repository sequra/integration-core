<?php

namespace SeQura\Core\BusinessLogic\Domain\HMAC;

/**
 * Class HMAC.
 *
 * @package SeQura\Core\BusinessLogic\Domain\HMAC
 */
class HMAC
{
    /**
     * @param string[] $payload
     * @param string $secret
     *
     * @return string
     */
    public static function generateHMAC(array $payload, string $secret): string
    {
        return hash_hmac(
            'sha256',
            implode('_', $payload),
            $secret
        );
    }

    /**
     * @param string[] $payload
     * @param string $secret
     * @param string $receivedHmac
     *
     * @return bool
     */
    public static function validateHMAC(array $payload, string $secret, string $receivedHmac): bool
    {
        $expectedHmac = self::generateHMAC($payload, $secret);

        return hash_equals($expectedHmac, $receivedHmac);
    }
}
