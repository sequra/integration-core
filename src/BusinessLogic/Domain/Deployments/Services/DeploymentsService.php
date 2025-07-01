<?php

namespace SeQura\Core\BusinessLogic\Domain\Deployments\Services;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\ProxyContracts\DeploymentsProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;

/**
 * Class DeploymentService.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Deployments\Services
 */
class DeploymentsService
{
    /**
     * @var DeploymentsProxyInterface $deploymentProxy
     */
    private $deploymentProxy;

    /**
     * @var DeploymentsRepositoryInterface $deploymentRepository
     */
    private $deploymentRepository;

    /**
     * @var array<string, Deployment>
     */
    private static $deployments = [];

    /**
     * @param DeploymentsProxyInterface $deploymentProxy
     * @param DeploymentsRepositoryInterface $deploymentRepository
     */
    public function __construct(
        DeploymentsProxyInterface $deploymentProxy,
        DeploymentsRepositoryInterface $deploymentRepository
    ) {
        $this->deploymentProxy = $deploymentProxy;
        $this->deploymentRepository = $deploymentRepository;
    }

    /**
     * Fetch deployments from API and store them to database.
     *
     * @return Deployment[]
     */
    public function getDeployments(): array
    {
        $deployments = $this->deploymentProxy->getDeployments();
        !empty($deployments) && $this->deploymentRepository->setDeployments($deployments);

        return $deployments;
    }

    /**
     * @param string $deploymentId
     *
     * @return ?Deployment
     */
    public function getDeploymentById(string $deploymentId): ?Deployment
    {
        if (!empty(self::$deployments[$deploymentId])) {
            return self::$deployments[$deploymentId];
        }

        $deployment = $this->deploymentRepository->getDeploymentById($deploymentId);

        if (!$deployment) {
            $allDeployments = $this->getDeployments();

            foreach ($allDeployments as $deployment) {
                if ($deployment->getId() === $deploymentId) {
                    self::$deployments[$deploymentId] = $deployment;

                    return $deployment;
                }
            }
        }

        self::$deployments[$deploymentId] = $deployment;

        return self::$deployments[$deploymentId];
    }
}
