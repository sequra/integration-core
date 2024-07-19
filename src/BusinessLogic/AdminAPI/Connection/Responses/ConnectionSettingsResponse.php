<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;

/**
 * Class ConnectionSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses
 */
class ConnectionSettingsResponse extends Response
{
    /**
     * @var ConnectionData
     */
    protected $connectionSettings;

    /**
     * @param ConnectionData|null $connectionSettings
     */
    public function __construct(?ConnectionData $connectionSettings)
    {
        $this->connectionSettings = $connectionSettings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->connectionSettings) {
            return [];
        }

        return [
            'environment' => $this->connectionSettings->getEnvironment(),
            'username' => $this->connectionSettings->getAuthorizationCredentials()->getUsername(),
            'password' => $this->connectionSettings->getAuthorizationCredentials()->getPassword(),
            'merchantId' => $this->connectionSettings->getMerchantId()
        ];
    }
}
