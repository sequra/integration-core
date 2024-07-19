<?php

namespace SeQura\Core\BusinessLogic\Domain\Disconnect\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;

/**
 * Class DisconnectService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectService
{
    /**
     * @var DisconnectServiceInterface
     */
    protected $integrationDisconnectService;

    /**
     * @var SendReportRepositoryInterface
     */
    protected $sendReportRepository;

    /**
     * @param DisconnectServiceInterface $integrationDisconnectService
     * @param SendReportRepositoryInterface $sendReportRepository
     */
    public function __construct(
        DisconnectServiceInterface $integrationDisconnectService,
        SendReportRepositoryInterface $sendReportRepository
    ) {
        $this->integrationDisconnectService = $integrationDisconnectService;
        $this->sendReportRepository = $sendReportRepository;
    }

    /**
     * Disconnects integration from store.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->integrationDisconnectService->disconnect();
        $this->sendReportRepository->deleteSendReportForContext(StoreContext::getInstance()->getStoreId());
    }
}
