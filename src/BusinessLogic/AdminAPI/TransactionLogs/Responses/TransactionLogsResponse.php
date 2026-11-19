<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\TransactionLogs\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;

class TransactionLogsResponse extends Response
{
    /**
     * @var bool
     */
    protected $hasNextPage;

    /**
     * @var TransactionLog[]
     */
    protected $transactionLogs;

    /**
     * @param bool $hasNextPage
     * @param TransactionLog[] $transactionLogs
     */
    public function __construct(bool $hasNextPage, array $transactionLogs)
    {
        $this->hasNextPage = $hasNextPage;
        $this->transactionLogs = $transactionLogs;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $logs = [];
        foreach ($this->transactionLogs as $log) {
            $logs[] = [
                'merchantReference' => $log->getMerchantReference(),
                'executionId' => $log->getExecutionId(),
                'paymentMethod' => $log->getPaymentMethod(),
                'timestamp' => $log->getTimestamp(),
                'eventCode' => $log->getEventCode(),
                'isSuccessful' => $log->isSuccessful(),
                'queueStatus' => $log->getQueueStatus(),
                'reason' => $log->getReason(),
                'failureDescription' => $log->getFailureDescription(),
                'sequraLink' => $log->getSequraLink(),
                'shopLink' => $log->getShopLink(),
            ];
        }

        return [
            'hasNextPage' => $this->hasNextPage,
            'transactionLogs' => $logs
        ];
    }
}
