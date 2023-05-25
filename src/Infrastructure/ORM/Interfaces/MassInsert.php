<?php

namespace SeQura\Core\Infrastructure\ORM\Interfaces;

use SeQura\Core\Infrastructure\ORM\Entity;

interface MassInsert
{
    /**
     * Executes mass insert query for all provided entities
     *
     * @param Entity[] $entities
     */
    public function massInsert(array $entities);
}
