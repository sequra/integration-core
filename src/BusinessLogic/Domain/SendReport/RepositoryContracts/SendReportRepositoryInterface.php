<?php

namespace SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;

/**
 * Interface SendReportRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts
 */
interface SendReportRepositoryInterface
{
    /**
     *  Insert/update send report data for current store context.
     *
     * @param SendReport $sendReport
     *
     * @return void
     */
    public function setSendReport(SendReport $sendReport): void;

    /**
     * Gets send report data for current store context.
     *
     * @return SendReport|null
     */
    public function getSendReport(): ?SendReport;

    /**
     * @param string $context
     *
     * @return void
     */
    public function deleteSendReportForContext(string $context): void;

    /**
     * Return array of contexts that report needs to be sent to.
     *
     * @return string[]
     */
    public function getReportSendingContexts(): array;
}
