<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Store\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\Store;

/**
 * Class StoresResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Store\Responses
 */
class StoresResponse extends Response
{
    /**
     * @var Store[]
     */
    protected $stores;

    /**
     * @param Store[] $stores
     */
    public function __construct(array $stores)
    {
        $this->stores = $stores;
    }

    /**
     * @inheritDocs
     */
    public function toArray(): array
    {
        return array_map(static function (Store $store): array {
            return (new StoreResponse($store))->toArray();
        }, $this->stores);
    }
}
