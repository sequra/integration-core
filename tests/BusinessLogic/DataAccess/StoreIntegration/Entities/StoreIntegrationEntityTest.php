<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\StoreIntegration\Entities;

use SeQura\Core\BusinessLogic\DataAccess\StoreIntegration\Entities\StoreIntegration;
use SeQura\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class StoreIntegrationEntityTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\StoreIntegration\Entities
 */
class StoreIntegrationEntityTest extends GenericEntityTest
{
    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return StoreIntegration::getClassName();
    }
}
