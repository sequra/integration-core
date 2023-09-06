<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderReport\Tasks;

use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Tasks\OrderReportTask;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseSerializationTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockOrderReportService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderReportTaskTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\OrderReport\Tasks
 */
class OrderReportTaskTest extends BaseSerializationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestServiceRegister::registerService(OrderReportServiceInterface::class, static function () {
            return new MockOrderReportService();
        });

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        TestServiceRegister::registerService(HttpClient::class, static function () use ($httpClient) {
            return $httpClient;
        });

        $httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->serializable = new OrderReportTask('testMerchantId', ['1', '2', '3'], ['4', '5', '6']);
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testSendingDeliveryReportOnly(): void
    {
        // arrange
        MockOrderReportService::$REPORT_SENT = false;
        MockOrderReportService::$STATISTICS_SENT = false;
        $task = new OrderReportTask('testMerchantId', ['1', '2', '3']);

        // act
        $task->execute();

        // assert
        self::assertTrue(MockOrderReportService::$REPORT_SENT);
        self::assertFalse(MockOrderReportService::$STATISTICS_SENT);
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testSendingStatisticsReportOnly(): void
    {
        // arrange
        MockOrderReportService::$REPORT_SENT = false;
        MockOrderReportService::$STATISTICS_SENT = false;
        $task = new OrderReportTask('testMerchantId', [], ['4', '5', '6']);

        // act
        $task->execute();

        // assert
        self::assertFalse(MockOrderReportService::$REPORT_SENT);
        self::assertTrue(MockOrderReportService::$STATISTICS_SENT);
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testSendingFullReport(): void
    {
        // arrange
        MockOrderReportService::$REPORT_SENT = false;
        MockOrderReportService::$STATISTICS_SENT = false;
        $task = new OrderReportTask('testMerchantId', ['1', '2', '3'], ['4', '5', '6']);

        // act
        $task->execute();

        // assert
        self::assertTrue(MockOrderReportService::$REPORT_SENT);
        self::assertTrue(MockOrderReportService::$STATISTICS_SENT);
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testSendingEmptyReport(): void
    {
        // arrange
        MockOrderReportService::$REPORT_SENT = false;
        MockOrderReportService::$STATISTICS_SENT = false;
        $task = new OrderReportTask('testMerchantId', []);

        // act
        $task->execute();

        // assert
        self::assertFalse(MockOrderReportService::$REPORT_SENT);
        self::assertFalse(MockOrderReportService::$STATISTICS_SENT);
    }
}
