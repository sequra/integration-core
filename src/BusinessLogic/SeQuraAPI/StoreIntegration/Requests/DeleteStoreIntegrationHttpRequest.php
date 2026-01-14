<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration\Requests;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class DeleteStoreIntegrationHttpRequest.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration\Requests
 */
class DeleteStoreIntegrationHttpRequest extends HttpRequest
{
    /**
     * @param DeleteStoreIntegrationRequest $request
     */
    public function __construct(DeleteStoreIntegrationRequest $request)
    {
        parent::__construct("store_integrations/{$request->getConnectionData()->getIntegrationId()}");
    }
}
