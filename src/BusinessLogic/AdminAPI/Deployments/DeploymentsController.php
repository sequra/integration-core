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
     * Gets all deployments from API.
     *
     * @return DeploymentsResponse
     */
    public function getAllDeployments(): DeploymentsResponse
    {
        return new DeploymentsResponse($this->deploymentsService->getDeployments());
    }

    /**
     * Gets only not connected deployments for current store context.
     *
     * @return DeploymentsResponse
     */
    public function getNotConnectedDeployments(): DeploymentsResponse
    {
        return new DeploymentsResponse($this->deploymentsService->getNotConnectedDeployments());
    }
}
