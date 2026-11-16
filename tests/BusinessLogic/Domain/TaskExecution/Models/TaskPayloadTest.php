<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Models;

use SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions\InvalidTaskPayloadException;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskPayload;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Runner\RunnerTask;
use SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Runner\UnbuildableTask;

/**
 * @package SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Models
 */
class TaskPayloadTest extends BaseTestCase
{
    public function testBuildsThePayloadFromAJobAndItsStore(): void
    {
        $payload = TaskPayload::fromTask(new RunnerTask(), 'store-7')->toArray();

        self::assertSame(
            [
                'task_class' => RunnerTask::class,
                'task_data' => ['orderRef' => 'ORD-1'],
                'context' => 'store-7',
            ],
            $payload
        );
    }

    public function testRebuildsTheJobAfterAJsonRoundTrip(): void
    {
        $array = TaskPayload::fromTask(new RunnerTask(), 'store-7')->toArray();

        $task = TaskPayload::fromArray(json_decode(json_encode($array), true))->createTask();

        self::assertInstanceOf(RunnerTask::class, $task);
        self::assertSame(['orderRef' => 'ORD-1'], $task->toArray());
    }

    public function testKeepsTheContextOfTheEmptyStore(): void
    {
        $array = TaskPayload::fromTask(new RunnerTask(), '')->toArray();

        self::assertSame('', TaskPayload::fromArray($array)->getContext());
    }

    /**
     * @dataProvider invalidPayloads
     *
     * @param array<string, mixed> $changes
     * @param string[] $removed
     */
    public function testRejectsAnInvalidPayload(array $changes, array $removed): void
    {
        $data = array_merge(TaskPayload::fromTask(new RunnerTask(), 'store-7')->toArray(), $changes);
        foreach ($removed as $key) {
            unset($data[$key]);
        }

        try {
            TaskPayload::fromArray($data);
            self::fail('InvalidTaskPayloadException was expected.');
        } catch (InvalidTaskPayloadException $e) {
            self::assertSame(400, $e->getCode());
            self::assertSame(
                'general.errors.taskExecution.invalidTaskPayload',
                $e->getTranslatableLabel()->getCode()
            );
        }
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string[]}>
     */
    public function invalidPayloads(): array
    {
        return [
            'task_class missing' => [[], ['task_class']],
            'task_data missing' => [[], ['task_data']],
            'context missing' => [[], ['context']],
            'task_class not a string' => [['task_class' => 123], []],
            'task_data not an array' => [['task_data' => 'ORD-1'], []],
            'context not a string' => [['context' => 7], []],
            'task_class does not exist' => [['task_class' => 'SeQura\Core\Tests\DoesNotExist'], []],
            'task_class is not a business job' => [['task_class' => \stdClass::class], []],
        ];
    }

    public function testIgnoresExtraTopLevelKeys(): void
    {
        $data = TaskPayload::fromTask(new RunnerTask(), 'store-7')->toArray();
        $data['extra'] = 'x';

        self::assertSame('store-7', TaskPayload::fromArray($data)->getContext());
    }

    public function testWrapsAFailureOfTheJobRebuildKeepingTheOriginal(): void
    {
        $payload = TaskPayload::fromArray([
            'task_class' => UnbuildableTask::class,
            'task_data' => [],
            'context' => '',
        ]);

        try {
            $payload->createTask();
            self::fail('InvalidTaskPayloadException was expected.');
        } catch (InvalidTaskPayloadException $e) {
            self::assertSame(UnbuildableTask::$error, $e->getPrevious());
        }
    }
}
