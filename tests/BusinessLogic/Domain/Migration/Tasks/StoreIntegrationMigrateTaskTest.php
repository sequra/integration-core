<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Migration\Tasks;

use ReflectionMethod;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Migration\Tasks\StoreIntegrationMigrateTask;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\Infrastructure\TaskExecution\Task;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAffiliateSettingsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAffiliateSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionDataRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDomainStoreService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSellingCountriesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreInfoService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreIntegrationProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;

/**
 * Class StoreIntegrationMigrateTaskTest.
 *
 * @package Domain\Migration\Tasks
 */
class StoreIntegrationMigrateTaskTest extends BaseTestCase
{
    /**
     * @var MockDomainStoreService $storeService
     */
    private $storeService;

    /**
     * @var MockStoreService $integrationStoreService
     */
    private $integrationStoreService;

    /**
     * @var MockConnectionService $connectionService
     */
    private $connectionService;

    /**
     * @var MockStoreIntegrationService $storeIntegrationService
     */
    private $storeIntegrationService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationStoreService = new MockStoreService();

        $this->storeService = new MockDomainStoreService(
            $this->integrationStoreService,
            TestServiceRegister::getService(ConnectionDataRepositoryInterface::class)
        );

        $this->storeIntegrationService = new MockStoreIntegrationService(
            new MockIntegrationStoreIntegrationService(),
            new MockStoreIntegrationProxy(),
            new MockConnectionDataRepository(),
            new MockStoreInfoService(),
            new MockCredentialsRepository()
        );

        TestServiceRegister::registerService(StoreService::class, function () {
            return $this->storeService;
        });

        $this->connectionService = new MockConnectionService(
            new MockConnectionDataRepository(),
            new MockCredentialsService(
                new MockConnectionProxy(),
                new MockCredentialsRepository(),
                new MockCountryConfigurationRepository(),
                new MockPaymentMethodRepository(),
                new MockAffiliateSettingsService(new MockAffiliateSettingsRepository()),
                new MockSellingCountriesService()
            ),
            $this->storeIntegrationService,
            TestServiceRegister::getService(DeploymentsRepositoryInterface::class),
            TestServiceRegister::getService(DeploymentsService::class)
        );

        TestServiceRegister::registerService(ConnectionService::class, function () {
            return $this->connectionService;
        });
    }


    /**
     * @throws InvalidEnvironmentException
     * @throws \Exception
     */
    public function testTaskNoConnectionDataForStoreContext(): void
    {
        // Arrange
        $this->storeService->setMockConnectedStores(['2']);

        $connectionData1 = new ConnectionData(
            'sandbox',
            'merchant',
            '1',
            new AuthorizationCredentials('username', 'password')
        );

        $connectionData2 = new ConnectionData(
            'sandbox',
            'merchant2',
            '2',
            new AuthorizationCredentials('username2', 'password2')
        );

        $this->connectionService->setMockAllConnectionData([$connectionData1, $connectionData2]);
        $task = new StoreIntegrationMigrateTask();

        // Act
        $task->execute();

        // Assert
        $createdIntegrations = $this->storeIntegrationService->getCreatedIntegrationIds();
        self::assertTrue($createdIntegrations['merchant']);
        self::assertTrue($createdIntegrations['merchant2']);
    }

    /**
     * @throws InvalidEnvironmentException
     * @throws \Exception
     */
    public function testWebhooksAreReRegisteredInsideEachStore(): void
    {
        $this->storeService->setMockConnectedStores(['store-1', 'store-2']);
        $byStore = [
            'store-1' => [$this->connection('d1'), $this->connection('d2')],
            'store-2' => [$this->connection('d3')],
        ];
        $calls = [];
        $service = $this->createMock(ConnectionService::class);
        $service->method('getAllConnectionData')->willReturnCallback(
            function () use ($byStore) {
                return $byStore[StoreContext::getInstance()->getStoreId()];
            }
        );
        $service->method('reRegisterWebhooks')->willReturnCallback(
            function (ConnectionData $data) use (&$calls) {
                $calls[] = [StoreContext::getInstance()->getStoreId(), $data->getDeployment()];
            }
        );
        $this->registerConnectionService($service);

        (new StoreIntegrationMigrateTask())->execute();

        self::assertSame(
            [['store-1', 'd1'], ['store-1', 'd2'], ['store-2', 'd3']],
            $calls
        );
    }

    /**
     * @throws \Exception
     */
    public function testNothingIsReRegisteredWithoutConnectedStores(): void
    {
        $this->storeService->setMockConnectedStores([]);
        $service = $this->createMock(ConnectionService::class);
        $service->expects(self::never())->method('reRegisterWebhooks');
        $this->registerConnectionService($service);

        (new StoreIntegrationMigrateTask())->execute();
    }

    /**
     * @throws InvalidEnvironmentException
     */
    public function testReRegistrationFailureReachesTheCaller(): void
    {
        $exception = new RuntimeException('webhook failure');
        $this->storeService->setMockConnectedStores(['store-1']);
        $service = $this->createMock(ConnectionService::class);
        $service->method('getAllConnectionData')->willReturn([$this->connection('d1')]);
        $service->method('reRegisterWebhooks')->willThrowException($exception);
        $this->registerConnectionService($service);

        try {
            (new StoreIntegrationMigrateTask())->execute();
            self::fail('Expected the migration to throw.');
        } catch (RuntimeException $e) {
            self::assertSame($exception, $e);
        }
    }

    public function testStoreIntegrationMigrateTaskIsNotATask(): void
    {
        $task = new StoreIntegrationMigrateTask();

        self::assertNotInstanceOf(Task::class, $task);
        $method = new ReflectionMethod($task, 'execute');
        self::assertTrue($method->isPublic());
        self::assertFalse($method->isFinal());
        self::assertSame('void', (string) $method->getReturnType());
    }

    public function testSubclassGetsServicesFromTheServiceRegister(): void
    {
        $task = new class extends StoreIntegrationMigrateTask {
            public function services(): array
            {
                return [$this->getStoreService(), $this->getConnectionService()];
            }
        };

        $services = $task->services();

        self::assertSame(TestServiceRegister::getService(StoreService::class), $services[0]);
        self::assertSame(TestServiceRegister::getService(ConnectionService::class), $services[1]);
    }

    /**
     * @throws InvalidEnvironmentException
     */
    private function connection(string $deploymentId): ConnectionData
    {
        return new ConnectionData(
            'sandbox',
            'merchant',
            $deploymentId,
            new AuthorizationCredentials('username', 'password')
        );
    }

    private function registerConnectionService(ConnectionService $service): void
    {
        TestServiceRegister::registerService(ConnectionService::class, function () use ($service) {
            return $service;
        });
    }
}
