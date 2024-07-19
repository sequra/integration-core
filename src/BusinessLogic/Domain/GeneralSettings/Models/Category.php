<?php

namespace SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\EmptyCategoryParameterException;
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
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $id
     * @param string $name
     *
     * @throws EmptyCategoryParameterException
     */
    public function __construct(string $id, string $name)
    {
        if (empty($id) || empty($name)) {
            throw new EmptyCategoryParameterException(
                new TranslatableLabel('No parameter can be an empty string.', 'general.errors.empty')
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

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
