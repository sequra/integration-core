<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\StoreIntegration\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\HMAC\HMAC;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\StoreIntegrationNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionDataRepository;
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
     * @var MockConnectionDataRepository $connectionDataRepository
     */
    private $connectionDataRepository;

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
        $this->connectionDataRepository = new MockConnectionDataRepository();

        $this->service = new StoreIntegrationService(
            $this->storeIntegrationService,
            $this->storeIntegrationProxy,
            $this->connectionDataRepository
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
        $this->expectException(CapabilitiesEmptyException::class);

        $this->service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     */
    public function testCreateStoreIntegrationCallsProxy(): void
    {
        $this->storeIntegrationService->setMockCapabilities([Capability::general()]);
        $this->storeIntegrationProxy->setMockCreateResponse(new CreateStoreIntegrationResponse('123456789'));

        $this->service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        self::assertEquals(1, $this->storeIntegrationProxy->getCreateCallCount());
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
        $this->storeIntegrationService->setMockWebhookUrl(new URL('https://test.com/webhook', []));
        $this->storeIntegrationService->setMockCapabilities([Capability::general()]);
        $this->storeIntegrationProxy->setMockCreateResponse(new CreateStoreIntegrationResponse('123456789'));

        $this->service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        $url = $this->storeIntegrationProxy->getWebhookUrl();

        self::assertEquals('https://test.com/webhook', $url->getPath());
        self::assertNotNull($url->getQueryByKey('signature'));
        self::assertNotNull($url->getQueryByKey('storeId'));
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     * @throws InvalidUrlException
     */
    public function testCreateStoreIntegrationSignatureIsHMAC(): void
    {
        $this->storeIntegrationService->setMockWebhookUrl(new URL('https://test.com/webhook', []));
        $this->storeIntegrationService->setMockCapabilities([Capability::general()]);
        $this->storeIntegrationProxy->setMockCreateResponse(new CreateStoreIntegrationResponse('123456789'));

        $this->service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        $url = $this->storeIntegrationProxy->getWebhookUrl();
        $expected = HMAC::generateHMAC([StoreContext::getInstance()->getStoreId()], 'password');

        self::assertEquals($expected, $url->getQueryByKey('signature')->getValue());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws InvalidUrlException
     */
    public function testDeleteStoreIntegration(): void
    {
        $this->storeIntegrationService->setMockWebhookUrl(new URL('https://test.com/webhook', []));

        $this->service->deleteStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        self::assertTrue($this->storeIntegrationProxy->isDeleted());
    }

    /**
     * @return void
     */
    public function testGetWebhookSignatureNoSignature(): void
    {
        $this->expectException(StoreIntegrationNotFoundException::class);

        $this->service->getWebhookSignature();
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws StoreIntegrationNotFoundException
     */
    public function testGetWebhookSignature(): void
    {
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        );
        $this->connectionDataRepository->setConnectionData($connectionData);

        $signature = $this->service->getWebhookSignature();

        $expected = HMAC::generateHMAC([StoreContext::getInstance()->getStoreId()], 'password');
        self::assertEquals($expected, $signature);
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws StoreIntegrationNotFoundException
     */
    public function testGetWebhookSignatureIsDeterministic(): void
    {
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        );
        $this->connectionDataRepository->setConnectionData($connectionData);

        $first = $this->service->getWebhookSignature();
        $second = $this->service->getWebhookSignature();

        self::assertEquals($first, $second);
    }
}
