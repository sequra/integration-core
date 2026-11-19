<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Store;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\StoreInfo;

/**
 * Class StoreInfoResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Store
 */
class StoreInfoResponse extends Response
{
    /**
     * @var StoreInfo $storeInfo
     */
    protected $storeInfo;

    /**
     * @param StoreInfo $storeInfo
     */
    public function __construct(StoreInfo $storeInfo)
    {
        $this->storeInfo = $storeInfo;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->storeInfo->toArray();
    }
}
