<?php

namespace SeQura\Core\BusinessLogic\Domain\Category\Models;

use SeQura\Core\BusinessLogic\Domain\Category\Exceptions\EmptyCategoryParameterException;

/**
 * Class Category
 *
 * @package SeQura\Core\BusinessLogic\Domain\Category\Models
 */
class Category
{
    /**
     * @var string
     */
    private $categoryId;

    /**
     * @var string
     */
    private $categoryName;

    /**
     * @param string $categoryId
     * @param string $categoryName
     *
     * @throws EmptyCategoryParameterException
     */
    public function __construct(string $categoryId, string $categoryName)
    {
        if(empty($categoryId) || empty($categoryName)) {
            throw new EmptyCategoryParameterException('No parameter can be an empty string.');
        }

        $this->categoryId = $categoryId;
        $this->categoryName = $categoryName;
    }

    /**
     * @return string
     */
    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    /**
     * @return string
     */
    public function getCategoryName(): string
    {
        return $this->categoryName;
    }
}
