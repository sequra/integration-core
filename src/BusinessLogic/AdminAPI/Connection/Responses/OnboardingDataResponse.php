<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;

/**
 * Class OnboardingDataResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses
 */
class OnboardingDataResponse extends Response
{
    /**
     * @var ConnectionData[]
     */
    protected $connectionData;

    /**
     * @var StatisticalData
     */
    protected $statisticalData;

    /**
     * @var string|null
     */
    protected $portalUrl;

    /**
     * @param ConnectionData[] $connectionData
     * @param StatisticalData|null $statisticalData
     * @param string|null $portalUrl URL of the SeQura portal the store is connected to
     */
    public function __construct(array $connectionData, ?StatisticalData $statisticalData, ?string $portalUrl = null)
    {
        $this->connectionData = $connectionData;
        $this->statisticalData = $statisticalData;
        $this->portalUrl = $portalUrl;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->connectionData) {
            return [];
        }

        $response['sendStatisticalData'] = $this->statisticalData && $this->statisticalData->isSendStatisticalData();
        $response['portalUrl'] = $this->portalUrl;

        foreach ($this->connectionData as $connectionData) {
            $response['environment'] = $connectionData->getEnvironment();
            $response['connectionData'][] = [
                'username' => $connectionData->getAuthorizationCredentials()->getUsername(),
                'password' => $connectionData->getAuthorizationCredentials()->getPassword(),
                'merchantId' => $connectionData->getMerchantId(),
                'deployment' => $connectionData->getDeployment()
            ];
        }

        return $response;
    }
}
