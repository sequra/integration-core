<?php

namespace SeQura\Core\Tests\Infrastructure\ORM;

use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use PHPUnit\Framework\TestCase;

/**
 * Class QueryFilterTest
 * @package SeQura\Core\Tests\Infrastructure\ORM
 */
class QueryFilterTest extends TestCase
{
    private static $validConditions = array(
        array(
            'chain' => 'and',
            'column' => 'a',
            'operator' => 'like',
            'value' => '%test%',
        ),
        array(
            'chain' => 'and',
            'column' => 'a',
            'operator' => '!=',
            'value' => 'test',
        ),
        array(
            'chain' => 'or',
            'column' => 'b',
            'operator' => '>',
            'value' => 123,
        ),
        array(
            'chain' => 'or',
            'column' => 'c',
            'operator' => 'IN',
            'value' => array(1, 2, 3),
        ),
        array(
            'chain' => 'and',
            'column' => 'c',
            'operator' => 'not in',
            'value' => array(4, 5, 6),
        ),
        array(
            'chain' => 'or',
            'column' => 'd',
            'operator' => 'is null',
            'value' => null,
        ),
        array(
            'chain' => 'and',
            'column' => 'e',
            'operator' => 'is not null',
            'value' => null,
        ),
    );

    public function testSetLimitOffset()
    {
        $queryFilter = new QueryFilter();
        $queryFilter->setLimit(123);
        $queryFilter->setOffset(10);

        $this->assertEquals(123, $queryFilter->getLimit());
        $this->assertEquals(10, $queryFilter->getOffset());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function testOrderBy()
    {
        $queryFilter = new QueryFilter();
        $queryFilter->orderBy('a');

        $this->assertEquals('a', $queryFilter->getOrderByColumn());
        $this->assertEquals('ASC', $queryFilter->getOrderDirection());

        $queryFilter->orderBy('b', 'ASC');

        $this->assertEquals('b', $queryFilter->getOrderByColumn());
        $this->assertEquals('ASC', $queryFilter->getOrderDirection());

        $queryFilter->orderBy('c', 'DESC');

        $this->assertEquals('c', $queryFilter->getOrderByColumn());
        $this->assertEquals('DESC', $queryFilter->getOrderDirection());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function testQueryFilterChaining()
    {
        $queryFilter = new QueryFilter();

        $a = $queryFilter->setLimit(123);
        $this->assertEquals($queryFilter, $a);

        $a = $queryFilter->setOffset(123);
        $this->assertEquals($queryFilter, $a);

        $a = $queryFilter->orderBy('a', 'ASC');
        $this->assertEquals($queryFilter, $a);

        $a = $queryFilter->where('a', '=', 'ASC');
        $this->assertEquals($queryFilter, $a);

        $a = $queryFilter->orWhere('a', '=', 'ASC');
        $this->assertEquals($queryFilter, $a);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function testQueryFilterCondition()
    {
        $queryFilter = new QueryFilter();
        foreach (self::$validConditions as $condition) {
            if ($condition['chain'] === 'and') {
                $queryFilter->where($condition['column'], $condition['operator'], $condition['value']);
            } else {
                $queryFilter->orWhere($condition['column'], $condition['operator'], $condition['value']);
            }
        }

        $queryConditions = $queryFilter->getConditions();
        $count = count(self::$validConditions);
        $this->assertCount($count, $queryConditions);
        for ($i = 0; $i < $count; $i++) {
            $b = $queryConditions[$i];
            $this->assertInstanceOf('\SeQura\Core\Infrastructure\ORM\QueryFilter\QueryCondition', $b);

            $a = self::$validConditions[$i];
            $this->assertEquals(strtoupper($a['chain']), $b->getChainOperator());
            $this->assertEquals($a['column'], $b->getColumn());
            $this->assertEquals($a['operator'], $b->getOperator());
            $this->assertEquals($a['value'], $b->getValue());
        }
    }

    public function testOrderByWrongColumn()
    {
        $this->expectException(QueryFilterInvalidParamException::class);

        $queryFilter = new QueryFilter();
        $queryFilter->orderBy(123);
    }

    public function testOrderByWrongDirection()
    {
        $this->expectException(QueryFilterInvalidParamException::class);

        $queryFilter = new QueryFilter();
        $queryFilter->orderBy('a', 123);
    }

    public function testConditionWrongTypeValue()
    {
        $this->expectException(QueryFilterInvalidParamException::class);

        $queryFilter = new QueryFilter();
        $queryFilter->where('a', '=', new \stdClass());
    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     *
     * @dataProvider wrongConditionProvider
     */
    public function testWrongCondition($column, $operator, $value)
    {
        $this->expectException(QueryFilterInvalidParamException::class);

        $queryFilter = new QueryFilter();
        $queryFilter->where($column, $operator, $value);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function wrongConditionProvider()
    {
        return array(
            array(
                'column' => 'a',
                'operator' => 'like',
                'value' => 123,
            ),
            array(
                'column' => 'a',
                'operator' => 'like',
                'value' => new \DateTime(),
            ),
            array(
                'column' => 'a',
                'operator' => 'in',
                'value' => new \DateTime(),
            ),
            array(
                'column' => 'a',
                'operator' => 'not in',
                'value' => 123,
            ),
            array(
                'column' => 'a',
                'operator' => '>',
                'value' => true,
            ),
            array(
                'column' => 456,
                'operator' => '>',
                'value' => true,
            ),
            array(
                'column' => 'a',
                'operator' => 'bla',
                'value' => true,
            ),
        );
    }
}
