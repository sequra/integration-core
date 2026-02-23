<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;

/**
 * Interface CredentialsRepositoryInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts
 */
interface CredentialsRepositoryInterface
{
    /**
     * @param Credentials[] $credentials
     *
     * @return void
     */
    public function setCredentials(array $credentials): void;

    /**
     * Returns all credentials for current store context.
     *
     * @return Credentials[]
     */
    public function getCredentials(): array;

    /**
     * Deletes all credentials for deploymentId from database and returns merchant ids connected that deployment.
     *
     * @param string $deploymentId
     *
     * @return array<string>
     */
    public function deleteCredentialsByDeploymentId(string $deploymentId): array;

    /**
     * Retrieves credentials for specific country code.
     *
     * @param string $countryCode
     *
     * @return ?Credentials
     */
    public function getCredentialsByCountryCode(string $countryCode): ?Credentials;

    /**
     * Retrieves credentials for specific merchant ID.
     *
     * @param string $merchantId
     *
     * @return ?Credentials
     */
    public function getCredentialsByMerchantId(string $merchantId): ?Credentials;
}
