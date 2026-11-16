<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Services;

use Generator;
use ReflectionClass;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions\InvalidTaskPayloadException;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskPayload;
use SeQura\Core\Infrastructure\Logger\Logger;

/**
 * @package SeQura\Core\BusinessLogic\Domain\TaskExecution\Services
 */
class BusinessTaskRunner
{
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
            $this->execute($taskPayload->createTask());
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
