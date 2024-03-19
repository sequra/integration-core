<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\TransactionLogs;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\TransactionLogs\Responses\TransactionLogsResponse;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionLogsControllerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\TransactionLogs
 */
class TransactionLogsControllerTest extends BaseTestCase
{
    /**
     * @var TransactionLogRepositoryInterface
     */
    private $transactionLogRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->transactionLogRepository = TestServiceRegister::getService(TransactionLogRepositoryInterface::class);
        TestServiceRegister::registerService(ShopOrderService::class, static function () {
            return new MockShopOrderService();
        });
    }

    /**
     * @throws Exception
     */
    public function testIsGetTransactionLogsResponseSuccessful(): void
    {
        // Arrange
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('3'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('2'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('1'));

        // Act
        $response = AdminAPI::get()->transactionLogs('1')->getTransactionLogs(1, 5);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionLogsResponse(): void
    {
        // Arrange
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('1'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('2'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('3'));

        $expectedResponse = new TransactionLogsResponse(
            false,
            [
                $this->getTransactionLog('3'),
                $this->getTransactionLog('2'),
                $this->getTransactionLog('1')
            ]
        );

        // Act
        $response = AdminAPI::get()->transactionLogs('1')->getTransactionLogs(1, 5);

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionLogsSecondPageResponse(): void
    {
        // Arrange
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('1'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('2'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('3'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('4'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('5'));

        $expectedResponse = new TransactionLogsResponse(
            true,
            [
                $this->getTransactionLog('3'),
                $this->getTransactionLog('2')
            ]
        );

        // Act
        $response = AdminAPI::get()->transactionLogs('1')->getTransactionLogs(2, 2);

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionLogsResponseToArray(): void
    {
        // Arrange
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('3'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('2'));
        $this->transactionLogRepository->setTransactionLog($this->getTransactionLog('1'));

        // Act
        $response = AdminAPI::get()->transactionLogs('1')->getTransactionLogs(1, 5);

        // Assert
        self::assertEquals($this->expectedToArrayResponse(), $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetNonExistingTransactionLogsResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->transactionLogs('1')->getTransactionLogs(1, 5);

        // Assert
        self::assertEquals(['hasNextPage' => false, 'transactionLogs' => []], $response->toArray());
    }

    /**
     * @param string $id
     *
     * @return TransactionLog
     */
    private function getTransactionLog(string $id): TransactionLog
    {
        $transactionLog = new TransactionLog();
        $transactionLog->setId($id);
        $transactionLog->setStoreId('1');
        $transactionLog->setMerchantReference('ref1');
        $transactionLog->setExecutionId($id);
        $transactionLog->setPaymentMethod('Payment method ' . $id);
        $transactionLog->setTimestamp(123456789);
        $transactionLog->setEventCode('code ' . $id);
        $transactionLog->setIsSuccessful(true);
        $transactionLog->setQueueStatus('Failed');
        $transactionLog->setReason('Reason ' . $id);
        $transactionLog->setFailureDescription('Failure description ' . $id);
        $transactionLog->setSequraLink('sequra.link.' . $id);
        $transactionLog->setShopLink('shop.link.' . $id);

        return $transactionLog;
    }

    private function expectedToArrayResponse(): array
    {
        return [
            'hasNextPage' => false,
            'transactionLogs' => [
                [
                    'merchantReference' => 'ref1',
                    'executionId' => '1',
                    'paymentMethod' => 'Payment method 1',
                    'timestamp' => 123456789,
                    'eventCode' => 'code 1',
                    'isSuccessful' => true,
                    'queueStatus' => 'Failed',
                    'reason' => 'Reason 1',
                    'failureDescription' => 'Failure description 1',
                    'sequraLink' => 'sequra.link.1',
                    'shopLink' => 'shop.link.1'
                ],
                [
                    'merchantReference' => 'ref1',
                    'executionId' => '2',
                    'paymentMethod' => 'Payment method 2',
                    'timestamp' => 123456789,
                    'eventCode' => 'code 2',
                    'isSuccessful' => true,
                    'queueStatus' => 'Failed',
                    'reason' => 'Reason 2',
                    'failureDescription' => 'Failure description 2',
                    'sequraLink' => 'sequra.link.2',
                    'shopLink' => 'shop.link.2'
                ],
                [
                    'merchantReference' => 'ref1',
                    'executionId' => '3',
                    'paymentMethod' => 'Payment method 3',
                    'timestamp' => 123456789,
                    'eventCode' => 'code 3',
                    'isSuccessful' => true,
                    'queueStatus' => 'Failed',
                    'reason' => 'Reason 3',
                    'failureDescription' => 'Failure description 3',
                    'sequraLink' => 'sequra.link.3',
                    'shopLink' => 'shop.link.3'
                ]
            ]
        ];
    }
}
