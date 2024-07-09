<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class TaskRunnerStatus
 *
 * @package SeQura\Core\Infrastructure\TaskExecution
 */
class TaskRunnerStatus
{
    /**
     * Maximal time allowed for runner instance to stay in alive (running) status in seconds
     */
    const MAX_ALIVE_TIME = 15;
    /**
     * Identifier of task runner.
     *
     * @var string
     */
    protected $guid;
    /**
     * Timestamp since task runner is alive.
     *
     * @var int|null
     */
    protected $aliveSinceTimestamp;
    /**
     * Time provider service instance.
     *
     * @var TimeProvider
     */
    protected $timeProvider;
    /**
     * Configuration service instance.
     *
     * @var Configuration
     */
    protected $configService;

    /**
     * TaskRunnerStatus constructor.
     *
     * @param string $guid Runner instance identifier.
     * @param int $aliveSinceTimestamp Timestamp of last alive moment.
     */
    public function __construct($guid, $aliveSinceTimestamp)
    {
        $this->guid = $guid;
        $this->aliveSinceTimestamp = $aliveSinceTimestamp;
        $this->timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
        $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
    }

    /**
     * Creates empty status object.
     *
     * @return TaskRunnerStatus Empty status object.
     */
    public static function createNullStatus()
    {
        return new self('', null);
    }

    /**
     * Gets runner instance identifier.
     *
     * @return string Instance identifier.
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * Gets timestamp since runner is in alive status.
     *
     * @return int|null Timestamp since runner is in alive status; otherwise, NULL.
     */
    public function getAliveSinceTimestamp()
    {
        return $this->aliveSinceTimestamp;
    }

    /**
     * Checks if task is expired.
     *
     * @return bool TRUE if task expired; otherwise, FALSE.
     */
    public function isExpired()
    {
        $currentTimestamp = $this->timeProvider->getCurrentLocalTime()->getTimestamp();

        return $this->aliveSinceTimestamp > 0 &&
            ($this->aliveSinceTimestamp + $this->getMaxAliveTimestamp() < $currentTimestamp);
    }

    /**
     * Retrieves max alive timestamp.
     *
     * @return int Max alive timestamp.
     */
    protected function getMaxAliveTimestamp()
    {
        $configurationValue = $this->configService->getTaskRunnerMaxAliveTime();

        return $configurationValue !== null ? $configurationValue : self::MAX_ALIVE_TIME;
    }
}
