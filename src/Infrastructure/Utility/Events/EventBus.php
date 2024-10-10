<?php

namespace SeQura\Core\Infrastructure\Utility\Events;

/**
 * Class EventBus
 *
 * @package SeQura\Core\Infrastructure\Utility\Events
 */
/**
 * @phpstan-consistent-constructor
 */
class EventBus extends EventEmitter
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Singleton instance of this class.
     *
     * @var EventBus | null
     */
    protected static $instance;

    /**
     * EventBus constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Returns singleton instance of EventBus.
     *
     * @return EventBus Instance of EventBus class.
     */
    public static function getInstance(): ?EventBus
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Resets singleton instance. Required for proper tests.
     */
    public static function resetInstance(): void
    {
        static::$instance = null;
    }

    /**
     * Fires requested event by calling all its registered handlers.
     *
     * @param Event $event Event to fire.
     */
    public function fire(Event $event): void
    {
        // just changed access type from protected to public
        parent::fire($event);
    }
}
