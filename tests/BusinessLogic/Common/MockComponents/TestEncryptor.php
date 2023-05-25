<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Utility\EncryptorInterface;

/**
 * Class TestEncryptor
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class TestEncryptor implements EncryptorInterface
{
    /**
     * @inheritDoc
     */
    public function encrypt(string $data): string
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function decrypt(string $encryptedData): string
    {
        return $encryptedData;
    }
}
