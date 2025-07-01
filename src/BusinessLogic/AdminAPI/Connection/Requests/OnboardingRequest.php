<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\OnboardingData;

class OnboardingRequest extends Request
{
    /**
     * @var ConnectionRequest[] $connectionRequests
     */
    protected $connectionRequests;

    /**
     * @var bool $sendStatisticalData
     */
    protected $sendStatisticalData;

    /**
     * @param ConnectionRequest[] $connectionRequests
     * @param bool $sendStatisticalData
     */
    public function __construct(
        array $connectionRequests,
        bool $sendStatisticalData
    ) {
        $this->connectionRequests = $connectionRequests;
        $this->sendStatisticalData = $sendStatisticalData;
    }

    /**
     * Transforms the request to a OnboardingData object.
     *
     * @return OnboardingData
     *
     * @throws InvalidEnvironmentException
     */
    public function transformToDomainModel(): object
    {
        $connections = [];

        foreach ($this->connectionRequests as $connectionRequest) {
            $connections[] = $connectionRequest->transformToDomainModel();
        }

        return new OnboardingData($connections, $this->sendStatisticalData);
    }
}
