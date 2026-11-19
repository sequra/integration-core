<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Services;

use Generator;
use ReflectionClass;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions\InvalidTaskPayloadException;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskPayload;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogRecorder;
use SeQura\Core\Infrastructure\Logger\Logger;

/**
 * @package SeQura\Core\BusinessLogic\Domain\TaskExecution\Services
 */
class BusinessTaskRunner
{
    /**
     * @var TransactionLogRecorder
     */
    private $recorder;

    public function __construct(TransactionLogRecorder $recorder)
    {
        $this->recorder = $recorder;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return void
     *
     * @throws InvalidTaskPayloadException
     */
    public function run(array $payload): void
    {
        $taskPayload = TaskPayload::fromArray($payload);

        StoreContext::doWithStore($taskPayload->getContext(), function () use ($taskPayload): void {
            $task = $taskPayload->createTask();

            $this->recorder->around($task, function () use ($task): void {
                $this->execute($task);
            });
        });
    }

    /**
     * @param BusinessTaskInterface $task
     *
     * @return void
     */
    private function execute(BusinessTaskInterface $task): void
    {
        $result = $task->execute();

        if (!$result instanceof Generator) {
            return;
        }

        $taskName = (new ReflectionClass($task))->getShortName();
        foreach ($result as $signal) {
            if (\is_string($signal)) {
                Logger::logDebug(sprintf('%s: %s', $taskName, $signal));
            }
        }
    }
}
