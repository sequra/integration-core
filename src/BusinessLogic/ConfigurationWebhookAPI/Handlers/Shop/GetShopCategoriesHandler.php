<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\Shop\GetShopCategoriesRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Shop\ShopCategoryResponse;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;

/**
 * Class GetShopCategoriesHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop
 */
class GetShopCategoriesHandler implements TopicHandlerInterface
{
    /**
     * @var CategoryServiceInterface
     */
    protected $categoryService;

    /**
     * @param CategoryServiceInterface $categoryService
     */
    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @param mixed[] $payload
     *
     * @return ShopCategoryResponse
     */
    public function handle(array $payload): Response
    {
        $request = GetShopCategoriesRequest::fromPayload($payload);

        return new ShopCategoryResponse(
            $this->categoryService->getCategories(
                $request->getPage(),
                $request->getLimit(),
                $request->getSearch()
            )
        );
    }
}
