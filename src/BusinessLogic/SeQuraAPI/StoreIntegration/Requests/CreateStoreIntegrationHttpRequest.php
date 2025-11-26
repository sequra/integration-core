<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration\Requests;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class StoreIntegrationHttpRequest.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration\Requests
 */
class CreateStoreIntegrationHttpRequest extends HttpRequest
{
    /**
     * @param CreateStoreIntegrationRequest $request
     */
    public function __construct(CreateStoreIntegrationRequest $request)
    {
        parent::__construct('store_integrations', $request->toArray());
    }
}
