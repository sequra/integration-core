<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\PromotionalWidgets;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSellingCountriesService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class PromotionalWidgetsControllerTest extends BaseTestCase
{
    /**
     * @var WidgetSettingsRepositoryInterface
     */
    private $widgetSettingsRepository;

    /**
     * Default widget settings.
     *
     * @var WidgetSettings
     */
    private $defaultWidgetSettings;

    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(
            SellingCountriesServiceInterface::class,
            function () {
                return new MockSellingCountriesService();
            }
        );

        $this->widgetSettingsRepository = TestServiceRegister::getService(WidgetSettingsRepositoryInterface::class);
        $this->defaultWidgetSettings = WidgetSettings::createDefault(
            '.product.price',
            '',
            '.cart.price',
            '',
            '',
            '.listing.selector'
        );
    }

    public function testGetConfigNoConfigSet()
    {
        // act
        $result = AdminAPI::get()->widgetConfiguration('store1')->getWidgetSettings($this->defaultWidgetSettings);

        // assert
        self::assertEquals([
            'productPriceSelector' => '.product.price',
            'defaultProductLocationSelector' => '',
            'altProductPriceSelector' => '',
            'altProductPriceTriggerSelector' => '',
            'cartPriceSelector' => '.cart.price',
            'cartLocationSelector' => '',
            'listingPriceSelector' => '',
            'listingLocationSelector' => '.listing.selector',
            'displayWidgetOnProductPage' => false,
            'showInstallmentAmountInProductListing' => false,
            'showInstallmentAmountInCartPage' => false,
            'widgetStyles' => null,
            'customLocations' => [],
            'widgetOnCartPage' => '',
            'widgetOnListingPage' => '',
        ], $result->toArray());
    }

    public function testGetSettings()
    {
        // arrange
        $settings = new WidgetSettings(
            false,
            false,
            false,
            '{"alignment":"center","amount-font-bold":"true","amount-font-color":"#1c1c1c","amount-font-size":"15","background-color":"white","border-color":"#ce5c00","border-radius":"","class":"","font-color":"#1c1c1c","link-font-color":"#1c1c1c","link-underline":"true","no-costs-claim":"","size":"M","starting-text":"only","type":"banner"}'
        );
        StoreContext::doWithStore('store1', [$this->widgetSettingsRepository, 'setWidgetSettings'], [$settings]);

        // act
        $result = AdminAPI::get()->widgetConfiguration('store1')->getWidgetSettings();

        // assert
        self::assertEquals(
            [
                'displayWidgetOnProductPage' => $settings->isDisplayOnProductPage(),
                'showInstallmentAmountInProductListing' => $settings->isShowInstallmentsInProductListing(),
                'showInstallmentAmountInCartPage' => $settings->isShowInstallmentsInCartPage(),
                'widgetStyles' => $settings->getWidgetConfig(),
            ],
            $result->toArray()
        );
    }

    public function testSetSettingsValidJson()
    {
        // arrange
        $settings = new WidgetSettingsRequest(
            false,
            true,
            true,
            '{"style":"banner"}',
            '.price',
            '.location',
            '.cart-price',
            '.cart-location',
            'sp1',
            'pp3',
            '.listing-price',
            '.listing-location',
            '.alt-price-test',
            '.alt-trigger-test',
            ['selForTarget' => 'target', 'product' => 'i1', 'displayWidget' => 'true', 'widgetStyles' => '{"style":"banner"}']
        );

        // act
        AdminAPI::get()->widgetConfiguration('store1')->setWidgetSettings($settings);

        // assert
        $savedSettings = StoreContext::doWithStore('store1', [$this->widgetSettingsRepository, 'getWidgetSettings']);
        self::assertEquals($settings->transformToDomainModel(), $savedSettings);
    }

    public function testSetSettingsInvalidJson()
    {
        // arrange
        $settings = new WidgetSettingsRequest(
            false,
            true,
            true,
            'banner',
            '.price',
            '.location',
            '.cart-price',
            '.cart-location',
            'sp1',
            'pp3',
            '.listing-price',
            '.listing-location',
            '.alt-price-test',
            '.alt-trigger-test',
            ['selForTarget' => 'target', 'product' => 'i1', 'displayWidget' => 'true', 'widgetStyles' => 'styles']
        );

        // act
        AdminAPI::get()->widgetConfiguration('store1')->setWidgetSettings($settings);

        // assert
        $savedSettings = StoreContext::doWithStore('store1', [$this->widgetSettingsRepository, 'getWidgetSettings']);
        self::assertNull($savedSettings);
    }
}
