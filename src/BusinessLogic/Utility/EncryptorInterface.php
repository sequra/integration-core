<?php

namespace SeQura\Core\BusinessLogic\Utility;

/**
 * Class EncryptorInterface
 *
 * @package SeQura\Core\BusinessLogic\Utility
 */
interface EncryptorInterface
{
    /**
     * Encrypts a given string.
     *
     * @param string $data
     *
     * @return string
     */
    public function encrypt(string $data): string;

    /**
     * Decrypts a given string.
     *
     * @param string $encryptedData
     *
     * @return string
     */
    public function decrypt(string $encryptedData): string;
}
