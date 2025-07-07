<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;

/**
 * Class MockCredentialsService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockCredentialsService extends CredentialsService
{
    /**
     * @var ?Credentials $credentials
     */
    private $credentials;

    /**
     * @param string $countryCode
     *
     * @return ?Credentials
     */
    public function getCredentialsByCountryCode(string $countryCode): ?Credentials
    {
        return $this->credentials;
    }

    /**
     * @param Credentials $credentials
     *
     * @return void
     */
    public function setCredentials(Credentials $credentials): void
    {
        $this->credentials = $credentials;
    }
}
