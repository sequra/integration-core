<?php

namespace SeQura\Core\BusinessLogic\Domain\Category\Models;

use SeQura\Core\BusinessLogic\Domain\Category\Exceptions\EmptyCategoryParameterException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

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
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $id
     * @param string $name
     *
     * @throws EmptyCategoryParameterException
     */
    public function __construct(string $id, string $name)
    {
        if(empty($id) || empty($name)) {
            throw new EmptyCategoryParameterException(
                new TranslatableLabel('No parameter can be an empty string.',400)
            );
        }

        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
