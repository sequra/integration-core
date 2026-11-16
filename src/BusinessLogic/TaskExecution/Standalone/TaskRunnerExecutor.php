<?php

namespace SeQura\Core\BusinessLogic\TaskExecution\Standalone;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskMetadataProviderInterface;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

class TaskRunnerExecutor implements TaskExecutorInterface
{
    private const MAX_LOW_PRIORITY = 33;
    private const MAX_NORMAL_PRIORITY = 66;

    /**
     * @var QueueService
     */
    private $queueService;

    /**
     * @var TaskMetadataProviderInterface
     */
    private $metadataProvider;

    public function __construct(QueueService $queueService, TaskMetadataProviderInterface $metadataProvider)
    {
        $this->queueService = $queueService;
        $this->metadataProvider = $metadataProvider;
    }

    public function enqueue(BusinessTaskInterface $task): void
    {
        $this->queue($task, null);
    }

    public function scheduleDelayed(BusinessTaskInterface $task, int $delaySeconds): void
    {
        $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);

        $this->queue($task, $timeProvider->getCurrentLocalTime()->getTimestamp() + $delaySeconds);
    }

    private function queue(BusinessTaskInterface $task, ?int $earliestStartTimestamp): void
    {
        $config = $this->metadataProvider->getExecutionConfig($task);

        $this->queueService->enqueue(
            $config->getQueueName(),
            new TaskAdapter($task, $config->getContext()),
            $config->getContext(),
            $this->mapPriority($config->getPriority()),
            $earliestStartTimestamp
        );
    }

    private function mapPriority(int $priority): int
    {
        if ($priority <= self::MAX_LOW_PRIORITY) {
            return Priority::LOW;
        }

        return $priority <= self::MAX_NORMAL_PRIORITY ? Priority::NORMAL : Priority::HIGH;
    }
}
