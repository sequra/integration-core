<?php

namespace SeQura\Core\Tests\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\AliveAnnouncedTaskEvent;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\TaskProgressEvent;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class TaskTest
 *
 * @package SeQura\Core\Tests\Infrastructure\TaskExecution
 */
class TaskTest extends TestCase
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
                },
            )
        );

        $this->timeProvider = $timeProvider;
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testItShouldBePossibleToExecuteTask()
    {
        $task = new FooTask();

        $task->execute();

        $this->assertEquals(1, $task->getMethodCallCount('execute'));
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testItShouldBePossibleToGetTaskType()
    {
        $task = new FooTask();

        $task->execute();

        $this->assertEquals('FooTask', $task->getType());
    }

    public function testItShouldBePossibleToSerializeTask()
    {
        $task = new FooTask('test dependency', 123);

        /** @var FooTask $unserializedTask */
        $unserializedTask = Serializer::unserialize(Serializer::serialize($task));

        $this->assertInstanceOf('\Serializable', $unserializedTask);
        $this->assertSame('test dependency', $unserializedTask->getDependency1());
        $this->assertSame(123, $unserializedTask->getDependency2());
    }

    public function testItShouldBePossibleToReportThatTasksIsAlive()
    {
        // Arrange
        $task = new FooTask();

        /** @var AliveAnnouncedTaskEvent $aliveAnnouncedEvent */
        $aliveAnnouncedEvent = null;
        $task->when(
            AliveAnnouncedTaskEvent::CLASS_NAME,
            function (AliveAnnouncedTaskEvent $event) use (&$aliveAnnouncedEvent) {
                $aliveAnnouncedEvent = $event;
            }
        );

        // Act
        $task->reportAlive();

        // Assert
        $this->assertNotNull(
            $aliveAnnouncedEvent,
            'Task must fire AliveAnnouncedTaskEvent when reporting that it is alive.'
        );
    }

    public function testItShouldNotBePossibleToReportThatTasksIsAliveTooFrequently()
    {
        // Arrange
        $task = new FooTask();

        /** @var AliveAnnouncedTaskEvent $aliveAnnouncedEvent */
        $aliveAnnouncedEventCount = 0;
        $task->when(
            AliveAnnouncedTaskEvent::CLASS_NAME,
            function () use (&$aliveAnnouncedEventCount) {
                $aliveAnnouncedEventCount++;
            }
        );

        $task->reportAlive();

        // Act
        $task->reportAlive();

        // Assert
        $this->assertSame(
            1,
            $aliveAnnouncedEventCount,
            'Task must fire AliveAnnouncedTaskEvent only when alive signal frequency time is elapsed.'
        );
    }

    public function testReportingProgressShouldDeferNextAliveSignal()
    {
        // Arrange
        $task = new FooTask();

        /** @var AliveAnnouncedTaskEvent $aliveAnnouncedEvent */
        $aliveAnnouncedEventCount = 0;
        $task->when(
            AliveAnnouncedTaskEvent::CLASS_NAME,
            function () use (&$aliveAnnouncedEventCount) {
                $aliveAnnouncedEventCount++;
            }
        );

        $task->reportProgress(10);

        // Act
        $task->reportAlive();

        // Assert
        $this->assertSame(
            0,
            $aliveAnnouncedEventCount,
            'Reporting progress should defer next AliveAnnouncedTaskEvent.'
        );
    }

    public function testItShouldBeAbleToReportProgressOnTask()
    {
        // Arrange
        $task = new FooTask();

        /** @var TaskProgressEvent $progressedEvent */
        $progressedEvent = null;
        $task->when(
            TaskProgressEvent::CLASS_NAME,
            function (TaskProgressEvent $event) use (&$progressedEvent) {
                $progressedEvent = $event;
            }
        );

        // Act
        $task->reportProgress(20.24);

        // Assert
        $this->assertNotNull($progressedEvent, 'Task must fire ProgressedTaskEvent when reporting progress.');
        $this->assertEquals(2024, $progressedEvent->getProgressBasePoints());
    }

    public function testItShouldNotBePossibleToReportNegativeProgress()
    {
        $this->expectException(\InvalidArgumentException::class);

        $task = new FooTask();

        $task->reportProgress(-1);

        $this->fail('Task must refuse reporting negative progress with InvalidArgumentException.');
    }

    public function testItShouldNotBePossibleToReportMoreThan100ForProgress()
    {
        $this->expectException(\InvalidArgumentException::class);

        $task = new FooTask();

        $task->reportProgress(100.01);

        $this->fail('Task must refuse reporting greater than 100% progress values with InvalidArgumentException.');
    }

    public function testItShouldNotBePossibleToReportNonIntegerValueForProgress()
    {
        $this->expectException(\InvalidArgumentException::class);

        $task = new FooTask();

        $task->reportProgress('boo');

        $this->fail('Task must refuse reporting non float progress values with InvalidArgumentException.');
    }
}
