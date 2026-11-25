<?php

namespace SeQura\Core\BusinessLogic\TaskExecution;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\RecurringWorkLockInterface;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;

/**
 * @package SeQura\Core\BusinessLogic\TaskExecution
 */
class FileRecurringWorkLock implements RecurringWorkLockInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        $this->path = $path ?? sys_get_temp_dir() . '/sequra-recurring-work-' . md5(__FILE__) . '.lock';
    }

    /**
     * @inheritDoc
     */
    public function tryAcquire(): bool
    {
        $handle = @fopen($this->path, 'c');
        if ($handle === false) {
            Logger::logWarning(
                'Recurring work lock file could not be opened, running without a lock.',
                'Core',
                [new LogContextData('path', $this->path)]
            );

            return true;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return false;
        }

        $this->handle = $handle;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function release(): void
    {
        if ($this->handle === null) {
            return;
        }

        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        $this->handle = null;
    }
}
