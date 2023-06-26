<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Category\Models\Category;

/**
 * Class ShopCategoriesResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses
 */
class ShopCategoriesResponse extends Response
{
    /**
     * @var Category[]
     */
    private $categories;

    /**
     * @param Category[]|null $categories
     */
    public function __construct(?array $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $shopCategories = [];
        foreach ($this->categories as $category) {
            $shopCategories[] = [
                'categoryId' => $category->getCategoryId(),
                'categoryName' => $category->getCategoryName()
            ];
        }

        return $shopCategories;
    }
}
