<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Deployments\Requests;

use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class GetDeploymentsRequest.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Deployments\Requests
 */
class GetDeploymentsRequest extends HttpRequest
{
    /**
     *  GetDeployments Request construct. No API validation needed.
     */
    public function __construct()
    {
        parent::__construct('deployments');
    }
}
