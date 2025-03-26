<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\PaymentMethod\Entities;

use SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\Entities\PaymentMethod;
use SeQura\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class PaymentMethodEntityTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\PaymentMethod\Entities
 */
class PaymentMethodEntityTest extends GenericEntityTest
{
    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return PaymentMethod::getClassName();
    }
}
