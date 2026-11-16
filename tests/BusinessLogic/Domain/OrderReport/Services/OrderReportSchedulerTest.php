<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderReport\Services;

use DateTime;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportScheduler;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\LogData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\RecordingTaskExecutor;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class OrderReportSchedulerTest extends BaseTestCase
{
    /**
     * @var SendReportRepositoryInterface
     */
    private $sendReportRepository;

    /**
     * @var TestTimeProvider
     */
    private $timeProvider;

    /**
     * @var TestShopLogger
     */
    private $logger;

    /**
     * @var int
     */
    private $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, function () {
            return $this->logger;
        });
        $this->timeProvider = new TestTimeProvider();
        $this->timeProvider->setCurrentLocalTime(new DateTime());
        TestServiceRegister::registerService(TimeProvider::class, function () {
            return $this->timeProvider;
        });
        $this->now = $this->timeProvider->getCurrentLocalTime()->getTimestamp();
        $this->sendReportRepository = TestServiceRegister::getService(SendReportRepositoryInterface::class);
        $this->setSendReportTime('store-7', $this->now - 3600);
        $this->setSendReportTime('store-9', $this->now - 3600);
        $this->setSendReportTime('store-3', $this->now + 3600);
    }

    public function testHandsOneReportJobToTheExecutorForEveryDueStore(): void
    {
        $executor = new RecordingTaskExecutor();

        $this->scheduler($executor)->scheduleDue();

        self::assertSame(['store-7', 'store-9'], $executor->stores);
        foreach ($executor->enqueued as $task) {
            self::assertInstanceOf(OrderReporter::class, $task);
            self::assertSame(['page' => 1], $task->toArray());
        }
        self::assertSame([], $executor->delayed);
    }

    public function testMovesTheNextReportOfDueStoresADayAhead(): void
    {
        $expected = (clone $this->timeProvider->getCurrentLocalTime())->modify('+1 day')->getTimestamp();

        $this->scheduler(new RecordingTaskExecutor())->scheduleDue();

        self::assertSame($expected, $this->getSendReportTime('store-7'));
        self::assertSame($expected, $this->getSendReportTime('store-9'));
        self::assertSame($this->now + 3600, $this->getSendReportTime('store-3'));
    }

    public function testFailureForOneStoreIsLoggedAndDoesNotStopTheOthers(): void
    {
        $executor = new RecordingTaskExecutor(new RuntimeException('queue down'), 'store-7');
        $expected = (clone $this->timeProvider->getCurrentLocalTime())->modify('+1 day')->getTimestamp();

        $this->scheduler($executor)->scheduleDue();

        self::assertSame(['store-9'], $executor->stores);
        $errors = array_values(array_filter($this->logger->loggedMessages, static function (LogData $data) {
            return $data->getLogLevel() === Logger::ERROR;
        }));
        self::assertCount(1, $errors);
        self::assertStringContainsString('store-7', $errors[0]->getMessage());
        self::assertSame($expected, $this->getSendReportTime('store-7'));
    }

    public function testErrorForOneStoreDoesNotStopTheOthers(): void
    {
        $executor = new RecordingTaskExecutor(new \TypeError('bad'), 'store-7');

        $this->scheduler($executor)->scheduleDue();

        self::assertSame(['store-9'], $executor->stores);
    }

    private function scheduler(TaskExecutorInterface $executor): OrderReportScheduler
    {
        return new OrderReportScheduler(TestServiceRegister::getService(StatisticalDataService::class), $executor);
    }

    private function setSendReportTime(string $storeId, int $timestamp): void
    {
        StoreContext::doWithStore($storeId, function () use ($timestamp) {
            $this->sendReportRepository->setSendReport(new SendReport($timestamp));
        });
    }

    private function getSendReportTime(string $storeId): int
    {
        return StoreContext::doWithStore($storeId, function () {
            return $this->sendReportRepository->getSendReport()->getSendReportTime();
        });
    }
}
