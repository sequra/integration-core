<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\OrderStatusSettings;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests\OrderStatusSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\OrderStatusSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\ShopOrderStatusResponse;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\SuccessfulOrderStatusSettingsResponse;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusSettings;
use SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses\ShopOrderStatusesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\EmptyOrderStatusMappingParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\InvalidSeQuraOrderStatusException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockShopOrderStatusesService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderStatusSettingsControllerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\OrderStatusSettings
 */
class OrderStatusSettingsControllerTest extends BaseTestCase
{
    /**
     * @var OrderStatusSettingsRepositoryInterface
     */
    private $orderStatusSettingsRepository;

    public function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(ShopOrderStatusesServiceInterface::class, static function () {
            return new MockShopOrderStatusesService();
        });

        $this->orderStatusSettingsRepository = TestServiceRegister::getService(OrderStatusSettingsRepositoryInterface::class);
    }

    public function testIsGetShopOrderStatusesResponseSuccessful(): void
    {
        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->getShopOrderStatuses();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    public function testGetShopOrderStatusesResponse(): void
    {
        // Arrange
        $statuses = [
            new OrderStatus('1', 'Success'),
            new OrderStatus('2', 'Failed'),
            new OrderStatus('3', 'Hold')
        ];

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->getShopOrderStatuses();
        $expectedResponse = new ShopOrderStatusResponse($statuses);

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    public function testGetShopOrderStatusesResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->getShopOrderStatuses();

        // Assert
        self::assertEquals($this->expectedShopOrderStatusesToArrayResponse(), $response->toArray());
    }


    public function testIsGetOrderStatusSettingsResponseSuccessful(): void
    {
        // Arrange
        $this->orderStatusSettingsRepository->setOrderStatusMapping(
            [
                new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
                new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
                new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
            ]
        );

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->getOrderStatusSettings();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testGetOrderStatusSettingsResponse(): void
    {
        // Arrange
        $orderStatusMappings = [
            new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
        ];

        StoreContext::doWithStore('1', [$this->orderStatusSettingsRepository, 'setOrderStatusMapping'], [$orderStatusMappings]);
        $expectedResponse = new OrderStatusSettingsResponse($orderStatusMappings);

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->getOrderStatusSettings();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetOrderStatusSettingsResponseToArray(): void
    {
        // Arrange
        $orderStatusMappings = [
            new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
        ];

        StoreContext::doWithStore('1', [$this->orderStatusSettingsRepository, 'setOrderStatusMapping'], [$orderStatusMappings]);

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->getOrderStatusSettings();

        // Assert
        self::assertEquals($this->expectedToArrayResponse(), $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetNonExistingOrderStatusSettingsResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->getOrderStatusSettings();

        // Assert
        self::assertEquals($this->defaultOrderStatusMappingsToArrayResponse(), $response->toArray());
    }


    /**
     * @throws InvalidSeQuraOrderStatusException
     * @throws EmptyOrderStatusMappingParameterException
     */
    public function testIsSaveResponseSuccessful(): void
    {
        // Arrange
        $orderStatusSettingsRequest = new OrderStatusSettingsRequest([
            [
                'sequraStatus' => OrderStates::STATE_APPROVED,
                'shopStatus' => 'Success',
            ],
            [
                'sequraStatus' => OrderStates::STATE_CANCELLED,
                'shopStatus' => 'Failed',
            ],
            [
                'sequraStatus' => OrderStates::STATE_NEEDS_REVIEW,
                'shopStatus' => 'Hold',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->saveOrderStatusSettings($orderStatusSettingsRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws InvalidSeQuraOrderStatusException
     * @throws EmptyOrderStatusMappingParameterException
     */
    public function testSaveResponse(): void
    {
        // Arrange
        $orderStatusSettingsRequest = new OrderStatusSettingsRequest([
            [
                'sequraStatus' => OrderStates::STATE_APPROVED,
                'shopStatus' => 'Success',
            ],
            [
                'sequraStatus' => OrderStates::STATE_CANCELLED,
                'shopStatus' => 'Failed',
            ],
            [
                'sequraStatus' => OrderStates::STATE_NEEDS_REVIEW,
                'shopStatus' => 'Hold',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->saveOrderStatusSettings($orderStatusSettingsRequest);
        $expectedResponse = new SuccessfulOrderStatusSettingsResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws InvalidSeQuraOrderStatusException
     * @throws EmptyOrderStatusMappingParameterException
     */
    public function testSaveResponseToArray(): void
    {
        // Arrange
        $orderStatusSettingsRequest = new OrderStatusSettingsRequest([
            [
                'sequraStatus' => OrderStates::STATE_APPROVED,
                'shopStatus' => 'Success',
            ],
            [
                'sequraStatus' => OrderStates::STATE_CANCELLED,
                'shopStatus' => 'Failed',
            ],
            [
                'sequraStatus' => OrderStates::STATE_NEEDS_REVIEW,
                'shopStatus' => 'Hold',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->saveOrderStatusSettings($orderStatusSettingsRequest);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testIsUpdateResponseSuccessful(): void
    {
        // Arrange
        $orderStatusMappings = [
            new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
        ];

        StoreContext::doWithStore('1', [$this->orderStatusSettingsRepository, 'setOrderStatusMapping'], [$orderStatusMappings]);

        $orderStatusSettingsRequest = new OrderStatusSettingsRequest([
            [
                'sequraStatus' => OrderStates::STATE_APPROVED,
                'shopStatus' => 'Success2',
            ],
            [
                'sequraStatus' => OrderStates::STATE_CANCELLED,
                'shopStatus' => 'Failed2',
            ],
            [
                'sequraStatus' => OrderStates::STATE_NEEDS_REVIEW,
                'shopStatus' => 'Hold2',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->saveOrderStatusSettings($orderStatusSettingsRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testUpdateResponse(): void
    {
        // Arrange
        $orderStatusMappings = [
            new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
        ];

        StoreContext::doWithStore('1', [$this->orderStatusSettingsRepository, 'setOrderStatusMapping'], [$orderStatusMappings]);

        $orderStatusSettingsRequest = new OrderStatusSettingsRequest([
            [
                'sequraStatus' => OrderStates::STATE_APPROVED,
                'shopStatus' => 'Success2',
            ],
            [
                'sequraStatus' => OrderStates::STATE_CANCELLED,
                'shopStatus' => 'Failed2',
            ],
            [
                'sequraStatus' => OrderStates::STATE_NEEDS_REVIEW,
                'shopStatus' => 'Hold2',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->saveOrderStatusSettings($orderStatusSettingsRequest);
        $expectedResponse = new SuccessfulOrderStatusSettingsResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testUpdateResponseToArray(): void
    {
        // Arrange
        $orderStatusMappings = [
            new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
        ];

        StoreContext::doWithStore('1', [$this->orderStatusSettingsRepository, 'setOrderStatusMapping'], [$orderStatusMappings]);

        $orderStatusSettingsRequest = new OrderStatusSettingsRequest([
            [
                'sequraStatus' => OrderStates::STATE_APPROVED,
                'shopStatus' => 'Success2',
            ],
            [
                'sequraStatus' => OrderStates::STATE_CANCELLED,
                'shopStatus' => 'Failed2',
            ],
            [
                'sequraStatus' => OrderStates::STATE_NEEDS_REVIEW,
                'shopStatus' => 'Hold2',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->orderStatusSettings('1')->saveOrderStatusSettings($orderStatusSettingsRequest);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testApprovedStatusMapping(): void
    {
        $mapping = new OrderStatusSettings();
        $mapping->setStoreId('');
        $mapping->setOrderStatusMappings([
            new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
        ]);

        $repository = RepositoryRegistry::getRepository(OrderStatusSettings::getClassName());
        $repository->save($mapping);

        /** @var OrderStatusSettingsService $service */
        $service = ServiceRegister::getService(OrderStatusSettingsService::class);

        $shopStatus = $service->getMapping(OrderStates::STATE_APPROVED);

        self::assertEquals('Success', $shopStatus);
    }

    /**
     * @throws Exception
     */
    public function testReviewStatusMapping(): void
    {
        $mapping = new OrderStatusSettings();
        $mapping->setStoreId('');
        $mapping->setOrderStatusMappings([
            new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
        ]);

        $repository = RepositoryRegistry::getRepository(OrderStatusSettings::getClassName());
        $repository->save($mapping);

        /** @var OrderStatusSettingsService $service */
        $service = ServiceRegister::getService(OrderStatusSettingsService::class);
        $shopStatus = $service->getMapping(OrderStates::STATE_NEEDS_REVIEW);

        self::assertEquals('Hold', $shopStatus);
    }

    /**
     * @throws Exception
     */
    public function testCancelledStatusMapping(): void
    {
        $mapping = new OrderStatusSettings();
        $mapping->setStoreId('');
        $mapping->setOrderStatusMappings([
            new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
        ]);

        $repository = RepositoryRegistry::getRepository(OrderStatusSettings::getClassName());
        $repository->save($mapping);

        /** @var OrderStatusSettingsService $service */
        $service = ServiceRegister::getService(OrderStatusSettingsService::class);

        $shopStatus = $service->getMapping(OrderStates::STATE_CANCELLED);

        self::assertEquals('Failed', $shopStatus);
    }

    /**
     * @throws Exception
     */
    public function testInvalidStatusMapping(): void
    {
        $mapping = new OrderStatusSettings();
        $mapping->setStoreId('');
        $mapping->setOrderStatusMappings([
            new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success'),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, 'Failed'),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, 'Hold')
        ]);

        $repository = RepositoryRegistry::getRepository(OrderStatusSettings::getClassName());
        $repository->save($mapping);

        /** @var OrderStatusSettingsService $service */
        $service = ServiceRegister::getService(OrderStatusSettingsService::class);

        $shopStatus = $service->getMapping('test');

        self::assertEquals('', $shopStatus);
    }

    /**
     * @return array
     */
    private function expectedToArrayResponse(): array
    {
        return [
            [
                'sequraStatus' => OrderStates::STATE_APPROVED,
                'shopStatus' => 'Success',
            ],
            [
                'sequraStatus' => OrderStates::STATE_CANCELLED,
                'shopStatus' => 'Failed',
            ],
            [
                'sequraStatus' => OrderStates::STATE_NEEDS_REVIEW,
                'shopStatus' => 'Hold',
            ]
        ];
    }

    private function expectedShopOrderStatusesToArrayResponse(): array
    {
        return [
            [
                'id' => '1',
                'name' => 'Success',
            ],
            [
                'id' => '2',
                'name' => 'Failed',
            ],
            [
                'id' => '3',
                'name' => 'Hold',
            ]
        ];
    }

    private function defaultOrderStatusMappingsToArrayResponse(): array
    {
        return [
            [
                'sequraStatus' => OrderStates::STATE_APPROVED,
                'shopStatus' => '',
            ],
            [
                'sequraStatus' => OrderStates::STATE_NEEDS_REVIEW,
                'shopStatus' => '',
            ],
            [
                'sequraStatus' => OrderStates::STATE_CANCELLED,
                'shopStatus' => '',
            ]
        ];
    }
}
