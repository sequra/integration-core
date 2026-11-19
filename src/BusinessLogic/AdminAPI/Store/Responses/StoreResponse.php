<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Store\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\Store;

/**
 * Class StoreResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Store\Responses
 */
class StoreResponse extends Response
{
    /**
     * @var Store
     */
    protected $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'storeId' => $this->store->getStoreId(),
            'storeName' => $this->store->getStoreName()
        ];
    }
}
