<?php

namespace SeQura\Core\Tests\Infrastructure\ORM;

use SeQura\Core\Infrastructure\ORM\Utility\IndexHelper;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\Entity\FooEntity;
use PHPUnit\Framework\TestCase;

/**
 * Class IndexHelperTest.
 *
 * @package SeQura\Core\Tests\Infrastructure\ORM
 */
class IndexHelperTest extends TestCase
{
    /**
     * @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\Entity\FooEntity
     */
    protected $entity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = new FooEntity();
    }

    public function testMapFieldsToIndexes()
    {
        $expected = array(
            'text' => 1,
            'int' => 2,
            'intNegative' => 3,
            'date' => 4,
            'boolTrue' => 5,
            'boolFalse' => 6,
            'double' => 7,
            'doubleNegative' => 8,
            'empty' => 9,
        );

        $result = IndexHelper::mapFieldsToIndexes($this->entity);

        $this->assertEquals($expected, $result);
    }

    public function testTransformFieldsToIndexes()
    {
        $date = \DateTime::createFromFormat('Y-m-d', '2018-11-20');
        $this->entity->date = $date;
        $expected = array(
            1 => 'Test',
            2 => '00000000123',
            3 => '-0000000234',
            4 => (string)$date->getTimestamp(),
            5 => '1',
            6 => '0',
            7 => '00000000123.50000',
            8 => '-0000000678.75000',
            9 => null,
        );

        $result = IndexHelper::transformFieldsToIndexes($this->entity);
        $this->assertEquals($expected, $result);
    }
}
