<?php

namespace SeQura\Core\Infrastructure;

use RuntimeException;

/**
 * Base class for all singleton implementations.
 * Every class that extends this class MUST have its own protected static field $instance!
 *
 * @package SeQura\Core\Infrastructure
 */
/**
 * @phpstan-consistent-constructor
 */
abstract class Singleton
{
    /**
     * @var Singleton|null
     */
    protected static $instance;

    /**
     * Hidden constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Returns singleton instance of callee class.
     *
     * @return static Instance of callee class.
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        if (!(static::$instance instanceof static)) {
            throw new RuntimeException('Invalid singleton instance.');
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
}
