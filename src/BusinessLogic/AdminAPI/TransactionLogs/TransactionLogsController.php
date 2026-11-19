<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\TransactionLogs;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\TransactionLogs\Responses\TransactionLogsResponse;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;

/**
 * Class TransactionLogsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\TransactionLogs
 */
class TransactionLogsController
{
    /**
     * @var TransactionLogService
     */
    protected $transactionLogService;

    /**
     * @param TransactionLogService $transactionLogService
     */
    public function __construct(TransactionLogService $transactionLogService)
    {
        $this->transactionLogService = $transactionLogService;
    }

    /**
     * Gets a numbered page and a custom number of transaction logs.
     *
     * @param int $page
     * @param int $limit
     *
     * @return TransactionLogsResponse
     *
     * @throws Exception
     */
    public function getTransactionLogs(int $page, int $limit): TransactionLogsResponse
    {
        $transactionLogs = $this->transactionLogService->find($limit, ($page - 1) * $limit);
        $hasNextPage = $this->transactionLogService->hasNextPage($page, $limit);

        return new TransactionLogsResponse($hasNextPage, $transactionLogs);
    }
}
