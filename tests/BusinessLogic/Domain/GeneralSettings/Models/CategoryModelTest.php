<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Models;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\EmptyCategoryParameterException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class CategoryModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Models
 */
class CategoryModelTest extends BaseTestCase
{
    public function testEmptyCategoryId(): void
    {
        $this->expectException(EmptyCategoryParameterException::class);

        new Category('', 'test');
    }

    public function testEmptyCategoryName(): void
    {
        $this->expectException(EmptyCategoryParameterException::class);

        new Category('test', '');
    }

    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $category = new Category('1', 'Test name 1');
        $category->setId('2');
        $category->setName('Tester 2');

        self::assertEquals('2', $category->getId());
        self::assertEquals('Tester 2', $category->getName());
    }
}
