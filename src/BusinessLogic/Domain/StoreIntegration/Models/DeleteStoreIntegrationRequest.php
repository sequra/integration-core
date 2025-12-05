<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models;

/**
 * Class DeleteStoreIntegrationRequest.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models
 */
class DeleteStoreIntegrationRequest
{
    /**
     * @var string $merchantId
     */
    private $merchantId;

    /**
     * @var string $integrationId
     */
    private $integrationId;

    /**
     * @param string $merchantId
     * @param string $integrationId
     */
    public function __construct(string $merchantId, string $integrationId)
    {
        $this->merchantId = $merchantId;
        $this->integrationId = $integrationId;
    }

    /**
     * @return string
     */
    public function getIntegrationId(): string
    {
        return $this->integrationId;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }
}
