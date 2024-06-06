<?php

namespace SeQura\Core\Infrastructure\Configuration;

use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\Singleton;

/**
 * Class ConfigurationManager
 *
 * @package SeQura\Core\Infrastructure\Configuration
 */
class ConfigurationManager extends Singleton
{
    /**
     * Class name constant.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
    /**
     * System user context.
     *
     * @var string
     */
    protected $context = '';

    /**
     * Sets task execution context.
     *
     * When integration supports multiple accounts (middleware integration) proper context must be set based on
     * middleware account that is using core library functionality. This context should then be used by business
     * services to fetch account specific data.Core will set context provided upon task enqueueing before task
     * execution.
     *
     * @param string $context Context to set.
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Gets task execution context.
     *
     * @return string
     *  Context in which task is being executed. If no context is provided empty string is returned (global context).
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Gets configuration value for given name.
     *
     * @param string $name Name of the config parameter.
     * @param mixed $default Default value if config entity does not exist.
     * @param bool $isContextSpecific Flag that identifies whether value is config specific.
     *
     * @return mixed Value of config entity if found; otherwise, default value provided in $default parameter.
     * @throws QueryFilterInvalidParamException
     */
    public function getConfigValue($name, $default = null, $isContextSpecific = true)
    {
        $entity = $this->getConfigEntity($name, $isContextSpecific);

        return $entity ? $entity->getValue() : $default;
    }

    /**
     * Saves configuration value or updates it if it already exists.
     *
     * @param string $name Configuration property name.
     * @param mixed $value Configuration property value.
     * @param bool $isContextSpecific Flag that indicates whether config property is context specific.
     *
     * @return ConfigEntity
     * @throws QueryFilterInvalidParamException
     */
    public function saveConfigValue($name, $value, $isContextSpecific = true)
    {
        /**
        * @var ConfigEntity $config
        */
        $config = $this->getConfigEntity($name, $isContextSpecific) ?: new ConfigEntity();
        if ($isContextSpecific) {
            $config->setContext($this->getContext());
        }

        $config->setValue($value);
        if ($config->getId() === null) {
            $config->setName($name);
            $this->getRepository()->save($config);
        } else {
            $this->getRepository()->update($config);
        }

        return $config;
    }

    /**
     * Returns configuration entity with provided name.
     *
     * @param string $name Configuration property name.
     * @param bool $isContextSpecific Configuration flag that indicates whether property is context specific.
     *
     * @return ConfigEntity Configuration entity, if found; otherwise, null;
     * @throws QueryFilterInvalidParamException
     */
    protected function getConfigEntity($name, $isContextSpecific = true)
    {
        $filter = new QueryFilter();
        /**
        * @noinspection PhpUnhandledExceptionInspection
        */
        $filter->where('name', '=', $name);
        if ($isContextSpecific) {
            /**
            * @noinspection PhpUnhandledExceptionInspection
            */
            $filter->where('context', '=', $this->getContext());
        }

        /**
        * @var ConfigEntity $config
        */
        $config = $this->getRepository()->selectOne($filter);

        return $config;
    }

    /**
    * @noinspection PhpDocMissingThrowsInspection
    */
    /**
     * Returns repository instance.
     *
     * @return RepositoryInterface Configuration repository.
     */
    protected function getRepository()
    {
        /**
        * @noinspection PhpUnhandledExceptionInspection
        */
        return RepositoryRegistry::getRepository(ConfigEntity::getClassName());
    }
}
