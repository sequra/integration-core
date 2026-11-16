<?php

namespace SeQura\Core\Tests\BusinessLogic\TaskExecution;

use SeQura\Core\BusinessLogic\BootstrapComponent;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskExecutionConfig;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskMetadataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Services\DefaultTaskMetadataProvider;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\TaskRunnerExecutor;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\FixedTaskMetadataProvider;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\SampleBusinessTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;

class StandaloneBootstrapTest extends BaseTestCase
{
    public function testRegistersTheBuiltInRunnerAndDefaultMetadata(): void
    {
        BootstrapComponent::init();

        self::assertInstanceOf(TaskRunnerExecutor::class, ServiceRegister::getService(TaskExecutorInterface::class));
        self::assertInstanceOf(
            DefaultTaskMetadataProvider::class,
            ServiceRegister::getService(TaskMetadataProviderInterface::class)
        );
    }

    public function testPlatformExecutorRegisteredAfterInitReplacesTheDefault(): void
    {
        BootstrapComponent::init();
        $platformExecutor = new class implements TaskExecutorInterface {
            public function enqueue(BusinessTaskInterface $task): void
            {
            }

            public function scheduleDelayed(
                BusinessTaskInterface $task,
                int $delaySeconds
            ): void {
            }
        };

        ServiceRegister::registerService(TaskExecutorInterface::class, static function () use ($platformExecutor) {
            return $platformExecutor;
        });

        self::assertSame($platformExecutor, ServiceRegister::getService(TaskExecutorInterface::class));
    }

    public function testPlatformMetadataRegisteredAfterInitDrivesTheDefaultExecutor(): void
    {
        BootstrapComponent::init();
        QueueItemStateTransitionEventBus::resetInstance();
        $queue = new TestQueueService();
        ServiceRegister::registerService(QueueService::CLASS_NAME, static function () use ($queue) {
            return $queue;
        });
        ServiceRegister::registerService(TaskRunnerWakeup::CLASS_NAME, static function () {
            return new TestTaskRunnerWakeupService();
        });
        $metadata = new FixedTaskMetadataProvider(new TaskExecutionConfig('platform-queue', 90, 'store-9'));
        ServiceRegister::registerService(TaskMetadataProviderInterface::class, static function () use ($metadata) {
            return $metadata;
        });

        ServiceRegister::getService(TaskExecutorInterface::class)->enqueue(new SampleBusinessTask());

        $items = RepositoryRegistry::getQueueItemRepository()->select();
        self::assertCount(1, $items);
        self::assertSame('platform-queue', $items[0]->getQueueName());
        self::assertSame('store-9', $items[0]->getContext());
        self::assertSame(Priority::HIGH, $items[0]->getPriority());
    }
}
