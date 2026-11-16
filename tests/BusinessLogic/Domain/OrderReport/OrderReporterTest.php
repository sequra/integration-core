<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderReport;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\ReportData;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportService;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class OrderReporterTest extends BaseTestCase
{
    /**
     * @var ShopOrderService&MockObject
     */
    private $shopOrderService;

    /**
     * @var OrderReportService&MockObject
     */
    private $reportService;

    /**
     * @var TaskExecutorInterface&MockObject
     */
    private $executor;

    /**
     * @var string[]
     */
    private $calls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopOrderService = $this->createMock(ShopOrderService::class);
        $this->reportService = $this->createMock(OrderReportService::class);
        $this->executor = $this->createMock(TaskExecutorInterface::class);
        TestServiceRegister::registerService(ShopOrderService::class, function () {
            return $this->shopOrderService;
        });
        TestServiceRegister::registerService(OrderReportService::class, function () {
            return $this->reportService;
        });
        TestServiceRegister::registerService(TaskExecutorInterface::class, function () {
            return $this->executor;
        });
        $this->configureStore(true, [new CountryConfiguration('ES', 'testMerchantId')]);
    }

    public function testSendsThePageThenSchedulesTheNextOne(): void
    {
        $this->shopOrderService->method('getReportOrderIds')->with(1, 5000)->willReturn(['r1', 'r2']);
        $this->shopOrderService->method('getStatisticsOrderIds')->with(1, 5000)->willReturn(['s1']);
        $this->reportService->expects(self::once())->method('sendReport')
            ->with(self::callback(static function (ReportData $data) {
                return $data->getMerchantId() === 'testMerchantId'
                    && $data->getReportOrderIds() === ['r1', 'r2']
                    && $data->getStatisticsOrderIds() === ['s1'];
            }))->willReturnCallback(function () {
                $this->calls[] = 'send';

                return true;
            });
        $this->executor->expects(self::once())->method('enqueue')
            ->with(self::callback(static function (OrderReporter $next) {
                return $next->toArray() === ['page' => 2];
            }))->willReturnCallback(function () {
                $this->calls[] = 'enqueue';
            });
        $this->executor->expects(self::never())->method('scheduleDelayed');

        $yielded = $this->runJob(new OrderReporter());

        self::assertSame([100], $yielded);
        self::assertSame(['send', 'enqueue'], $this->calls);
    }

    public function testSendsAPageThatOnlyHasStatistics(): void
    {
        $this->shopOrderService->method('getReportOrderIds')->with(3, 5000)->willReturn([]);
        $this->shopOrderService->method('getStatisticsOrderIds')->with(3, 5000)->willReturn(['s1']);
        $this->reportService->expects(self::once())->method('sendReport')
            ->with(self::callback(static function (ReportData $data) {
                return $data->getReportOrderIds() === [] && $data->getStatisticsOrderIds() === ['s1'];
            }));
        $this->executor->expects(self::once())->method('enqueue')
            ->with(self::callback(static function (OrderReporter $next) {
                return $next->toArray() === ['page' => 4];
            }));

        $this->runJob(new OrderReporter(3));
    }

    public function testEndsTheChainAtThePageWithoutOrders(): void
    {
        $this->shopOrderService->method('getReportOrderIds')->willReturn([]);
        $this->shopOrderService->method('getStatisticsOrderIds')->willReturn([]);
        $this->reportService->expects(self::never())->method('sendReport');
        $this->executor->expects(self::never())->method('enqueue');
        $this->executor->expects(self::never())->method('scheduleDelayed');

        self::assertSame([100], $this->runJob(new OrderReporter(2)));
    }

    public function testSkipsStatisticsWhenTheStoreDoesNotSendThem(): void
    {
        $this->configureStore(false, [new CountryConfiguration('ES', 'testMerchantId')]);
        $this->shopOrderService->method('getReportOrderIds')->willReturn(['r1']);
        $this->shopOrderService->expects(self::never())->method('getStatisticsOrderIds');
        $this->reportService->expects(self::once())->method('sendReport')
            ->with(self::callback(static function (ReportData $data) {
                return $data->getStatisticsOrderIds() === null;
            }));

        $this->runJob(new OrderReporter());
    }

    public function testSkipsStatisticsWhenThereIsNoStatisticalData(): void
    {
        StoreContext::doWithStore('store-7', static function () {
            TestServiceRegister::getService(StatisticalDataRepositoryInterface::class)->deleteStatisticalData();
        });
        $this->shopOrderService->method('getReportOrderIds')->willReturn(['r1']);
        $this->shopOrderService->expects(self::never())->method('getStatisticsOrderIds');
        $this->reportService->expects(self::once())->method('sendReport')
            ->with(self::callback(static function (ReportData $data) {
                return $data->getStatisticsOrderIds() === null;
            }));

        $this->runJob(new OrderReporter());
    }

    public function testSendsNothingWhenTheStoreHasNoMerchantId(): void
    {
        $this->configureStore(true, []);
        $this->shopOrderService->method('getReportOrderIds')->willReturn(['r1']);
        $this->reportService->expects(self::never())->method('sendReport');
        $this->executor->expects(self::never())->method('enqueue');

        self::assertSame([100], $this->runJob(new OrderReporter()));
    }

    public function testUsesTheFirstCountryConfiguration(): void
    {
        $this->configureStore(true, [
            new CountryConfiguration('ES', 'merchantES'),
            new CountryConfiguration('FR', 'merchantFR'),
        ]);
        $this->shopOrderService->method('getReportOrderIds')->willReturn(['r1']);
        $this->reportService->expects(self::once())->method('sendReport')
            ->with(self::callback(static function (ReportData $data) {
                return $data->getMerchantId() === 'merchantES';
            }));

        $this->runJob(new OrderReporter());
    }

    public function testFailingPageDoesNotScheduleTheNext(): void
    {
        $failure = new RuntimeException('down');
        $this->shopOrderService->method('getReportOrderIds')->willReturn(['r1']);
        $this->reportService->method('sendReport')->willThrowException($failure);
        $this->executor->expects(self::never())->method('enqueue');

        try {
            $this->runJob(new OrderReporter());
            self::fail('The failure must reach the caller.');
        } catch (RuntimeException $exception) {
            self::assertSame($failure, $exception);
        }
    }

    public function testSchedulingFailureReachesTheCaller(): void
    {
        $failure = new RuntimeException('queue down');
        $this->shopOrderService->method('getReportOrderIds')->willReturn(['r1']);
        $this->reportService->expects(self::once())->method('sendReport');
        $this->executor->method('enqueue')->willThrowException($failure);

        try {
            $this->runJob(new OrderReporter());
            self::fail('The failure must reach the caller.');
        } catch (RuntimeException $exception) {
            self::assertSame($failure, $exception);
        }
    }

    public function testSurvivesBeingStoredAndRebuilt(): void
    {
        self::assertSame(['page' => 7], (new OrderReporter(7))->toArray());
        self::assertSame(['page' => 1], (new OrderReporter())->toArray());
        self::assertSame(['page' => 7], OrderReporter::fromArray(['page' => 7])->toArray());
    }

    /**
     * @param CountryConfiguration[] $countryConfigurations
     */
    private function configureStore(bool $sendStatistics, array $countryConfigurations): void
    {
        StoreContext::doWithStore('store-7', static function () use ($sendStatistics, $countryConfigurations) {
            TestServiceRegister::getService(StatisticalDataRepositoryInterface::class)
                ->setStatisticalData(new StatisticalData($sendStatistics));
            TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class)
                ->setCountryConfiguration($countryConfigurations);
        });
    }

    /**
     * @return array<int, mixed>
     *
     * @throws Exception
     */
    private function runJob(OrderReporter $job): array
    {
        return StoreContext::doWithStore('store-7', static function () use ($job) {
            return iterator_to_array($job->execute(), false);
        });
    }
}
