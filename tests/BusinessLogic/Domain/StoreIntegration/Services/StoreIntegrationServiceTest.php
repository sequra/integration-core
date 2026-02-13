<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\StoreIntegration\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\StoreIntegration;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreIntegrationProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreIntegrationRepository;

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
     * @var MockStoreIntegrationRepository
     */
    private $storeIntegrationRepository;

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
        $this->storeIntegrationRepository = new MockStoreIntegrationRepository();

        $this->service = new StoreIntegrationService(
            $this->storeIntegrationService,
            $this->storeIntegrationProxy,
            $this->storeIntegrationRepository
        );
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
        $this->storeIntegrationRepository->setStoreIntegration(null);
        $this->expectException(CapabilitiesEmptyException::class);

        //act
        $this->service->getOrCreateStoreIntegration(new ConnectionData(
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
        $this->service->getOrCreateStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        //assert
        $savedIntegration = $this->storeIntegrationRepository->getStoreIntegration();
        self::assertEquals('123456789', $savedIntegration->getIntegrationId());
        self::assertNotEmpty($savedIntegration->getSignature());
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
        $this->service->getOrCreateStoreIntegration(new ConnectionData(
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
        $this->service->deleteStoreIntegration(
            new ConnectionData(
                'sandbox',
                'merchant',
                'svea',
                new AuthorizationCredentials('username', 'password')
            ),
            new StoreIntegration(
                '1',
                'signature',
                '4'
            )
        );

        //assert
        self::assertTrue($this->storeIntegrationProxy->isDeleted());
    }
}
