<?php

namespace SeQura\Core\BusinessLogic\Domain\StatisticalData\Services;

use DateTime;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;

/**
 * Class StatisticalDataService
 *
 * @package SeQura\Core\BusinessLogic\Domain\StatisticalData\Services
 */
class StatisticalDataService
{
    /**
     * @var StatisticalDataRepositoryInterface
     */
    private $statisticalDataRepository;

    /**
     * @var SendReportRepositoryInterface
     */
    private $sendReportRepository;

    /**
     * @param StatisticalDataRepositoryInterface $statisticalDataRepository
     * @param SendReportRepositoryInterface $sendReportRepository
     */
    public function __construct(
        StatisticalDataRepositoryInterface $statisticalDataRepository,
        SendReportRepositoryInterface $sendReportRepository
    ) {
        $this->statisticalDataRepository = $statisticalDataRepository;
        $this->sendReportRepository = $sendReportRepository;
    }

    /**
     * Retrieves statistical data from the database via statistical data repository.
     *
     * @return StatisticalData|null
     */
    public function getStatisticalData(): ?StatisticalData
    {
        return $this->statisticalDataRepository->getStatisticalData();
    }

    /**
     * Calls the repository to save statistical data to the database.
     *
     * @param StatisticalData $statisticalData
     *
     * @return void
     */
    public function saveStatisticalData(StatisticalData $statisticalData): void
    {
        $this->statisticalDataRepository->setStatisticalData($statisticalData);

        if ($statisticalData->isSendStatisticalData()) {
            $this->sendReportRepository->setSendReport(
                new SendReport((new DateTime())->modify('+1 day')->getTimestamp())
            );
        }
    }

    /**
     * @return string[]
     */
    public function getContextsForSendingReport(): array
    {
        return $this->sendReportRepository->getReportSendingContexts();
    }
}
