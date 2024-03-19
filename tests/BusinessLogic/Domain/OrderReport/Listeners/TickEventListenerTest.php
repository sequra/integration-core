<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderReport\Listeners;

use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners\TickEventListener;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class TickEventListenerTest
 *
 * @package BusinessLogic\Domain\OrderReport\Listeners
 */
class TickEventListenerTest extends BaseTestCase
{
    /**
     * @var SendReportRepositoryInterface
     */
    public $sendReportRepository;

    /**
     * @var TestTimeProvider
     */
    public $timeProvider;
    /**
     * @var QueueItemRepository
     */
    public $queueRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sendReportRepository = TestServiceRegister::getService(SendReportRepositoryInterface::class);
        $this->queueRepository = TestRepositoryRegistry::getQueueItemRepository();
        $timeProvider = new TestTimeProvider();
        $this->timeProvider = $timeProvider;
        TestServiceRegister::registerService(
            TimeProvider::class,
            function () use ($timeProvider) {
                return $timeProvider;
            }
        );
        TestServiceRegister::registerService(
            StoreServiceInterface::class,
            function () {
                return new MockStoreService();
            }
        );
    }

    public function testTaskNotEnqueued()
    {
        // arrange
        $this->setSendReportDataNotToEnqueueTask();

        // act
        TickEventListener::handle();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertEmpty($queueItems);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testTaskEnqueuedForEveryStore()
    {
        // arrange
        $this->timeProvider->setCurrentLocalTime((new \DateTime())->setTime(4, 0));
        $this->setSendReportDataToEnqueueTask();

        // act
        TickEventListener::handle();

        // assert
        $queueItems = $this->queueRepository->select();
        self::assertCount(3, $queueItems);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    private function setSendReportDataNotToEnqueueTask(): void
    {
        $sendReport1 = new SendReport(PHP_INT_MAX);
        $sendReport2 = new SendReport(PHP_INT_MAX - 100);
        $sendReport3 = new SendReport(PHP_INT_MAX - 200);

        StoreContext::doWithStore('store1', function () use ($sendReport1) {
            $this->sendReportRepository->setSendReport($sendReport1);
        });
        StoreContext::doWithStore('store2', function () use ($sendReport2) {
            $this->sendReportRepository->setSendReport($sendReport2);
        });
        StoreContext::doWithStore('store3', function () use ($sendReport3) {
            $this->sendReportRepository->setSendReport($sendReport3);
        });
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    private function setSendReportDataToEnqueueTask(): void
    {
        $sendReport1 = new SendReport(1);
        $sendReport2 = new SendReport(2);
        $sendReport3 = new SendReport(3);

        StoreContext::doWithStore('store1', function () use ($sendReport1) {
            $this->sendReportRepository->setSendReport($sendReport1);
        });
        StoreContext::doWithStore('store2', function () use ($sendReport2) {
            $this->sendReportRepository->setSendReport($sendReport2);
        });
        StoreContext::doWithStore('store3', function () use ($sendReport3) {
            $this->sendReportRepository->setSendReport($sendReport3);
        });
    }
}
