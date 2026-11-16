<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Runner;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;

class UnbuildableTask implements BusinessTaskInterface
{
    public static $error;

    public static $executions = 0;

    public function execute()
    {
        self::$executions++;

        return null;
    }

    public function toArray(): array
    {
        return [];
    }

    public static function fromArray(array $data): BusinessTaskInterface
    {
        self::$error = new \TypeError('bad data');
        throw self::$error;
    }
}
