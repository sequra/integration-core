<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Models;

use SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions\InvalidTaskExecutionConfigException;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskExecutionConfig;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

class TaskExecutionConfigTest extends BaseTestCase
{
    public function testExposesConstructorValues(): void
    {
        $config = new TaskExecutionConfig('order-reports-store-7', 50, 'store-7');

        self::assertSame('order-reports-store-7', $config->getQueueName());
        self::assertSame(50, $config->getPriority());
        self::assertSame('store-7', $config->getContext());
    }

    /**
     * @dataProvider boundaryPriorities
     */
    public function testAcceptsBoundaryPriorities(int $priority): void
    {
        self::assertSame($priority, (new TaskExecutionConfig('queue', $priority, 'store'))->getPriority());
    }

    /**
     * @return array<string, array{int}>
     */
    public function boundaryPriorities(): array
    {
        return ['lowest' => [0], 'highest' => [100]];
    }

    /**
     * @dataProvider outOfRangePriorities
     */
    public function testRejectsOutOfRangePriority(int $priority): void
    {
        $exception = $this->capture(function () use ($priority) {
            new TaskExecutionConfig('queue', $priority, 'store');
        });

        self::assertSame(400, $exception->getCode());
        self::assertSame(
            'general.errors.taskExecution.priorityOutOfRange',
            $exception->getTranslatableLabel()->getCode()
        );
    }

    /**
     * @return array<string, array{int}>
     */
    public function outOfRangePriorities(): array
    {
        return ['below' => [-1], 'above' => [101]];
    }

    /**
     * @dataProvider blankQueueNames
     */
    public function testRejectsBlankQueueName(string $queueName): void
    {
        $exception = $this->capture(function () use ($queueName) {
            new TaskExecutionConfig($queueName, 50, 'store');
        });

        self::assertSame(400, $exception->getCode());
        self::assertSame(
            'general.errors.taskExecution.emptyQueueName',
            $exception->getTranslatableLabel()->getCode()
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public function blankQueueNames(): array
    {
        return ['empty' => [''], 'whitespace' => ['   ']];
    }

    public function testAcceptsQueueNameZero(): void
    {
        self::assertSame('0', (new TaskExecutionConfig('0', 50, 'store'))->getQueueName());
    }

    public function testAcceptsEmptyContext(): void
    {
        $config = new TaskExecutionConfig('order-cleanup', 1, '');

        self::assertSame('', $config->getContext());
    }

    public function testRejectsBothInvalidQueueAndPriorityWith400(): void
    {
        $exception = $this->capture(function () {
            new TaskExecutionConfig('', 101, 'store-7');
        });

        self::assertSame(400, $exception->getCode());
    }

    public function testExceptionIsTranslatable(): void
    {
        $exception = $this->capture(function () {
            new TaskExecutionConfig('', 50, '');
        });

        self::assertInstanceOf(BaseTranslatableException::class, $exception);
    }

    private function capture(callable $callback): InvalidTaskExecutionConfigException
    {
        try {
            $callback();
        } catch (InvalidTaskExecutionConfigException $exception) {
            return $exception;
        }

        self::fail('InvalidTaskExecutionConfigException was not thrown.');
    }
}
