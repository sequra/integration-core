<?php

namespace SeQura\Core\BusinessLogic\Domain\Product\Models;

use SeQura\Core\BusinessLogic\Domain\Product\Exceptions\EmptyProductParameterException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class Product
 *
 * @package SeQura\Core\BusinessLogic\Domain\Product\Models
 */
class Product
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
     * @throws EmptyProductParameterException
     */
    public function __construct(string $id, string $name)
    {
        if(empty($id) || empty($name)) {
            throw new EmptyProductParameterException(
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
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
