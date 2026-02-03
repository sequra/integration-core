<?php

namespace SeQura\Core\Tests\BusinessLogic\Logger;

use Exception;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services\AdvancedSettingsService;
use SeQura\Core\BusinessLogic\Logger\Logger;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\LoggerConfiguration;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAdvancedSettingsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAdvancedSettingsService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class LoggerTest.
 *
 * @package Logger
 */
class LoggerTest extends BaseTestCase
{
    /** @var MockAdvancedSettingsService $advancedSettingsService */
    private $advancedSettingsService;

    /**
     * @var TestShopLogger
     */
    private $shopLogger;

    /**
     * @return void
     *
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->advancedSettingsService = new MockAdvancedSettingsService(
            new MockAdvancedSettingsRepository()
        );

        $this->shopLogger = new TestShopLogger();
        $this->shopConfig = new TestShopConfiguration();

        TestServiceRegister::registerService(
            AdvancedSettingsService::class,
            function () {
                return $this->advancedSettingsService;
            }
        );

        TestServiceRegister::registerService(
            ShopLoggerAdapter::class,
            function () {
                return $this->shopLogger;
            }
        );

        TestServiceRegister::registerService(
            Configuration::CLASS_NAME,
            function () {
                return $this->shopConfig;
            }
        );

        TestServiceRegister::registerService(
            ConfigurationManager::CLASS_NAME,
            function () {
                return new TestConfigurationManager();
            }
        );
        LoggerConfiguration::getInstance()->setMinLogLevel(2);
        Logger::resetInstance();
        $this->advancedSettingsService->setAdvancedSettings(null);
    }

    /**
     * Test if error log level is passed to shop logger
     */
    public function testErrorLogLevelIsPassedNoAdvancedSettings(): void
    {
        // arrange

        // act
        Logger::logError('Some data');

        // assert
        self::assertEquals('Some data', $this->shopLogger->loggedMessages[0]->getMessage());
    }

    /**
     * Test if error log level is passed to shop logger
     */
    public function testErrorLogLevelIsPassedNoAdvancedSettingsDisabled(): void
    {
        // arrange
        $this->advancedSettingsService->setAdvancedSettings(new AdvancedSettings(false, 1));

        // act
        Logger::logError('Some data');

        // assert
        self::assertEquals('Some data', $this->shopLogger->loggedMessages[0]->getMessage());
    }

    /**
     * @return void
     */
    public function testDebugLogLevelNotPassed(): void
    {
        // arrange
        $this->advancedSettingsService->setAdvancedSettings(new AdvancedSettings(true, 2));

        // act
        Logger::logDebug('Debug data');
        // assert
        self::assertEmpty($this->shopLogger->loggedMessages);
    }

    /**
     * @return void
     */
    public function testDebugLogLevelPassed(): void
    {
        // arrange
        $this->advancedSettingsService->setAdvancedSettings(new AdvancedSettings(true, 3));

        // act
        Logger::logDebug('Debug data');
        // assert
        self::assertEquals('Debug data', $this->shopLogger->loggedMessages[0]->getMessage());
    }
}
