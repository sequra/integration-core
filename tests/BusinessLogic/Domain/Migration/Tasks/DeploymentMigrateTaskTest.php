<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Migration\Tasks;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData as ConnectionDataEntity;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Repositories\ConnectionDataRepository;
use SeQura\Core\BusinessLogic\DataAccess\Credentials\Repositories\CredentialsRepository;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\DeploymentURL;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\Domain\Migration\Tasks\DeploymentMigrateTask;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseSerializationTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDeploymentsProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDeploymentsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDeploymentsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDomainStoreService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use SeQura\Core\BusinessLogic\DataAccess\Credentials\Entities\Credentials as CredentialsEntity;

/**
 * Class DeploymentMigrateTaskTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Migration\Tasks
 */
class DeploymentMigrateTaskTest extends BaseSerializationTestCase
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
     * @var RepositoryInterface $connectionDataRepository
     */
    private $connectionDataRepository;

    /**
     * @var MockDeploymentsService $deploymentsService
     */
    private $deploymentsService;

    /**
     * @var MockConnectionProxy $connectionProxy
     */
    private $connectionProxy;

    /**
     * @var RepositoryInterface $credentialsRepository
     */
    private $credentialsRepository;

    /**
     * @var ConnectionDataRepositoryInterface $connectionRepository
     */
    private $connectionRepository;

    /**
     * @var CredentialsRepositoryInterface $credentialsRepo
     */
    private $credentialsRepo;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionDataRepository = TestRepositoryRegistry::getRepository(ConnectionDataEntity::getClassName());

        $this->connectionRepository = new ConnectionDataRepository(
            $this->connectionDataRepository,
            StoreContext::getInstance()
        );
        $this->credentialsRepository = TestRepositoryRegistry::getRepository(CredentialsEntity::getClassName());
        $this->credentialsRepo = new CredentialsRepository(
            $this->credentialsRepository,
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(CredentialsRepositoryInterface::class, function () {
            return $this->credentialsRepo;
        });

        $this->integrationStoreService = new MockStoreService();

        $this->storeService = new MockDomainStoreService(
            $this->integrationStoreService,
            TestServiceRegister::getService(ConnectionDataRepositoryInterface::class)
        );

        TestServiceRegister::registerService(StoreService::class, function () {
            return $this->storeService;
        });

        $this->deploymentsService = new MockDeploymentsService(
            new MockDeploymentsProxy(),
            new MockDeploymentsRepository(),
            TestServiceRegister::getService(ConnectionDataRepositoryInterface::class)
        );

        TestServiceRegister::registerService(DeploymentsService::class, function () {
            return $this->deploymentsService;
        });

        $this->connectionProxy = new MockConnectionProxy();

        TestServiceRegister::registerService(ConnectionProxyInterface::class, function () {
            return $this->connectionProxy;
        });
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     * @throws InvalidEnvironmentException
     * @throws Exception
     */
    public function testDeploymentMigrateTaskNoConnectionDataForStoreContext(): void
    {
        // Arrange
        $this->storeService->setMockConnectedStores(['2']);
        $this->deploymentsService->setMockDeployments($this->getDeployments());
        $this->connectionProxy->setMockCredentials($this->getSequraCredentials());

        $connectionData = new ConnectionData(
            'sandbox',
            'merchant',
            '',
            new AuthorizationCredentials('username', 'password')
        );

        $connectionDataEntity = new ConnectionDataEntity();
        $connectionDataEntity->setConnectionData($connectionData);
        $connectionDataEntity->setStoreId('1');
        $this->connectionDataRepository->save($connectionDataEntity);
        $task = new DeploymentMigrateTask();

        // Act
        $task->execute();

        // Assert
        $connections = StoreContext::doWithStore(
            '2',
            [$this->connectionRepository, 'getAllConnectionSettings']
        );

        $credentials = StoreContext::doWithStore(
            '2',
            [$this->credentialsRepo, 'getCredentials']
        );

        self::assertEmpty($connections);
        self::assertEmpty($credentials);
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     * @throws InvalidEnvironmentException
     * @throws Exception
     */
    public function testDeploymentMigrateTaskNoCredentials(): void
    {
        // Arrange
        $this->storeService->setMockConnectedStores(['1', '2', '3']);
        $this->deploymentsService->setMockDeployments($this->getDeployments());

        $connectionData = new ConnectionData(
            'sandbox',
            'merchant',
            '',
            new AuthorizationCredentials('username', 'password')
        );

        $connectionDataEntity = new ConnectionDataEntity();
        $connectionDataEntity->setConnectionData($connectionData);
        $connectionDataEntity->setStoreId('1');
        $this->connectionDataRepository->save($connectionDataEntity);

        $task = new DeploymentMigrateTask();

        // Act
        $task->execute();

        // Assert
        $credentials = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepo, 'getCredentials']
        );

        self::assertEmpty($credentials);
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     * @throws InvalidEnvironmentException
     * @throws Exception
     */
    public function testDeploymentMigrateTaskOnlyOneDeployment(): void
    {
        // Arrange
        $this->storeService->setMockConnectedStores(['1', '2', '3']);
        $this->deploymentsService->setMockDeployments([$this->getDeployments()[0]]);
        $this->connectionProxy->setMockCredentials($this->getSequraCredentials());

        $connectionData = new ConnectionData(
            'sandbox',
            'merchant',
            '',
            new AuthorizationCredentials('username', 'password')
        );

        $connectionDataEntity = new ConnectionDataEntity();
        $connectionDataEntity->setConnectionData($connectionData);
        $connectionDataEntity->setStoreId('1');
        $this->connectionDataRepository->save($connectionDataEntity);

        $task = new DeploymentMigrateTask();

        // Act
        $task->execute();

        // Assert
        $connections = StoreContext::doWithStore(
            '1',
            [$this->connectionRepository, 'getAllConnectionSettings']
        );

        $credentials = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepo, 'getCredentials']
        );
        $expectedConnectionData = new ConnectionData(
            'sandbox',
            'merchant',
            'sequra',
            new AuthorizationCredentials('username', 'password')
        );

        self::assertNotEmpty($connections);
        self::assertCount(1, $connections);
        self::assertEquals($expectedConnectionData, $connections[0]);
        self::assertNotEmpty($credentials);
        self::assertCount(4, $credentials);
        self::assertEquals($this->getSequraCredentials(), $credentials);
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     * @throws InvalidEnvironmentException
     * @throws Exception
     */
    public function testDeploymentMigrateTask(): void
    {
        // Arrange
        $this->storeService->setMockConnectedStores(['1', '2', '3']);
        $this->deploymentsService->setMockDeployments($this->getDeployments());
        $this->connectionProxy->setMockCredentials($this->getSequraCredentials());

        $connectionData = new ConnectionData(
            'sandbox',
            'merchant',
            '',
            new AuthorizationCredentials('username', 'password')
        );

        $connectionDataEntity = new ConnectionDataEntity();
        $connectionDataEntity->setConnectionData($connectionData);
        $connectionDataEntity->setStoreId('1');
        $this->connectionDataRepository->save($connectionDataEntity);

        $task = new DeploymentMigrateTask();

        // Act
        $task->execute();

        // Assert
        $connections = StoreContext::doWithStore(
            '1',
            [$this->connectionRepository, 'getAllConnectionSettings']
        );

        $credentials = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepo, 'getCredentials']
        );
        $expectedConnectionData1 = new ConnectionData(
            'sandbox',
            'merchant',
            'sequra',
            new AuthorizationCredentials('username', 'password')
        );
        $expectedConnectionData2 = new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        );

        self::assertNotEmpty($connections);
        self::assertCount(2, $connections);
        self::assertEquals($expectedConnectionData1, $connections[0]);
        self::assertEquals($expectedConnectionData2, $connections[1]);
        self::assertNotEmpty($credentials);
        self::assertCount(4, $credentials);
        self::assertEquals($this->getSequraCredentials(), $credentials);
    }

    /**
     * @return Deployment[]
     */
    private function getDeployments(): array
    {
        return [
            new Deployment(
                'sequra',
                'seQura',
                new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
                new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
            ),
            new Deployment(
                'svea',
                'SVEA',
                new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
                new DeploymentURL(
                    'https://next-sandbox.sequra.svea.com/',
                    'https://next-sandbox.cdn.sequra.svea.com/assets/'
                )
            )
        ];
    }

    /**
     * @return Credentials[]
     */
    private function getSequraCredentials(): array
    {
        return [
            new Credentials(
                'logeecom1',
                'PT',
                'EUR',
                'assetsKey1',
                [
                    "ref" => "logeecom1",
                    "name" => null,
                    "country" => "PT",
                    "allowed_countries" => [
                        "ES"
                    ],
                    "currency" => "EUR",
                    "assets_key" => "assetsKey1",
                    "contract_options" => [],
                    "extra_information" => [
                        "type" => "regular",
                        "phone_number" => ""
                    ],
                    "verify_signature" => false,
                    "signature_secret" => "signature",
                    "confirmation_path" => "default",
                    "realm" => "svea"
                ],
                'sequra'
            ),
            new Credentials(
                'logeecom2',
                'FR',
                'EUR',
                'assetsKey2',
                [
                    "ref" => "logeecom2",
                    "name" => null,
                    "country" => "FR",
                    "allowed_countries" => [
                        "ES"
                    ],
                    "currency" => "EUR",
                    "assets_key" => "assetsKey2",
                    "contract_options" => [],
                    "extra_information" => [
                        "type" => "regular",
                        "phone_number" => ""
                    ],
                    "verify_signature" => false,
                    "signature_secret" => "123",
                    "confirmation_path" => "default",
                    "realm" => "svea"
                ],
                'sequra'
            ),
            new Credentials(
                'logeecom3',
                'IT',
                'EUR',
                'assetsKey3',
                [
                    "ref" => "logeecom3",
                    "name" => null,
                    "country" => "IT",
                    "allowed_countries" => [
                        "ES"
                    ],
                    "currency" => "EUR",
                    "assets_key" => "assetsKey3",
                    "contract_options" => [],
                    "extra_information" => [
                        "type" => "regular",
                        "phone_number" => ""
                    ],
                    "verify_signature" => false,
                    "signature_secret" => "123",
                    "confirmation_path" => "default",
                    "realm" => "svea"
                ],
                'sequra'
            ),
            new Credentials(
                'logeecom4',
                'ES',
                'EUR',
                'assetsKey4',
                [
                    "ref" => "logeecom4",
                    "name" => null,
                    "country" => "ES",
                    "allowed_countries" => [
                        "ES"
                    ],
                    "currency" => "EUR",
                    "assets_key" => "assetsKey4",
                    "contract_options" => [],
                    "extra_information" => [
                        "type" => "regular",
                        "phone_number" => ""
                    ],
                    "verify_signature" => false,
                    "signature_secret" => "123",
                    "confirmation_path" => "default",
                    "realm" => "sequra"
                ],
                'sequra'
            )
        ];
    }
}
