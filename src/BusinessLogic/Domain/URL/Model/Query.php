<?php

namespace SeQura\Core\BusinessLogic\Domain\URL\Model;

/**
 * Class Query.
 *
 * @package SeQura\Core\BusinessLogic\Domain\URL\Model
 */
class Query
{
    /**
     * @var string $key
     */
    private $key;

    /**
     * @var string $value
     */
    private $value;

    /**
     * @param string $key
     * @param string $value
     */
    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
