<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution;

use ReflectionClass;
use ReflectionMethod;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskMetadataProviderInterface;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Doubles\FixedTaskMetadataProvider;
use SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Doubles\KeepAliveBusinessTask;
use SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Doubles\RecordingTaskExecutor;
use SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Doubles\SilentBusinessTask;

class TaskContractsTest extends BaseTestCase
{
    public function testExecutorDeclaresOnlyEnqueueAndScheduleDelayed(): void
    {
        $methods = $this->methodNames(TaskExecutorInterface::class);

        self::assertSame(['enqueue', 'scheduleDelayed'], $methods);
        $reflection = new ReflectionClass(TaskExecutorInterface::class);
        self::assertSame('void', (string) $reflection->getMethod('enqueue')->getReturnType());
        self::assertSame('void', (string) $reflection->getMethod('scheduleDelayed')->getReturnType());
    }

    public function testExecutorImplementationIsResolvedFromServiceRegister(): void
    {
        $executor = new RecordingTaskExecutor();
        ServiceRegister::registerService(TaskExecutorInterface::class, function () use ($executor) {
            return $executor;
        });

        self::assertSame($executor, ServiceRegister::getService(TaskExecutorInterface::class));
    }

    public function testBusinessTaskExtendsNothingAndDeclaresThreeMethods(): void
    {
        $reflection = new ReflectionClass(BusinessTaskInterface::class);

        self::assertSame([], $reflection->getInterfaceNames());
        self::assertSame(['execute', 'toArray', 'fromArray'], $this->methodNames(BusinessTaskInterface::class));
        self::assertFalse($reflection->getMethod('execute')->hasReturnType());
        self::assertSame('array', (string) $reflection->getMethod('toArray')->getReturnType());
        self::assertTrue($reflection->getMethod('fromArray')->isStatic());
        self::assertSame(BusinessTaskInterface::class, (string) $reflection->getMethod('fromArray')->getReturnType());
    }

    public function testGeneratorYieldingAndSilentTasksImplementContract(): void
    {
        $yielding = new KeepAliveBusinessTask();
        $silent = new SilentBusinessTask();

        self::assertInstanceOf(BusinessTaskInterface::class, $yielding);
        self::assertInstanceOf(BusinessTaskInterface::class, $silent);
        self::assertSame([null, 50, 'syncing'], iterator_to_array($yielding->execute(), false));
        self::assertNull($silent->execute());
    }

    public function testMetadataProviderDeclaresOnlyGetExecutionConfig(): void
    {
        self::assertSame(['getExecutionConfig'], $this->methodNames(TaskMetadataProviderInterface::class));
    }

    public function testMetadataProviderImplementationIsResolvedFromServiceRegister(): void
    {
        $provider = new FixedTaskMetadataProvider();
        ServiceRegister::registerService(TaskMetadataProviderInterface::class, function () use ($provider) {
            return $provider;
        });

        self::assertSame($provider, ServiceRegister::getService(TaskMetadataProviderInterface::class));
    }

    /**
     * @param class-string $interface
     *
     * @return string[]
     */
    private function methodNames(string $interface): array
    {
        return array_map(function (ReflectionMethod $method) {
            return $method->getName();
        }, (new ReflectionClass($interface))->getMethods());
    }
}
