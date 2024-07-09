<?php

namespace SeQura\Core\BusinessLogic\Domain\SendReport\Models;

/**
 * Class SendReport
 *
 * @package SeQura\Core\BusinessLogic\Domain\StatisticalData\Models
 */
class SendReport
{
    /**
     * @var int
     */
    protected $sendReportTime;

    /**
     * @param int $sendReportTime
     */
    public function __construct(int $sendReportTime)
    {
        $this->sendReportTime = $sendReportTime;
    }

    /**
     * @return int
     */
    public function getSendReportTime(): int
    {
        return $this->sendReportTime;
    }
}
