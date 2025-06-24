<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Deployments\Service;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\DeploymentURL;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
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
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->deploymentsProxy = new MockDeploymentsProxy();
        $this->deploymentsRepository = new MockDeploymentsRepository();

        $this->deploymentService = new DeploymentsService($this->deploymentsProxy, $this->deploymentsRepository);
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
}
