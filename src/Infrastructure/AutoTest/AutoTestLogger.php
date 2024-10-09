<?php

namespace SeQura\Core\Infrastructure\AutoTest;

use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\LogData;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\Singleton;

/**
 * Class AutoTestLogger.
 *
 * @package SeQura\Core\Infrastructure\AutoConfiguration
 */
class AutoTestLogger extends Singleton implements ShopLoggerAdapter
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * @inheritDoc
     */
    public function logMessage(LogData $data)
    {
        $repo = RepositoryRegistry::getRepository(LogData::CLASS_NAME);
        $repo->save($data);
    }

    /**
     * Gets all log entities.
     *
     * @return LogData[] An array of the LogData entities, if any.
     *
     * @throws RepositoryNotRegisteredException
     */
    public function getLogs()
    {
        return array_filter(
            RepositoryRegistry::getRepository(LogData::CLASS_NAME)->select(),
            function ($log) {
                return $log instanceof LogData;
            }
        );
    }

    /**
     * Transforms logs to the plain array.
     *
     * @return mixed[] An array of logs.
     *
     * @throws RepositoryNotRegisteredException
     */
    public function getLogsArray()
    {
        $result = array();
        foreach ($this->getLogs() as $log) {
            $result[] = $log->toArray();
        }

        return $result;
    }
}
