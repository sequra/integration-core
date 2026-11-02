<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Services;

use DateTime;
use DateTimeZone;
use Exception;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MemoryRepositoryWithConditionalDelete;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderCleanupServiceTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Services
 */
class OrderCleanupServiceTest extends BaseTestCase
{
    private const NOW = '2026-10-30 03:00:00';

    /**
     * @var TestTimeProvider
     */
    private $timeProvider;

    /**
     * @var RepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderCleanupService
     */
    private $service;

    /**
     * @var int
     */
    private $nextId = 1;

    /**
     * @var mixed[]|null
     */
    private static $orderData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timeProvider = new TestTimeProvider();
        $this->timeProvider->setCurrentLocalTime(new DateTime(self::NOW));
        TestServiceRegister::registerService(TimeProvider::class, function () {
            return $this->timeProvider;
        });

        $this->orderRepository = TestRepositoryRegistry::getRepository(SeQuraOrder::getClassName());
        $this->service = $this->createService($this->orderRepository);
    }

    /**
     * @throws Exception
     */
    public function testDeletesASolicitationLastUpdatedMoreThanSevenDaysAgo(): void
    {
        $this->storeSolicitation('2026-10-20 03:00:00');

        $this->service->cleanup();

        self::assertEmpty($this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testDeletesASolicitationLastUpdatedExactlySevenDaysAgo(): void
    {
        $this->storeSolicitation('2026-10-23 03:00:00');

        $this->service->cleanup();

        self::assertEmpty($this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsASolicitationUpdatedSixDaysAgo(): void
    {
        $this->storeSolicitation('2026-10-24 03:00:00');

        $this->service->cleanup();

        self::assertCount(1, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsASolicitationUpdatedAnHourAgo(): void
    {
        $this->storeSolicitation('2026-10-30 02:00:00');

        $this->service->cleanup();

        self::assertCount(1, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testDeletesASolicitationWithoutACartUpdateTime(): void
    {
        $this->storeSolicitation(null);

        $this->service->cleanup();

        self::assertEmpty($this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsAnOrderWithAShopOrderReferenceWhateverItsAge(): void
    {
        $this->storeSolicitation('2020-01-01 00:00:00', '000000123');
        $this->storeSolicitation(null, '000000124');

        $this->service->cleanup();

        self::assertCount(2, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testReadsBothTheShopAndTheIsoTimestampFormats(): void
    {
        $this->storeSolicitation('2026-10-20 03:00:00');
        $this->storeSolicitation('2026-10-20T03:00:00+00:00');
        $this->storeSolicitation('2026-10-30T02:00:00+00:00');

        $deleted = $this->service->cleanup();

        self::assertSame(2, $deleted);
        self::assertCount(1, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsAndLogsASolicitationWithAnUnreadableCartUpdateTime(): void
    {
        $logger = $this->useTestLogger();
        $this->storeSolicitation('not a date');

        $deleted = $this->service->cleanup();

        self::assertSame(0, $deleted);
        self::assertCount(1, $this->orderRepository->select());
        self::assertTrue($logger->isMessageContainedInLog('unreadable cart update time'));
    }

    /**
     * @throws Exception
     */
    public function testLogsTheNumberOfDeletedSolicitations(): void
    {
        $logger = $this->useTestLogger();
        $this->storeSolicitation('2026-10-20 03:00:00');

        $this->service->cleanup();

        self::assertTrue($logger->isMessageContainedInLog('Order cleanup deleted 1 stale solicitations.'));
    }

    /**
     * @throws Exception
     */
    public function testCleansUpMoreThanOneBatch(): void
    {
        for ($i = 0; $i < 550; $i++) {
            $this->storeSolicitation('2026-10-20 03:00:00');
        }
        for ($i = 0; $i < 50; $i++) {
            $this->storeSolicitation('2026-10-30 02:00:00');
        }

        $deleted = $this->service->cleanup();

        self::assertSame(550, $deleted);
        self::assertCount(50, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testReachesStaleSolicitationsBehindAFullBatchOfFreshOnes(): void
    {
        for ($i = 0; $i < 500; $i++) {
            $this->storeSolicitation('2026-10-30 02:00:00');
        }
        $this->storeSolicitation('2026-10-20 03:00:00');

        $deleted = $this->service->cleanup();

        self::assertSame(1, $deleted);
        self::assertCount(500, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testCallsBackAfterEachBatch(): void
    {
        for ($i = 0; $i < 501; $i++) {
            $this->storeSolicitation('2026-10-20 03:00:00');
        }
        $batches = 0;

        $this->service->cleanup(function () use (&$batches) {
            $batches++;
        });

        self::assertSame(2, $batches);
    }

    /**
     * @throws Exception
     */
    public function testFinishesWhenTheRepositoryDeletesNothing(): void
    {
        $repository = new class extends MemoryRepository {
            public function delete(Entity $entity)
            {
                return false;
            }
        };
        $repository->setEntityClass(SeQuraOrder::getClassName());
        for ($i = 0; $i < 501; $i++) {
            $repository->save($this->solicitation('2026-10-20 03:00:00'));
        }

        $this->createService($repository)->cleanup();

        self::assertCount(501, $repository->select());
    }

    /**
     * @throws Exception
     */
    public function testDeletesEachBatchWithOneConditionalDelete(): void
    {
        $repository = $this->createMock(MemoryRepositoryWithConditionalDelete::class);
        $repository->method('select')->willReturnOnConsecutiveCalls([
            $this->solicitation('2026-10-20 03:00:00', '', 11),
            $this->solicitation('2026-10-30 02:00:00', '', 12),
            $this->solicitation(null, '', 13),
        ], []);
        $repository->expects(self::never())->method('delete');
        $repository->expects(self::once())
            ->method('deleteWhere')
            ->with(
                (new QueryFilter())
                    ->where('id', Operators::IN, [11, 13])
                    ->where('orderRef1', Operators::EQUALS, '')
            );

        $deleted = $this->createService($repository)->cleanup();

        self::assertSame(2, $deleted);
    }

    /**
     * @throws Exception
     */
    public function testDeletesOneByOneWhenTheRepositoryHasNoConditionalDelete(): void
    {
        $stale = $this->solicitation('2026-10-20 03:00:00', '', 21);
        $repository = $this->createMock(MemoryRepository::class);
        $repository->method('select')->willReturnOnConsecutiveCalls(
            [$stale, $this->solicitation('2026-10-30 02:00:00', '', 22)],
            [$stale],
            []
        );
        $repository->expects(self::once())->method('delete')->with($stale);

        $deleted = $this->createService($repository)->cleanup();

        self::assertSame(1, $deleted);
    }

    /**
     * @throws Exception
     */
    public function testSchedulesTheFirstRun(): void
    {
        $this->service->schedule(1793322000);

        self::assertSame(1793322000, $this->service->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testKeepsAnExistingScheduleUnlessToldToOverwriteIt(): void
    {
        $this->service->schedule(1793322000);

        $this->service->schedule(1793408400);
        self::assertSame(1793322000, $this->service->getNextRun());

        $this->service->schedule(1793408400, true);
        self::assertSame(1793408400, $this->service->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testIsNotDueBeforeItIsScheduled(): void
    {
        self::assertNull($this->service->getNextRun());
        self::assertFalse($this->service->isDue());
    }

    /**
     * @throws Exception
     */
    public function testIsDueOnceTheScheduledTimeHasCome(): void
    {
        $now = (new DateTime(self::NOW))->getTimestamp();

        $this->service->schedule($now + 1);
        self::assertFalse($this->service->isDue());

        $this->service->schedule($now, true);
        self::assertTrue($this->service->isDue());
    }

    /**
     * @throws Exception
     */
    public function testAdvancesTheNextRunToTheScheduledTimeOfTheNextDay(): void
    {
        $this->service->schedule((new DateTime('2026-10-30 02:30:00'))->getTimestamp());

        $this->service->advanceNextRun();

        self::assertSame((new DateTime('2026-10-31 02:30:00'))->getTimestamp(), $this->service->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testKeepsTheScheduledTimeOfDayAfterMissedRuns(): void
    {
        $this->service->schedule((new DateTime('2026-10-26 02:30:00'))->getTimestamp());

        $this->service->advanceNextRun();

        self::assertSame((new DateTime('2026-10-31 02:30:00'))->getTimestamp(), $this->service->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testKeepsTheScheduledLocalTimeAcrossADaylightSavingChange(): void
    {
        $madridTime = new class extends TestTimeProvider {
            public function getCurrentLocalTime(): DateTime
            {
                return parent::getCurrentLocalTime()->setTimezone(new DateTimeZone('Europe/Madrid'));
            }
        };
        $madridTime->setCurrentLocalTime(new DateTime('2026-10-26 12:00:00', new DateTimeZone('Europe/Madrid')));
        $service = new OrderCleanupService(
            $this->orderRepository,
            $madridTime,
            TestServiceRegister::getService(ConfigurationManager::class)
        );
        $service->schedule((new DateTime('2026-10-24 03:00:00', new DateTimeZone('Europe/Madrid')))->getTimestamp());

        $service->advanceNextRun();

        self::assertSame(
            (new DateTime('2026-10-27 03:00:00', new DateTimeZone('Europe/Madrid')))->getTimestamp(),
            $service->getNextRun()
        );
    }

    /**
     * @throws Exception
     */
    public function testKeepsASolicitationThatBecameAShopOrderSinceItWasReadWithoutConditionalDelete(): void
    {
        $repository = new class extends MemoryRepository {
            public function select(?QueryFilter $filter = null)
            {
                $orders = parent::select($filter);
                foreach ($orders as $order) {
                    $converted = clone $order;
                    $converted->setOrderRef1('000000123');
                    $this->update($converted);
                }

                return $orders;
            }
        };
        $repository->setEntityClass(SeQuraOrder::getClassName());
        $repository->save($this->solicitation('2026-10-20 03:00:00'));

        $this->createService($repository)->cleanup();

        self::assertCount(1, TestRepositoryRegistry::getRepository(SeQuraOrder::getClassName())->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsASolicitationThatBecameAShopOrderSinceItWasRead(): void
    {
        $repository = new class extends MemoryRepositoryWithConditionalDelete {
            public function select(?QueryFilter $filter = null)
            {
                $orders = parent::select($filter);
                foreach ($orders as $order) {
                    $converted = clone $order;
                    $converted->setOrderRef1('000000123');
                    $this->update($converted);
                }

                return $orders;
            }
        };
        $repository->setEntityClass(SeQuraOrder::getClassName());
        $repository->save($this->solicitation('2026-10-20 03:00:00'));

        $this->createService($repository)->cleanup();

        self::assertCount(1, TestRepositoryRegistry::getRepository(SeQuraOrder::getClassName())->select());
    }

    /**
     * @return TestShopLogger
     */
    private function useTestLogger(): TestShopLogger
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });

        return $logger;
    }

    /**
     * @param RepositoryInterface $repository
     *
     * @return OrderCleanupService
     */
    private function createService(RepositoryInterface $repository): OrderCleanupService
    {
        return new OrderCleanupService(
            $repository,
            $this->timeProvider,
            TestServiceRegister::getService(ConfigurationManager::class)
        );
    }

    /**
     * @param string|null $cartUpdatedAt
     * @param string $orderRef1
     *
     * @return void
     */
    private function storeSolicitation(?string $cartUpdatedAt, string $orderRef1 = ''): void
    {
        $this->orderRepository->save($this->solicitation($cartUpdatedAt, $orderRef1));
    }

    /**
     * @param string|null $cartUpdatedAt
     * @param string $orderRef1
     * @param int|null $id
     *
     * @return SeQuraOrder
     */
    private function solicitation(?string $cartUpdatedAt, string $orderRef1 = '', ?int $id = null): SeQuraOrder
    {
        if (self::$orderData === null) {
            $order = file_get_contents(__DIR__ . '/../../../Common/MockObjects/SeQuraOrder.json');
            self::$orderData = json_decode($order, true)['order'];
        }
        $seQuraOrder = SeQuraOrder::fromArray(self::$orderData);
        $seQuraOrder->setReference('sequra-ref-' . $this->nextId++);
        $seQuraOrder->setOrderRef1($orderRef1);
        $seQuraOrder->setUnshippedCart(new Cart('EUR', false, [], null, null, $cartUpdatedAt));
        if ($id !== null) {
            $seQuraOrder->setId($id);
        }

        return $seQuraOrder;
    }
}
