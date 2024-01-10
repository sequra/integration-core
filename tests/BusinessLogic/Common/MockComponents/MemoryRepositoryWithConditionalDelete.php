<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;

/**
 * Class MemoryRepositoryWithConditionalDelete
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MemoryRepositoryWithConditionalDelete extends MemoryRepository implements ConditionallyDeletes
{
    use MockConditionalDeleteTrait;

    public const THIS_CLASS_NAME = __CLASS__;
}
