<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\PromotionalWidgets;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLocation;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLocationConfig;
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
    }

    public function testGetConfigNoConfigSet()
    {
        // act
        $result = AdminAPI::get()->widgetConfiguration('store1')->getWidgetSettings();

        // assert
        self::assertEquals([], $result->toArray());
    }

    public function testGetSettings()
    {
        // arrange
        $settings = new WidgetSettings(
            true,
            'qwerty',
            false,
            false,
            false,
            '',
            '{"alignment":"center","amount-font-bold":"true","amount-font-color":"#1c1c1c","amount-font-size":"15","background-color":"white","border-color":"#ce5c00","border-radius":"","class":"","font-color":"#1c1c1c","link-font-color":"#1c1c1c","link-underline":"true","no-costs-claim":"","size":"M","starting-text":"only","type":"banner"}',
            new WidgetLabels(
                [
                    'ES' => 'test es',
                    'IT' => 'test it',
                ],
                [
                    'ES' => 'test test es',
                    'IT' => 'test test it',
                ]
            ),
            new WidgetLocationConfig(
                'selector-for-price',
                'selector-for-alt-price',
                'selector-for-alt-price-trigger',
                'selector-for-default-location',
                [
                    new WidgetLocation(
                        'selector-for-location',
                        'pp3',
                        'ES'
                    ),
                    new WidgetLocation(
                        'selector-for-location2',
                        'i1',
                        'IT'
                    )
                ]
            )
        );
        StoreContext::doWithStore('store1', [$this->widgetSettingsRepository, 'setWidgetSettings'], [$settings]);

        // act
        $result = AdminAPI::get()->widgetConfiguration('store1')->getWidgetSettings();

        // assert
        self::assertEquals(
            [
                'useWidgets' => $settings->isEnabled(),
                'displayWidgetOnProductPage' => $settings->isDisplayOnProductPage(),
                'showInstallmentAmountInProductListing' => $settings->isShowInstallmentsInProductListing(),
                'showInstallmentAmountInCartPage' => $settings->isShowInstallmentsInCartPage(),
                'assetsKey' => $settings->getAssetsKey(),
                'miniWidgetSelector' => '',
                'widgetConfiguration' => '{"alignment":"center","amount-font-bold":"true","amount-font-color":"#1c1c1c","amount-font-size":"15","background-color":"white","border-color":"#ce5c00","border-radius":"","class":"","font-color":"#1c1c1c","link-font-color":"#1c1c1c","link-underline":"true","no-costs-claim":"","size":"M","starting-text":"only","type":"banner"}',
                'widgetLabels' => [
                    'messages' => $settings->getWidgetLabels()->getMessages(),
                    'messagesBelowLimit' => $settings->getWidgetLabels()->getMessagesBelowLimit(),
                ],
                'selForPrice' => 'selector-for-price',
                'selForAltPrice' => 'selector-for-alt-price',
                'selForAltPriceTrigger' => 'selector-for-alt-price-trigger',
                'selForDefaultLocation' => 'selector-for-default-location',
                'customLocations' => [
                    [
                        'selForTarget' => 'selector-for-location',
                        'product' => 'pp3',
                        'country' => 'ES',
                    ],
                    [
                        'selForTarget' => 'selector-for-location2',
                        'product' => 'i1',
                        'country' => 'IT',
                    ],
                ],
            ],
            $result->toArray()
        );
    }

    public function testSetSettings()
    {
        // arrange
        $settings = new WidgetSettingsRequest(
            false,
            'qqqwerty',
            false,
            true,
            true,
            '',
            'banner'
        );

        // act
        AdminAPI::get()->widgetConfiguration('store1')->setWidgetSettings($settings);

        // assert
        $savedSettings = StoreContext::doWithStore('store1', [$this->widgetSettingsRepository, 'getWidgetSettings']);
        self::assertEquals($settings->transformToDomainModel(), $savedSettings);
    }
}
