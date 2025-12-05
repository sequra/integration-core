<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Models;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class ConnectionData
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Models
 */
class ConnectionData extends DataTransferObject
{
    /**
     * @var string
     */
    protected $environment;

    /**
     * @var string|null
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $deployment;

    /**
     * @var AuthorizationCredentials
     */
    protected $authorizationCredentials;

    /**
     * @var string
     */
    protected $integrationId;

    /**
     * @param string $environment
     * @param string|null $merchantId
     * @param string $deployment
     * @param AuthorizationCredentials $authorizationCredentials
     * @param string $integrationId
     *
     * @throws InvalidEnvironmentException
     */
    public function __construct(
        string $environment,
        ?string $merchantId,
        string $deployment,
        AuthorizationCredentials $authorizationCredentials,
        string $integrationId = ''
    ) {
        if (!in_array($environment, [BaseProxy::LIVE_MODE, BaseProxy::TEST_MODE], true)) {
            throw new InvalidEnvironmentException();
        }

        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->deployment = $deployment;
        $this->authorizationCredentials = $authorizationCredentials;
        $this->integrationId = $integrationId;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    /**
     * @return AuthorizationCredentials
     */
    public function getAuthorizationCredentials(): AuthorizationCredentials
    {
        return $this->authorizationCredentials;
    }

    /**
     * @param AuthorizationCredentials $authorizationCredentials
     */
    public function setAuthorizationCredentials(AuthorizationCredentials $authorizationCredentials): void
    {
        $this->authorizationCredentials = $authorizationCredentials;
    }

    /**
     * @return string|null
     */
    public function getMerchantId(): ?string
    {
        return $this->merchantId;
    }

    /**
     * @param string|null $merchantId
     */
    public function setMerchantId(?string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getDeployment(): string
    {
        return $this->deployment;
    }

    /**
     * @param string $deployment
     *
     * @return void
     */
    public function setDeployment(string $deployment): void
    {
        $this->deployment = $deployment;
    }

    /**
     * @return string
     */
    public function getIntegrationId(): string
    {
        return $this->integrationId;
    }

    /**
     * @param string $integrationId
     *
     * @return void
     */
    public function setIntegrationId(string $integrationId): void
    {
        $this->integrationId = $integrationId;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['connectionData'] = [
            'environment' => $this->environment,
            'merchantId' => $this->merchantId,
            'deployment' => $this->deployment,
            'authorizationCredentials' => $this->authorizationCredentials->toArray(),
            'integrationId' => $this->integrationId
        ];

        return $data;
    }
}
