<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\Deployments;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Deployments\Responses\DeploymentsResponse;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\DeploymentURL;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDeploymentsProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDeploymentsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDeploymentsService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class DeploymentsControllerTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\Deployments
 */
class DeploymentsControllerTest extends BaseTestCase
{
    /**
     * @var MockDeploymentsService
     */
    private $deploymentsService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->deploymentsService = new MockDeploymentsService(
            new MockDeploymentsProxy(),
            new MockDeploymentsRepository()
        );

        TestServiceRegister::registerService(DeploymentsService::class, function () {
            return $this->deploymentsService;
        });
    }

    /**
     * @return void
     */
    public function testIsGetDeploymentsResponseSuccessful(): void
    {
        // Act
        /** @var DeploymentsResponse $response */
        $response = AdminAPI::get()->deployments('1')->getAllDeployments();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testGetDeploymentsToArrayEmpty(): void
    {
        // Act
        /** @var DeploymentsResponse $response */
        $response = AdminAPI::get()->deployments('1')->getAllDeployments();

        // Assert
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     */
    public function testGetDeploymentsToArray(): void
    {
        // Act
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
        $this->deploymentsService->setMockDeployments($deployments);

        /** @var DeploymentsResponse $response */
        $response = AdminAPI::get()->deployments('1')->getAllDeployments();

        // Assert
        self::assertEquals(
            [
            ['id' => 'sequra', 'name' => 'seQura'],
            ['id' => 'svea', 'name' => 'SVEA'],
            ],
            $response->toArray()
        );
    }
}
