<?php

namespace Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidLocationHeaderException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\LocationHeaderEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class CreateStoreIntegrationResponseTest.
 *
 * @package Domain\StoreIntegration\Models
 */
class CreateStoreIntegrationResponseTest extends BaseTestCase
{
    /**
     * @return void
     *
     * @throws InvalidLocationHeaderException
     */
    public function testEmptyLocationHeader(): void
    {
        // arrange
        $this->expectException(LocationHeaderEmptyException::class);
        // act

        CreateStoreIntegrationResponse::fromLocationHeader('');
        // assert
    }

    /**
     * @return void
     *
     * @throws InvalidLocationHeaderException
     * @throws LocationHeaderEmptyException
     */
    public function testInvalidLocationHeader(): void
    {
        // arrange
        $this->expectException(InvalidLocationHeaderException::class);
        // act

        CreateStoreIntegrationResponse::fromLocationHeader('https://test.com');
        // assert
    }

    /**
     * @return void
     *
     * @throws InvalidLocationHeaderException
     * @throws LocationHeaderEmptyException
     */
    public function testValidLocationHeader(): void
    {
        // arrange

        // act
        $response = CreateStoreIntegrationResponse::fromLocationHeader('https://sandbox.sequrapi.com/store_integrations/4');
        // assert
        self::assertEquals('4', $response->getIntegrationId());
    }
}
