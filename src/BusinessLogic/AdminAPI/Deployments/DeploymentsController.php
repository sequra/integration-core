<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Deployments;

use SeQura\Core\BusinessLogic\AdminAPI\Deployments\Responses\DeploymentsResponse;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;

/**
 * Class DeploymentsController.
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Deployments
 */
class DeploymentsController
{
    /**
     * @var DeploymentsService
     */
    protected $deploymentsService;

    /**
     * @param DeploymentsService $deploymentsService
     */
    public function __construct(DeploymentsService $deploymentsService)
    {
        $this->deploymentsService = $deploymentsService;
    }

    /**
     * Disconnects integration and removes necessary data of the merchant.
     *
     * @return DeploymentsResponse
     */
    public function getAllDeployments(): DeploymentsResponse
    {
        return new DeploymentsResponse($this->deploymentsService->getDeployments());
    }
}
