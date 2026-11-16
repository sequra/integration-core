<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;

class SampleBusinessTask implements BusinessTaskInterface
{
    /**
     * @var string[]
     */
    public static $observedStoreIds = [];

    /**
     * @var string
     */
    private $orderRef;

    public function __construct(string $orderRef = 'ORD-1')
    {
        $this->orderRef = $orderRef;
    }

    public function execute()
    {
        self::$observedStoreIds[] = StoreContext::getInstance()->getStoreId();
    }

    public function toArray(): array
    {
        return ['orderRef' => $this->orderRef];
    }

    public static function fromArray(array $data): BusinessTaskInterface
    {
        return new self($data['orderRef']);
    }
}
