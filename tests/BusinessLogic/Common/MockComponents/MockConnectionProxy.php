<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\CredentialsRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiInvalidUrlParameterException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiUnauthorizedException;

/**
 * Class MockConnectionProxy.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockConnectionProxy implements ConnectionProxyInterface
{
    /**
     * @var Credentials[]
     */
    private $credentials = [];

    /**
     * @var bool
     */
    private $wrongCredentials = false;

    /**
     * @var bool
     */
    private $badMerchantId = false;

    /**
     * @param CredentialsRequest $request
     *
     * @return Credentials[]
     *
     * @throws HttpApiInvalidUrlParameterException
     * @throws HttpApiUnauthorizedException
     */
    public function getCredentials(CredentialsRequest $request): array
    {
        if ($this->wrongCredentials) {
            throw new HttpApiUnauthorizedException();
        }

        if ($this->badMerchantId) {
            throw new HttpApiInvalidUrlParameterException();
        }

        return $this->credentials;
    }

    /**
     * @param Credentials[] $credentials
     *
     * @return void
     */
    public function setMockCredentials(array $credentials): void
    {
        $this->credentials = $credentials;
    }

    /**
     * @param bool $badMerchantId
     */
    public function setBadMerchantId(bool $badMerchantId): void
    {
        $this->badMerchantId = $badMerchantId;
    }

    /**
     * @param bool $wrongCredentials
     *
     * @return void
     */
    public function setWrongCredentials(bool $wrongCredentials): void
    {
        $this->wrongCredentials = $wrongCredentials;
    }
}
