<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;

/**
 * Class StatisticalDataRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests
 */
class StatisticalDataRequest extends Request
{
    /**
     * @var bool
     */
    protected $sendStatisticalData;

    /**
     * @param bool $sendStatisticalData
     */
    public function __construct(bool $sendStatisticalData)
    {
        $this->sendStatisticalData = $sendStatisticalData;
    }

    /**
     * Transforms the request to a StatisticalData object.
     *
     * @return StatisticalData
     */
    public function transformToDomainModel(): object
    {
        return new StatisticalData($this->sendStatisticalData);
    }
}
