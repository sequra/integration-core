<?php

namespace Domain\StoreIntegration\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreIntegrationProxy;

/**
 * Class StoreIntegrationServiceTest.
 *
 * @package Domain\StoreIntegration\Services
 */
class StoreIntegrationServiceTest extends BaseTestCase
{
    /**
     * @var StoreIntegrationService $service
     */
    private $service;

    /**
     * @var MockStoreIntegrationProxy $storeIntegrationProxy
     */
    private $storeIntegrationProxy;

    /**
     * @var MockIntegrationStoreIntegrationService $storeIntegrationService
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

        $this->storeIntegrationProxy = new MockStoreIntegrationProxy();
        $this->storeIntegrationService = new MockIntegrationStoreIntegrationService();

        $this->service = new StoreIntegrationService($this->storeIntegrationService, $this->storeIntegrationProxy);
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     */
    public function testEmptyCapabilitiesException(): void
    {
        // arrange
        $this->expectException(CapabilitiesEmptyException::class);
        ;

        //act
        $this->service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        //assert
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     */
    public function testCreateStoreIntegrationId(): void
    {
        // arrange
        $this->storeIntegrationService->setMockCapabilities([Capability::general()]);
        $this->storeIntegrationProxy->setMockCreateResponse(new CreateStoreIntegrationResponse('123456789'));

        //act
        $storeIntegrationId = $this->service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        //assert
        self::assertEquals('123456789', $storeIntegrationId);
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     * @throws InvalidUrlException
     */
    public function testCreateStoreQueries(): void
    {
        // arrange
        $this->storeIntegrationService->setMockWebhookUrl(new URL('https://test.com/webhook', []));
        $this->storeIntegrationService->setMockCapabilities([Capability::general()]);
        $this->storeIntegrationProxy->setMockCreateResponse(new CreateStoreIntegrationResponse('123456789'));

        //act
        $this->service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        $url = $this->storeIntegrationProxy->getWebhookUrl();

        //assert
        self::assertEquals('https://test.com/webhook', $url->getPath());
        self::assertNotNull($url->getQueryByKey('signature'));
        self::assertNotNull($url->getQueryByKey('storeId'));
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testDeleteStoreIntegration(): void
    {
        // arrange
        $this->storeIntegrationProxy->setMockDeleteResponse(new DeleteStoreIntegrationResponse());

        //act
        $this->service->deleteStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        //assert
        self::assertTrue($this->storeIntegrationProxy->isDeleted());
    }
}
