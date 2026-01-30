<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;

/**
 * Class MockCategoryService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockCategoryService implements CategoryServiceInterface
{
    /**
     * @var Category[] $categories
     */
    private $categories = [];

    /**
     * @inheritDoc
     */
    public function getCategories(?int $page = null, ?int $limit = null, ?string $search = null): array
    {
        if (!empty($this->categories)) {
            return $this->categories;
        }

        return [
            new Category('1', 'Test 1'),
            new Category('2', 'Test 2'),
            new Category('3', 'Test 3')
        ];
    }

    /**
     * @param Category[] $categories
     *
     * @return void
     */
    public function setMockCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @param string[] $ids
     *
     * @return Category[]
     */
    public function getCategoriesByIds(array $ids): array
    {
        return $this->getCategories();
    }
}
