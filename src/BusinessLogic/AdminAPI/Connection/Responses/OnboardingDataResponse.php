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
     * @var ConnectionData
     */
    protected $connectionData;

    /**
     * @var StatisticalData
     */
    protected $statisticalData;

    /**
     * @param ConnectionData|null $connectionData
     * @param StatisticalData|null $statisticalData
     */
    public function __construct(?ConnectionData $connectionData, ?StatisticalData $statisticalData)
    {
        $this->connectionData = $connectionData;
        $this->statisticalData = $statisticalData;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->connectionData) {
            return [];
        }

        return [
            'environment' => $this->connectionData->getEnvironment(),
            'username' => $this->connectionData->getAuthorizationCredentials()->getUsername(),
            'password' => $this->connectionData->getAuthorizationCredentials()->getPassword(),
            'merchantId' => $this->connectionData->getMerchantId(),
            'sendStatisticalData' => $this->statisticalData && $this->statisticalData->isSendStatisticalData()
        ];
    }
}
