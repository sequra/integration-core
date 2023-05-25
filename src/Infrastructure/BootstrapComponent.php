<?php

namespace SeQura\Core\Infrastructure;

use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Http\CurlHttpClient;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\LoggingHttpclient;
use SeQura\Core\Infrastructure\TaskExecution\AsyncProcessStarterService;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerManager;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\TaskExecution\RunnerStatusStorage;
use SeQura\Core\Infrastructure\TaskExecution\TaskRunner;
use SeQura\Core\Infrastructure\TaskExecution\TaskRunnerWakeupService;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\GuidProvider;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class BootstrapComponent.
 *
 * @package SeQura\Core\Infrastructure
 */
class BootstrapComponent
{
    /**
     * Initializes infrastructure components.
     */
    public static function init()
    {
        static::initServices();
        static::initRepositories();
        static::initEvents();
    }

    /**
     * Initializes services and utilities.
     */
    protected static function initServices()
    {
        ServiceRegister::registerService(
            ConfigurationManager::CLASS_NAME,
            function () {
                return ConfigurationManager::getInstance();
            }
        );
        ServiceRegister::registerService(
            TimeProvider::CLASS_NAME,
            function () {
                return TimeProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            GuidProvider::CLASS_NAME,
            function () {
                return GuidProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            EventBus::CLASS_NAME,
            function () {
                return EventBus::getInstance();
            }
        );
        ServiceRegister::registerService(
            AsyncProcessService::CLASS_NAME,
            function () {
                return AsyncProcessStarterService::getInstance();
            }
        );
        ServiceRegister::registerService(
            QueueService::CLASS_NAME,
            function () {
                return new QueueService();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerWakeup::CLASS_NAME,
            function () {
                return new TaskRunnerWakeupService();
            }
        );
        ServiceRegister::registerService(
            TaskRunner::CLASS_NAME,
            function () {
                return new TaskRunner();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerStatusStorage::CLASS_NAME,
            function () {
                return new RunnerStatusStorage();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerManager::CLASS_NAME,
            function () {
                return new TaskExecution\TaskRunnerManager();
            }
        );
        ServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () {
                return new LoggingHttpclient(new CurlHttpClient());
            }
        );
        ServiceRegister::registerService(
            QueueItemStateTransitionEventBus::CLASS_NAME,
            function () {
                return QueueItemStateTransitionEventBus::getInstance();
            }
        );
    }

    /**
     * Initializes repositories.
     */
    protected static function initRepositories()
    {
    }

    /**
     * Initializes events.
     */
    protected static function initEvents()
    {
    }
}
