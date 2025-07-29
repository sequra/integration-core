<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\PromotionalWidgets;

use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests\PromotionalWidgetsCheckoutRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses\GetWidgetsCheckoutResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses\PromotionalWidgetsCheckoutResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\MiniWidgetMessagesProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetConfiguratorInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\Widget;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetInitializer;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetValidationService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSellingCountriesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockWidgetSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockWidgetValidator;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PromotionalWidgetsApiTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\CheckoutAPI\PromotionalWidgets
 */
class PromotionalWidgetsApiTest extends BaseTestCase
{
    /**
     * @var MockWidgetSettingsService
     */
    private $mockWidgetSettingsService;

    /**
     * @var MockWidgetValidator
     */
    private $mockWidgetValidator;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(
            SellingCountriesServiceInterface::class,
            static function () {
                return new MockSellingCountriesService();
            }
        );

        $this->mockWidgetValidator = new MockWidgetValidator(
            TestServiceRegister::getService(GeneralSettingsService::class),
            TestServiceRegister::getService(ProductServiceInterface::class)
        );

        TestServiceRegister::registerService(
            WidgetValidationService::class,
            function () {
                return $this->mockWidgetValidator;
            }
        );

        $this->mockWidgetSettingsService = new MockWidgetSettingsService(
            TestServiceRegister::getService(WidgetSettingsRepositoryInterface::class),
            TestServiceRegister::getService(PaymentMethodsService::class),
            TestServiceRegister::getService(CredentialsService::class),
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(WidgetConfiguratorInterface::class),
            TestServiceRegister::getService(MiniWidgetMessagesProviderInterface::class),
            TestServiceRegister::getService(DeploymentsService::class)
        );

        TestServiceRegister::registerService(
            WidgetSettingsService::class,
            function () {
                return $this->mockWidgetSettingsService;
            }
        );
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetPromotionalWidgetInitializeDataSuccess(): void
    {
        //Arrange
        $this->mockWidgetSettingsService->setMockWidgetInitializeData(
            new WidgetInitializer(
                'assets',
                'merchant1',
                ['product1', 'product2'],
                'test'
            )
        );

        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getPromotionalWidgetInitializeData(new PromotionalWidgetsCheckoutRequest('ES', 'ES'));

        //Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetPromotionalWidgetInitializeDataToArray(): void
    {
        //Arrange
        $this->mockWidgetSettingsService->setMockWidgetInitializeData(
            new WidgetInitializer(
                'assets1',
                'merchant1',
                ['product1', 'product2'],
                'testScriptUri.com',
                'es',
                'EUR',
                ',',
                '.'
            )
        );

        //Act
        /** @var PromotionalWidgetsCheckoutResponse $response */
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getPromotionalWidgetInitializeData(new PromotionalWidgetsCheckoutRequest('ES', 'ES'));

        //Assert
        self::assertEquals([
            'assetKey' => 'assets1',
            'merchantId' => 'merchant1',
            'products' => ['product1', 'product2'],
            'scriptUri' => 'testScriptUri.com',
            'locale' => 'es',
            'currency' => 'EUR',
            'decimalSeparator' => ',',
            'thousandSeparator' => '.',
        ], $response->toArray());
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetAvailableWidgetForCartPageSuccess(): void
    {
        //Arrange
        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableWidgetForCartPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetAvailableWidgetForCartPageToArray(): void
    {
        //Arrange
        $this->mockWidgetSettingsService->setMockWidget(new Widget(
            'product1',
            'campaign',
            'priceSel',
            'destination',
            'theme',
            '0',
            123,
            321,
            'altPrice',
            'triggerSel',
            'message',
            'below message'
        ));

        //Act
        /** @var GetWidgetsCheckoutResponse $response */
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableWidgetForCartPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertEquals(
            [
                [
                    'product' => 'product1',
                    'dest' => 'destination',
                    'theme' => 'theme',
                    'reverse' => '0',
                    'campaign' => 'campaign',
                    'priceSel' => 'priceSel',
                    'altPriceSel' => 'altPrice',
                    'altTriggerSelector' => 'triggerSel',
                    'minAmount' => 123,
                    'maxAmount' => 321,
                    'miniWidgetMessage' => 'message',
                    'miniWidgetBelowLimitMessage' => 'below message'
                ]
            ],
            $response->toArray()
        );
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetAvailableMiniWidgetForProductListingPageSuccess(): void
    {
        //Arrange
        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableMiniWidgetForProductListingPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetAvailableMiniWidgetForProductListingPageToArray(): void
    {
        //Arrange
        $this->mockWidgetSettingsService->setMockWidget(new Widget(
            'product1',
            'campaign',
            'priceSel',
            'destination',
            'theme',
            '0',
            123,
            321,
            'altPrice',
            'triggerSel',
            'message',
            'below message'
        ));

        //Act
        /** @var GetWidgetsCheckoutResponse $response */
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableMiniWidgetForProductListingPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertEquals(
            [
                [
                    'product' => 'product1',
                    'dest' => 'destination',
                    'theme' => 'theme',
                    'reverse' => '0',
                    'campaign' => 'campaign',
                    'priceSel' => 'priceSel',
                    'altPriceSel' => 'altPrice',
                    'altTriggerSelector' => 'triggerSel',
                    'minAmount' => 123,
                    'maxAmount' => 321,
                    'miniWidgetMessage' => 'message',
                    'miniWidgetBelowLimitMessage' => 'below message'
                ]
            ],
            $response->toArray()
        );
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetAvailableWidgetsForProductPageSuccess(): void
    {
        //Arrange
        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableWidgetsForProductPage(new PromotionalWidgetsCheckoutRequest('ES', 'ES'));

        //Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetAvailableWidgetsForProductPageToArray(): void
    {
        //Arrange
        $this->mockWidgetSettingsService->setMockWidgets([
            new Widget(
                'product1',
                'campaign',
                'priceSel',
                'destination',
                'theme',
                '0',
                123,
                321,
                'altPrice',
                'triggerSel',
                'message',
                'below message'
            ),
            new Widget(
                'product2',
                'campaign2',
                'priceSel2',
                'destination2',
                'theme2',
                '0',
                125,
                311,
                'altPrice2',
                'triggerSel2',
                'message2',
                'below message2'
            )
        ]);


        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableWidgetsForProductPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertEquals(
            [
                [
                    'product' => 'product1',
                    'dest' => 'destination',
                    'theme' => 'theme',
                    'reverse' => '0',
                    'campaign' => 'campaign',
                    'priceSel' => 'priceSel',
                    'altPriceSel' => 'altPrice',
                    'altTriggerSelector' => 'triggerSel',
                    'minAmount' => 123,
                    'maxAmount' => 321,
                    'miniWidgetMessage' => 'message',
                    'miniWidgetBelowLimitMessage' => 'below message'
                ],
                [
                    'product' => 'product2',
                    'dest' => 'destination2',
                    'theme' => 'theme2',
                    'reverse' => '0',
                    'campaign' => 'campaign2',
                    'priceSel' => 'priceSel2',
                    'altPriceSel' => 'altPrice2',
                    'altTriggerSelector' => 'triggerSel2',
                    'minAmount' => 125,
                    'maxAmount' => 311,
                    'miniWidgetMessage' => 'message2',
                    'miniWidgetBelowLimitMessage' => 'below message2'
                ]
            ],
            $response->toArray()
        );
    }

    /**
     * @return void
     */
    public function testGetAvailableWidgetForCartPageCurrencyInvalid(): void
    {
        //Arrange
        $this->mockWidgetValidator->setCurrencyValid(false);

        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableWidgetForCartPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     */
    public function testGetAvailableWidgetForCartPageIpAddressInvalid(): void
    {
        //Arrange
        $this->mockWidgetValidator->setCurrencyValid(true);
        $this->mockWidgetValidator->setAddressValid(false);

        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableWidgetForCartPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     */
    public function testGetAvailableMiniWidgetForProductListingPageCurrencyInvalid(): void
    {
        //Arrange
        $this->mockWidgetValidator->setCurrencyValid(false);
        $this->mockWidgetValidator->setAddressValid(true);

        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableMiniWidgetForProductListingPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     */
    public function testGetAvailableMiniWidgetForProductListingPageIpAddressInvalid(): void
    {
        //Arrange
        $this->mockWidgetValidator->setCurrencyValid(true);
        $this->mockWidgetValidator->setAddressValid(false);

        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableMiniWidgetForProductListingPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     */
    public function testGetAvailableMiniWidgetForProductListingPageProductNotSupported(): void
    {
        //Arrange
        $this->mockWidgetValidator->setCurrencyValid(true);
        $this->mockWidgetValidator->setAddressValid(true);
        $this->mockWidgetValidator->setProductValid(false);

        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableMiniWidgetForProductListingPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     */
    public function testGetAvailableWidgetsForProductPageCurrencyInvalid(): void
    {
        //Arrange
        $this->mockWidgetValidator->setCurrencyValid(false);
        $this->mockWidgetValidator->setAddressValid(true);

        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableWidgetsForProductPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     */
    public function testGetAvailableWidgetsForProductPageIpAddressInvalid(): void
    {
        //Arrange
        $this->mockWidgetValidator->setCurrencyValid(true);
        $this->mockWidgetValidator->setAddressValid(false);

        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableWidgetsForProductPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     */
    public function testGetAvailableWidgetsForProductPageProductNotSupported(): void
    {
        //Arrange
        $this->mockWidgetValidator->setCurrencyValid(true);
        $this->mockWidgetValidator->setAddressValid(true);
        $this->mockWidgetValidator->setProductValid(false);

        //Act
        $response = CheckoutAPI::get()->promotionalWidgets('1')
            ->getAvailableWidgetsForProductPage(
                new PromotionalWidgetsCheckoutRequest(
                    'ES',
                    'ES',
                    'EUR',
                    '127.0.0.1'
                )
            );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }
}
