<?php

namespace SeQura\Core\BusinessLogic\Domain\Multistore;

use Exception;

/**
 * Class StoreContext
 *
 * @package SeQura\Core\BusinessLogic\Domain\Multistore
 */
class StoreContext
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var string
     */
    private $storeId = '';

    private function __construct()
    {
    }

    /**
     * Executes callback method with set store id.
     *
     * @param string $storeId
     * @param callable $callback
     * @param array $params
     *
     * @return mixed
     * @throws Exception
     *
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

    public static function getInstance(): StoreContext
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
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
}
