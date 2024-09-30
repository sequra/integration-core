<?php

namespace SeQura\Core\Infrastructure;

use SeQura\Core\Infrastructure\Exceptions\ServiceNotRegisteredException;
use InvalidArgumentException;

/**
 * Class ServiceRegister.
 *
 * @package SeQura\Core\Infrastructure
 */
class ServiceRegister
{
    /**
     * Service register instance.
     *
     * @var ServiceRegister
     */
    protected static $instance;
    /**
     * Array of registered services.
     *
     * @var array<string,callable>
     */
    protected $services;

    /**
     * ServiceRegister constructor.
     *
     * @param array<string,callable> $services
     *
     * @throws InvalidArgumentException
     *  In case delegate of a registered service is not a callable.
     */
    protected function __construct(array $services = array())
    {
        if (!empty($services)) {
            foreach ($services as $type => $service) {
                $this->register($type, $service);
            }
        }

        self::$instance = $this;
    }

    /**
     * Getting service register instance
     *
     * @return ServiceRegister
     */
    public static function getInstance(): ServiceRegister
    {
        if (self::$instance === null) {
            self::$instance = new ServiceRegister();
        }

        return self::$instance;
    }

    /**
     * Gets service for specified type.
     *
     * @param string $type Type of service. Should be fully qualified class name.
     *
     * @return       mixed Instance of service.
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function getService(string $type)
    {
        // Unhandled exception warning suppressed on purpose so that all classes using service
        // would not need @throws tag.
        /**
        * @noinspection PhpUnhandledExceptionInspection
        */
        return self::getInstance()->get($type);
    }

    /**
     * Registers service with delegate as second parameter which represents function for creating new service instance.
     *
     * @param string $type Type of service. Should be fully qualified class name.
     * @param callable $delegate Delegate that will give instance of registered service.
     *
     * @throws InvalidArgumentException
     *  In case delegate is not a callable.
     */
    public static function registerService(string $type, callable $delegate): void
    {
        self::getInstance()->register($type, $delegate);
    }

    /**
     * Register service class.
     *
     * @param string $type Type of service. Should be fully qualified class name.
     * @param callable $delegate Delegate that will give instance of registered service.
     *
     * @throws InvalidArgumentException
     *  In case delegate is not a callable.
     */
    protected function register(string $type, callable $delegate): void
    {
        if (!is_callable($delegate)) {
            throw new InvalidArgumentException("$type delegate is not callable.");
        }

        $this->services[$type] = $delegate;
    }

    /**
     * Getting service instance.
     *
     * @param string $type Type of service. Should be fully qualified class name.
     *
     * @return mixed Instance of service.
     *
     * @throws ServiceNotRegisteredException
     */
    protected function get($type)
    {
        if (empty($this->services[$type])) {
            throw new ServiceNotRegisteredException($type);
        }

        return call_user_func($this->services[$type]);
    }
}
