<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\Store;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Store\Responses\StoreResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Store\Responses\StoresResponse;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Stores\Exceptions\FailedToRetrieveStoresException;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\Store;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class StoreControllerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\Store
 */
class StoreControllerTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(StoreServiceInterface::class, static function () {
            return new MockStoreService();
        });
    }

    /**
     * @throws FailedToRetrieveStoresException
     */
    public function testIsGetStoresResponseSuccessful(): void
    {
        // Act
        $response = AdminAPI::get()->store('1')->getStores();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws FailedToRetrieveStoresException
     */
    public function testGetStoresResponse(): void
    {
        // Arrange
        $expectedResponse = new StoresResponse([
            new Store('1', 'Default store'),
            new Store('2', 'Test store 2')
        ]);

        // Act
        $response = AdminAPI::get()->store('1')->getStores();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws FailedToRetrieveStoresException
     */
    public function testGetStoresResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->store('1')->getStores();

        // Assert
        self::assertEquals($this->expectedStoresToArrayResponse(), $response->toArray());
    }

    /**
     * @throws FailedToRetrieveStoresException
     */
    public function testIsGetCurrentStoreResponseSuccessful(): void
    {
        // Act
        $response = AdminAPI::get()->store('1')->getCurrentStore();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws FailedToRetrieveStoresException
     */
    public function testGetCurrentStoreResponse(): void
    {
        // Arrange
        $expectedResponse = new StoreResponse(new Store('1', 'Default store'));

        // Act
        $response = AdminAPI::get()->store('1')->getCurrentStore();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws FailedToRetrieveStoresException
     */
    public function testGetCurrentStoreResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->store('1')->getCurrentStore();

        // Assert
        self::assertEquals($this->expectedCurrentStoreToArrayResponse(), $response->toArray());
    }

    private function expectedStoresToArrayResponse(): array
    {
        return [
            [
                'storeId' => '1',
                'storeName' => 'Default store'
            ],
            [
                'storeId' => '2',
                'storeName' => 'Test store 2'
            ]
        ];
    }

    private function expectedCurrentStoreToArrayResponse(): array
    {
        return [
            'storeId' => '1',
            'storeName' => 'Default store'
        ];
    }
}
