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
     * @var AuthorizationCredentials
     */
    protected $authorizationCredentials;

    /**
     * @param string $environment
     * @param string|null $merchantId
     * @param AuthorizationCredentials $authorizationCredentials
     *
     * @throws InvalidEnvironmentException
     */
    public function __construct(
        string $environment,
        ?string $merchantId,
        AuthorizationCredentials $authorizationCredentials
    ) {
        if (!in_array($environment, [BaseProxy::LIVE_MODE,BaseProxy::TEST_MODE], true)) {
            throw new InvalidEnvironmentException();
        }

        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->authorizationCredentials = $authorizationCredentials;
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
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['connectionData'] = [
            'environment' => $this->environment,
            'merchantId' => $this->merchantId,
            'authorizationCredentials' => $this->authorizationCredentials->toArray()
        ];

        return $data;
    }
}
