<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\StoreIntegration\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\HMAC\HMAC;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\StoreInfo;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\StoreIntegrationNotFoundException;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidSignatureException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionDataRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreInfoAndUrlService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreInfoService;
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
     * @var MockStoreInfoService $storeInfoService
     */
    private $storeInfoService;

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
        $this->storeInfoService = new MockStoreInfoService();
        $this->storeInfoService->setMockStoreInfo(
            new StoreInfo('Test Shop', 'https://shop.example.com', '', '', '', '', '', '')
        );

        $this->service = new StoreIntegrationService(
            $this->storeIntegrationService,
            $this->storeIntegrationProxy,
            $this->connectionDataRepository,
            $this->storeInfoService
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
    public function testCreateStoreIntegrationInvokesProxyOnce(): void
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
        $expected = HMAC::generateHMAC([StoreContext::getInstance()->getStoreId(), 'https://shop.example.com'], 'password');

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

        $expected = HMAC::generateHMAC([StoreContext::getInstance()->getStoreId(), 'https://shop.example.com'], 'password');
        self::assertEquals($expected, $signature);
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws StoreIntegrationNotFoundException
     */
    public function testValidateWebhookSignatureAcceptsNonFirstDeployment(): void
    {
        $this->connectionDataRepository->setConnectionData(new ConnectionData(
            'sandbox',
            'merchant',
            'deployment-1',
            new AuthorizationCredentials('username', 'password1')
        ));
        $this->connectionDataRepository->setConnectionData(new ConnectionData(
            'sandbox',
            'merchant',
            'deployment-2',
            new AuthorizationCredentials('username', 'password2')
        ));

        $secondSignature = HMAC::generateHMAC(
            [StoreContext::getInstance()->getStoreId(), 'https://shop.example.com'],
            'password2'
        );

        $this->service->validateWebhookSignature($secondSignature);
        $this->addToAssertionCount(1);
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testValidateWebhookSignatureRejectsUnknownSignature(): void
    {
        $this->expectException(InvalidSignatureException::class);

        $this->connectionDataRepository->setConnectionData(new ConnectionData(
            'sandbox',
            'merchant',
            'deployment-1',
            new AuthorizationCredentials('username', 'password1')
        ));

        $this->service->validateWebhookSignature('totally-invalid-signature');
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

    /**
     * When the injected StoreInfoService also implements
     * {@see \SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreUrlProviderInterface},
     * the HMAC computation should use the cheap getStoreUrl() path and never
     * build a full StoreInfo. Integrations whose getStoreInfo() is expensive
     * rely on this to keep webhook validation fast.
     *
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     */
    public function testSignaturePayloadPrefersStoreUrlProviderWhenAvailable(): void
    {
        $storeInfoService = new MockStoreInfoAndUrlService('https://shop.example.com');

        $service = new StoreIntegrationService(
            $this->storeIntegrationService,
            $this->storeIntegrationProxy,
            $this->connectionDataRepository,
            $storeInfoService
        );

        $this->storeIntegrationService->setMockCapabilities([Capability::general()]);
        $this->storeIntegrationProxy->setMockCreateResponse(new CreateStoreIntegrationResponse('123'));

        $service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        self::assertSame(1, $storeInfoService->getStoreUrlCallCount(), 'getStoreUrl() should be used for the HMAC payload.');
        self::assertSame(0, $storeInfoService->getStoreInfoCallCount(), 'getStoreInfo() should not be called when StoreUrlProviderInterface is available.');
    }

    /**
     * Signatures produced via the legacy getStoreInfo() path and via the new
     * getStoreUrl() opt-in path must be byte-identical for the same shop, so a
     * plugin can adopt the optimisation without invalidating any previously
     * issued webhook URL.
     *
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     * @throws InvalidUrlException
     */
    public function testStoreUrlProviderProducesTheSameSignatureAsGetStoreInfo(): void
    {
        $this->storeIntegrationService->setMockWebhookUrl(new URL('https://test.com/webhook', []));
        $this->storeIntegrationService->setMockCapabilities([Capability::general()]);
        $this->storeIntegrationProxy->setMockCreateResponse(new CreateStoreIntegrationResponse('123'));

        $this->service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));
        $legacySignature = $this->storeIntegrationProxy->getWebhookUrl()->getQueryByKey('signature')->getValue();

        $proxy = new MockStoreIntegrationProxy();
        $proxy->setMockCreateResponse(new CreateStoreIntegrationResponse('123'));

        $optInService = new StoreIntegrationService(
            $this->storeIntegrationService,
            $proxy,
            $this->connectionDataRepository,
            new MockStoreInfoAndUrlService('https://shop.example.com')
        );

        $optInService->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));
        $optInSignature = $proxy->getWebhookUrl()->getQueryByKey('signature')->getValue();

        self::assertSame($legacySignature, $optInSignature);
    }

    /**
     * A plugin that only implements the legacy StoreInfoServiceInterface keeps
     * working — getStoreInfo() is called and its storeUrl flows into the
     * signature. The new interface is a pure opt-in extension.
     *
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     */
    public function testSignaturePayloadFallsBackToGetStoreInfoWhenStoreUrlProviderIsNotImplemented(): void
    {
        $this->storeIntegrationService->setMockCapabilities([Capability::general()]);
        $this->storeIntegrationProxy->setMockCreateResponse(new CreateStoreIntegrationResponse('123'));

        $this->service->createStoreIntegration(new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        ));

        self::assertSame(1, $this->storeInfoService->getStoreInfoCallCount(), 'Legacy plugins must still go through getStoreInfo().');
    }
}
