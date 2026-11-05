<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Services;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestStates;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
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
    private $nextReference = 1;

    /**
     * @var mixed[]|null
     */
    private static $orderData;

    protected function setUp(): void
    {
        parent::setUp();

        $timeProvider = new TestTimeProvider();
        $timeProvider->setCurrentLocalTime(new DateTime(self::NOW));
        TestServiceRegister::registerService(TimeProvider::class, static function () use ($timeProvider) {
            return $timeProvider;
        });

        $this->orderRepository = TestRepositoryRegistry::getRepository(SeQuraOrder::getClassName());
        $this->service = TestServiceRegister::getService(OrderCleanupService::class);
    }

    /**
     * @throws Exception
     */
    public function testDeletesASolicitationSolicitedMoreThanSevenDaysAgo(): void
    {
        $this->store($this->solicitation('2026-10-20 03:00:00'));

        $deleted = $this->service->cleanup();

        self::assertSame(1, $deleted);
        self::assertEmpty($this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testDeletesASolicitationSolicitedExactlySevenDaysAgo(): void
    {
        $this->store($this->solicitation('2026-10-23 03:00:00'));

        $this->service->cleanup();

        self::assertEmpty($this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsASolicitationSolicitedSixDaysAgo(): void
    {
        $this->store($this->solicitation('2026-10-24 03:00:00'));

        $this->service->cleanup();

        self::assertCount(1, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsAFreshSolicitationOfACartLastUpdatedLongAgo(): void
    {
        $this->store($this->solicitation('2026-10-30 02:00:00', '2026-10-01 00:00:00'));

        $this->service->cleanup();

        self::assertCount(1, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsAFreshSolicitationWhoseCartHasNoUpdateTime(): void
    {
        $this->store($this->solicitation('2026-10-30 02:00:00', null));

        $this->service->cleanup();

        self::assertCount(1, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testFallsBackToTheCartUpdateTimeForAnOrderStoredWithoutASolicitationTime(): void
    {
        $this->store($this->solicitation(null, '2026-10-20 03:00:00'));
        $this->store($this->solicitation(null, '2026-10-20T03:00:00+00:00'));
        $this->store($this->solicitation(null, '2026-10-30T02:00:00+00:00'));

        $deleted = $this->service->cleanup();

        self::assertSame(2, $deleted);
        self::assertCount(1, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsAnOrderWithoutAnySolicitationOrCartUpdateTime(): void
    {
        $this->store($this->solicitation(null, null));
        $this->store($this->solicitation(null, ''));

        $this->service->cleanup();

        self::assertCount(2, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsAnOrderWithoutASolicitationTimeAndAnUnreadableCartUpdateTime(): void
    {
        $this->store($this->solicitation(null, 'not a date'));

        $deleted = $this->service->cleanup();

        self::assertSame(0, $deleted);
        self::assertCount(1, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsAnOrderWithAShopOrderReferenceWhateverItsAge(): void
    {
        $this->store($this->solicitation('2020-01-01 00:00:00')->setOrderRef1('000000123'));

        $this->service->cleanup();

        self::assertCount(1, $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsAnOrderSeQuraSettledWithoutAShopOrderReference(): void
    {
        foreach (OrderRequestStates::SETTLED_STATES as $state) {
            $this->store($this->solicitation('2026-10-01 00:00:00')->setState($state));
        }

        $deleted = $this->service->cleanup();

        self::assertSame(0, $deleted);
        self::assertCount(\count(OrderRequestStates::SETTLED_STATES), $this->orderRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testCleansUpMoreThanOneBatch(): void
    {
        for ($i = 0; $i < 550; $i++) {
            $this->store($this->solicitation('2026-10-20 03:00:00'));
        }
        for ($i = 0; $i < 50; $i++) {
            $this->store($this->solicitation('2026-10-30 02:00:00'));
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
            $this->store($this->solicitation('2026-10-30 02:00:00'));
        }
        $this->store($this->solicitation('2026-10-20 03:00:00'));

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
            $this->store($this->solicitation('2026-10-20 03:00:00'));
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
     * @param SeQuraOrder $order
     *
     * @return void
     */
    private function store(SeQuraOrder $order): void
    {
        $this->orderRepository->save($order);
    }

    /**
     * @param string|null $solicitedAt
     * @param string|null $cartUpdatedAt
     *
     * @return SeQuraOrder
     *
     * @throws Exception
     */
    private function solicitation(?string $solicitedAt, ?string $cartUpdatedAt = '2026-10-01 00:00:00'): SeQuraOrder
    {
        if (self::$orderData === null) {
            $order = file_get_contents(__DIR__ . '/../../../Common/MockObjects/SeQuraOrder.json');
            self::$orderData = json_decode($order, true)['order'];
        }
        $seQuraOrder = SeQuraOrder::fromArray(self::$orderData);
        $seQuraOrder->setReference('sequra-ref-' . $this->nextReference++);
        $seQuraOrder->setOrderRef1('');
        $seQuraOrder->setState('');
        $seQuraOrder->setUnshippedCart(new Cart('EUR', false, [], null, null, $cartUpdatedAt));
        if ($solicitedAt !== null) {
            $seQuraOrder->setSolicitedAt((new DateTime($solicitedAt))->getTimestamp());
        }

        return $seQuraOrder;
    }
}
