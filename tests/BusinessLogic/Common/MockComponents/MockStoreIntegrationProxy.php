<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts\StoreIntegrationsProxyInterface;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;

/**
 * Class MockStoreIntegrationProxy.
 *
 * @package Common\MockComponents
 */
class MockStoreIntegrationProxy implements StoreIntegrationsProxyInterface
{
    /**
     * @var ?CreateStoreIntegrationResponse $createResponse
     */
    private $createResponse = null;

    /**
     * @var ?DeleteStoreIntegrationResponse $deleteResponse
     */
    private $deleteResponse = null;

    /**
     * @var ?URL $webhookUrl
     */
    private $webhookUrl = null;

    /**
     * @var bool $deleted
     */
    private $deleted = false;

    /**
     * @var int $createCallCount
     */
    private $createCallCount = 0;

    /**
     * @inheritDoc
     */
    public function createStoreIntegration(CreateStoreIntegrationRequest $request): CreateStoreIntegrationResponse
    {
        $this->createCallCount++;
        $this->webhookUrl = $request->getWebhookUrl();

        if ($this->createResponse) {
            return $this->createResponse;
        }

        return new CreateStoreIntegrationResponse('integrationId');
    }

    /**
     * @inheritDoc
     */
    public function deleteStoreIntegration(DeleteStoreIntegrationRequest $request): DeleteStoreIntegrationResponse
    {
        $this->deleted = true;

        if ($this->deleteResponse) {
            return $this->deleteResponse;
        }

        return new DeleteStoreIntegrationResponse();
    }

    /**
     * @param CreateStoreIntegrationResponse $createResponse
     *
     * @return void
     */
    public function setMockCreateResponse(CreateStoreIntegrationResponse $createResponse): void
    {
        $this->createResponse = $createResponse;
    }

    /**
     * @param DeleteStoreIntegrationResponse $deleteStoreIntegrationResponse
     *
     * @return void
     */
    public function setMockDeleteResponse(DeleteStoreIntegrationResponse $deleteStoreIntegrationResponse): void
    {
        $this->deleteResponse = $deleteStoreIntegrationResponse;
    }

    /**
     * @return ?URL
     */
    public function getWebhookUrl(): ?URL
    {
        return $this->webhookUrl;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @return int
     */
    public function getCreateCallCount(): int
    {
        return $this->createCallCount;
    }
}
