<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Authorization;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\HttpClient;

/**
 * Class AuthorizedProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Authorization
 */
class AuthorizedProxy extends BaseProxy
{
    public const AUTHORIZATION_HEADER_KEY = 'Authorization';
    public const AUTHORIZATION_HEADER_VALUE_PREFIX = 'Authorization: Basic ';

    public const MERCHANT_ID_HEADER_KEY = 'Sequra-Merchant-Id';

    /**
     * @var ConnectionData $connectionData
     */
    protected $connectionData;

    /**
     * @var Deployment $deployment
     */
    protected $deployment;

    /**
     * @var string $merchantId
     */
    protected $merchantId;

    /**
     * AuthorizedProxy constructor.
     *
     * @param HttpClient $client
     * @param ConnectionData $connectionData
     * @param Deployment $deployment
     * @param string $merchantId
     */
    public function __construct(
        HttpClient $client,
        ConnectionData $connectionData,
        Deployment $deployment,
        string $merchantId
    ) {
        $this->httpClient = $client;
        $this->connectionData = $connectionData;
        $this->deployment = $deployment;
        $this->merchantId = $merchantId;

        parent::__construct(
            $client,
            $connectionData->getEnvironment() === self::LIVE_MODE ?
                $deployment->getLiveDeploymentURL()->getApiBaseUrl() : $deployment->getSandboxDeploymentURL()->getApiBaseUrl()
        );
    }

    /**
     * Retrieves request headers.
     *
     * @return array<string,string> Complete list of request headers.
     */
    protected function getHeaders(): array
    {
        $token = base64_encode(sprintf(
            '%s:%s',
            $this->connectionData->getAuthorizationCredentials()->getUsername(),
            $this->connectionData->getAuthorizationCredentials()->getPassword()
        ));

        return array_merge(
            parent::getHeaders(),
            [
                self::AUTHORIZATION_HEADER_KEY => self::AUTHORIZATION_HEADER_VALUE_PREFIX . $token,
                self::MERCHANT_ID_HEADER_KEY => $this->merchantId,
            ]
        );
    }
}
