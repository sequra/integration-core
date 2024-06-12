<?php

namespace SeQura\Core\Tests\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueItemTest
 *
 * @package SeQura\Core\Tests\Infrastructure\TaskExecution
 */
class QueueItemTest extends TestCase
{
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider */
    private $timeProvider;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $timeProvider = new TestTimeProvider();

        new TestServiceRegister(
            array(
                TimeProvider::CLASS_NAME => function () use ($timeProvider) {
                    return $timeProvider;
                },
                Serializer::CLASS_NAME => function () {
                    return new NativeSerializer();
                }
            )
        );

        $this->timeProvider = $timeProvider;
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \Exception
     */
    public function testWhenQueueItemIsCreatedItShouldBeInCreatedStatus()
    {
        $task = new FooTask();
        $queueItem = new QueueItem($task);

        $this->assertEquals(
            QueueItem::CREATED,
            $queueItem->getStatus(),
            'When created queue item must set status to "created".'
        );
        $this->assertEquals(
            $task->getType(),
            $queueItem->getTaskType(),
            'When created queue item must set record task type.'
        );
        $this->assertNull($queueItem->getId(), 'When created queue item should not be in storage. Id must be null.');
        $this->assertNull(
            $queueItem->getQueueName(),
            'When created queue should not be in storage. Queue name must be null.'
        );
        $this->assertSame(
            0,
            $queueItem->getLastExecutionProgressBasePoints(),
            'When created queue item must set last execution progress to 0.'
        );
        $this->assertSame(0, $queueItem->getProgressBasePoints(), 'When created queue item must set progress to 0.');
        $this->assertSame(0, $queueItem->getRetries(), 'When created queue item must set retries to 0.');
        $this->assertSame(
            '',
            $queueItem->getFailureDescription(),
            'When created queue item must set failure description to empty string.'
        );
        $this->assertEquals(
            Serializer::serialize($task),
            $queueItem->getSerializedTask(),
            'When created queue item must record given task.'
        );
        $this->assertSame(
            $this->timeProvider->getCurrentLocalTime()->getTimestamp(),
            $queueItem->getCreateTimestamp(),
            'When created queue item must record create time.'
        );
        $this->assertNull($queueItem->getQueueTimestamp(), 'When created queue item must set queue time to null.');
        $this->assertNull($queueItem->getStartTimestamp(), 'When created queue item must set start time to null.');
        $this->assertNull($queueItem->getFinishTimestamp(), 'When created queue item must set finish time to null.');
        $this->assertNull($queueItem->getFailTimestamp(), 'When created queue item must set fail time to null.');
        $this->assertNull(
            $queueItem->getEarliestStartTimestamp(),
            'When created queue item must set earliest start time to null.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function testItShouldBePossibleToCreateQueueItemWithSerializedTask()
    {
        $task = new FooTask('test task', 123);
        $queueItem = new QueueItem();

        $queueItem->setSerializedTask(Serializer::serialize($task));

        /** @var FooTask $actualTask */
        $actualTask = $queueItem->getTask();
        $this->assertSame($task->getDependency1(), $actualTask->getDependency1());
        $this->assertSame($task->getDependency2(), $actualTask->getDependency2());
    }

    public function testQueueItemShouldThrowExceptionWhenSerializationFails()
    {
        $this->expectException(QueueItemDeserializationException::class);

        $task = new FooTask('test task', 123);
        $queueItem = new QueueItem();

        $queueItem->setSerializedTask('invalid serialized task content');

        /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask $actualTask */
        $actualTask = $queueItem->getTask();
        $this->assertSame($task->getDependency1(), $actualTask->getDependency1());
        $this->assertSame($task->getDependency2(), $actualTask->getDependency2());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function testItShouldUpdateTaskWhenSettingSerializedTask()
    {
        $newTask = new FooTask('new task', 123);
        $queueItem = new QueueItem(new FooTask('initial task', 1));

        $queueItem->setSerializedTask(Serializer::serialize($newTask));

        /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask $actualTask */
        $actualTask = $queueItem->getTask();
        $this->assertSame(
            'new task',
            $actualTask->getDependency1(),
            'Setting serialized task must update task instance.'
        );
        $this->assertSame(123, $actualTask->getDependency2(), 'Setting serialized task must update task instance.');
    }

    public function testItShouldNotBePossibleToSetNotSupportedStatus()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid QueueItem status: "Not supported". Status must be one of "created", "queued", "in_progress", ' .
            '"completed", "failed" or "aborted" values.'
        );

        $queueItem = new QueueItem();

        $queueItem->setStatus('Not supported');

        $this->fail('Setting not supported status should fail.');
    }

    public function testItShouldNotBePossibleToSetNegativeLastExecutionProgress()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Last execution progress percentage must be value between 0 and 100.');

        $queueItem = new QueueItem();

        $queueItem->setLastExecutionProgressBasePoints(-1);

        $this->fail('QueueItem must refuse setting negative last execution progress with InvalidArgumentException.');
    }

    public function testItShouldNotBePossibleToSetMoreThan10000ForLastExecutionProgress()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Last execution progress percentage must be value between 0 and 100.');

        $queueItem = new QueueItem();

        $queueItem->setLastExecutionProgressBasePoints(10001);

        $this->fail(
            'QueueItem must refuse setting greater than 100 last execution progress values with InvalidArgumentException.'
        );
    }

    public function testItShouldNotBePossibleToSetNegativeProgress()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Progress percentage must be value between 0 and 100.');

        $queueItem = new QueueItem();

        $queueItem->setProgressBasePoints(-1);

        $this->fail('QueueItem must refuse setting negative progress with InvalidArgumentException.');
    }

    public function testItShouldNotBePossibleToSetMoreThan100ForProgress()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Progress percentage must be value between 0 and 100.');

        $queueItem = new QueueItem();

        $queueItem->setProgressBasePoints(10001);

        $this->fail('QueueItem must refuse setting greater than 100 progress values with InvalidArgumentException.');
    }

    public function testItShouldBePossibleToGetFormattedProgressValue()
    {
        $queueItem = new QueueItem();

        $queueItem->setProgressBasePoints(2548);

        $this->assertSame(
            25.48,
            $queueItem->getProgressFormatted(),
            'Formatted progress should be string representation of progress percentage rounded to two decimals.'
        );
    }

    public function testItShouldNotBePossibleToReportNonIntegerValueForProgress()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Progress percentage must be value between 0 and 100.');

        $queueItem = new QueueItem();

        $queueItem->setProgressBasePoints('50%');

        $this->fail('QueueItem must refuse setting non integer progress values with InvalidArgumentException.');
    }

    public function testItShouldBePossibleToSetTaskExecutionContext()
    {
        $queueItem = new QueueItem();

        $queueItem->setContext('test');

        $this->assertSame('test', $queueItem->getContext(), 'Queue item must return proper task execution context.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function testQueueItemIdToTask()
    {
        $task = new FooTask('test task', 123);
        $queueItem = new QueueItem($task);
        $queueItem->setId(27);

        self::assertEquals(27, $task->getExecutionId());

        /** @var FooTask $actualTask */
        $actualTask = $queueItem->getTask();
        self::assertEquals(27, $actualTask->getExecutionId());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function testQueueItemIdToSerializedTask()
    {
        $task = new FooTask('test task', 123);
        $queueItem = new QueueItem();
        $queueItem->setId(27);

        $queueItem->setSerializedTask(Serializer::serialize($task));

        /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask $actualTask */
        $actualTask = $queueItem->getTask();

        self::assertEquals(27, $actualTask->getExecutionId());
    }

    /**
     * Asserts that valid queue item priorities are properly set.
     */
    public function testQueueItemSetValidPriority()
    {
        $queueItem = new QueueItem();

        $queueItem->setPriority(Priority::LOW);
        $this->assertEquals(Priority::LOW, $queueItem->getPriority());

        $queueItem->setPriority(Priority::NORMAL);
        $this->assertEquals(Priority::NORMAL, $queueItem->getPriority());

        $queueItem->setPriority(Priority::HIGH);
        $this->assertEquals(Priority::HIGH, $queueItem->getPriority());
    }

    /**
     * Asserts that exception is thrown when invalid priority is set.
     */
    public function testQueueItemSetIvalidPriority()
    {
        $this->expectException(\InvalidArgumentException::class);

        $queueItem = new QueueItem();
        $queueItem->setPriority(2);
    }
}
