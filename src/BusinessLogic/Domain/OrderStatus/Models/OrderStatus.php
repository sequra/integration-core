<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderStatus\Models;

use SeQura\Core\BusinessLogic\Domain\OrderStatus\Exceptions\EmptyOrderStatusParameterException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class OrderStatus
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderStatus\Models
 */
class OrderStatus
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
     * @throws EmptyOrderStatusParameterException
     */
    public function __construct(string $id, string $name)
    {
        if ($id === "" || empty($name)) {
            throw new EmptyOrderStatusParameterException(
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
