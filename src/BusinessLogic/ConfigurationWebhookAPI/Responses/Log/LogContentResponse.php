<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Log;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class LogContentResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Log
 */
class LogContentResponse extends Response
{
    /**
     * @var bool
     */
    protected $successful = true;

    /**
     * @var string[]
     */
    protected $logEntries;

    /**
     * @param string[] $logEntries
     */
    public function __construct(array $logEntries)
    {
        $this->logEntries = $logEntries;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->logEntries;
    }
}
