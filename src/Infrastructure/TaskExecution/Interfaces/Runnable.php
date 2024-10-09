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
     *
     * @return void
     */
    public function run();
}
