<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Doubles;

use Generator;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;

class KeepAliveBusinessTask implements BusinessTaskInterface
{
    public function execute(): Generator
    {
        yield null;
        yield 50;
        yield 'syncing';
    }

    public function toArray(): array
    {
        return [];
    }

    public static function fromArray(array $data): BusinessTaskInterface
    {
        return new self();
    }
}
