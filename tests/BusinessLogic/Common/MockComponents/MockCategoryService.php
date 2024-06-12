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
     * @inheritDoc
     */
    public function getCategories(): array
    {
        return [
          new Category('1', 'Test 1'),
          new Category('2', 'Test 2'),
          new Category('3', 'Test 3')
        ];
    }
}
