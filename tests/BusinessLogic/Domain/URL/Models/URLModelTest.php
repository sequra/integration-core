<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\URL\Models;

use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\URL\Model\Query;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class URLModelTest.
 *
 * @package Domain\URL\Models
 */
class URLModelTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testInvalidBaseUrl(): void
    {
        // arrange
        $this->expectException(InvalidUrlException::class);
        // act

        new URL('invalid-url');
        // assert
    }

    /**
     * @return void
     */
    public function testBuildWithoutQueries(): void
    {
        // arrange
        // act

        $url = new URL('https://test.com');
        // assert

        self::assertEquals('https://test.com', $url->buildUrl());
    }

    /**
     * @return void
     *
     * @throws InvalidUrlException
     */
    public function testBuildWithQueries(): void
    {
        // arrange
        // act

        $url = new URL('https://test.com', [new Query('test', '1') , new Query('test2', '2')]);
        // assert

        self::assertEquals('https://test.com?test=1&test2=2', $url->buildUrl());
    }

    /**
     * @return void
     *
     * @throws InvalidUrlException
     */
    public function testBuildWithAddingQueries(): void
    {
        // arrange
        // act

        $url = new URL('https://test.com', [new Query('test', '1') , new Query('test2', '2')]);
        $url->addQuery(new Query('test3', '3'));
        // assert

        self::assertEquals('https://test.com?test=1&test2=2&test3=3', $url->buildUrl());
    }
}
