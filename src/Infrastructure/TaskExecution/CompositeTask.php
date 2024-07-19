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
abstract class CompositeTask extends Task
{
    /**
     * A map of progress per task. Array key is task FQN and current progress is value.
     *
     * @var array
     */
    protected $taskProgressMap = array();
    /**
     * A map of progress share per task. Array key is task FQN and value is percentage of progress share (0 - 100).
     *
     * @var array
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
     * @param array $subTasks List of all tasks for this composite task. Key is task FQN and value is percentage share.
     * @param int $initialProgress Initial progress in percents.
     */
    public function __construct(array $subTasks, $initialProgress = 0)
    {
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
     * @param array $serializedData Data that is used to instantiate serializable object.
     *
     * @return Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $serializedData)
    {
        $tasks = array();

        foreach ($serializedData['tasks'] as $index => $task) {
            $tasks[$index] = Serializer::unserialize($task);
        }

        $entity = static::createTask($tasks, $serializedData['initial_progress']);
        $entity->tasks = $tasks;
        $entity->initialProgress = $serializedData['initial_progress'];
        $entity->taskProgressMap = $serializedData['task_progress_map'];
        $entity->tasksProgressShare = $serializedData['tasks_progress_share'];

        $entity->onUnserialized();

        return $entity;
    }

    /**
     * Creates composite task instance.
     *
     * @param array $tasks
     * @param int $initialProgress
     *
     * @return static
     */
    protected static function createTask(array $tasks, $initialProgress)
    {
        return new static($tasks, $initialProgress);
    }

    /**
     * Transforms serializable object into an array.
     *
     * @return array Array representation of a serializable object.
     */
    public function toArray()
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
    public function __unserialize($data)
    {
        $this->initialProgress = $data['initial_progress'];
        $this->taskProgressMap = $data['task_progress_map'];
        $this->tasksProgressShare = $data['tasks_progress_share'];

        $tasks = array();
        foreach ($data['tasks'] as $task) {
            $tasks[] = Serializer::serialize($task);
        }

        $this->tasks = $tasks;

        $this->registerSubTasksEvents();
    }

    /**
     * @inheritdoc
     */
    public function serialize()
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
    public function onUnserialized()
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
    public function execute()
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
    public function canBeReconfigured()
    {
        $activeTask = $this->getActiveTask();

        return $activeTask !== null ? $activeTask->canBeReconfigured() : false;
    }

    /**
     * Reconfigures the task.
     */
    public function reconfigure()
    {
        $activeTask = $this->getActiveTask();

        if ($activeTask !== null) {
            $activeTask->reconfigure();
        }
    }

    /**
     * Gets progress by each task.
     *
     * @return array A map of progress per task. Array key is task FQN and current progress is value.
     */
    public function getProgressByTask()
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
    abstract protected function createSubTask($taskKey);

    /**
     * Returns active task.
     *
     * @return Task|null Active task if any; otherwise, NULL.
     */
    protected function getActiveTask()
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
    protected function getSubTask($taskKey)
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
    protected function registerSubTasksEvents()
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
    protected function registerSubTaskEvents($taskKey)
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
    protected function calculateProgress($subTaskProgress, $subTaskKey)
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
    protected function isProcessCompleted()
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
    protected function registerReportAliveEvent(Task $task)
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
    protected function registerReportProgressEvent($taskKey)
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
    protected function executeSubTask(Task $activeTask)
    {
        $activeTask->execute();
    }
}
