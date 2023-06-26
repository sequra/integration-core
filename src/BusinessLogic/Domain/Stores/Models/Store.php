<?php

namespace SeQura\Core\BusinessLogic\Domain\Stores\Models;

use SeQura\Core\BusinessLogic\Domain\Stores\Exceptions\EmptyStoreParameterException;

/**
 * Class Store
 *
 * @package SeQura\Core\BusinessLogic\Domain\Stores\Models
 */
class Store
{
    /**
     * @var string
     */
    private $storeId;

    /**
     * @var string
     */
    private $storeName;

    /**
     * @param string $storeId
     * @param string $storeName
     *
     * @throws EmptyStoreParameterException
     */
    public function __construct(string $storeId, string $storeName)
    {
        if(empty($storeId) || empty($storeName)) {
            throw new EmptyStoreParameterException('No parameter can be an empty string.');
        }

        $this->storeId = $storeId;
        $this->storeName = $storeName;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @return string
     */
    public function getStoreName(): string
    {
        return $this->storeName;
    }
}
