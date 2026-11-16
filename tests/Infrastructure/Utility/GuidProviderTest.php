<?php

namespace SeQura\Core\Tests\Infrastructure\Utility;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\AsyncProcessStarterService;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use SeQura\Core\Infrastructure\TaskExecution\Process;
use SeQura\Core\Infrastructure\TaskExecution\TaskRunnerWakeupService;
use SeQura\Core\Infrastructure\Utility\GuidProvider;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FakeRunnable;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestRunnerStatusStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class GuidProviderTest extends TestCase
{
    private const GUID_PATTERN = '/^[0-9a-f]{64}$/';

    /** @var TestRunnerStatusStorage */
    private $runnerStatusStorage;

    protected function setUp(): void
    {
        $this->resetGuidProviderInstance();

        RepositoryRegistry::registerRepository(Process::CLASS_NAME, MemoryRepository::getClassName());
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $this->runnerStatusStorage = new TestRunnerStatusStorage();
        $runnerStatusStorage = $this->runnerStatusStorage;
        $guidProvider = GuidProvider::getInstance();
        $timeProvider = new TestTimeProvider();
        $shopLogger = new TestShopLogger();

        new TestServiceRegister(
            array(
                ConfigurationManager::CLASS_NAME => function () {
                    return new TestConfigurationManager();
                },
                AsyncProcessService::CLASS_NAME => function () {
                    return AsyncProcessStarterService::getInstance();
                },
                TaskRunnerStatusStorage::CLASS_NAME => function () use ($runnerStatusStorage) {
                    return $runnerStatusStorage;
                },
                TimeProvider::CLASS_NAME => function () use ($timeProvider) {
                    return $timeProvider;
                },
                GuidProvider::CLASS_NAME => function () use ($guidProvider) {
                    return $guidProvider;
                },
                DefaultLoggerAdapter::CLASS_NAME => function () {
                    return new TestDefaultLogger();
                },
                ShopLoggerAdapter::CLASS_NAME => function () use ($shopLogger) {
                    return $shopLogger;
                },
                Configuration::CLASS_NAME => function () {
                    return new TestShopConfiguration();
                },
                HttpClient::CLASS_NAME => function () {
                    return new TestHttpClient();
                },
                Serializer::CLASS_NAME => function () {
                    return new NativeSerializer();
                },
            )
        );

        Logger::resetInstance();
        AsyncProcessStarterService::resetInstance();
    }

    protected function tearDown(): void
    {
        MemoryStorage::reset();
        AsyncProcessStarterService::resetInstance();
        $this->resetGuidProviderInstance();
        parent::tearDown();
    }

    public function testGeneratedGuidIs64LowercaseHexCharacters()
    {
        $guid = GuidProvider::getInstance()->generateGuid();

        $this->assertSame(GuidProvider::class, get_class(GuidProvider::getInstance()));
        $this->assertRegExp(self::GUID_PATTERN, $guid);
    }

    public function testGeneratedGuidsAreDistinct()
    {
        $provider = GuidProvider::getInstance();

        $guids = array();
        for ($i = 0; $i < 1000; $i++) {
            $guids[] = $provider->generateGuid();
        }

        $this->assertCount(1000, array_unique($guids));
    }

    public function testWakeupStoresRunnerStatusWithRandomGuid()
    {
        (new TaskRunnerWakeupService())->wakeup();

        $history = $this->runnerStatusStorage->getMethodCallHistory('setStatus');
        $this->assertCount(1, $history);
        $this->assertRegExp(self::GUID_PATTERN, $history[0]['status']->getGuid());
    }

    public function testAsyncProcessStartSavesProcessFindableByRandomGuid()
    {
        AsyncProcessStarterService::getInstance()->start(new FakeRunnable());

        $processes = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(1, $processes);
        $guid = $processes[0]->getGuid();
        $this->assertRegExp(self::GUID_PATTERN, $guid);

        $filter = new QueryFilter();
        $filter->where('guid', '=', $guid);
        $found = RepositoryRegistry::getRepository(Process::CLASS_NAME)->selectOne($filter);
        $this->assertNotNull($found);
        $this->assertSame($guid, $found->getGuid());
    }

    private function resetGuidProviderInstance(): void
    {
        $property = new ReflectionProperty(GuidProvider::class, 'instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }
}
