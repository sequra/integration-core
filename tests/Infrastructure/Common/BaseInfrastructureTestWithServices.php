<?php

/** @noinspection PhpDuplicateArrayKeysInspection */

namespace SeQura\Core\Tests\Infrastructure\Common;

use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Logger\LoggerConfiguration;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseTest.
 *
 * @package SeQura\Core\Tests\Infrastructure\Common
 */
abstract class BaseInfrastructureTestWithServices extends TestCase
{
    /**
     * @var TestShopConfiguration
     */
    public $shopConfig;
    /**
     * @var TestShopLogger
     */
    public $shopLogger;
    /**
     * @var TestTimeProvider
     */
    public $timeProvider;
    /**
     * @var TestDefaultLogger
     */
    public $defaultLogger;
    /**
     * @var array
     */
    public $eventHistory;
    /**
     * @var \SeQura\Core\Infrastructure\Serializer\Serializer
     */
    public $serializer;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $me = $this;

        $this->timeProvider = new TestTimeProvider();
        $this->timeProvider->setCurrentLocalTime(new DateTime());
        $this->shopConfig = new TestShopConfiguration();
        $this->shopLogger = new TestShopLogger();
        $this->defaultLogger = new TestDefaultLogger();
        $this->serializer = new NativeSerializer();

        new TestServiceRegister(
            array(
                ConfigurationManager::CLASS_NAME => function () {
                    return new TestConfigurationManager();
                },
                Configuration::CLASS_NAME => function () use ($me) {
                    return $me->shopConfig;
                },
                TimeProvider::CLASS_NAME => function () use ($me) {
                    return $me->timeProvider;
                },
                DefaultLoggerAdapter::CLASS_NAME => function () use ($me) {
                    return $me->defaultLogger;
                },
                ShopLoggerAdapter::CLASS_NAME => function () use ($me) {
                    return $me->shopLogger;
                },
                EventBus::CLASS_NAME => function () {
                    return EventBus::getInstance();
                },
                Serializer::CLASS_NAME => function () use ($me) {
                    return $me->serializer;
                },
            )
        );
    }

    protected function tearDown(): void
    {
        Logger::resetInstance();
        LoggerConfiguration::resetInstance();
        MemoryStorage::reset();
        TestRepositoryRegistry::cleanUp();

        parent::tearDown();
    }
}
