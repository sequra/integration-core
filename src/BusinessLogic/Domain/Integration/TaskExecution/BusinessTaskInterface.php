<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution
 */
interface BusinessTaskInterface
{
    /**
     * @return \Generator<int, int|float|string|null, mixed, void>|null
     */
    public function execute();

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * @param array<string, mixed> $data
     *
     * @return BusinessTaskInterface
     */
    public static function fromArray(array $data): BusinessTaskInterface;
}
