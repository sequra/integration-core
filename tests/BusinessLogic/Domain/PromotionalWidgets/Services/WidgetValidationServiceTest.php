<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\PromotionalWidgets\Services;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetValidationService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockGeneralSettingsService;
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


    protected function setUp(): void
    {
        parent::setUp();

        $this->mockGeneralSettingsService = new MockGeneralSettingsService(
            TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class)
        );

        TestServiceRegister::registerService(
            GeneralSettingsService::class,
            function () {
                return $this->mockGeneralSettingsService;
            }
        );

        $this->widgetValidationService = TestServiceRegister::getService(WidgetValidationService::class);
    }

    /**
     * @return void
     */
    public function testWidgetAvailabilityWrongCurrency(): void
    {
        //arrange

        // act
        $result = $this->widgetValidationService->validateCurrentCurrencyAndIpAddress('USD', 'test');

        // assert
        self::assertFalse($result);
    }

    /**
     * @return void
     */
    public function testWidgetAvailabilityNoGeneralSettings(): void
    {
        //arrange

        // act
        $result = $this->widgetValidationService->validateCurrentCurrencyAndIpAddress('EUR', 'test');

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
        $result = $this->widgetValidationService->validateCurrentCurrencyAndIpAddress('EUR', 'test');

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
        $result = $this->widgetValidationService->validateCurrentCurrencyAndIpAddress('EUR', 'test');

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
        $result = $this->widgetValidationService->validateCurrentCurrencyAndIpAddress('EUR', 'test');

        // assert
        self::assertTrue($result);
    }
}
