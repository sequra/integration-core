<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation;

use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Requests\SolicitationRequest;
use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\MerchantDataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\OrderCreationInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\MerchantOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents\MockCreateOrderRequestBuilder;
use SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents\MockOrderProxy;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockMerchantDataProvider;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockGeneralSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockMerchantOrderBuilder;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockProductService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class SolicitationCheckoutAPITest
 *
 * @package BusinessLogic\CheckoutAPI\Solicitation
 */
class SolicitationCheckoutApiTest extends BaseTestCase
{
    /**
     * @var MockOrderProxy
     */
    private $orderProxy;
    /**
     * @var SeQuraOrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var MerchantOrderRequestBuilder $merchantOrderBuilder
     */
    private $merchantOrderBuilder;

    /**
     * @var OrderCreationInterface
     */
    private $shopOrderCreation;

    /**
     * @var MockCountryConfigurationService
     */
    private $countryConfigurationService;

    /**
     * @var MockGeneralSettingsService
     */
    private $generalSettingsService;

    /**
     * @var MockProductService
     */
    private $productService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderProxy = new MockOrderProxy();
        $this->orderRepository = TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class);
        $this->merchantOrderBuilder = new MockMerchantOrderBuilder(
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(CredentialsService::class),
            TestServiceRegister::getService(MerchantDataProviderInterface::class)
        );
        $this->shopOrderCreation = TestServiceRegister::getService(OrderCreationInterface::class);

        TestServiceRegister::registerService(
            OrderService::class,
            function () {
                return new OrderService(
                    $this->orderProxy,
                    $this->orderRepository,
                    $this->merchantOrderBuilder,
                    $this->shopOrderCreation
                );
            }
        );

        // CheckoutService caches GeneralSettings in statics for the duration of a request.
        CheckoutService::$generalSettings = null;
        CheckoutService::$generalSettingsFetched = false;

        $this->generalSettingsService = new MockGeneralSettingsService(
            TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class),
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(CountryConfigurationService::class)
        );
        TestServiceRegister::registerService(GeneralSettingsService::class, function () {
            return $this->generalSettingsService;
        });

        $this->countryConfigurationService = new MockCountryConfigurationService(
            TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
            TestServiceRegister::getService(SellingCountriesService::class)
        );
        TestServiceRegister::registerService(CountryConfigurationService::class, function () {
            return $this->countryConfigurationService;
        });

        // ServiceRegister does not memoize, so share one instance the tests can arrange.
        $this->productService = new MockProductService();
        TestServiceRegister::registerService(ProductServiceInterface::class, function () {
            return $this->productService;
        });

        // MockCreateOrderRequestBuilder ships an 'ES' delivery address.
        $this->countryConfigurationService->saveCountryConfiguration([
            new CountryConfiguration('ES', 'testMerchantId'),
        ]);
    }

    public function testStartFreshSolicitation()
    {
        // Arrange
        $expectedSeQuraOrder = (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef');
        $this->orderProxy->setMockResult(
            $expectedSeQuraOrder
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder())
        );

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));

        $actualSeQuraOrder = $this->orderRepository->getByOrderReference('testOrderRef');
        self::assertNotNull($actualSeQuraOrder);

        self::assertEquals($expectedSeQuraOrder, $actualSeQuraOrder);
        self::assertEquals($actualSeQuraOrder, $response->getSolicitedOrder());
        self::assertEquals([], $response->getAvailablePaymentMethods());
    }

    public function testStartAlreadyStartedSocilitation()
    {
        // Arrange
        $expectedSeQuraOrder = (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef');
        $this->orderProxy->setMockResult(
            $expectedSeQuraOrder
        );
        CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder())
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder())
        );

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertEquals(1, RepositoryRegistry::getRepository(SeQuraOrder::getClassName())->count());

        $actualSeQuraOrder = $this->orderRepository->getByOrderReference('testOrderRef');
        self::assertNotNull($actualSeQuraOrder);

        self::assertEquals($expectedSeQuraOrder, $actualSeQuraOrder);
        self::assertEquals($actualSeQuraOrder, $response->getSolicitedOrder());
    }

    public function testStartSocilitationFailure()
    {
        // Arrange
        $expectedException = new \InvalidArgumentException('Test exception during the request building');
        $this->orderProxy->setMockResult(
            (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef')
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder($expectedException))
        );

        // Assert
        self::assertFalse($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertStringContainsString($expectedException->getMessage(), $response->toArray()['errorMessage']);
    }

    public function testSolicitationReturnsAvailablePaymentMethodsForSolictedOrder()
    {
        // Arrange
        $expectedAvailablePaymentMethod = new SeQuraPaymentMethod(
            'i1',
            'title1',
            'longTitle1',
            'category1',
            new SeQuraCost(1, 2, 3, 4),
            new \DateTime(),
            new \DateTime(),
            'campaign1',
            'claim1',
            'description1',
            'icon1',
            'costDescription1',
            1234567.89,
            321
        );
        $this->orderProxy->setMockResult(
            (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef'),
            [$expectedAvailablePaymentMethod]
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder())
        );

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertEquals([$expectedAvailablePaymentMethod], $response->getAvailablePaymentMethods());
    }

    public function testSolicitationUnavailableWhenShippingCountryNotConfigured()
    {
        // Arrange
        $this->countryConfigurationService->saveCountryConfiguration([
            new CountryConfiguration('FR', 'testMerchantId'),
        ]);
        $this->orderProxy->setMockResult(
            (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef')
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder())
        );

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertNull($response->getSolicitedOrder());
        self::assertEquals([], $response->getAvailablePaymentMethods());
        self::assertEquals(['order' => null, 'availablePaymentMethods' => []], $response->toArray());
        self::assertSame(0, $this->orderProxy->getCreateOrderCallCount());
        self::assertSame(0, RepositoryRegistry::getRepository(SeQuraOrder::getClassName())->count());
    }

    public function testSolicitationUnavailableWhenNoCountryConfigurationSaved()
    {
        // Arrange
        $this->countryConfigurationService->saveCountryConfiguration([]);

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder())
        );

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertNull($response->getSolicitedOrder());
        self::assertSame(0, $this->orderProxy->getCreateOrderCallCount());
    }

    public function testSolicitationSkipsCountryCheckWhenDisabled()
    {
        // Arrange
        $this->countryConfigurationService->saveCountryConfiguration([]);
        $expectedSeQuraOrder = (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef');
        $this->orderProxy->setMockResult($expectedSeQuraOrder);

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder(), '', [], [], false)
        );

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertEquals($expectedSeQuraOrder, $response->getSolicitedOrder());
        self::assertSame(1, $this->orderProxy->getCreateOrderCallCount());
    }

    public function testSolicitationUnavailableWhenIpAddressNotAllowed()
    {
        // Arrange
        $this->generalSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, ['9.9.9.9'], null, null)
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder(), '1.2.3.4')
        );

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertNull($response->getSolicitedOrder());
        self::assertSame(0, $this->orderProxy->getCreateOrderCallCount());
    }

    public function testSolicitationUnavailableWhenProductExcluded()
    {
        // Arrange
        $this->generalSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, null, ['excluded-sku'], null)
        );
        $this->productService->setMockProductSku('excluded-sku');

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new SolicitationRequest(new MockCreateOrderRequestBuilder(), '', ['p1'])
        );

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertNull($response->getSolicitedOrder());
        self::assertSame(0, $this->orderProxy->getCreateOrderCallCount());
    }

    public function testSolicitationBuildsTheOrderRequestOnlyOnce()
    {
        // Arrange
        $expectedSeQuraOrder = (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef');
        $this->orderProxy->setMockResult($expectedSeQuraOrder);
        $builder = new MockCreateOrderRequestBuilder();

        // Act
        CheckoutAPI::get()->solicitation('test1')->solicitFor(new SolicitationRequest($builder));

        // Assert
        self::assertSame(1, $builder->getBuildCallCount());
    }
}
