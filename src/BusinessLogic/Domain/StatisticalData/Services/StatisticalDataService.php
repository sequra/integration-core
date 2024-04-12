<?php

namespace SeQura\Core\BusinessLogic\Domain\StatisticalData\Services;

use DateTime;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

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
    protected $statisticalDataRepository;

    /**
     * @var SendReportRepositoryInterface
     */
    protected $sendReportRepository;

    /**
     * @var TimeProvider
     */
    protected $timeProvider;

    /**
     * @param StatisticalDataRepositoryInterface $statisticalDataRepository
     * @param SendReportRepositoryInterface $sendReportRepository
     * @param TimeProvider $timeProvider
     */
    public function __construct(
        StatisticalDataRepositoryInterface $statisticalDataRepository,
        SendReportRepositoryInterface $sendReportRepository,
        TimeProvider $timeProvider
    ) {
        $this->statisticalDataRepository = $statisticalDataRepository;
        $this->sendReportRepository = $sendReportRepository;
        $this->timeProvider = $timeProvider;
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

    /**
     * @return void
     */
    public function setSendReportTime(): void
    {
        $time = $this->timeProvider->getCurrentLocalTime()->modify('+1 day')->getTimestamp();
        $this->sendReportRepository->setSendReport(new SendReport($time));
    }
}
