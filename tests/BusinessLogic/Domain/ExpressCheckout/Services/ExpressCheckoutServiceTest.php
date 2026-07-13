<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\MerchantDataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\OrderCreationInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPageConfig;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents\MockCreateOrderRequestBuilder;
use SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents\MockOrderProxy;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockGeneralSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockMerchantOrderBuilder;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockProductService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSeQuraOrderRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ExpressCheckoutServiceTest.
 *
 * Exercises every availability guard branch on the domain service in isolation.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Services
 */
class ExpressCheckoutServiceTest extends BaseTestCase
{
    /**
     * @var MockGeneralSettingsService
     */
    private $generalSettingsService;

    /**
     * @var MockCountryConfigurationService
     */
    private $countryConfigurationService;

    /**
     * @var MockPaymentMethodService
     */
    private $paymentMethodsService;

    /**
     * @var MockProductService
     */
    private $productService;

    /**
     * @var ExpressCheckoutService
     */
    private $service;

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

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

        $this->paymentMethodsService = new MockPaymentMethodService(
            TestServiceRegister::getService(MerchantProxyInterface::class),
            TestServiceRegister::getService(PaymentMethodRepositoryInterface::class),
            TestServiceRegister::getService(CountryConfigurationService::class)
        );
        TestServiceRegister::registerService(PaymentMethodsService::class, function () {
            return $this->paymentMethodsService;
        });

        $this->productService = new MockProductService();
        TestServiceRegister::registerService(ProductServiceInterface::class, function () {
            return $this->productService;
        });

        $this->service = TestServiceRegister::getService(ExpressCheckoutService::class);
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws PaymentMethodNotFoundException
     * @throws HttpRequestException
     */
    public function testNotAvailableWhenSettingsNotSaved(): void
    {
        $this->seedCountryConfiguration();
        $this->seedGeneralSettings();
        $this->seedPaymentMethods();

        self::assertFalse($this->callIsAvailable());
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testNotAvailableWhenPageIsDisabled(): void
    {
        $this->service->saveExpressCheckoutSettings(new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), false),
        ]));
        $this->seedCountryConfiguration();
        $this->seedGeneralSettings();
        $this->seedPaymentMethods();

        self::assertFalse($this->callIsAvailable());
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function testNotAvailableWhenCurrencyUnsupported(): void
    {
        $this->seedHappyState();

        self::assertFalse($this->callIsAvailable(['currency' => 'USD']));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function testNotAvailableWhenIpAddressNotAllowed(): void
    {
        $this->seedHappyState();
        $this->generalSettingsService->saveGeneralSettings(
            new GeneralSettings(false, null, ['9.9.9.9'], null, null)
        );

        self::assertFalse($this->callIsAvailable());
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function testNotAvailableWhenCountryHasNoMerchant(): void
    {
        $this->seedHappyState();

        self::assertFalse($this->callIsAvailable(['shippingCountry' => 'XX']));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function testNotAvailableWhenNoCachedPaymentMethods(): void
    {
        $this->seedHappyState();
        $this->paymentMethodsService->setMockPaymentMethods([]);

        self::assertFalse($this->callIsAvailable());
    }

    /**
     * The availability probe must fail safe to "unavailable" rather than surfacing the
     * payment-methods lookup error to the storefront.
     *
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function testNotAvailableWhenPaymentMethodsLookupThrows(): void
    {
        $this->seedHappyState();
        $this->paymentMethodsService->setThrowPaymentMethodNotFound(true);

        self::assertFalse($this->callIsAvailable());
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function testIsAvailableHappyPath(): void
    {
        $this->seedHappyState();

        self::assertTrue($this->callIsAvailable());
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function testNotAvailableWhenProductIsExcluded(): void
    {
        $this->seedHappyState();
        $this->generalSettingsService->saveGeneralSettings(
            new GeneralSettings(false, null, null, ['excluded-sku'], null)
        );
        $this->productService->setMockProductSku('excluded-sku');

        self::assertFalse($this->callIsAvailable([], ['product-1']));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function testNotAvailableWhenCategoryIsExcluded(): void
    {
        $this->seedHappyState();
        $this->generalSettingsService->saveGeneralSettings(
            new GeneralSettings(false, null, null, null, ['excluded-category'])
        );

        self::assertFalse($this->callIsAvailable([], [], ['excluded-category']));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function testIsAvailableHappyPathWithEligibleProduct(): void
    {
        $this->seedHappyState();
        $this->productService->setMockProductSku('eligible-sku');
        $this->productService->setMockProductCategories(['eligible-category']);

        self::assertTrue($this->callIsAvailable([], ['product-1']));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws PaymentMethodNotFoundException
     * @throws HttpRequestException
     */
    public function testGuestNotAvailableWhenSettingsNotSaved(): void
    {
        $this->seedGeneralSettings();

        self::assertFalse($this->callGuestAvailable());
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws WrongCredentialsException
     */
    public function testGuestNotAvailableWhenPageIsDisabled(): void
    {
        $this->service->saveExpressCheckoutSettings(new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), false),
        ]));
        $this->seedGeneralSettings();

        self::assertFalse($this->callGuestAvailable());
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws WrongCredentialsException
     */
    public function testGuestNotAvailableWhenCurrencyUnsupported(): void
    {
        $this->seedHappyState();

        self::assertFalse($this->callGuestAvailable(['currency' => 'USD']));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws WrongCredentialsException
     */
    public function testGuestNotAvailableWhenIpAddressNotAllowed(): void
    {
        $this->seedHappyState();
        $this->generalSettingsService->saveGeneralSettings(
            new GeneralSettings(false, null, ['9.9.9.9'], null, null)
        );

        self::assertFalse($this->callGuestAvailable());
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws WrongCredentialsException
     */
    public function testGuestNotAvailableWhenProductIsExcluded(): void
    {
        $this->seedHappyState();
        $this->generalSettingsService->saveGeneralSettings(
            new GeneralSettings(false, null, null, ['excluded-sku'], null)
        );
        $this->productService->setMockProductSku('excluded-sku');

        self::assertFalse($this->callGuestAvailable([], ['product-1']));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws WrongCredentialsException
     */
    public function testGuestNotAvailableWhenCategoryIsExcluded(): void
    {
        $this->seedHappyState();
        $this->generalSettingsService->saveGeneralSettings(
            new GeneralSettings(false, null, null, null, ['excluded-category'])
        );

        self::assertFalse($this->callGuestAvailable([], [], ['excluded-category']));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws WrongCredentialsException
     */
    public function testGuestAvailableHappyPath(): void
    {
        $this->seedHappyState();

        self::assertTrue($this->callGuestAvailable([], ['product-1'], ['category-1']));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSolicitDelegatesToOrderService(): void
    {
        // Arrange
        $solicitedOrder = (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('ref-ec');
        $solicitedOrder->setCartId('cart-ec');
        $expectedForm = new SeQuraForm('<html>delegated-form</html>');

        $orderProxy = new MockOrderProxy();
        $orderProxy->setMockResult($solicitedOrder, [], $expectedForm);

        $merchantOrderBuilder = new MockMerchantOrderBuilder(
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(CredentialsService::class),
            TestServiceRegister::getService(MerchantDataProviderInterface::class)
        );

        $orderService = new OrderService(
            $orderProxy,
            new MockSeQuraOrderRepository(),
            $merchantOrderBuilder,
            TestServiceRegister::getService(OrderCreationInterface::class)
        );

        TestServiceRegister::registerService(OrderService::class, static function () use ($orderService) {
            return $orderService;
        });

        $service = TestServiceRegister::getService(ExpressCheckoutService::class);
        $builder = new MockCreateOrderRequestBuilder();

        // Act
        $form = $service->solicit($builder);

        // Assert
        self::assertSame($expectedForm, $form);
        $formRequest = $orderProxy->getLastGetFormRequest();
        self::assertNull($formRequest->getProduct());
        self::assertNull($formRequest->getCampaign());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSolicitWithCountryCheckReturnsNullForUnsupportedCountry(): void
    {
        // Arrange: configured countries do not include the builder's delivery country (ES).
        $this->countryConfigurationService->saveCountryConfiguration([
            new CountryConfiguration('FR', 'merchantFR'),
        ]);

        $orderProxy = new MockOrderProxy();
        $orderService = new OrderService(
            $orderProxy,
            new MockSeQuraOrderRepository(),
            new MockMerchantOrderBuilder(
                TestServiceRegister::getService(ConnectionService::class),
                TestServiceRegister::getService(CredentialsService::class),
                TestServiceRegister::getService(MerchantDataProviderInterface::class)
            ),
            TestServiceRegister::getService(OrderCreationInterface::class)
        );
        TestServiceRegister::registerService(OrderService::class, static function () use ($orderService) {
            return $orderService;
        });
        $service = TestServiceRegister::getService(ExpressCheckoutService::class);

        // Act
        $form = $service->solicit(new MockCreateOrderRequestBuilder(), true);

        // Assert: rejected before the order service was involved.
        self::assertNull($form);
        self::assertSame(0, $orderProxy->getFormCallCount());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSolicitWithCountryCheckDelegatesForSupportedCountry(): void
    {
        // Arrange: ES (the builder's delivery country) maps to a configured merchant.
        $this->seedCountryConfiguration();

        $solicitedOrder = (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('ref-ec-cc');
        $solicitedOrder->setCartId('cart-ec-cc');
        $expectedForm = new SeQuraForm('<html>country-checked-form</html>');

        $orderProxy = new MockOrderProxy();
        $orderProxy->setMockResult($solicitedOrder, [], $expectedForm);

        $orderService = new OrderService(
            $orderProxy,
            new MockSeQuraOrderRepository(),
            new MockMerchantOrderBuilder(
                TestServiceRegister::getService(ConnectionService::class),
                TestServiceRegister::getService(CredentialsService::class),
                TestServiceRegister::getService(MerchantDataProviderInterface::class)
            ),
            TestServiceRegister::getService(OrderCreationInterface::class)
        );
        TestServiceRegister::registerService(OrderService::class, static function () use ($orderService) {
            return $orderService;
        });
        $service = TestServiceRegister::getService(ExpressCheckoutService::class);

        // Act
        $form = $service->solicit(new MockCreateOrderRequestBuilder(), true);

        // Assert
        self::assertSame($expectedForm, $form);
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    private function seedHappyState(): void
    {
        $this->service->saveExpressCheckoutSettings(new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
        ]));
        $this->seedCountryConfiguration();
        $this->seedGeneralSettings();
        $this->seedPaymentMethods();
    }

    /**
     * @return void
     */
    private function seedCountryConfiguration(): void
    {
        $this->countryConfigurationService->saveCountryConfiguration([
            new CountryConfiguration('ES', 'merchant1'),
        ]);
    }

    /**
     * @return void
     */
    private function seedGeneralSettings(): void
    {
        $this->generalSettingsService->saveGeneralSettings(
            new GeneralSettings(false, null, null, null, null)
        );
    }

    /**
     * @return void
     */
    private function seedPaymentMethods(): void
    {
        $this->paymentMethodsService->setMockPaymentMethods([
            new SeQuraPaymentMethod(
                'pp3',
                'Paga en 3',
                'Paga en 3',
                'part_payment',
                new SeQuraCost(0, 0, 0, 0),
                new \DateTime(),
                new \DateTime('+1 year'),
                null,
                null,
                null,
                '',
                null,
                null,
                null
            ),
        ]);
    }

    /**
     * @param array<string, string> $overrides
     * @param string[] $productIds
     * @param string[] $categoryIds
     *
     * @return bool
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws PaymentMethodNotFoundException
     * @throws HttpRequestException
     */
    private function callIsAvailable(array $overrides = [], array $productIds = [], array $categoryIds = []): bool
    {
        $defaults = [
            'page' => ExpressCheckoutPage::product()->getPage(),
            'shippingCountry' => 'ES',
            'currency' => 'EUR',
            'ipAddress' => '1.2.3.4',
        ];
        $args = array_merge($defaults, $overrides);

        return $this->service->isExpressCheckoutAvailable(
            $args['page'],
            $args['shippingCountry'],
            $args['currency'],
            $args['ipAddress'],
            $productIds,
            $categoryIds
        );
    }

    /**
     * @param array<string, string> $overrides
     * @param string[] $productIds
     * @param string[] $categoryIds
     *
     * @return bool
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     */
    private function callGuestAvailable(array $overrides = [], array $productIds = [], array $categoryIds = []): bool
    {
        $defaults = [
            'page' => ExpressCheckoutPage::product()->getPage(),
            'currency' => 'EUR',
            'ipAddress' => '1.2.3.4',
        ];
        $args = array_merge($defaults, $overrides);

        return $this->service->isAvailableForGuest(
            $args['page'],
            $args['currency'],
            $args['ipAddress'],
            $productIds,
            $categoryIds
        );
    }
}
