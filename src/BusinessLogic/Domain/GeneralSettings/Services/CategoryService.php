<?php

namespace SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveCategoriesException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class CategoryService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Category\Services
 */
class CategoryService
{
    /**
     * @var CategoryServiceInterface
     */
    protected $integrationCategoryService;

    /**
     * @param CategoryServiceInterface $integrationCategoryService
     */
    public function __construct(CategoryServiceInterface $integrationCategoryService)
    {
        $this->integrationCategoryService = $integrationCategoryService;
    }

    /**
     * @return Category[]
     *
     * @throws FailedToRetrieveCategoriesException
     */
    public function getCategories(): array
    {
        try {
            return $this->integrationCategoryService->getCategories();
        } catch (Exception $e) {
            throw new FailedToRetrieveCategoriesException(new TranslatableLabel('Failed to retrieve categories.', 'general.errors.generalSettings.categories'));
        }
    }
}
