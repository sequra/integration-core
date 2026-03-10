<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\AdvancedSettings\Services;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services\AdvancedLoggerSettingsProvider;
use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAdvancedSettingsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAdvancedSettingsService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class AdvancedLoggerSettingsProviderTest.
 *
 * @package Domain\AdvancedSettings\Services
 */
class AdvancedLoggerSettingsProviderTest extends TestCase
{
    /**
     * @var AdvancedLoggerSettingsProvider
     */
    private $provider;

    /**
     * @var MockAdvancedSettingsService
     */
    private $advancedSettingsService;

    /**
     * @var TestShopConfiguration
     */
    private $configuration;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $this->advancedSettingsService = new MockAdvancedSettingsService(
            new MockAdvancedSettingsRepository()
        );

        $this->configuration = new TestShopConfiguration();

        new TestServiceRegister(
            [
                ConfigurationManager::CLASS_NAME => function () {
                    return new TestConfigurationManager();
                },
                Configuration::CLASS_NAME => function () {
                    return $this->configuration;
                },
            ]
        );

        $this->provider = new AdvancedLoggerSettingsProvider(
            $this->advancedSettingsService,
            $this->configuration
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        MemoryStorage::reset();
        TestRepositoryRegistry::cleanUp();

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testIsDefaultLoggerEnabledFromAdvancedSettings(): void
    {
        // Arrange
        $this->advancedSettingsService->setAdvancedSettings(new AdvancedSettings(true, Logger::INFO));

        // Act
        $result = $this->provider->isDefaultLoggerEnabled();

        // Assert
        self::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testIsDefaultLoggerDisabledFromAdvancedSettings(): void
    {
        // Arrange
        $this->advancedSettingsService->setAdvancedSettings(new AdvancedSettings(false, Logger::INFO));

        // Act
        $result = $this->provider->isDefaultLoggerEnabled();

        // Assert
        self::assertFalse($result);
    }

    /**
     * @return void
     */
    public function testIsDefaultLoggerEnabledFallsBackToConfiguration(): void
    {
        // Arrange
        $this->advancedSettingsService->setAdvancedSettings(null);
        $this->configuration->setDefaultLoggerEnabled(true);

        // Act
        $result = $this->provider->isDefaultLoggerEnabled();

        // Assert
        self::assertTrue($result);
    }

    /**
     * @return void
     */
    public function testGetMinLogLevelFromAdvancedSettings(): void
    {
        // Arrange
        $this->advancedSettingsService->setAdvancedSettings(new AdvancedSettings(true, Logger::WARNING));

        // Act
        $result = $this->provider->getMinLogLevel();

        // Assert
        self::assertEquals(Logger::WARNING, $result);
    }

    /**
     * @return void
     */
    public function testGetMinLogLevelFallsBackToConfiguration(): void
    {
        // Arrange
        $this->advancedSettingsService->setAdvancedSettings(null);
        $this->configuration->saveMinLogLevel(Logger::ERROR);

        // Act
        $result = $this->provider->getMinLogLevel();

        // Assert
        self::assertEquals(Logger::ERROR, $result);
    }

    /**
     * @return void
     */
    public function testSaveMinLogLevelToAdvancedSettings(): void
    {
        // Arrange
        $this->advancedSettingsService->setAdvancedSettings(new AdvancedSettings(true, Logger::INFO));

        // Act
        $this->provider->saveMinLogLevel(Logger::DEBUG);

        // Assert
        $advancedSettings = $this->advancedSettingsService->getAdvancedSettings();
        self::assertNotNull($advancedSettings);
        self::assertTrue($advancedSettings->isEnabled());
        self::assertEquals(Logger::DEBUG, $advancedSettings->getLevel());
    }

    /**
     * @return void
     */
    public function testSaveMinLogLevelFallsBackToConfiguration(): void
    {
        // Arrange
        $this->advancedSettingsService->setAdvancedSettings(null);

        // Act
        $this->provider->saveMinLogLevel(Logger::WARNING);

        // Assert
        self::assertEquals(Logger::WARNING, $this->configuration->getMinLogLevel());
    }
}
