<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Log;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SuccessResponse;
use SeQura\Core\BusinessLogic\Domain\Integration\Log\LogServiceInterface;

/**
 * Class RemoveLogContentHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Log
 */
class RemoveLogContentHandler implements TopicHandlerInterface
{
    /**
     * @var LogServiceInterface
     */
    protected $logService;

    /**
     * @param LogServiceInterface $logService
     */
    public function __construct(LogServiceInterface $logService)
    {
        $this->logService = $logService;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        $this->logService->removeLogContent();

        return new SuccessResponse();
    }
}
