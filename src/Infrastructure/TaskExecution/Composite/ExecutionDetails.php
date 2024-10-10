<?php

namespace SeQura\Core\Infrastructure\TaskExecution\Composite;

use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;
use SeQura\Core\Infrastructure\Serializer\Serializer;

/**
 * Class ExecutionDetails
 *
 * @package SeQura\Core\Infrastructure\TaskExecution\Composite
 *
 * @access protected
 */
/**
 * @phpstan-consistent-constructor
 */
class ExecutionDetails implements Serializable
{
    /**
     * Execution id.
     *
     * @var int
     */
    protected $executionId;
    /**
     * Positive (grater than zero) integer. Higher number implies higher impact of subtask's progress on total progress.
     *
     * @var int
     */
    protected $weight;
    /**
     * Task progress.
     *
     * @var float
     */
    protected $progress;

    /**
     * ExecutionDetails constructor.
     *
     * @param int $executionId
     * @param int $weight
     */
    public function __construct($executionId, $weight = 1)
    {
        $this->executionId = $executionId;
        $this->weight = $weight;
        $this->progress = 0.0;
    }

    /**
     * @return int
     */
    public function getExecutionId()
    {
        return $this->executionId;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return float
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param float $progress
     */
    public function setProgress(float $progress): void
    {
        $this->progress = $progress;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize([$this->executionId, $this->weight, $this->progress]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($data)
    {
        list($this->executionId, $this->weight, $this->progress) = Serializer::unserialize($data);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'progress' => $this->getProgress(),
            'executionId' => $this->getExecutionId(),
            'weight' => $this->getWeight(),
        ];
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
        $this->progress = $data['progress'];
        $this->executionId = $data['executionId'];
        $this->weight = $data['weight'];
    }

    /**
     * @return ExecutionDetails
     */
    public static function fromArray(array $array): Serializable
    {
        $entity = new static($array['executionId'], $array['weight']);
        $entity->setProgress($array['progress']);

        return $entity;
    }
}
