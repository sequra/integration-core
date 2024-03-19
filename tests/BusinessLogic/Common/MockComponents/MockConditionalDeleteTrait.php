<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Trait MockConditionalDeleteTrait
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
trait MockConditionalDeleteTrait
{
    /**
     * @inheritDoc
     *
     * @throws EntityClassException
     * @throws QueryFilterInvalidParamException
     */
    public function deleteWhere(QueryFilter $queryFilter = null): void
    {
        $entities = $this->select($queryFilter);
        foreach ($entities as $entity) {
            $this->delete($entity);
        }
    }
}
