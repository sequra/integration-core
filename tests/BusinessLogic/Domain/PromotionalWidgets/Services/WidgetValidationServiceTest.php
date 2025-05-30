<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\PromotionalWidgets\Services;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetValidationService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockGeneralSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockProductService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class WidgetValidationServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\PromotionalWidgets\Services
 */
class WidgetValidationServiceTest extends BaseTestCase
{
    /**
     * @var WidgetValidationService
     */
    private $widgetValidationService;

    /**
     * @var MockGeneralSettingsService
     */
    private $mockGeneralSettingsService;

    /**
     * @var MockProductService
     */
    private $mockProductService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneralSettingsService = new MockGeneralSettingsService(
            TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class)
        );

        $this->mockProductService = new MockProductService();

        TestServiceRegister::registerService(
            ProductServiceInterface::class,
            function () {
                return $this->mockProductService;
            }
        );

        TestServiceRegister::registerService(
            GeneralSettingsService::class,
            function () {
                return $this->mockGeneralSettingsService;
            }
        );

        WidgetValidationService::$generalSettingsFetched = false;
        WidgetValidationService::$generalSettings = null;

        $this->widgetValidationService = TestServiceRegister::getService(WidgetValidationService::class);
    }

    /**
     * @return void
     */
    public function testWidgetAvailabilityWrongCurrency(): void
    {
        //arrange

        // act
        $result = $this->widgetValidationService->isCurrencySupported('USD');

        // assert
        self::assertFalse($result);
    }

    /**
     * @return void
     */
    public function testWidgetAvailabilityCorrectCurrency(): void
    {
        //arrange

        // act
        $result = $this->widgetValidationService->isCurrencySupported('EUR');

        // assert
        self::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testWidgetAvailabilityNoGeneralSettings(): void
    {
        //arrange
        WidgetValidationService::$generalSettingsFetched = true;

        // act
        $result = $this->widgetValidationService->isIpAddressValid('test');

        // assert
        self::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testWidgetAvailabilityNoIpAddressesInGeneralSettings(): void
    {
        //arrange
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                null,
                null,
                null
            )
        );
        // act
        $result = $this->widgetValidationService->isIpAddressValid('test');

        // assert
        self::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testWidgetAvailabilityIpAddressesInvalidInGeneralSettings(): void
    {
        //arrange
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7'],
                null,
                null
            )
        );
        // act
        $result = $this->widgetValidationService->isIpAddressValid('test');

        // assert
        self::assertFalse($result);
    }

    /**
     * @return void
     */
    public function testWidgetAvailabilityTrue(): void
    {
        //arrange
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                null,
                null
            )
        );
        // act
        $result = $this->widgetValidationService->isIpAddressValid('test');

        // assert
        self::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testIsProductSupportedForVirtualProduct(): void
    {
        //arrange
        $this->mockProductService->setMockProductVirtual(true);

        // act
        $result = $this->widgetValidationService->isProductSupported('sku1');

        // assert
        self::assertFalse($result);
    }

    /**
     * @return void
     */
    public function testIsProductSupportedNoGeneralSettings(): void
    {
        //arrange

        // act
        $result = $this->widgetValidationService->isProductSupported('sku1');

        // assert
        self::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testIsProductSupportedSkuExcluded(): void
    {
        //arrange
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                ['sku1', 'test1'],
                null
            )
        );
        $this->mockProductService->setMockProductSku('sku1');

        // act
        $result = $this->widgetValidationService->isProductSupported('sku1');

        // assert
        self::assertFalse($result);
    }

    /**
     * @return void
     */
    public function testIsProductSupportedCategoryExcluded(): void
    {
        //arrange
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                ['sku12', 'test21'],
                ['test1', 'test2', 'test3', 'test4', 'test5']
            )
        );
        $this->mockProductService->setMockProductCategories(['test1', 'test8', 'test2']);

        // act
        $result = $this->widgetValidationService->isProductSupported('sku1');

        // assert
        self::assertFalse($result);
    }

    /**
     * @return void
     */
    public function testIsProductSupportedNoExclusionSet(): void
    {
        //arrange
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                null,
                null
            )
        );
        // act
        $result = $this->widgetValidationService->isProductSupported('sku1');

        // assert
        self::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testIsProductSupportedTrue(): void
    {
        //arrange
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                null,
                null
            )
        );
        // act
        $result = $this->widgetValidationService->isProductSupported('sku1123');

        // assert
        self::assertTrue($result);
    }
}
