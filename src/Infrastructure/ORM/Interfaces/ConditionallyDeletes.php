<?php

namespace SeQura\Core\Infrastructure\ORM\Interfaces;

use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Interface ConditionallyDeletes
 *
 * @package SeQura\Core\BusinessLogic\ORM\Interfaces
 */
interface ConditionallyDeletes
{
    /**
     * Deletes entities that match the given query filter.
     *
     * @param QueryFilter|null $queryFilter
     *
     * @return void
     */
    public function deleteWhere(QueryFilter $queryFilter = null);
}
