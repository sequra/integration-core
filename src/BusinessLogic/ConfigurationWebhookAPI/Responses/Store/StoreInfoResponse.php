<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Store;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class StoreInfoResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Store
 */
class StoreInfoResponse extends Response
{
    /**
     * @var bool
     */
    protected $successful = true;

    /**
     * @var array{
     *     store_name: string,
     *     store_url: string,
     *     platform: string,
     *     platform_version: string,
     *     plugin_version: string,
     *     php_version: string,
     *     db: string,
     *     os: string,
     *     plugins: string[]
     * }
     */
    protected $storeInfo;

    /**
     * @param array{
     *     store_name: string,
     *     store_url: string,
     *     platform: string,
     *     platform_version: string,
     *     plugin_version: string,
     *     php_version: string,
     *     db: string,
     *     os: string,
     *     plugins: string[]
     * } $storeInfo
     */
    public function __construct(array $storeInfo)
    {
        $this->storeInfo = $storeInfo;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->storeInfo;
    }
}
