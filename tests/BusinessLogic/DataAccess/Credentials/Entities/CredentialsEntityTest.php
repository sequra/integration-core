<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Credentials\Entities;

use SeQura\Core\BusinessLogic\DataAccess\Credentials\Entities\Credentials;
use SeQura\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class CredentialsEntityTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\Credentials\Entities
 */
class CredentialsEntityTest extends GenericEntityTest
{
    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return Credentials::getClassName();
    }
}
