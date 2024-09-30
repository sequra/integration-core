<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\AliveAnnouncedTaskEvent;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\TaskProgressEvent;

/**
 * Class CompositeTask
 *
 * This type of task should be used when there is a need for synchronous execution of several tasks.
 *
 * @package SeQura\Core\Infrastructure\TaskExecution
 */
/** @phpstan-consistent-constructor */
abstract class CompositeTask extends Task
{
    /**
     * A map of progress per task. Array key is task FQN and current progress is value.
     *
     * @var mixed[]
     */
    protected $taskProgressMap = array();
    /**
     * A map of progress share per task. Array key is task FQN and value is percentage of progress share (0 - 100).
     *
     * @var mixed[]
     */
    protected $tasksProgressShare = array();
    /**
     * An array of all tasks that compose this task.
     *
     * @var Task[]
     */
    protected $tasks = array();
    /**
     * Percentage of initial progress.
     *
     * @var int
     */
    protected $initialProgress;

    /**
     * CompositeTask constructor.
     *
     * @param mixed[] $subTasks List of all tasks for this composite task. Key is task FQN and value is percentage share.
     * @param int $initialProgress Initial progress in percents.
     */
    public function __construct(array $subTasks, int $initialProgress = 0)
    {
        parent::__construct();
        $this->initialProgress = $initialProgress;

        $this->taskProgressMap = array(
            'overallTaskProgress' => 0,
        );

        $this->tasksProgressShare = array();

        foreach ($subTasks as $subTaskKey => $subTaskProgressShare) {
            $this->taskProgressMap[$subTaskKey] = 0;
            $this->tasksProgressShare[$subTaskKey] = $subTaskProgressShare;
        }
    }

    /**
     * Transforms array into an serializable object,
     *
     * @param mixed[] $array Data that is used to instantiate serializable object.
     *
     * @return Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array): Serializable
    {
        $tasks = array();

        foreach ($array['tasks'] as $index => $task) {
            $tasks[$index] = Serializer::unserialize($task);
        }

        $entity = static::createTask($tasks, $array['initial_progress']);
        $entity->tasks = $tasks;
        $entity->initialProgress = $array['initial_progress'];
        $entity->taskProgressMap = $array['task_progress_map'];
        $entity->tasksProgressShare = $array['tasks_progress_share'];

        $entity->onUnserialized();

        return $entity;
    }

    /**
     * Creates composite task instance.
     *
     * @param mixed[] $tasks
     * @param int $initialProgress
     *
     * @return static
     */
    protected static function createTask(array $tasks, int $initialProgress): CompositeTask
    {
        return new static($tasks, $initialProgress);
    }

    /**
     * Transforms serializable object into an array.
     *
     * @return mixed[] Array representation of a serializable object.
     */
    public function toArray(): array
    {
        $tasks = array();

        foreach ($this->tasks as $index => $task) {
            $tasks[$index] = Serializer::serialize($task);
        }

        return array(
            'initial_progress' => $this->initialProgress,
            'task_progress_map' => $this->taskProgressMap,
            'tasks_progress_share' => $this->tasksProgressShare,
            'tasks' => $tasks
        );
    }

    /**
     * @inheritDoc
     */
    public function __serialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function __unserialize($data): void
    {
        $this->initialProgress = $data['initial_progress'];
        $this->taskProgressMap = $data['task_progress_map'];
        $this->tasksProgressShare = $data['tasks_progress_share'];

        $tasks = array();
        foreach ($data['tasks'] as $task) {
            $tasks[] = Serializer::unserialize($task);
        }

        $this->tasks = $tasks;

        $this->registerSubTasksEvents();
    }

    /**
     * @inheritdoc
     */
    public function serialize(): ?string
    {
        return Serializer::serialize(
            array(
                'initialProgress' => $this->initialProgress,
                'taskProgress' => $this->taskProgressMap,
                'subTasksProgressShare' => $this->tasksProgressShare,
                'tasks' => $this->tasks,
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $unserializedStateData = Serializer::unserialize($serialized);

        $this->initialProgress = $unserializedStateData['initialProgress'];
        $this->taskProgressMap = $unserializedStateData['taskProgress'];
        $this->tasksProgressShare = $unserializedStateData['subTasksProgressShare'];
        $this->tasks = $unserializedStateData['tasks'];

        $this->onUnserialized();
    }

    /**
     * Called upon composite task deserialization.
     * Allows bootstrapping operations to be completed when the deserialization is complete.
     */
    public function onUnserialized(): void
    {
        $this->registerSubTasksEvents();
    }

    /**
     * Runs task logic. Executes each task sequentially.
     *
     * @final
     *
     * @throws AbortTaskExecutionException
     */
    public function execute(): void
    {
        while ($activeTask = $this->getActiveTask()) {
            $this->executeSubTask($activeTask);
        }
    }

    /**
     * Determines whether task can be reconfigured.
     *
     * @return bool TRUE if active task can be reconfigures; otherwise, FALSE.
     */
    public function canBeReconfigured(): bool
    {
        $activeTask = $this->getActiveTask();

        return $activeTask !== null ? $activeTask->canBeReconfigured() : false;
    }

    /**
     * Reconfigures the task.
     */
    public function reconfigure(): void
    {
        $activeTask = $this->getActiveTask();

        if ($activeTask !== null) {
            $activeTask->reconfigure();
        }
    }

    /**
     * Gets progress by each task.
     *
     * @return mixed[] A map of progress per task. Array key is task FQN and current progress is value.
     */
    public function getProgressByTask(): array
    {
        return $this->taskProgressMap;
    }

    /**
     * Creates a sub task for specified task FQN.
     *
     * @param string $taskKey Fully qualified name of the task.
     *
     * @return Task Created task.
     */
    abstract protected function createSubTask(string $taskKey): Task;

    /**
     * Returns active task.
     *
     * @return Task|null Active task if any; otherwise, NULL.
     */
    protected function getActiveTask(): ?Task
    {
        $task = null;
        foreach ($this->taskProgressMap as $taskKey => $taskProgress) {
            if ($taskKey === 'overallTaskProgress') {
                continue;
            }

            if ($taskProgress < 100) {
                $task = $this->getSubTask($taskKey);

                break;
            }
        }

        return $task;
    }

    /**
     * Gets sub task by the task FQN. If sub task does not exist, creates it.
     *
     * @param string $taskKey Task FQN.
     *
     * @return Task An instance of task for given FQN.
     */
    protected function getSubTask(string $taskKey): Task
    {
        if (empty($this->tasks[$taskKey])) {
            $this->tasks[$taskKey] = $this->createSubTask($taskKey);
            $this->registerSubTaskEvents($taskKey);
        }

        return $this->tasks[$taskKey];
    }

    /**
     * Registers "report progress" and "report alive" events to all sub tasks.
     */
    protected function registerSubTasksEvents(): void
    {
        foreach ($this->tasks as $key => $task) {
            $this->registerSubTaskEvents($key);
        }
    }

    /**
     * Registers "report progress" and "report alive" events to a sub task.
     *
     * @param string $taskKey KeyA Task for which to register listener.
     */
    protected function registerSubTaskEvents(string $taskKey): void
    {
        $task = $this->tasks[$taskKey];
        $task->setExecutionId($this->getExecutionId());
        $this->registerReportAliveEvent($task);
        $this->registerReportProgressEvent($taskKey);
    }

    /**
     * Calculates overall progress based on current progress for all tasks.
     *
     * @param float $subTaskProgress Progress for current sub task.
     * @param string $subTaskKey FQN of current task.
     */
    protected function calculateProgress(float $subTaskProgress, string $subTaskKey): void
    {
        // set current task progress to overall map
        $this->taskProgressMap[$subTaskKey] = $subTaskProgress;

        if (!$this->isProcessCompleted()) {
            $overallProgress = $this->initialProgress;
            foreach ($this->tasksProgressShare as $key => $share) {
                $overallProgress += $this->taskProgressMap[$key] * $share / 100;
            }

            $this->taskProgressMap['overallTaskProgress'] = $overallProgress;
        } else {
            $this->taskProgressMap['overallTaskProgress'] = 100;
        }
    }

    /**
     * Checks if all sub tasks are completed.
     *
     * @return bool TRUE if all tasks are completed; otherwise, FALSE.
     */
    protected function isProcessCompleted(): bool
    {
        foreach (array_keys($this->tasksProgressShare) as $subTaskKey) {
            if ($this->taskProgressMap[$subTaskKey] < 100) {
                return false;
            }
        }

        return true;
    }

    /**
     * Registers "report alive" event listener so that this composite task can broadcast event.
     *
     * @param Task $task A Task for which to register listener.
     */
    protected function registerReportAliveEvent(Task $task): void
    {
        $self = $this;

        $task->when(
            AliveAnnouncedTaskEvent::CLASS_NAME,
            function () use ($self) {
                $self->reportAlive();
            }
        );
    }

    /**
     * Registers "report progress" event listener so that this composite task can calculate and report overall progress.
     *
     * @param string $taskKey A Task for which to register listener.
     */
    protected function registerReportProgressEvent(string $taskKey): void
    {
        $self = $this;
        $task = $this->tasks[$taskKey];

        $task->when(
            TaskProgressEvent::CLASS_NAME,
            function (TaskProgressEvent $event) use ($self, $taskKey) {
                $self->calculateProgress($event->getProgressFormatted(), $taskKey);
                $self->reportProgress($self->taskProgressMap['overallTaskProgress']);
            }
        );
    }

    /**
     * Executes subtask.
     *
     * @param Task $activeTask
     *
     * @throws AbortTaskExecutionException
     */
    protected function executeSubTask(Task $activeTask): void
    {
        $activeTask->execute();
    }
}
