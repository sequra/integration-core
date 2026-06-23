<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Affiliate\Entities;

use SeQura\Core\BusinessLogic\DataAccess\Affiliate\Entities\AffiliateSettings;
use SeQura\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class AffiliateSettingsEntityTest.
 *
 * @package DataAccess\Affiliate\Entities
 */
class AffiliateSettingsEntityTest extends GenericEntityTest
{
    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return AffiliateSettings::getClassName();
    }
}
