<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\AliveAnnouncedTaskEvent;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\TaskProgressEvent;
use SeQura\Core\Infrastructure\Utility\Events\EventEmitter;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use DateTime;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class Task
 *
 * @package SeQura\Core\Infrastructure\TaskExecution
 */
/** @phpstan-consistent-constructor */
abstract class Task extends EventEmitter implements Serializable
{
    /**
     * Max inactivity period for a task in seconds
     */
    const MAX_INACTIVITY_PERIOD = 300;
    /**
     * Minimal number of seconds that must pass between two alive signals
     */
    const ALIVE_SIGNAL_FREQUENCY = 2;
    /**
     * Time of last invoked alive signal.
     *
     * @var DateTime
     */
    protected $lastAliveSignalTime;
    /**
     * An instance of Configuration service.
     *
     * @var Configuration
     */
    protected $configService;
    /**
     * Task execution id.
     *
     * @var string|int
     */
    protected $executionId;

    public function __construct()
    {
    }

    /**
     * Runs task logic.
     *
     * @throws AbortTaskExecutionException
     */
    abstract public function execute(): void;

    /**
     * @inheritdoc
     */
    public function serialize(): ?string
    {
        return Serializer::serialize(array());
    }

    /**
     * @inheritdoc
     */
    public function unserialize($data)
    {
        // This method was intentionally left blank because
        // this task doesn't have any properties which needs to encapsulate.
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array): Serializable
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array();
    }

    /**
     * Retrieves task priority.
     *
     * @return int Task priority.
     */
    public function getPriority(): int
    {
        return Priority::NORMAL;
    }

    /**
     * Reports task progress by emitting progress percent
     *   Float representation of progress percentage, value between 0 and 100 that will immediately
     *   be converted to base points. One base point is equal to 0.01%. For example 23.58% is
     *   equal to 2358 base points
     *
     * @param float|int $progressPercent
     *
     * @throws InvalidArgumentException In case when progress percent is outside of 0 - 100 boundaries or not an float
     * @see    TaskProgressEvent and defers next @see AliveAnnouncedTaskEvent.
     */
    public function reportProgress($progressPercent): void
    {
        if (!is_int($progressPercent) && !is_float($progressPercent)) {
            throw new InvalidArgumentException('Progress percentage must be value integer or float value');
        }

        /**
         * @var TimeProvider $timeProvider
        */
        $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
        $this->lastAliveSignalTime = $timeProvider->getCurrentLocalTime();

        $this->fire(new TaskProgressEvent($this->percentToBasePoints($progressPercent)));
    }

    /**
     * Reports that task is alive by emitting
     *
     * @param boolean $force
     *
     * @see AliveAnnouncedTaskEvent.
     */
    public function reportAlive(bool $force = false): void
    {
        /**
         * @var TimeProvider $timeProvider
        */
        $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
        $currentTime = $timeProvider->getCurrentLocalTime();
        if (
            $force || $this->lastAliveSignalTime === null
            || $this->lastAliveSignalTime->getTimestamp() + self::ALIVE_SIGNAL_FREQUENCY < $currentTime->getTimestamp()
        ) {
            $this->fire(new AliveAnnouncedTaskEvent());
            $this->lastAliveSignalTime = $timeProvider->getCurrentLocalTime();
        }
    }

    /**
     * Gets max inactivity period for a task.
     *
     * @return int Max inactivity period for a task in seconds.
     */
    public function getMaxInactivityPeriod(): int
    {
        $configurationValue = $this->getConfigService()->getMaxTaskInactivityPeriod();

        return $configurationValue !== null ? $configurationValue : static::MAX_INACTIVITY_PERIOD;
    }

    /**
     * Gets name of the class.
     * Alias method for static method {@see self::getClassName()}
     *
     * @return string FQN of the task.
     */
    public function getType(): string
    {
        return static::getClassName();
    }

    /**
     * Gets name of the class.
     *
     * @return string FQN of the task.
     */
    public static function getClassName(): string
    {
        $namespaceParts = explode('\\', get_called_class());
        $name = end($namespaceParts);

        if ($name === 'Task') {
            throw new RuntimeException('Constant CLASS_NAME not defined in class ' . get_called_class());
        }

        return $name;
    }

    /**
     * Determines whether task can be reconfigured.
     *
     * @return bool TRUE if task can be reconfigured; otherwise, FALSE.
     */
    public function canBeReconfigured(): bool
    {
        return false;
    }

    /**
     * Reconfigures the task.
     */
    public function reconfigure(): void
    {
    }

    /**
     * Gets execution Id.
     *
     * @return string|int Execution Id.
     */
    public function getExecutionId()
    {
        return $this->executionId;
    }

    /**
     * Sets Execution id.
     *
     * @param int|string $executionId Execution id.
     */
    public function setExecutionId($executionId): void
    {
        $this->executionId = $executionId;
    }

    /**
     * Cleans up resources upon failure.
     */
    public function onFail(): void
    {
        // Extension stub.
    }

    /**
     * Cleans up resources upon abort.
     */
    public function onAbort(): void
    {
        // Extension stub.
    }

    /**
     * Calculates base points for progress tracking from percent value.
     *
     * @param float $percentValue Value in float representation.
     *
     * @return int Base points representation of percentage.
     */
    protected function percentToBasePoints(float $percentValue): int
    {
        return (int)round($percentValue * 100, 2);
    }

    /**
     * Gets Configuration service.
     *
     * @return Configuration Service instance.
     */
    protected function getConfigService(): Configuration
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
