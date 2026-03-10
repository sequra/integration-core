<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Store;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Store\StoreInfoResponse;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreInfoServiceInterface;

/**
 * Class GetStoreInfoHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Store
 */
class GetStoreInfoHandler implements TopicHandlerInterface
{
    /**
     * @var StoreInfoServiceInterface
     */
    protected $storeInfoService;

    /**
     * @param StoreInfoServiceInterface $storeInfoService
     */
    public function __construct(StoreInfoServiceInterface $storeInfoService)
    {
        $this->storeInfoService = $storeInfoService;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        return new StoreInfoResponse($this->storeInfoService->getStoreInfo());
    }
}
