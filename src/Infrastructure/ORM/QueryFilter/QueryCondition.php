<?php

namespace SeQura\Core\Infrastructure\ORM\QueryFilter;

use DateTime;

/**
 * Class Condition
 *
 * @package SeQura\Core\Infrastructure\ORM\QueryFilter
 */
class QueryCondition
{
    /**
     * @var string - AND | OR
     */
    protected $chainOperator;
    /**
     * @var string
     */
    protected $column;
    /**
     * @var string
     */
    protected $operator;
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var string
     */
    protected $valueType;

    /**
     * Condition constructor.
     *
     * @param string $chainOperator
     * @param string $column
     * @param string $operator
     * @param mixed $value
     */
    public function __construct($chainOperator, $column, $operator, $value)
    {
        $this->chainOperator = $chainOperator;
        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;

        $this->valueType = gettype($value);
        if ($this->valueType === 'object' && $value instanceof DateTime) {
            $this->valueType = 'dateTime';
        }
    }

    /**
     * @return string
     */
    public function getChainOperator()
    {
        return $this->chainOperator;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getValueType()
    {
        return $this->valueType;
    }
}
