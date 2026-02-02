<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\AdvancedSettings\Entities;

use SeQura\Core\BusinessLogic\DataAccess\AdvancedSettings\Entities\AdvancedSettings;
use SeQura\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

/**
 * Class AdvancedSettingsEntityTest.
 *
 * @package DataAccess\AdvancedSettings\Entities
 */
class AdvancedSettingsEntityTest extends GenericEntityTest
{
    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return AdvancedSettings::getClassName();
    }
}
