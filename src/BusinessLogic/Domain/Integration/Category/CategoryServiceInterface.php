<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Category;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;

/**
 * Interface CategoryServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Category
 */
interface CategoryServiceInterface
{
    /**
     * Returns all categories from a shop.
     *
     * @return Category[]
     */
    public function getCategories(): array;
}
