<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;

/**
 * Class MockSendReportRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockSendReportRepository implements SendReportRepositoryInterface
{
    /**
     * @var ?SendReport
     */
    private $report;

    /**
     * @inheritDoc
     */
    public function setSendReport(SendReport $sendReport): void
    {
        $this->report = $sendReport;
    }

    /**
     * @inheritDoc
     */
    public function getSendReport(): ?SendReport
    {
        return $this->report;
    }

    /**
     * @inheritDoc
     */
    public function deleteSendReportForContext(string $context): void
    {
        $this->report = null;
    }

    /**
     * @inheritDoc
     */
    public function getReportSendingContexts(): array
    {
        return [];
    }
}
