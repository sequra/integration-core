<?php

namespace SeQura\Core\Infrastructure\AutoTest;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Exceptions\StorageNotAccessibleException;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\LogData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use Exception;

/**
 * Class AutoTestService.
 *
 * @package SeQura\Core\Infrastructure\AutoTest
 */
class AutoTestService
{
    /**
     * Configuration service instance.
     *
     * @var Configuration
     */
    protected $configService;

    /**
     * Starts the auto-test.
     *
     * @return int The queue item ID.
     *
     * @throws StorageNotAccessibleException
     * @throws QueueStorageUnavailableException
     */
    public function startAutoTest()
    {
        try {
            $this->setAutoTestMode(true);
            $this->deletePreviousLogs();
            Logger::logInfo('Start auto-test');
        } catch (Exception $e) {
            throw new StorageNotAccessibleException('Cannot start the auto-test because storage is not accessible.');
        }

        $this->logHttpOptions();

        /**
        * @var QueueService $queueService
        */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);
        $queueItem = $queueService->enqueue('Auto-test', new AutoTestTask('DUMMY TEST DATA'));

        return $queueItem->getId();
    }

    /**
     * Activates the auto-test mode and registers the necessary components.
     *
     * @param bool $persist Indicates whether to store the mode change in configuration.
     */
    public function setAutoTestMode($persist = false): void
    {
        Logger::resetInstance();
        ServiceRegister::registerService(
            ShopLoggerAdapter::CLASS_NAME,
            function () {
                return AutoTestLogger::getInstance();
            }
        );

        if ($persist) {
            $this->getConfigService()->setAutoTestMode(true);
        }
    }

    /**
     * Gets the status of the auto-test task.
     *
     * @param int $queueItemId The ID of the queue item that started the task.
     *
     * @return AutoTestStatus The status of the auto-test task.
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    public function getAutoTestTaskStatus($queueItemId = 0)
    {
        $this->setAutoTestMode();

        $filter = new QueryFilter();
        if ($queueItemId) {
            $filter->where('id', Operators::EQUALS, $queueItemId);
        } else {
            $filter->where('taskType', Operators::EQUALS, 'AutoTestTask');
            $filter->orderBy('queueTime', 'DESC');
        }

        $status = '';
        $item = RepositoryRegistry::getQueueItemRepository()->selectOne($filter);
        if ($item) {
            if ($item->getStatus() === QueueItem::QUEUED && $item->getQueueTimestamp() < time() - 30) {
                // if item is queued and task runner did not start it within 30 seconds, task expired
                Logger::logError('Auto-test task did not finish within expected time frame.');

                $status = 'timeout';
            } else {
                $status = $item->getStatus();
            }
        }

        return new AutoTestStatus(
            $status,
            in_array($status, array('timeout', QueueItem::COMPLETED, QueueItem::FAILED), true),
            $status === 'timeout' ? 'Task could not be started.' : '',
            AutoTestLogger::getInstance()->getLogs()
        );
    }

    /**
     * Resets the auto-test mode.
     * When auto-test finishes, this is needed to reset the flag in configuration service and
     * re-initialize shop logger. Otherwise, logs and async calls will still use auto-test mode.
     *
     * @param callable $loggerInitializerDelegate Delegate that will give instance of the shop logger service.
     */
    public function stopAutoTestMode($loggerInitializerDelegate): void
    {
        $this->getConfigService()->setAutoTestMode(false);
        ServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, $loggerInitializerDelegate);
        Logger::resetInstance();
    }

    /**
     * Deletes previous auto-test logs.
     *
     * @throws RepositoryNotRegisteredException
     */
    protected function deletePreviousLogs(): void
    {
        $repo = RepositoryRegistry::getRepository(LogData::getClassName());
        $logs = $repo->select();
        foreach ($logs as $log) {
            $repo->delete($log);
        }
    }

    /**
     * Logs current HTTP configuration options.
     */
    protected function logHttpOptions(): void
    {
        $testDomain = parse_url($this->getConfigService()->getAsyncProcessUrl(''), PHP_URL_HOST);
        $options = array();
        foreach ($this->getConfigService()->getHttpConfigurationOptions($testDomain) as $option) {
            $options[$option->getName()] = $option->getValue();
        }

        Logger::logInfo(
            'HTTP configuration options',
            'Core',
            [new LogContextData($testDomain, ['HTTPOptions' => $options])]
        );
    }

    /**
     * Gets the configuration service instance.
     *
     * @return Configuration Configuration service instance.
     */
    protected function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
