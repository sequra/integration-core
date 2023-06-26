<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderStatus\Models;

/**
 * Class OrderStatus
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderStatus\Models
 */
class OrderStatus
{
    /**
     * @var string
     */
    private $statusId;

    /**
     * @var string
     */
    private $statusName;

    /**
     * @param string $statusId
     * @param string $statusName
     */
    public function __construct(string $statusId, string $statusName)
    {
        $this->statusId = $statusId;
        $this->statusName = $statusName;
    }

    /**
     * @return string
     */
    public function getStatusId(): string
    {
        return $this->statusId;
    }

    /**
     * @param string $statusId
     */
    public function setStatusId(string $statusId): void
    {
        $this->statusId = $statusId;
    }

    /**
     * @return string
     */
    public function getStatusName(): string
    {
        return $this->statusName;
    }

    /**
     * @param string $statusName
     */
    public function setStatusName(string $statusName): void
    {
        $this->statusName = $statusName;
    }
}
