<?php

namespace SeQura\Core\BusinessLogic\Domain\Deployments\ProxyContracts;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;

/**
 * Interface DeploymentsProxyInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Deployments\ProxyContracts
 */
interface DeploymentsProxyInterface
{
    /**
     * @return Deployment[]
     */
    public function getDeployments(): array;
}
