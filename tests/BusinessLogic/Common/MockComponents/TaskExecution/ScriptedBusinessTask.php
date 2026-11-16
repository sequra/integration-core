<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;

class ScriptedBusinessTask implements BusinessTaskInterface
{
    /**
     * @var callable|null
     */
    public static $script;

    public function execute()
    {
        return (self::$script)();
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
