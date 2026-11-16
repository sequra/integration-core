<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Doubles;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;

class SilentBusinessTask implements BusinessTaskInterface
{
    public function execute()
    {
        return null;
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
