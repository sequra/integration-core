<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;

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
    protected $categories;

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
                'id' => $category->getId(),
                'name' => $category->getName()
            ];
        }

        return $shopCategories;
    }
}
