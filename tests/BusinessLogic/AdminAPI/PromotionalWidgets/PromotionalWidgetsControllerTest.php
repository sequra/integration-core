<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\PromotionalWidgets;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetConfiguration;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
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
            new WidgetConfiguration(
                'text',
                'medium',
                'blue',
                'red',
                'center',
                'black',
                'only',
                '15',
                '#1c1c1c',
                'true',
                '#1c1c1c',
                'true',
                'pink',
                '',
                ''
            ),
            new WidgetLabels(
                [
                    'ES' => 'test es',
                    'IT' => 'test it',
                ],
                [
                    'ES' => 'test test es',
                    'IT' => 'test test it',
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
                'showInstallmentAmountInProductListing' => $settings->isShowInProductListing(),
                'showInstallmentAmountInCartPage' => $settings->isShowInCartPage(),
                'assetsKey' => $settings->getAssetsKey(),
                'widgetConfiguration' => [
                    'type' => $settings->getWidgetConfig()->getType(),
                    'size' => $settings->getWidgetConfig()->getSize(),
                    'fontColor' => $settings->getWidgetConfig()->getFontColor(),
                    'backgroundColor' => $settings->getWidgetConfig()->getBackgroundColor(),
                    'alignment' => $settings->getWidgetConfig()->getAlignment(),
                    'branding' => $settings->getWidgetConfig()->getBranding(),
                    'startingText' => $settings->getWidgetConfig()->getStartingText(),
                    'amountFontSize' => $settings->getWidgetConfig()->getAmountFontSize(),
                    'amountFontColor' => $settings->getWidgetConfig()->getAmountFontColor(),
                    'amountFontBold' => $settings->getWidgetConfig()->getAmountFontBold(),
                    'linkFontColor' => $settings->getWidgetConfig()->getLinkFontColor(),
                    'linkUnderline' => $settings->getWidgetConfig()->getLinkUnderline(),
                    'borderColor' => $settings->getWidgetConfig()->getBorderColor(),
                    'borderRadius' => $settings->getWidgetConfig()->getBorderRadius(),
                    'noCostsClaim' => $settings->getWidgetConfig()->getNoCostsClaim(),
                ],
                'widgetLabels' => [
                    'messages' => $settings->getWidgetLabels()->getMessages(),
                    'messagesBelowLimit' => $settings->getWidgetLabels()->getMessagesBelowLimit(),
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
            'banner'
        );

        // act
        AdminAPI::get()->widgetConfiguration('store1')->setWidgetSettings($settings);

        // assert
        $savedSettings = StoreContext::doWithStore('store1', [$this->widgetSettingsRepository, 'getWidgetSettings']);
        self::assertEquals($settings->transformToDomainModel(), $savedSettings);
    }
}