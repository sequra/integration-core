<?php

namespace SeQura\Core\BusinessLogic\Domain\Multistore;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreIdProvider;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class StoreContext
 *
 * @package SeQura\Core\BusinessLogic\Domain\Multistore
 */
/**
 * @phpstan-consistent-constructor
 */
class StoreContext
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var string
     */
    protected $storeId;

    protected function __construct()
    {
        $this->setDefaultStoreId();
    }

    public static function getInstance(): StoreContext
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Executes callback method with set store id.
     *
     * @param string $storeId
     * @param callable $callback
     * @param mixed[] $params
     *
     * @throws Exception
     *
     * @return mixed
     */
    public static function doWithStore(string $storeId, callable $callback, array $params = [])
    {
        $previousStoreId = self::getInstance()->storeId;
        try {
            self::getInstance()->storeId = $storeId;

            $result = call_user_func_array($callback, $params);
        } finally {
            self::getInstance()->storeId = $previousStoreId;
        }

        return $result;
    }

    /**
     * Retrieves store id.
     *
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * Sets the default store ID.
     */
    protected function setDefaultStoreId(): void
    {
        /**
         * @var StoreIdProvider $storeIdProvider
         */
        $storeIdProvider = ServiceRegister::getService(StoreIdProvider::class);
        $this->storeId = $storeIdProvider->getCurrentStoreId();
    }
}
