<?php

/**
 * @noinspection PhpDocMissingThrowsInspection
*/

/**
 * @noinspection PhpUnusedParameterInspection
*/

namespace SeQura\Core\Infrastructure\Configuration;

use SeQura\Core\Infrastructure\Http\DTO\Options;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\Singleton;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;

/**
 * Class Configuration.
 *
 * @package SeQura\Core\Infrastructure\Configuration
 */
abstract class Configuration extends Singleton
{
    /**
     * Fully qualified name of this interface.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Minimal log level
     */
    const MIN_LOG_LEVEL = 3;
    /**
     * Default maximum number of tasks that can run in the same time
     */
    const DEFAULT_MAX_STARTED_TASK_LIMIT = 64;
    /**
     * Default HTTP method to use for async call.
     */
    const ASYNC_CALL_METHOD = 'POST';
    /**
     * Default batch size for the asynchronous execution.
     */
    const DEFAULT_ASYNC_STARTER_BATCH_SIZE = 8;

    /**
     * List of global (non-user specific) values
     *
     * @var string[]
     */
    protected static $globalConfigValues = array(
        'taskRunnerStatus',
        'isTaskRunnerHalted',
        'maxTaskInactivityPeriod',
        'maxTaskExecutionRetries',
        'maxStartedTasksLimit',
        'taskRunnerMaxAliveTime',
        'taskRunnerWakeupDelay',
        'asyncStarterBatchSize',
        'asyncRequestTimeout',
        'syncRequestTimeout',
        'asyncRequestWithProgress',
    );

    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
    /**
     * Instance of the configuration manager.
     *
     * @var ConfigurationManager Configuration manager.
     */
    protected $configurationManager;

    /**
     * Retrieves integration name.
     *
     * @return string Integration name.
     */
    abstract public function getIntegrationName();

    /**
     * Returns async process starter url, always in http.
     *
     * @param string $guid Process identifier.
     *
     * @return string Formatted URL of async process starter endpoint.
     */
    abstract public function getAsyncProcessUrl($guid);

    /**
     * Saves min log level in integration database.
     *
     * @param int $minLogLevel Min log level.
     */
    public function saveMinLogLevel($minLogLevel)
    {
        $this->saveConfigValue('minLogLevel', $minLogLevel);
    }

    /**
     * Retrieves min log level from integration database.
     *
     * @return int Min log level.
     */
    public function getMinLogLevel()
    {
        return $this->getConfigValue('minLogLevel', static::MIN_LOG_LEVEL);
    }

    /**
     * Set default logger status (enabled/disabled).
     *
     * @param bool $status TRUE if default logger is enabled; otherwise, false.
     */
    public function setDefaultLoggerEnabled($status)
    {
        $this->saveConfigValue('defaultLoggerEnabled', $status);
    }

    /**
     * Return whether default logger is enabled or not.
     *
     * @return bool TRUE if default logger is enabled; otherwise, false.
     */
    public function isDefaultLoggerEnabled()
    {
        return $this->getConfigValue('defaultLoggerEnabled', false);
    }

    /**
     * Sets debug mode status (enabled/disabled).
     *
     * @param bool $status TRUE if debug mode is enabled; otherwise, false.
     */
    public function setDebugModeEnabled($status)
    {
        $this->saveConfigValue('debugModeEnabled', (bool)$status);
    }

    /**
     * Retrieves debug mode status (enabled/disabled).
     *
     * @return bool
     */
    public function getDebugModeEnabled()
    {
        return $this->getConfigValue('debugModeEnabled', false);
    }

    /**
     * Returns debug mode status.
     *
     * @return bool TRUE if debug mode is enabled; otherwise, false.
     */
    public function isDebugModeEnabled()
    {
        return $this->getConfigValue('debugModeEnabled', false);
    }

    /**
     * Gets the number of maximum allowed started task at the point in time. This number will determine how many tasks
     * can be in "in_progress" status at the same time.
     *
     * @return int Max started tasks limit.
     */
    public function getMaxStartedTasksLimit()
    {
        return $this->getConfigValue('maxStartedTasksLimit', static::DEFAULT_MAX_STARTED_TASK_LIMIT);
    }

    /**
     * Sets the number of maximum allowed started task at the point in time. This number will determine how many tasks
     * can be in "in_progress" status at the same time.
     *
     * @param int $limit Max started tasks limit.
     */
    public function setMaxStartedTasksLimit($limit)
    {
        $this->saveConfigValue('maxStartedTasksLimit', $limit);
    }

    /**
     * Retrieves async starter batch size.
     *
     * @return int Async starter batch size.
     */
    public function getAsyncStarterBatchSize()
    {
        return $this->getConfigValue('asyncStarterBatchSize', static::DEFAULT_ASYNC_STARTER_BATCH_SIZE);
    }

    /**
     * Sets async process batch size.
     *
     * @param int $size
     */
    public function setAsyncStarterBatchSize($size)
    {
        $this->saveConfigValue('asyncStarterBatchSize', $size);
    }

    /**
     * Automatic task runner wakeup delay in seconds. Task runner will sleep at the end of its lifecycle for this value
     * seconds before it sends wakeup signal for a new lifecycle. Return null to use default system value (10).
     *
     * @return int|null Task runner wakeup delay in seconds if set; otherwise, null.
     */
    public function getTaskRunnerWakeupDelay()
    {
        return $this->getConfigValue('taskRunnerWakeupDelay');
    }

    /**
     * Sets task runner wakeup delay.
     *
     * @param int $delay Delay in seconds.
     */
    public function setTaskRunnerWakeupDelay($delay)
    {
        $this->saveConfigValue('taskRunnerWakeupDelay', $delay);
    }

    /**
     * Gets maximal time in seconds allowed for runner instance to stay in alive (running) status. After this period
     * system will automatically start new runner instance and shutdown old one. Return null to use default system
     * value (60).
     *
     * @return int|null Task runner max alive time in seconds if set; otherwise, null;
     */
    public function getTaskRunnerMaxAliveTime()
    {
        return $this->getConfigValue('taskRunnerMaxAliveTime');
    }

    /**
     * Sets max alive time.
     *
     * @param int $maxAliveTime Max alive time in seconds.
     */
    public function setTaskRunnerMaxAliveTime($maxAliveTime)
    {
        $this->saveConfigValue('taskRunnerMaxAliveTime', $maxAliveTime);
    }

    /**
     * Gets maximum number of failed task execution retries. System will retry task execution in case of error until
     * this number is reached. Return null to use default system value (5).
     *
     * @return int|null Number of max execution retries if set; otherwise, false.
     */
    public function getMaxTaskExecutionRetries()
    {
        return $this->getConfigValue('maxTaskExecutionRetries');
    }

    /**
     * Sets max task execution retries.
     *
     * @param int $maxRetries Max number of retries.
     */
    public function setMaxTaskExecutionRetries($maxRetries)
    {
        $this->saveConfigValue('maxTaskExecutionRetries', $maxRetries);
    }

    /**
     * Gets max inactivity period for a task in seconds. After inactivity period is passed, system will fail such tasks
     * as expired. Return null to use default system value (30).
     *
     * @return int|null Max task inactivity period in seconds if set; otherwise, null.
     */
    public function getMaxTaskInactivityPeriod()
    {
        return $this->getConfigValue('maxTaskInactivityPeriod');
    }

    /**
     * Sets max task inactivity period.
     *
     * @param int $maxInactivityPeriod Max inactivity period in seconds.
     */
    public function setMaxTaskInactivityPeriod($maxInactivityPeriod)
    {
        $this->saveConfigValue('maxTaskInactivityPeriod', $maxInactivityPeriod);
    }

    /**
     * Returns task runner status information
     *
     * @return array Guid and timestamp information
     */
    public function getTaskRunnerStatus()
    {
        return $this->getConfigValue('taskRunnerStatus', array());
    }

    /**
     * Sets task runner status information as JSON encoded string.
     *
     * @param string $guid Global unique identifier.
     * @param int $timestamp Timestamp.
     *
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    public function setTaskRunnerStatus($guid, $timestamp)
    {
        $taskRunnerStatus = array('guid' => $guid, 'timestamp' => $timestamp);
        $config = $this->saveConfigValue('taskRunnerStatus', $taskRunnerStatus);

        if (!$config || !$config->getId()) {
            throw new TaskRunnerStatusStorageUnavailableException('Task runner status storage is not available.');
        }
    }

    /**
     * Gets current auto-configuration state.
     *
     * @return string Current state.
     */
    public function getAutoConfigurationState()
    {
        return $this->getConfigValue('autoConfigurationState', '');
    }

    /**
     * Gets auto-configuration controller URL.
     *
     * @return string Auto-configuration URL.
     */
    public function getAutoConfigurationUrl()
    {
        return $this->getAsyncProcessUrl('auto-configure');
    }

    /**
     * Sets current auto-configuration state.
     *
     * @param string $state Current state.
     */
    public function setAutoConfigurationState($state)
    {
        $this->saveConfigValue('autoConfigurationState', $state);
    }

    /**
     * Gets current HTTP configuration options for given domain.
     *
     * @param string $domain A domain for which to return configuration options.
     *
     * @return Options[]
     */
    public function getHttpConfigurationOptions($domain)
    {
        $data = json_decode($this->getConfigValue('httpConfigurationOptions', '[]'), true);
        if (isset($data[$domain])) {
            return Options::fromBatch($data[$domain]);
        }

        return array();
    }

    /**
     * Sets HTTP configuration options for given domain.
     *
     * @param string $domain A domain for which to save configuration options.
     * @param Options[] $options HTTP configuration options
     */
    public function setHttpConfigurationOptions($domain, array $options)
    {
        // get all current options and append new ones for given domain
        $data = json_decode($this->getConfigValue('httpConfigurationOptions', '[]'), true);
        $data[$domain] = array();
        foreach ($options as $option) {
            $data[$domain][] = $option->toArray();
        }

        $this->saveConfigValue('httpConfigurationOptions', json_encode($data));
    }

    /**
     * Sets the auto-test mode flag.
     *
     * @param bool $status
     */
    public function setAutoTestMode($status)
    {
        $this->saveConfigValue('autoTestMode', $status);
    }

    /**
     * Returns whether the auto-test mode is active.
     *
     * @return bool TRUE if the auto-test mode is active; otherwise, FALSE.
     */
    public function isAutoTestMode()
    {
        return (bool)$this->getConfigValue('autoTestMode', false);
    }

    /**
     * Sets the HTTP method to be used for the async call.
     *
     * @param string $method Http method (GET or POST).
     */
    public function setAsyncProcessCallHttpMethod($method)
    {
        $this->saveConfigValue('asyncProcessCallHttpMethod', $method);
    }

    /**
     * Returns current HTTP method used for the async call.
     *
     * @return string The async call HTTP method (GET or POST).
     */
    public function getAsyncProcessCallHttpMethod()
    {
        return $this->getConfigValue('asyncProcessCallHttpMethod', static::ASYNC_CALL_METHOD);
    }

    /**
     * Retrieves config value that indicates whether task runner is halted or not.
     *
     * @return bool Task runner halted status.
     */
    public function isTaskRunnerHalted()
    {
        return (bool)$this->getConfigValue('isTaskRunnerHalted', false);
    }

    /**
     * Returns async process timeout in milliseconds.
     *
     * @return int|null
     */
    public function getAsyncRequestTimeout()
    {
        return $this->getConfigValue('asyncRequestTimeout');
    }

    /**
     * Sets async process timeout in milliseconds.
     *
     * @param int $timeout
     */
    public function setAsyncRequestTimeout($timeout)
    {
        $this->saveConfigValue('asyncRequestTimeout', $timeout);
    }

    /**
     * Returns synchronous process timeout in milliseconds.
     *
     * @return int|null
     */
    public function getSyncRequestTimeout()
    {
        return $this->getConfigValue('syncRequestTimeout');
    }

    /**
     * Sets synchronous process timeout in milliseconds.
     *
     * @param int $timeout
     */
    public function setSyncRequestTimeout($timeout)
    {
        $this->saveConfigValue('syncRequestTimeout', $timeout);
    }

    /**
     * Sets config value for task runner halted flag.
     *
     * @param bool $isHalted Flag that indicates whether task runner is halted.
     */
    public function setTaskRunnerHalted($isHalted)
    {
        $this->saveConfigValue('isTaskRunnerHalted', $isHalted);
    }

    /**
     * Save config value.
     *
     * @param string $name Name of the configuration value.
     * @param mixed $value Configuration value.
     *
     * @return ConfigEntity
     */
    protected function saveConfigValue($name, $value)
    {
        /**
        * @noinspection PhpUnhandledExceptionInspection
        */
        return $this->getConfigurationManager()->saveConfigValue($name, $value, $this->isContextSpecific($name));
    }

    /**
     * Retrieves saved config value.
     *
     * @param string $name Config value name.
     * @param mixed $default Default config value.
     *
     * @return mixed Config value.
     */
    protected function getConfigValue($name, $default = null)
    {
        /**
        * @noinspection PhpUnhandledExceptionInspection
        */
        return $this->getConfigurationManager()->getConfigValue($name, $default, $this->isContextSpecific($name));
    }

    /**
     * Determines whether the configuration entry is system specific.
     *
     * @param string $name Configuration entry name.
     *
     * @return bool
     */
    protected function isContextSpecific($name)
    {
        return !in_array($name, static::$globalConfigValues, true);
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager Configuration manager instance.
     */
    protected function getConfigurationManager()
    {
        if ($this->configurationManager === null) {
            $this->configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);
        }

        return $this->configurationManager;
    }
}
