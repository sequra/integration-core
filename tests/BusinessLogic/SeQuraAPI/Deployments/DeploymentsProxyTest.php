<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Deployments;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\DeploymentURL;
use SeQura\Core\BusinessLogic\Domain\Deployments\ProxyContracts\DeploymentsProxyInterface;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class DeploymentsProxyTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Deployments
 */
class DeploymentsProxyTest extends BaseTestCase
{
    /**
     * @var DeploymentsProxyInterface
     */
    public $proxy;
    /**
     * @var TestHttpClient
     */
    public $httpClient;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->proxy = TestServiceRegister::getService(DeploymentsProxyInterface::class);
    }

    /**
     * @return void
     */
    public function testDeploymentsRequestUrl(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Deployments/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getDeployments();

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals('https://live.sequrapi.com/deployments', $lastRequest['url']);
    }

    /**
     * @return void
     */
    public function testDeploymentsRequestMethod(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Deployments/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getDeployments();

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    /**
     * @return void
     */
    public function testDeploymentsResponse(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Deployments/SuccessfulResponse.json'
            ))
        ]);

        $deployments = $this->proxy->getDeployments();

        $expectedSequraDeployment = new Deployment(
            'sequra',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );

        $expectedSVEADeployment = new Deployment(
            'svea',
            'SVEA',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL('https://next-sandbox.sequra.svea.com/', 'https://next-sandbox.cdn.sequra.svea.com/assets/')
        );

        self::assertEquals($expectedSequraDeployment, $deployments[0]);
        self::assertEquals($expectedSVEADeployment, $deployments[1]);
    }
}
