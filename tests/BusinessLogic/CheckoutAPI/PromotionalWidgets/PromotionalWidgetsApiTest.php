<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\PromotionalWidgets;

use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests\PromotionalWidgetsCheckoutRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses\PromotionalWidgetsCheckoutResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetInitializer;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\ProxyContracts\WidgetsProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSellingCountriesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockWidgetSettingsService;
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
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(
            SellingCountriesServiceInterface::class, static function () {
            return new MockSellingCountriesService();
        });

        $this->mockWidgetSettingsService = new MockWidgetSettingsService(
            TestServiceRegister::getService(WidgetSettingsRepositoryInterface::class),
            TestServiceRegister::getService(PaymentMethodsService::class),
            TestServiceRegister::getService(CountryConfigurationService::class),
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(WidgetsProxyInterface::class)
        );

        TestServiceRegister::registerService(
            WidgetSettingsService::class, function () {
            return $this->mockWidgetSettingsService;
        });
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
                'test')
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
}
