<?php

namespace SeQura\Core\Infrastructure\Http\DTO;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class Options. Represents HTTP options set for Request by HttpClient.
 *
 * @package SeQura\Core\Infrastructure\Http\DTO
 */
/** @phpstan-consistent-constructor */
class Options extends DataTransferObject
{
    /**
     * Name of the option.
     *
     * @var mixed
     */
    protected $name;
    /**
     * Value of the option.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Options constructor.
     *
     * @param mixed $name Name of the option.
     * @param mixed $value Value of the option.
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Gets name of the option.
     *
     * @return mixed Name of the option.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets value of the option.
     *
     * @return mixed Value of the option.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Transforms DTO to an array representation.
     *
     * @return mixed[] DTO in array format.
     */
    public function toArray(): array
    {
        return array(
            'name' => $this->getName(),
            'value' => $this->getValue(),
        );
    }

    /**
     * Transforms raw array data to Options.
     *
     * @param mixed[] $raw Raw array data.
     *
     * @return Options Transformed object.
     */
    public static function fromArray(array $raw)
    {
        return new static($raw['name'], $raw['value']);
    }
}
