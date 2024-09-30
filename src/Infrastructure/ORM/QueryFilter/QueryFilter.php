<?php

namespace SeQura\Core\Infrastructure\ORM\QueryFilter;

use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use DateTime;

use function in_array;

/**
 * Class QueryFilter.
 *
 * @package SeQura\Core\Infrastructure\ORM
 */
class QueryFilter
{
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    /**
     * List of filter conditions.
     *
     * @var QueryCondition[]
     */
    protected $conditions = array();
    /**
     * Order by column name.
     *
     * @var string
     */
    protected $orderByColumn;
    /**
     * Order direction.
     *
     * @var string
     */
    protected $orderDirection = 'ASC';
    /**
     * Limit for select.
     *
     * @var int
     */
    protected $limit;
    /**
     * Offset for select.
     *
     * @var int
     */
    protected $offset;

    /**
     * Gets limit for select.
     *
     * @return int|null Limit for select.
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Sets limit for select.
     *
     * @param int $limit Limit for select.
     *
     * @return self This instance for chaining.
     */
    public function setLimit(int $limit): QueryFilter
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Gets select offset.
     *
     * @return int|null Offset.
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * Sets select offset.
     *
     * @param int $offset Offset.
     *
     * @return self This instance for chaining.
     */
    public function setOffset(int $offset): QueryFilter
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Sets order by column and direction
     *
     * @param mixed $column Column name.
     * @param string $direction Order direction (@see self::ORDER_ASC or @see self::ORDER_DESC).
     *
     * @return self This instance for chaining.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function orderBy($column, string $direction = self::ORDER_ASC): QueryFilter
    {
        if (!is_string($column) || !in_array($direction, array(self::ORDER_ASC, self::ORDER_DESC), false)) {
            throw new QueryFilterInvalidParamException(
                'Column value must be string type and direction must be ASC or DESC'
            );
        }

        $this->orderByColumn = $column;
        $this->orderDirection = $direction;

        return $this;
    }

    /**
     * Gets name for order by column.
     *
     * @return string|null Order column name.
     */
    public function getOrderByColumn(): ?string
    {
        return $this->orderByColumn;
    }

    /**
     * Gets order direction.
     *
     * @return string|null Order direction (@see self::ORDER_ASC or @see self::ORDER_DESC)
     */
    public function getOrderDirection(): ?string
    {
        return $this->orderDirection;
    }

    /**
     * Gets all conditions for this filter.
     *
     * @return QueryCondition[] Filter conditions.
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Sets where condition, if chained AND operator will be used
     *
     * @param string $column Column name.
     * @param string $operator Operator. Use constants from @see Operator class.
     * @param mixed $value Value of condition.
     *
     * @return self This instance for chaining.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function where(string $column, string $operator, $value = null): QueryFilter
    {
        $this->validateConditionParameters($column, $operator, $value);

        $this->conditions[] = new QueryCondition('AND', $column, $operator, $value);

        return $this;
    }

    /**
     * Sets where condition, if chained OR operator will be used.
     *
     * @param string $column Column name.
     * @param string $operator Operator. Use constants from @see Operator class.
     * @param mixed $value Value of condition.
     *
     * @return self This instance for chaining.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function orWhere(string $column, string $operator, $value = null): QueryFilter
    {
        $this->validateConditionParameters($column, $operator, $value);

        $this->conditions[] = new QueryCondition('OR', $column, $operator, $value);

        return $this;
    }

    /**
     * Validates condition parameters.
     *
     * @param mixed $column Column name.
     * @param mixed $operator Operator. Use constants from @see Operator class.
     * @param mixed $value Value of condition.
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function validateConditionParameters($column, $operator, $value): void
    {
        if (!is_string($column) || !is_string($operator)) {
            throw new QueryFilterInvalidParamException('Column and operator values must be string types');
        }

        $operator = strtoupper($operator);
        if (!in_array($operator, Operators::$AVAILABLE_OPERATORS, true)) {
            throw new QueryFilterInvalidParamException("Operator $operator is not supported");
        }

        $valueType = gettype($value);
        if ($valueType === 'object' && $value instanceof DateTime) {
            $valueType = 'dateTime';
        }

        if (!array_key_exists($valueType, Operators::$TYPE_OPERATORS)) {
            throw new QueryFilterInvalidParamException('Value type is not supported');
        }

        if (!in_array($operator, Operators::$TYPE_OPERATORS[$valueType], true)) {
            throw new QueryFilterInvalidParamException("Operator $operator is not supported for $valueType type");
        }
    }
}
