<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Runnable;

class AsyncBatchStarter implements Runnable
{
    /**
     * Batch size.
     *
     * @var int
     */
    protected $batchSize;
    /**
     * List of sub-batches.
     *
     * @var AsyncBatchStarter[]
     */
    protected $subBatches = array();
    /**
     * List of runners.
     *
     * @var Runnable[]
     */
    protected $runners = array();
    /**
     * Current add index.
     *
     * @var int
     */
    protected $addIndex = 0;
    /**
     * Instance of async process starter.
     *
     * @var AsyncProcessService
     */
    protected $asyncProcessStarter;

    /**
     * @inheritDoc
     */
    public function serialize(): ?string
    {
        return Serializer::serialize(array($this->batchSize, $this->subBatches, $this->runners, $this->addIndex));
    }

    /**
     * @inheritDoc
     */
    public function unserialize($data)
    {
        list(
            $this->batchSize, $this->subBatches, $this->runners, $this->addIndex
            ) =
            Serializer::unserialize($data);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array): Serializable
    {
        $runners = array();
        $subBatches = array();
        foreach ($array['runners'] as $runner) {
            $runners[] = Serializer::unserialize($runner);
        }

        foreach ($array['subBatches'] as $subBatch) {
            $subBatches[] = Serializer::unserialize($subBatch);
        }

        $instance = new self($array['batchSize'], $runners);
        $instance->subBatches = $subBatches;
        $instance->addIndex = $array['addIndex'];

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $runners = array();
        $subBatches = array();
        foreach ($this->runners as $runner) {
            $runners[] = Serializer::serialize($runner);
        }

        foreach ($this->subBatches as $subBatch) {
            $subBatches[] = Serializer::serialize($subBatch);
        }

        return array(
            'batchSize' => $this->batchSize,
            'subBatches' => $subBatches,
            'runners' => $runners,
            'addIndex' => $this->addIndex,
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
        $this->batchSize = $data['batchSize'];
        $this->addIndex = $data['addIndex'];

        $runners = array();
        $subBatches = array();
        foreach ($data['runners'] as $runner) {
            $runners[] = Serializer::unserialize($runner);
        }

        foreach ($data['subBatches'] as $subBatch) {
            $subBatches[] = Serializer::unserialize($subBatch);
        }

        $this->runners = $runners;
        $this->subBatches = $subBatches;
    }


    /**
     * AsyncBatchStarter constructor.
     *
     * @param int $batchSize
     * @param Runnable[] $runners
     */
    public function __construct(int $batchSize, array $runners = array())
    {
        $this->batchSize = $batchSize;
        foreach ($runners as $runner) {
            $this->addRunner($runner);
        }
    }

    /**
     * Add runnable to the batch
     *
     * @param Runnable $runner
     */
    public function addRunner(Runnable $runner): void
    {
        if ($this->isCapacityFull()) {
            $this->subBatches[$this->addIndex]->addRunner($runner);
            $this->addIndex = ($this->addIndex + 1) % $this->batchSize;

            return;
        }

        if ($this->isRunnersCapacityFull()) {
            $this->subBatches[] = new self($this->batchSize, $this->runners);
            $this->runners = array();
        }

        $this->runners[] = $runner;
    }

    /**
     * @inheritDoc
     *
     * @throws ProcessStarterSaveException
     */
    public function run(): void
    {
        foreach ($this->subBatches as $subBatch) {
            $this->getAsyncProcessStarter()->start($subBatch);
        }

        foreach ($this->runners as $runner) {
            $this->getAsyncProcessStarter()->start($runner);
        }
    }

    /**
     * Returns max number of nested sub-batch levels. No sub-batches will return 0, one sub-batch 1, sub-batch with
     * sub-batch 2....
     *
     * @return int Max number of nested sub-batch levels
     */
    public function getMaxNestingLevels(): int
    {
        if (empty($this->subBatches)) {
            return 0;
        }

        $maxLevel = 0;
        foreach ($this->subBatches as $subBatch) {
            $subBatchMaxLevel = $subBatch->getMaxNestingLevels();
            if ($maxLevel < $subBatchMaxLevel) {
                $maxLevel = $subBatchMaxLevel;
            }
        }

        return $maxLevel + 1;
    }

    /**
     * Calculates time required for whole batch with its sub-batches to run. Wait time calculation si based on HTTP
     * request duration provided as method argument
     *
     * @param float $requestDuration Expected HTTP request duration in microseconds.
     *
     * @return float Wait period in micro seconds that is required for whole batch (with sub-batches) to run
     */
    public function getWaitTime(float $requestDuration)
    {
        // Without sub-batches all requests are started as soon as run method is done
        if (empty($this->subBatches)) {
            return 0;
        }

        $subBatchWaitTime = $this->batchSize * $this->getMaxNestingLevels() * $requestDuration;
        $runnersStartupTime = count($this->runners) * $requestDuration;

        return $subBatchWaitTime - $runnersStartupTime;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $out = implode(', ', $this->subBatches);
        $countOfRunners = count($this->runners);
        for ($i = 0; $i < $countOfRunners; $i++) {
            $out .= empty($out) ? 'R' : ', R';
        }

        return "B({$out})";
    }

    /**
     * @return bool
     *      True if current batch cant take any more runners nor create any more sub-batches itself; False otherwise
     */
    protected function isCapacityFull(): bool
    {
        return $this->isRunnersCapacityFull() && $this->isSubBatchCapacityFull();
    }

    /**
     * @return bool
     *      True if current batch cant create any more sub-batches itself; False otherwise
     */
    protected function isSubBatchCapacityFull(): bool
    {
        return count($this->subBatches) >= $this->batchSize;
    }

    /**
     * @return bool
     *      True if current batch cant take any more runners itself; False otherwise
     */
    protected function isRunnersCapacityFull(): bool
    {
        return count($this->runners) >= $this->batchSize;
    }

    /**
     * Gets instance of async process starter.
     *
     * @return AsyncProcessService
     *   Instance of async process starter.
     */
    protected function getAsyncProcessStarter(): AsyncProcessService
    {
        if ($this->asyncProcessStarter === null) {
            $this->asyncProcessStarter = ServiceRegister::getService(AsyncProcessService::CLASS_NAME);
        }

        return $this->asyncProcessStarter;
    }
}
