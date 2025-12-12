<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Migration\Tasks;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Migration\Tasks\StoreIntegrationMigrateTask;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionDataRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDomainStoreService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreIntegrationProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

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
            new MockStoreIntegrationProxy()
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
                new MockPaymentMethodRepository()
            ),
            $this->storeIntegrationService
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
        $this->storeIntegrationService->setMockIntegrationId('int1');

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
        $connectionData = $this->connectionService->getConnectionDataByMerchantId('merchant2');

        self::assertTrue($createdIntegrations['merchant']);
        self::assertTrue($createdIntegrations['merchant2']);
        self::assertEquals('int1', $connectionData->getIntegrationId());
    }
}
