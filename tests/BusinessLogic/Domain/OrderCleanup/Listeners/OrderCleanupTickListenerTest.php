<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Listeners;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Listeners\OrderCleanupTickListener;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderCleanupTickListenerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Listeners
 */
class OrderCleanupTickListenerTest extends BaseTestCase
{
    /**
     * @var QueueItemRepository
     */
    private $queueRepository;

    /**
     * @var OrderCleanupService
     */
    private $cleanupService;

    /**
     * @var int
     */
    private $now;

    protected function setUp(): void
    {
        parent::setUp();

        $timeProvider = new TestTimeProvider();
        $timeProvider->setCurrentLocalTime(new DateTime('2026-10-30 03:00:00'));
        TestServiceRegister::registerService(TimeProvider::class, static function () use ($timeProvider) {
            return $timeProvider;
        });

        $this->now = $timeProvider->getCurrentLocalTime()->getTimestamp();
        $this->queueRepository = TestRepositoryRegistry::getQueueItemRepository();
        $this->cleanupService = TestServiceRegister::getService(OrderCleanupService::class);
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesNothingWhenTheCleanupIsNotScheduled(): void
    {
        OrderCleanupTickListener::handle();

        self::assertEmpty($this->queueRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesNothingBeforeTheNextRun(): void
    {
        $this->cleanupService->schedule($this->now + 60);

        OrderCleanupTickListener::handle();

        self::assertEmpty($this->queueRepository->select());
        self::assertSame($this->now + 60, $this->cleanupService->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesTheCleanupAndMovesTheNextRunADayAheadWhenDue(): void
    {
        $this->cleanupService->schedule($this->now - 60);

        OrderCleanupTickListener::handle();

        $queueItems = $this->queueRepository->select();
        self::assertCount(1, $queueItems);
        self::assertSame(OrderCleanupTask::getClassName(), $queueItems[0]->getTaskType());
        self::assertSame(OrderCleanupTickListener::QUEUE_NAME, $queueItems[0]->getQueueName());
        self::assertSame(Priority::LOW, $queueItems[0]->getPriority());
        self::assertSame($this->now - 60 + 86400, $this->cleanupService->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesNothingWhileAnEarlierCleanupIsStillQueued(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $this->enqueueCleanup();

        OrderCleanupTickListener::handle();

        self::assertCount(1, $this->queueRepository->select());
        self::assertSame($this->now - 60, $this->cleanupService->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesAgainOnceTheEarlierCleanupHasFinished(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $earlier = $this->enqueueCleanup();
        $earlier->setStatus(QueueItem::COMPLETED);
        $this->queueRepository->update($earlier);

        OrderCleanupTickListener::handle();

        self::assertCount(2, $this->queueRepository->select());
    }

    /**
     * @return QueueItem
     *
     * @throws Exception
     */
    private function enqueueCleanup(): QueueItem
    {
        /**
         * @var QueueService $queueService
         */
        $queueService = TestServiceRegister::getService(QueueService::class);

        return $queueService->enqueue(OrderCleanupTickListener::QUEUE_NAME, new OrderCleanupTask());
    }
}
