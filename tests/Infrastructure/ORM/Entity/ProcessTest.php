<?php

namespace SeQura\Core\Tests\Infrastructure\ORM\Entity;

use SeQura\Core\Infrastructure\TaskExecution\Process;

/**
 * Class ProcessTest.
 *
 * @package SeQura\Core\Tests\Infrastructure\ORM\Entity
 */
class ProcessTest extends GenericEntityTest
{
    /**
     * Returns entity full class name
     *
     * @return string
     */
    public function getEntityClass()
    {
        return Process::getClassName();
    }
}
