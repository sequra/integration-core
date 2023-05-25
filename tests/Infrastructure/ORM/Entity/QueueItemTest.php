<?php

namespace SeQura\Core\Tests\Infrastructure\ORM\Entity;

use SeQura\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemTest.
 *
 * @package SeQura\Core\Tests\Infrastructure\ORM\Entity
 */
class QueueItemTest extends GenericEntityTest
{
    /**
     * Returns entity full class name
     *
     * @return string
     */
    public function getEntityClass()
    {
        return QueueItem::getClassName();
    }
}
