<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Deployments\Service;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\DeploymentURL;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionDataRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDeploymentsProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDeploymentsRepository;

/**
 * Class DeploymentsServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Deployments\Service
 */
class DeploymentsServiceTest extends BaseTestCase
{
    /**
     * @var DeploymentsService $deploymentService
     */
    private $deploymentService;

    /**
     * @var MockDeploymentsProxy $deploymentsProxy
     */
    private $deploymentsProxy;

    /**
     * @var MockDeploymentsRepository $deploymentsRepository
     */
    private $deploymentsRepository;

    /**
     * @var MockConnectionDataRepository $connectionDataRepository
     */
    private $connectionDataRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->deploymentsProxy = new MockDeploymentsProxy();
        $this->deploymentsRepository = new MockDeploymentsRepository();
        $this->connectionDataRepository = new MockConnectionDataRepository();
        $this->deploymentService = new DeploymentsService(
            $this->deploymentsProxy,
            $this->deploymentsRepository,
            $this->connectionDataRepository
        );
    }

    /**
     * @throws Exception
     */
    public function testGetDeploymentsNoDeployments(): void
    {
        // Arrange

        // Act
        $response = StoreContext::doWithStore('1', [$this->deploymentService, 'getDeployments']);

        // Assert
        self::assertEmpty($response);
    }

    /**
     * @throws Exception
     */
    public function testGetDeployments(): void
    {
        // Arrange
        $deployments = [
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
        $this->deploymentsProxy->setMockDeployments($deployments);

        // Act
        $response = StoreContext::doWithStore('1', [$this->deploymentService, 'getDeployments']);

        // Assert
        self::assertEquals($deployments, $response);
    }

    /**
     * @throws Exception
     */
    public function testDeploymentsSavedInRepository(): void
    {
        // Arrange
        $deployments = [
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
        $this->deploymentsProxy->setMockDeployments($deployments);

        // Act
        StoreContext::doWithStore('1', [$this->deploymentService, 'getDeployments']);

        // Assert
        self::assertEquals($deployments, $this->deploymentsRepository->getDeployments());
    }

    /**
     * @throws Exception
     */
    public function testGetDeploymentByIdNoDeployment(): void
    {
        // Arrange

        // Act
        $deployment = StoreContext::doWithStore('1', [$this->deploymentService, 'getDeploymentById'], ['sequra']);

        // Assert
        self::assertNull($deployment);
    }

    /**
     * @throws Exception
     */
    public function testGetDeploymentById(): void
    {
        // Arrange
        $sequraDeployment = new Deployment(
            'sequra',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
        $sveaDeployment = new Deployment(
            'svea',
            'SVEA',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL(
                'https://next-sandbox.sequra.svea.com/',
                'https://next-sandbox.cdn.sequra.svea.com/assets/'
            )
        );
        $deployments = [$sequraDeployment, $sveaDeployment];

        $this->deploymentsRepository->setDeployments($deployments);

        // Act
        $expectedSequra = StoreContext::doWithStore('1', [$this->deploymentService, 'getDeploymentById'], ['sequra']);
        $expectedSvea = StoreContext::doWithStore('1', [$this->deploymentService, 'getDeploymentById'], ['svea']);

        // Assert
        self::assertEquals($expectedSequra, $sequraDeployment);
        self::assertEquals($expectedSvea, $sveaDeployment);
    }

    /**
     * @throws Exception
     */
    public function testGetDeploymentByIdCached(): void
    {
        // Arrange
        $sequraDeployment = new Deployment(
            'sequra2',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
        $sveaDeployment = new Deployment(
            'svea2',
            'SVEA',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL(
                'https://next-sandbox.sequra.svea.com/',
                'https://next-sandbox.cdn.sequra.svea.com/assets/'
            )
        );
        $deployments = [$sequraDeployment, $sveaDeployment];

        $this->deploymentsRepository->setDeployments($deployments);

        // Act
        $expectedSequraFirstTime = StoreContext::doWithStore(
            '1',
            [$this->deploymentService, 'getDeploymentById'],
            ['sequra2']
        );
        $expectedSequraSecondTime = StoreContext::doWithStore(
            '1',
            [$this->deploymentService, 'getDeploymentById'],
            ['sequra2']
        );

        // Assert
        self::assertEquals($expectedSequraFirstTime, $sequraDeployment);
        self::assertEquals($expectedSequraSecondTime, $sequraDeployment);
    }

    /**
     * @throws Exception
     */
    public function testGetDeploymentByIdNoDeploymentsInDatabase(): void
    {
        // Arrange
        $sequraDeployment = new Deployment(
            'sequra1',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
        $sveaDeployment = new Deployment(
            'svea1',
            'SVEA',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL(
                'https://next-sandbox.sequra.svea.com/',
                'https://next-sandbox.cdn.sequra.svea.com/assets/'
            )
        );
        $deployments = [$sequraDeployment, $sveaDeployment];

        $this->deploymentsProxy->setMockDeployments($deployments);

        // Act
        $expectedSequra = StoreContext::doWithStore('1', [$this->deploymentService, 'getDeploymentById'], ['sequra1']);
        $expectedSvea = StoreContext::doWithStore('1', [$this->deploymentService, 'getDeploymentById'], ['svea1']);

        // Assert
        self::assertEquals($expectedSequra, $sequraDeployment);
        self::assertEquals($expectedSvea, $sveaDeployment);
    }

    /**
     * @throws Exception
     */
    public function testGetNotConnectedDeploymentsAllConnected(): void
    {
        // Arrange
        $sequraDeployment = new Deployment(
            'sequra1',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
        $sveaDeployment = new Deployment(
            'svea1',
            'SVEA',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL(
                'https://next-sandbox.sequra.svea.com/',
                'https://next-sandbox.cdn.sequra.svea.com/assets/'
            )
        );
        $deployments = [$sequraDeployment, $sveaDeployment];

        $this->deploymentsRepository->setDeployments($deployments);
        $this->connectionDataRepository->setConnectionData(
            new ConnectionData(
                'sandbox',
                'merchant',
                'sequra1',
                new AuthorizationCredentials('username', 'password')
            )
        );
        $this->connectionDataRepository->setConnectionData(
            new ConnectionData(
                'sandbox',
                'merchant',
                'svea1',
                new AuthorizationCredentials('username', 'password')
            )
        );
        // Act
        $result = StoreContext::doWithStore('1', [$this->deploymentService, 'getNotConnectedDeployments']);

        // Assert
        self::assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testGetNotConnectedDeployments(): void
    {
        // Arrange
        $sequraDeployment = new Deployment(
            'sequra1',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
        $sveaDeployment = new Deployment(
            'svea1',
            'SVEA',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL(
                'https://next-sandbox.sequra.svea.com/',
                'https://next-sandbox.cdn.sequra.svea.com/assets/'
            )
        );

        $sequraDeployment2 = new Deployment(
            'sequra2',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
        $sveaDeployment2 = new Deployment(
            'svea2',
            'SVEA',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL(
                'https://next-sandbox.sequra.svea.com/',
                'https://next-sandbox.cdn.sequra.svea.com/assets/'
            )
        );
        $deployments = [$sequraDeployment, $sveaDeployment, $sequraDeployment2, $sveaDeployment2];

        $this->deploymentsProxy->setMockDeployments($deployments);
        $this->connectionDataRepository->setConnectionData(
            new ConnectionData(
                'sandbox',
                'merchant',
                'sequra1',
                new AuthorizationCredentials('username', 'password')
            )
        );
        $this->connectionDataRepository->setConnectionData(
            new ConnectionData(
                'sandbox',
                'merchant',
                'svea1',
                new AuthorizationCredentials('username', 'password')
            )
        );
        // Act
        $result = StoreContext::doWithStore('1', [$this->deploymentService, 'getNotConnectedDeployments']);

        // Assert
        self::assertCount(2, $result);
        self::assertEquals($sequraDeployment2, $result[0]);
        self::assertEquals($sveaDeployment2, $result[1]);
    }
}
