<?php

namespace SeQura\Core\BusinessLogic\Domain\Stores\Models;

use SeQura\Core\BusinessLogic\Domain\Stores\Exceptions\EmptyStoreParameterException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

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
    protected $storeId;

    /**
     * @var string
     */
    protected $storeName;

    /**
     * @param string $storeId
     * @param string $storeName
     *
     * @throws EmptyStoreParameterException
     */
    public function __construct(string $storeId, string $storeName)
    {
        if (empty($storeId) || empty($storeName)) {
            throw new EmptyStoreParameterException(
                new TranslatableLabel('No parameter can be an empty string.', 'general.errors.empty')
            );
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

    /**
     * @param string $storeId
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @param string $storeName
     */
    public function setStoreName(string $storeName): void
    {
        $this->storeName = $storeName;
    }
}
