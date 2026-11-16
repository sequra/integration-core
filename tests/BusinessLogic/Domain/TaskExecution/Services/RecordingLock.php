<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\RecurringWorkLockInterface;

class RecordingLock implements RecurringWorkLockInterface
{
    public $acquirable;
    public $acquisitions = 0;
    public $releases = 0;
    public $itemsAtRelease;
    public $onRelease;

    public function __construct(bool $acquirable)
    {
        $this->acquirable = $acquirable;
    }

    public function tryAcquire(): bool
    {
        $this->acquisitions++;

        return $this->acquirable;
    }

    public function release(): void
    {
        $this->releases++;
        if ($this->onRelease !== null) {
            call_user_func($this->onRelease);
        }
    }
}
