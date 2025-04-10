<?php

namespace SeQura\Core\Infrastructure\ORM;

use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class RepositoryRegistry.
 *
 * @package SeQura\Core\Infrastructure\ORM
 */
class RepositoryRegistry
{
    /**
     * @var RepositoryInterface[]
     */
    protected static $instantiated = array();
    /**
     * @var array<string,string>
     */
    protected static $repositories = array();

    /**
     * Returns an instance of repository that is responsible for handling the entity
     *
     * @param string $entityClass Class name of entity.
     *
     * @return RepositoryInterface
     *
     * @throws RepositoryNotRegisteredException
     */
    public static function getRepository(string $entityClass): RepositoryInterface
    {
        if (!static::isRegistered($entityClass)) {
            throw new RepositoryNotRegisteredException("Repository for entity $entityClass not found or registered.");
        }

        if (!array_key_exists($entityClass, static::$instantiated)) {
            $repositoryClass = static::$repositories[$entityClass];
            /**
             * @var RepositoryInterface $repository
            */
            $repository = new $repositoryClass();
            $repository->setEntityClass($entityClass);
            static::$instantiated[$entityClass] = $repository;
        }

        return static::$instantiated[$entityClass];
    }

    /**
     * Registers repository for provided entity class
     *
     * @param string $entityClass Class name of entity.
     * @param string $repositoryClass Class name of repository.
     *
     * @throws RepositoryClassException
     */
    public static function registerRepository(string $entityClass, string $repositoryClass): void
    {
        if (!is_subclass_of($repositoryClass, RepositoryInterface::CLASS_NAME)) {
            throw new RepositoryClassException("Class $repositoryClass is not implementation of RepositoryInterface.");
        }

        unset(static::$instantiated[$entityClass]);
        static::$repositories[$entityClass] = $repositoryClass;
    }

    /**
     * Checks whether repository has been registered for a particular entity.
     *
     * @param string $entityClass Entity for which check has to be performed.
     *
     * @return boolean Returns TRUE if repository has been registered; FALSE otherwise.
     */
    public static function isRegistered(string $entityClass): bool
    {
        return isset(static::$repositories[$entityClass]);
    }

    /**
     * Returns queue item repository.
     *
     * @return QueueItemRepository
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    public static function getQueueItemRepository(): QueueItemRepository
    {
        /**
         * @var QueueItemRepository $repository
        */
        $repository = static::getRepository(QueueItem::getClassName());
        if (!($repository instanceof QueueItemRepository)) {
            throw new RepositoryClassException('Instance class is not implementation of QueueItemRepository');
        }

        return $repository;
    }
}
