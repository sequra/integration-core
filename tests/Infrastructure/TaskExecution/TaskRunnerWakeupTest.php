<?php

/** @noinspection PhpDuplicateArrayKeysInspection */

namespace SeQura\Core\Tests\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\AsyncProcessStarterService;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use SeQura\Core\Infrastructure\TaskExecution\Process;
use SeQura\Core\Infrastructure\TaskExecution\TaskRunnerStarter;
use SeQura\Core\Infrastructure\TaskExecution\TaskRunnerStatus;
use SeQura\Core\Infrastructure\TaskExecution\TaskRunnerWakeupService;
use SeQura\Core\Infrastructure\Utility\GuidProvider;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestRunnerStatusStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestGuidProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class TaskRunnerWakeupTest
 *
 * @package SeQura\Core\Tests\Infrastructure\TaskExecution
 */
class TaskRunnerWakeupTest extends TestCase
{
    /** @var AsyncProcessService */
    private $asyncProcessStarter;
    /** @var TestRunnerStatusStorage */
    private $runnerStatusStorage;
    /** @var TestTimeProvider */
    private $timeProvider;
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestGuidProvider */
    private $guidProvider;
    /** @var TaskRunnerWakeupService */
    private $runnerWakeup;
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger */
    private $logger;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        RepositoryRegistry::registerRepository(Process::CLASS_NAME, MemoryRepository::getClassName());

        $runnerStatusStorage = new TestRunnerStatusStorage();
        $timeProvider = new TestTimeProvider();
        $guidProvider = TestGuidProvider::getInstance();

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

        $this->asyncProcessStarter = AsyncProcessStarterService::getInstance();
        $this->runnerStatusStorage = $runnerStatusStorage;
        $this->timeProvider = $timeProvider;
        $this->guidProvider = $guidProvider;
        $this->runnerWakeup = new TaskRunnerWakeupService();
        $this->logger = $shopLogger;
    }

    protected function tearDown(): void
    {
        MemoryStorage::reset();
        AsyncProcessStarterService::resetInstance();
        parent::tearDown();
    }

    /**
     *
     * @throws \Exception
     */
    public function testWakeupWhenThereIsNoLiveRunner()
    {
        // Arrange
        $guid = 'test_runner_guid';
        $this->guidProvider->setGuid($guid);

        // Act
        $this->runnerWakeup->wakeup();

        // Assert
        /** @var Process[] $startCallHistory */
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(
            1,
            $startCallHistory,
            'Wakeup call when there is no live runner must start runner asynchronously.'
        );

        /** @var TaskRunnerStarter $runnerStarter */
        $runnerStarter = $startCallHistory[0]->getRunner();
        $this->assertInstanceOf(
            '\SeQura\Core\Infrastructure\TaskExecution\TaskRunnerStarter',
            $runnerStarter,
            'Wakeup call when there is no live runner must start runner asynchronously using TaskRunnerStarter as runner starter component.'
        );
        $this->assertSame($guid, $runnerStarter->getGuid(), 'Wakeup call must generate guid for new runner starter.');

        $setStatusCallHistory = $this->runnerStatusStorage->getMethodCallHistory('setStatus');
        $this->assertCount(
            1,
            $setStatusCallHistory,
            'Wakeup call when there is no live runner must set new status before starting runner again.'
        );

        /** @var TaskRunnerStatus $runnerStatus */
        $runnerStatus = $setStatusCallHistory[0]['status'];
        $this->assertEquals($guid, $runnerStatus->getGuid(), 'Wakeup call must generate guid for new runner status.');
        $this->assertSame(
            $this->timeProvider->getCurrentLocalTime()->getTimestamp(),
            $runnerStatus->getAliveSinceTimestamp(),
            'Wakeup call must active since timestamp to current timestamp for new runner instance.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     * @throws \Exception
     */
    public function testWakeupWhenRunnerIsAlreadyLive()
    {
        $currentTimestamp = $this->timeProvider->getCurrentLocalTime()->getTimestamp();
        $this->runnerStatusStorage->setStatus(new TaskRunnerStatus('test', $currentTimestamp));

        $this->runnerWakeup->wakeup();

        /** @var Process[] $startCallHistory */
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(
            0,
            $startCallHistory,
            'Wakeup call when there is already live runner must not start runner again.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     * @throws \Exception
     */
    public function testWakeupWhenRunnerIsExpired()
    {
        $currentTimestamp = $this->timeProvider->getCurrentLocalTime()->getTimestamp();
        $expiredAliveSinceTimestamp = $currentTimestamp - TaskRunnerStatus::MAX_ALIVE_TIME - 1;
        $this->runnerStatusStorage->setStatus(new TaskRunnerStatus('test', $expiredAliveSinceTimestamp));

        $this->runnerWakeup->wakeup();

        /** @var Process[] $startCallHistory */
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(
            1,
            $startCallHistory,
            'Wakeup call when there is expired runner must start runner asynchronously.'
        );

        /** @var TaskRunnerStarter $runnerStarter */
        $runnerStarter = $startCallHistory[0]->getRunner();
        $this->assertInstanceOf(
            '\SeQura\Core\Infrastructure\TaskExecution\TaskRunnerStarter',
            $runnerStarter,
            'Wakeup call when there is expired runner must start runner asynchronously using TaskRunnerStarter as runner starter component'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testWakeupWhenRunnerStatusServiceFailToSaveNewStatus()
    {
        // Arrange
        $this->runnerStatusStorage->setExceptionResponse(
            'setStatus',
            new TaskRunnerStatusChangeException('Disallow status change')
        );

        // Act
        $this->runnerWakeup->wakeup();

        // Assert
        /** @var Process[] $startCallHistory */
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(
            0,
            $startCallHistory,
            'Wakeup call when new status setting fails must not start new runner instance.'
        );
        $this->assertStringContainsString(
            'Runner status storage failed to set new active state.',
            $this->logger->data->getMessage()
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testWakeupWhenRunnerStatusServiceIsUnavailable()
    {
        // Arrange
        $this->runnerStatusStorage->setExceptionResponse(
            'getStatus',
            new TaskRunnerStatusStorageUnavailableException('Simulation for unavailable storage exception.')
        );

        // Act
        $this->runnerWakeup->wakeup();

        // Assert
        /** @var Process[] $startCallHistory */
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(
            0,
            $startCallHistory,
            'Wakeup call when tasks status storage is unavailable must not start new runner instance.'
        );
        $this->assertStringContainsString('Runner status storage unavailable.', $this->logger->data->getMessage());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testWakeupInCaseOfUnexpectedException()
    {
        // Arrange
        $this->runnerStatusStorage->setExceptionResponse(
            'getStatus',
            new Exception('Simulation for unexpected exception.')
        );

        // Act
        $this->runnerWakeup->wakeup();

        // Assert
        /** @var Process[] $startCallHistory */
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(
            0,
            $startCallHistory,
            'Wakeup call when exception occurs must not start new runner instance.'
        );
        $this->assertStringContainsString('Unexpected error occurred.', $this->logger->data->getMessage());
    }
}
