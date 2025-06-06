<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;

/**
 * Class MockCredentialsRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockCredentialsRepository implements CredentialsRepositoryInterface
{
    /**
     * @var Credentials[]
     */
    private static $credentials = [];

    /**
     * @param Credentials[] $credentials
     *
     * @return void
     */
    public function setCredentials(array $credentials): void
    {
        self::$credentials = $credentials;
    }

    /**
     * @return Credentials[]
     */
    public function getCredentials(): array
    {
        return self::$credentials;
    }

    /**
     * @return void
     */
    public function deleteCredentials(): void
    {
    }

    /**
     * @param string $countryCode
     *
     * @return ?Credentials
     */
    public function getCredentialsByCountryCode(string $countryCode): ?Credentials
    {
        foreach (self::$credentials as $credential) {
            if ($credential->getCountry() === $countryCode) {
                return $credential;
            }
        }

        return null;
    }
}
