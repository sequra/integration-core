<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\PromotionalWidgets;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetConfigRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetLabelsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetConfiguration;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetConfigRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetLabelsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class PromotionalWidgetsControllerTest extends BaseTestCase
{
    /**
     * @var WidgetConfigRepositoryInterface
     */
    private $widgetConfigRepository;
    /**
     * @var WidgetSettingsRepositoryInterface
     */
    private $widgetSettingsRepository;
    /**
     * @var WidgetLabelsRepositoryInterface
     */
    private $widgetLabelsRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->widgetConfigRepository = TestServiceRegister::getService(WidgetConfigRepositoryInterface::class);
        $this->widgetSettingsRepository = TestServiceRegister::getService(WidgetSettingsRepositoryInterface::class);
        $this->widgetLabelsRepository = TestServiceRegister::getService(WidgetLabelsRepositoryInterface::class);
    }

    public function testGetConfig()
    {
        // arrange
        $config = new WidgetConfiguration(
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
        );
        StoreContext::doWithStore('store1', [$this->widgetConfigRepository, 'setWidgetConfig'], [$config]);

        // act
        $result = AdminAPI::get()->widgetConfiguration('store1')->getWidgetConfig();

        // assert
        self::assertEquals(
            [
                'type' => $config->getType(),
                'size' => $config->getSize(),
                'font-color' => $config->getFontColor(),
                'background-color' => $config->getBackgroundColor(),
                'alignment' => $config->getAlignment(),
                'branding' => $config->getBranding(),
                'starting-text' => $config->getStartingText(),
                'amount-font-size' => $config->getAmountFontSize(),
                'amount-font-color' => $config->getAmountFontColor(),
                'amount-font-bold' => $config->getAmountFontBold(),
                'link-font-color' => $config->getLinkFontColor(),
                'link-underline' => $config->getLinkUnderline(),
                'border-color' => $config->getBorderColor(),
                'border-radius' => $config->getBorderRadius(),
                'no-costs-claim' => $config->getNoCostsClaim(),
            ],
            $result->toArray()
        );
    }

    public function testGetConfigNoConfigSet()
    {
        // act
        $result = AdminAPI::get()->widgetConfiguration('store1')->getWidgetConfig();

        // assert
        self::assertEquals([], $result->toArray());
    }

    public function testGetSettings()
    {
        // arrange
        $settings = new WidgetSettings(
            true,
            'qwerty',
            false
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
            ],
            $result->toArray()
        );
    }

    public function testGetLabels()
    {
        // arrange
        $labels = new WidgetLabels(
            [
                'ES' => 'test es',
                'IT' => 'test it',
            ],
            [
                'ES' => 'test test es',
                'IT' => 'test test it',
            ]
        );
        StoreContext::doWithStore('store1', [$this->widgetLabelsRepository, 'setWidgetLabels'], [$labels]);

        // act
        $result = AdminAPI::get()->widgetConfiguration('store1')->getWidgetLabels();

        // assert
        self::assertEquals(
            [
                'messages' => $labels->getMessages(),
                'messagesBelowLimit' => $labels->getMessagesBelowLimit(),
            ],
            $result->toArray()
        );
    }

    public function testSetConfig()
    {
        // arrange
        $config = new WidgetConfigRequest(
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
        );

        // act
        AdminAPI::get()->widgetConfiguration('store1')->setWidgetConfig($config);

        // assert
        $savedConfig = StoreContext::doWithStore('store1', [$this->widgetConfigRepository, 'getWidgetConfig']);
        self::assertEquals($config->transformToDomainModel(), $savedConfig);
    }

    public function testSetSettings()
    {
        // arrange
        $settings = new WidgetSettingsRequest(
            false,
            'qqqwerty',
            true,
            false,
            true
        );

        // act
        AdminAPI::get()->widgetConfiguration('store1')->setWidgetSettings($settings);

        // assert
        $savedSettings = StoreContext::doWithStore('store1', [$this->widgetSettingsRepository, 'getWidgetSettings']);
        self::assertEquals($settings->transformToDomainModel(), $savedSettings);
    }

    public function testSetLabels()
    {
        // arrange
        $labels = new WidgetLabelsRequest(
            [
                'ES' => 'test es',
                'IT' => 'test it',
            ],
            [
                'ES' => 'test test es',
                'IT' => 'test test it',
            ]
        );

        // act
        AdminAPI::get()->widgetConfiguration('store1')->setWidgetLabels($labels);

        // assert
        $savedLabels = StoreContext::doWithStore('store1', [$this->widgetLabelsRepository, 'getWidgetLabels']);
        self::assertEquals($labels->transformToDomainModel(), $savedLabels);
    }
}