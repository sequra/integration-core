<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Services;

use DateTime;
use SeQura\Core\BusinessLogic\BootstrapComponent;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\RecurringWorkLockInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Services\RecurringWorkRunner;
use SeQura\Core\BusinessLogic\TaskExecution\FileRecurringWorkLock;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\TickEvent;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class RecurringWorkRunnerBootstrapTest extends BaseTestCase
{
    /**
     * @var int
     */
    private $now;

    protected function setUp(): void
    {
        parent::setUp();

        EventBus::resetInstance();
        BootstrapComponent::init();

        $timeProvider = new TestTimeProvider();
        $timeProvider->setCurrentLocalTime(new DateTime());
        $this->now = $timeProvider->getCurrentLocalTime()->getTimestamp();
        TestServiceRegister::registerService(TimeProvider::class, static function () use ($timeProvider) {
            return $timeProvider;
        });
        TestServiceRegister::registerService(
            StoreIntegrationServiceInterface::class,
            static function () {
                return new MockIntegrationStoreIntegrationService();
            }
        );
        TestServiceRegister::registerService(QueueService::class, static function () {
            return new TestQueueService();
        });
        TestServiceRegister::registerService(TaskRunnerWakeup::class, static function () {
            return new TestTaskRunnerWakeupService();
        });
    }

    protected function tearDown(): void
    {
        EventBus::resetInstance();

        parent::tearDown();
    }

    public function testServicesAreRegistered(): void
    {
        self::assertInstanceOf(RecurringWorkRunner::class, ServiceRegister::getService(RecurringWorkRunner::class));
        self::assertInstanceOf(
            FileRecurringWorkLock::class,
            ServiceRegister::getService(RecurringWorkLockInterface::class)
        );
    }

    public function testTickStartsTheDueWorkThroughTheRunner(): void
    {
        $this->registerLock(true);
        $this->makeWorkDue();

        EventBus::getInstance()->fire(new TickEvent());

        self::assertCount(2, TestRepositoryRegistry::getQueueItemRepository()->select());
    }

    public function testTickStartsNothingWhenTheLockIsHeld(): void
    {
        $this->registerLock(false);
        $this->makeWorkDue();

        EventBus::getInstance()->fire(new TickEvent());

        self::assertEmpty(TestRepositoryRegistry::getQueueItemRepository()->select());
    }

    public function testRunnerStartsNothingWhenTheLockIsHeld(): void
    {
        $this->registerLock(false);
        $this->makeWorkDue();

        ServiceRegister::getService(RecurringWorkRunner::class)->runDue();

        self::assertEmpty(TestRepositoryRegistry::getQueueItemRepository()->select());
    }

    private function makeWorkDue(): void
    {
        $sendReports = ServiceRegister::getService(SendReportRepositoryInterface::class);
        StoreContext::doWithStore('store-7', function () use ($sendReports) {
            $sendReports->setSendReport(new SendReport($this->now - 3600));
        });
        ServiceRegister::getService(OrderCleanupService::class)->schedule($this->now - 3600);
    }

    private function registerLock(bool $acquirable): void
    {
        TestServiceRegister::registerService(
            RecurringWorkLockInterface::class,
            static function () use ($acquirable) {
                return new RecordingLock($acquirable);
            }
        );
    }
}
