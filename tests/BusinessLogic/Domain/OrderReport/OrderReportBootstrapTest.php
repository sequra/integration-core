<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderReport;

use DateTime;
use SeQura\Core\BusinessLogic\BootstrapComponent;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportScheduler;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportService;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\TaskAdapter;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class OrderReportBootstrapTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        EventBus::resetInstance();
        BootstrapComponent::init();

        $timeProvider = new TestTimeProvider();
        $timeProvider->setCurrentLocalTime(new DateTime());
        TestServiceRegister::registerService(TimeProvider::class, static function () use ($timeProvider) {
            return $timeProvider;
        });
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

    public function testSchedulerIsRegisteredAndTheOldClassesAreGone(): void
    {
        self::assertInstanceOf(OrderReportScheduler::class, ServiceRegister::getService(OrderReportScheduler::class));
        self::assertFalse(class_exists('SeQura\Core\BusinessLogic\Domain\OrderReport\Tasks\OrderReportTask'));
        self::assertFalse(class_exists('SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners\TickEventListener'));
    }

    public function testDueReportIsQueuedOnTheQueueOfTheStore(): void
    {
        $this->setSendReportTime('store-7', (new DateTime())->getTimestamp() - 3600);

        ServiceRegister::getService(OrderReportScheduler::class)->scheduleDue();

        $items = TestRepositoryRegistry::getQueueItemRepository()->select();
        self::assertCount(1, $items);
        self::assertSame('order-reports-store-7', $items[0]->getQueueName());
        self::assertSame(Priority::NORMAL, $items[0]->getPriority());
        self::assertSame('store-7', $items[0]->getContext());
        self::assertSame('OrderReporter', $items[0]->getTaskType());
        self::assertSame('OrderReporter', $items[0]->getTask()->getType());
    }

    public function testNextPageIsQueuedOnTheSameQueueAndSurvivesStorage(): void
    {
        $shopOrders = $this->createMock(ShopOrderService::class);
        $shopOrders->method('getReportOrderIds')->with(1, 5000)->willReturn(['r1']);
        TestServiceRegister::registerService(ShopOrderService::class, static function () use ($shopOrders) {
            return $shopOrders;
        });
        $transactionLog = $this->createMock(TransactionLogService::class);
        TestServiceRegister::registerService(TransactionLogService::class, static function () use ($transactionLog) {
            return $transactionLog;
        });
        $reportService = $this->createMock(OrderReportService::class);
        TestServiceRegister::registerService(OrderReportService::class, static function () use ($reportService) {
            return $reportService;
        });

        StoreContext::doWithStore('store-7', static function () {
            ServiceRegister::getService(CountryConfigurationRepositoryInterface::class)
                ->setCountryConfiguration([new CountryConfiguration('ES', 'testMerchantId')]);
            iterator_to_array((new OrderReporter())->execute(), false);
        });

        $items = TestRepositoryRegistry::getQueueItemRepository()->select();
        self::assertCount(1, $items);
        self::assertSame('order-reports-store-7', $items[0]->getQueueName());
        self::assertSame('store-7', $items[0]->getContext());
        self::assertSame('OrderReporter', $items[0]->getTaskType());
        $task = $items[0]->getTask();
        self::assertInstanceOf(TaskAdapter::class, $task);
        self::assertSame(['page' => 2], $task->toArray()['task_data']);
    }

    private function setSendReportTime(string $storeId, int $timestamp): void
    {
        StoreContext::doWithStore($storeId, static function () use ($timestamp) {
            ServiceRegister::getService(SendReportRepositoryInterface::class)
                ->setSendReport(new SendReport($timestamp));
        });
    }
}
