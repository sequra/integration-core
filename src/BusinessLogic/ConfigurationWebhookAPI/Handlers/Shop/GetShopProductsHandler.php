<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Shop\ShopProductsResponse;
use SeQura\Core\BusinessLogic\Domain\Integration\ShopProduct\ShopProductServiceInterface;

/**
 * Class GetShopProductsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop
 */
class GetShopProductsHandler implements TopicHandlerInterface
{
    /**
     * @var ShopProductServiceInterface
     */
    protected $shopProductService;

    /**
     * @param ShopProductServiceInterface $shopProductService
     */
    public function __construct(ShopProductServiceInterface $shopProductService)
    {
        $this->shopProductService = $shopProductService;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        return new ShopProductsResponse($this->shopProductService->getShopProducts());
    }
}
