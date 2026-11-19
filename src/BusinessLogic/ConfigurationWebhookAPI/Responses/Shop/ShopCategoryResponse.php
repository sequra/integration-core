<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Shop;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;

/**
 * Class ShopCategoryResponse.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Shop
 */
class ShopCategoryResponse extends Response
{
    /**
     * @var Category[]
     */
    protected $categories;

    /**
     * @param Category[] $categories
     */
    public function __construct(array $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_map(function (Category $category) {
            return $category->toArray();
        }, $this->categories);
    }
}
