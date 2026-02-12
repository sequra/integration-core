<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\Shop\GetShopProductsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Shop\ShopProductsResponse;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;

/**
 * Class GetShopProductsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop
 */
class GetShopProductsHandler implements TopicHandlerInterface
{
    /**
     * @var ProductServiceInterface
     */
    protected $productService;

    /**
     * @param ProductServiceInterface $productService
     */
    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        $request = GetShopProductsRequest::fromPayload($payload);

        return new ShopProductsResponse(
            $this->productService->getShopProducts(
                $request->getPage(),
                $request->getLimit(),
                $request->getSearch()
            )
        );
    }
}
