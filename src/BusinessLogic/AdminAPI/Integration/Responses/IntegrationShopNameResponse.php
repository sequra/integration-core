<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class IntegrationShopNameResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses
 */
class IntegrationShopNameResponse extends Response
{
    /**
     * @var string
     */
    protected $shopName;

    /**
     * @param string $shopName
     */
    public function __construct(string $shopName)
    {
        $this->shopName = $shopName;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'shopName' => $this->shopName
        ];
    }
}
