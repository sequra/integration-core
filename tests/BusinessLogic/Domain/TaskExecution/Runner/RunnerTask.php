<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Runner;

use Generator;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Throwable;

class RunnerTask implements BusinessTaskInterface
{
    public const MODE_PLAIN = 'plain';
    public const MODE_NULL = 'null';
    public const MODE_SIGNALS = 'signals';
    public const MODE_THROW = 'throw';
    public const MODE_YIELD_THEN_READ_STORE = 'yield-then-read-store';
    public const MODE_YIELD_THEN_THROW = 'yield-then-throw';

    /**
     * @var int
     */
    public static $executions = 0;

    /**
     * @var string[]
     */
    public static $trace = [];

    /**
     * @var ?string
     */
    public static $seenStore;

    /**
     * @var ?string
     */
    public static $seenOrderRef;

    /**
     * @var ?Throwable
     */
    public static $thrown;

    /**
     * @var ?string
     */
    public static $storeWhenRebuilt;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $orderRef;

    public function __construct(string $mode = self::MODE_PLAIN, string $orderRef = 'ORD-1')
    {
        $this->mode = $mode;
        $this->orderRef = $orderRef;
    }

    public static function reset(): void
    {
        self::$executions = 0;
        self::$trace = [];
        self::$seenStore = null;
        self::$seenOrderRef = null;
        self::$thrown = null;
        self::$storeWhenRebuilt = null;
    }

    public function execute()
    {
        self::$executions++;
        self::$seenOrderRef = $this->orderRef;
        self::$seenStore = StoreContext::getInstance()->getStoreId();

        switch ($this->mode) {
            case self::MODE_THROW:
                self::$thrown = new RuntimeException('boom');
                throw self::$thrown;
            case self::MODE_SIGNALS:
                return $this->signals();
            case self::MODE_YIELD_THEN_READ_STORE:
                return $this->yieldThenReadStore();
            case self::MODE_YIELD_THEN_THROW:
                return $this->yieldThenThrow();
            default:
                return null;
        }
    }

    private function signals(): Generator
    {
        yield 10;
        self::$trace[] = 'after-progress';
        yield null;
        self::$trace[] = 'after-keep-alive';
        yield 'syncing';
        self::$trace[] = 'end';
    }

    private function yieldThenReadStore(): Generator
    {
        yield null;
        self::$seenStore = StoreContext::getInstance()->getStoreId();
    }

    private function yieldThenThrow(): Generator
    {
        yield null;
        self::$thrown = new RuntimeException('late');
        throw self::$thrown;
    }

    public function toArray(): array
    {
        $data = ['orderRef' => $this->orderRef];
        if ($this->mode !== self::MODE_PLAIN) {
            $data['mode'] = $this->mode;
        }

        return $data;
    }

    public static function fromArray(array $data): BusinessTaskInterface
    {
        self::$storeWhenRebuilt = StoreContext::getInstance()->getStoreId();

        return new self($data['mode'] ?? self::MODE_PLAIN, $data['orderRef']);
    }
}
