<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderReport\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\ReportData;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportService;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockOrderReportService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderReportServiceTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\OrderReport\Services
 */
class OrderReportServiceTest extends BaseTestCase
{
    /**
     * @var OrderReportService
     */
    private $orderReportService;

    /**
     * @var TestHttpClient
     */
    public $httpClient;

    public function setUp(): void
    {
        parent::setUp();
        TestServiceRegister::registerService(OrderReportServiceInterface::class, static function () {
            return new MockOrderReportService();
        });

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, static function () use ($httpClient) {
            return $httpClient;
        });

        $this->orderReportService = TestServiceRegister::getService(OrderReportService::class);
    }

    /**
     * @throws Exception
     */
    public function testIsSendReportResponseSuccessful(): void
    {
        // Arrange
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $sendReportRequest = $this->getSendReportRequest();

        // Act
        $response = $this->orderReportService->sendReport($sendReportRequest);

        // Assert
        self::assertTrue($response);
    }

    /**
     * @return ReportData
     */
    private function getSendReportRequest(): ReportData
    {
        return new ReportData('testMerchantId', [], []);
    }
}
