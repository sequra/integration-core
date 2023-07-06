<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\OnboardingData;

class OnboardingRequest extends Request
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var bool
     */
    private $sendStatisticalData;

    /**
     * @param string $environment
     * @param string $merchantId
     * @param string $username
     * @param string $password
     * @param bool $sendStatisticalData
     */
    public function __construct(string $environment, string $merchantId, string $username, string $password, bool $sendStatisticalData)
    {
        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->username = $username;
        $this->password = $password;
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
        return new OnboardingData(
            new ConnectionData(
                $this->environment,
                $this->merchantId,
                new AuthorizationCredentials(
                    $this->username,
                    $this->password
                )
            ),
            $this->sendStatisticalData
        );
    }
}
