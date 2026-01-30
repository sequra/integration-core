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
     * Returns categories from a shop.
     *
     * @param ?int $page
     * @param ?int $limit
     * @param ?string $search
     *
     * @return Category[]
     */
    public function getCategories(?int $page = null, ?int $limit = null, ?string $search = null): array;

    /**
     * @param string[] $ids
     *
     * @return Category[]
     */
    public function getCategoriesByIds(array $ids): array;
}
