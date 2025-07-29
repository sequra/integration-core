<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Deployments\Entities;

use SeQura\Core\BusinessLogic\DataAccess\Deployments\Entities\Deployment;
use SeQura\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class DeploymentsEntityTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\Deployments\Entities
 */
class DeploymentsEntityTest extends GenericEntityTest
{
    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return Deployment::getClassName();
    }
}
