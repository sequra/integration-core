<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderReport;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Tasks\OrderReportTask;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseSerializationTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockOrderReporterTask;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockQueueService;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderReportTaskTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\OrderReport
 */
class OrderReporterTest extends BaseSerializationTestCase
{
    public $task;
    public $shopOrderService;
    public $queueService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = new MockOrderReporterTask();
        $mockShopOrderService = new MockShopOrderService();
        $this->shopOrderService = $mockShopOrderService;
        $mockQueueService = new MockQueueService();
        $this->queueService = $mockQueueService;

        TestServiceRegister::registerService(
            ShopOrderService::class,
            static function () use ($mockShopOrderService) {
                return $mockShopOrderService;
            }
        );

        TestServiceRegister::registerService(
            QueueService::class,
            static function () use ($mockQueueService) {
                return $mockQueueService;
            }
        );

        $statisticalRepository = TestServiceRegister::getService(StatisticalDataRepositoryInterface::class);
        $statisticalRepository->setStatisticalData(new StatisticalData(true));

        $countryConfigurationRepository = TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class);
        $countryConfigurationRepository->setCountryConfiguration([new CountryConfiguration('ES', 'testMerchantId')]);
    }

    public function testNativeSerialization(): void
    {
        // arrange
        $this->task->page = 123;
        TestServiceRegister::registerService(Serializer::CLASS_NAME, static function () {
            return new NativeSerializer();
        });

        // act
        $serialized = Serializer::serialize($this->task);

        // assert
        self::assertEquals($this->task, Serializer::unserialize($serialized));
    }

    public function testJsonSerialization(): void
    {
        // arrange
        $this->task->page = 123;
        TestServiceRegister::registerService(Serializer::CLASS_NAME, static function () {
            return new JsonSerializer();
        });

        // act
        $serialized = Serializer::serialize($this->task);

        // assert
        self::assertEquals($this->task, Serializer::unserialize($serialized));
    }

    public function testSubTasksCreated(): void
    {
        // arrange
        $this->task->page = 0;
        $this->shopOrderService->reportOrderIds = range(0, 27000);
        $this->shopOrderService->statisticsOrderIds = range(0, 36000);

        // act
        $this->task->execute();

        // assert
        self::assertCount(8, $this->queueService->queueItems);
    }

    public function testCorrectTaskCreated(): void
    {
        // arrange
        $this->task->page = 0;
        $this->shopOrderService->reportOrderIds = range(0, 27000);
        $this->shopOrderService->statisticsOrderIds = range(0, 36000);

        // act
        $this->task->execute();

        // assert
        foreach ($this->queueService->queueItems as $item) {
            self::assertInstanceOf(OrderReportTask::class, $item->getTask());
        }
    }
}
