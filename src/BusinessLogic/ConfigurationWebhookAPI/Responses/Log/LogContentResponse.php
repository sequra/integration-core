<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Log;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Log\Model\Log;

/**
 * Class LogContentResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Log
 */
class LogContentResponse extends Response
{
    /**
     * @var Log
     */
    protected $log;

    /**
     * @param Log $logEntries
     */
    public function __construct(Log $logEntries)
    {
        $this->log = $logEntries;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->log->toArray();
    }
}
