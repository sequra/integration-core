<?php

namespace SeQura\Core\Infrastructure\TaskExecution\Interfaces;

use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;

/**
 * Interface Runnable.
 *
 * @package SeQura\Core\Infrastructure\TaskExecution\Interfaces
 */
interface Runnable extends Serializable
{
    /**
     * Starts runnable run logic
     */
    public function run();
}
