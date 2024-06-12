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
    public function deleteWhere(QueryFilter $queryFilter = null);
}
